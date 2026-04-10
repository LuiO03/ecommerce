<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        // Obtener datos del usuario de Google (en modo stateless para evitar problemas de estado)
        $googleUser = Socialite::driver('google')->stateless()->user();

        // Extraer nombre y apellido desde la respuesta de Google
        $raw      = $googleUser->user ?? [];
        $firstName = $raw['given_name'] ?? $googleUser->name;
        $lastName  = $raw['family_name'] ?? null;
        $avatarUrl = $googleUser->avatar ?? null;

        // Buscar usuario por email
        $user = User::where('email', $googleUser->email)->first();

        if (!$user) {

            // Generar slug y guardar avatar de Google como imagen local
            $slug = User::generateUniqueSlug($firstName);
            $imagePath = $this->storeGoogleAvatar($avatarUrl, $slug);

            // Crear usuario nuevo
            $user = User::create([
                'name' => $firstName,
                'last_name' => $lastName,
                'email' => $googleUser->email,
                'slug' => $slug,
                'provider' => 'google',
                'provider_id' => $googleUser->id,
                'email_verified_at' => now(),
                'password' => null,
                'status' => true,
                'image' => $imagePath,
            ]);

            $user->assignRole('Cliente');

        } else {

            // Si el usuario existe pero no tiene Google vinculado
            if (!$user->provider_id) {

                $user->update([
                    'provider' => 'google',
                    'provider_id' => $googleUser->id,
                ]);
            }

            // Completar avatar local si aún no tuviera imagen y Google lo envía
            if (!$user->image && $avatarUrl) {
                $slug = $user->slug ?: User::generateUniqueSlug($user->name, $user->id);
                $imagePath = $this->storeGoogleAvatar($avatarUrl, $slug);
                if ($imagePath) {
                    $user->forceFill(['image' => $imagePath])->save();
                }
            }

            // Completar apellido si aún no tuviera y Google lo envía
            if (!$user->last_name && $lastName) {
                $user->forceFill(['last_name' => $lastName])->save();
            }

            if (!$user->email_verified_at) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }
        }

        Auth::login($user);

        return redirect()->route('site.home');
    }

    /**
     * Manejar el login de Google One Tap recibiendo el ID token desde el frontend.
     */
    public function handleOneTap(Request $request)
    {
        $credential = $request->input('credential');

        if (!$credential) {
            return response()->json([
                'success' => false,
                'message' => 'Falta el token de Google (credential).',
            ], 400);
        }

        try {
            $clientId = config('services.google.client_id');

            // Verificar el ID token contra Google
            $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $credential,
            ]);

            if (!$response->ok()) {
                Log::warning('Google One Tap: respuesta no OK de tokeninfo', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo validar el token de Google.',
                ], 401);
            }

            $payload = $response->json();

            // Validar audiencia (client_id) para asegurarnos que el token es para esta app
            if (!isset($payload['aud']) || $payload['aud'] !== $clientId) {
                Log::warning('Google One Tap: aud inválido en token', [
                    'aud' => $payload['aud'] ?? null,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Token de Google inválido para esta aplicación.',
                ], 401);
            }

            $email = $payload['email'] ?? null;

            if (!$email) {
                return response()->json([
                    'success' => false,
                    'message' => 'El token de Google no contiene un email válido.',
                ], 422);
            }

            $firstName = $payload['given_name'] ?? ($payload['name'] ?? 'Usuario');
            $lastName  = $payload['family_name'] ?? null;
            $avatarUrl = $payload['picture'] ?? null;
            $googleId  = $payload['sub'] ?? null;

            $user = User::where('email', $email)->first();

            if (!$user) {
                $slug      = User::generateUniqueSlug($firstName);
                $imagePath = $this->storeGoogleAvatar($avatarUrl, $slug);

                $user = User::create([
                    'name'              => $firstName,
                    'last_name'         => $lastName,
                    'email'             => $email,
                    'slug'              => $slug,
                    'provider'          => 'google',
                    'provider_id'       => $googleId,
                    'email_verified_at' => now(),
                    'password'          => null,
                    'status'            => true,
                    'image'             => $imagePath,
                ]);

                $user->assignRole('Cliente');
            } else {
                if (!$user->provider_id && $googleId) {
                    $user->update([
                        'provider'    => 'google',
                        'provider_id' => $googleId,
                    ]);
                }

                if (!$user->image && $avatarUrl) {
                    $slug      = $user->slug ?: User::generateUniqueSlug($user->name, $user->id);
                    $imagePath = $this->storeGoogleAvatar($avatarUrl, $slug);
                    if ($imagePath) {
                        $user->forceFill(['image' => $imagePath])->save();
                    }
                }

                if (!$user->last_name && $lastName) {
                    $user->forceFill(['last_name' => $lastName])->save();
                }

                if (!$user->email_verified_at && ($payload['email_verified'] ?? 'false') === 'true') {
                    $user->forceFill(['email_verified_at' => now()])->save();
                }
            }

            Auth::login($user);

            return response()->json([
                'success' => true,
                'redirect_url' => route('site.home'),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en Google One Tap', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al procesar el login con Google.',
            ], 500);
        }
    }

    /**
     * Descargar el avatar de Google y almacenarlo en storage/app/public/users
     * devolviendo la ruta relativa para guardar en users.image.
     */
    protected function storeGoogleAvatar(?string $avatarUrl, string $slug): ?string
    {
        if (!$avatarUrl) {
            return null;
        }

        try {
            $contents = @file_get_contents($avatarUrl);
            if ($contents === false) {
                return null;
            }

            $path = parse_url($avatarUrl, PHP_URL_PATH) ?: '';
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION) ?: 'jpg');

            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $ext = 'jpg';
            }

            $filename = $slug . '-google-' . time() . '.' . $ext;
            $relativePath = 'users/' . $filename;

            Storage::disk('public')->put($relativePath, $contents);

            return $relativePath;
        } catch (\Throwable $e) {
            Log::warning('No se pudo descargar el avatar de Google', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
