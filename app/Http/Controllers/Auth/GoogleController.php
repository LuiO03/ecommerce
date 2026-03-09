<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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

        return redirect()->route('welcome.index');
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

    public function handleOneTap()
    {
        $token = request('credential');

        if (!$token) {
            return response()->json(['error' => 'Token de Google no proporcionado'], 400);
        }

        try {
            $googleUser = Socialite::driver('google')->stateless()->userFromToken($token);

            // El resto del proceso es similar a handleGoogleCallback
            // (buscar o crear usuario, asignar rol, etc.)

            // ... (puedes reutilizar la lógica de handleGoogleCallback aquí)

            return response()->json(['message' => 'Login exitoso']);
        } catch (\Exception $e) {
            Log::error('Error en Google One Tap', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error al autenticar con Google'], 500);
        }
    }
}
