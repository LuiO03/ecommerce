<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\ClaimMessage;
use App\Models\User;
use App\Notifications\AdminDatabaseNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ClaimsController extends Controller
{
    private const MAX_ATTEMPTS_PER_MINUTE = 3;

    public function store(Request $request): JsonResponse
    {
        $ip = (string) $request->ip();
        $emailForRateLimit = Str::lower((string) $request->input('email', ''));

        if ($emailForRateLimit !== '' && $this->isRateLimited($ip, $emailForRateLimit)) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'form' => 'Has alcanzado el límite de envíos por minuto. Intenta nuevamente en unos segundos.',
                ],
            ], 429);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'email' => ['required', 'email:rfc,dns', 'max:160'],
            'phone' => ['nullable', 'string', 'max:30'],
            'claim_type' => ['required', Rule::in(['reclamo', 'queja'])],
            'claim_detail' => ['required', 'string', 'min:10', 'max:3000'],
            'idempotency_key' => ['required', 'uuid', 'max:64'],
            'website' => ['nullable', 'max:0'],
            'recaptcha_token' => ['nullable', 'string', 'max:2048'],
        ], [
            'website.max' => 'No se pudo validar el envío.',
        ]);

        $validator->after(function ($validator) use ($request) {
            $detail = (string) $request->input('claim_detail', '');

            if ($this->containsSuspiciousContent($detail)) {
                $validator->errors()->add('claim_detail', 'El contenido del mensaje parece sospechoso. Ajusta tu texto e intenta de nuevo.');
            }

            if (! $request->user() && ! $this->verifyRecaptcha($request)) {
                $validator->errors()->add('recaptcha', 'No se pudo validar reCAPTCHA. Intenta nuevamente.');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $this->flattenErrors($validator->errors()->toArray()),
            ], 422);
        }

        $idempotencyKey = (string) $request->string('idempotency_key');
        $existingMessage = ClaimMessage::query()
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if ($existingMessage) {
            return response()->json([
                'success' => true,
                'message' => 'Tu registro ya ha sido recibido. Nos pondremos en contacto contigo pronto.',
            ]);
        }

        $message = ClaimMessage::create([
            'name' => (string) $request->string('name'),
            'email' => Str::lower((string) $request->string('email')),
            'phone' => $request->filled('phone') ? (string) $request->string('phone') : null,
            'claim_type' => (string) $request->string('claim_type'),
            'claim_detail' => (string) $request->string('claim_detail'),
            'status' => 'new',
            'idempotency_key' => $idempotencyKey,
            'ip_address' => $ip,
            'user_agent' => Str::limit((string) $request->userAgent(), 255),
            'submitted_at' => now(),
        ]);

        $this->notifyAdminsAboutNewClaimMessage($message);

        $this->hitRateLimit($ip, Str::lower((string) $request->string('email')));
        $this->registerLastSubmission($ip, Str::lower((string) $request->string('email')));

        return response()->json([
            'success' => true,
            'message' => 'Tu reclamo o queja ha sido registrado correctamente.',
        ]);
    }

    private function notifyAdminsAboutNewClaimMessage(ClaimMessage $message): void
    {
        $admins = User::query()
            ->role(['Administrador', 'Superadministrador'])
            ->where('status', true)
            ->get();

        $claimTypeLabel = $message->claim_type === 'queja' ? 'Queja' : 'Reclamo';

        $bodyParts = [
            "Remitente: {$message->name}",
            "Correo: {$message->email}",
            "Tipo: {$claimTypeLabel}",
        ];

        if (!empty($message->phone)) {
            $bodyParts[] = "Teléfono: {$message->phone}";
        }

        foreach ($admins as $admin) {
            $admin->notify(
                new AdminDatabaseNotification(
                    title: 'Nuevo registro en Libro de Reclamaciones',
                    body: implode(' | ', $bodyParts),
                    url: route('admin.claim-messages.index', $message->id),
                    icon: 'ri-file-damage-line',
                    level: 'info',
                )
            );
        }
    }

    private function flattenErrors(array $errors): array
    {
        $flattened = [];

        foreach ($errors as $field => $messages) {
            $flattened[$field] = is_array($messages) ? (string) ($messages[0] ?? 'Dato inválido.') : (string) $messages;
        }

        return $flattened;
    }

    private function isRateLimited(string $ip, string $email): bool
    {
        return RateLimiter::tooManyAttempts($this->rateLimitIpKey($ip), self::MAX_ATTEMPTS_PER_MINUTE)
            || RateLimiter::tooManyAttempts($this->rateLimitEmailKey($email), self::MAX_ATTEMPTS_PER_MINUTE);
    }

    private function hitRateLimit(string $ip, string $email): void
    {
        RateLimiter::hit($this->rateLimitIpKey($ip), 60);
        RateLimiter::hit($this->rateLimitEmailKey($email), 60);
    }

    private function rateLimitIpKey(string $ip): string
    {
        return 'claims:submit:ip:' . $ip;
    }

    private function rateLimitEmailKey(string $email): string
    {
        return 'claims:submit:email:' . $email;
    }

    private function registerLastSubmission(string $ip, string $email): void
    {
        $timestamp = now()->toIso8601String();

        Cache::put('claims:last_submission:ip:' . $ip, $timestamp, now()->addDay());
        Cache::put('claims:last_submission:email:' . $email, $timestamp, now()->addDay());
    }

    private function verifyRecaptcha(Request $request): bool
    {
        $secretKey = (string) config('services.recaptcha.secret_key', '');

        if ($secretKey === '') {
            return true;
        }

        $token = (string) $request->input('recaptcha_token', '');

        if ($token === '') {
            return false;
        }

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secretKey,
            'response' => $token,
            'remoteip' => $request->ip(),
        ]);

        if (! $response->ok()) {
            return false;
        }

        $payload = $response->json();

        if (! is_array($payload) || ! ($payload['success'] ?? false)) {
            return false;
        }

        $minimumScore = (float) config('services.recaptcha.minimum_score', 0.5);

        if (isset($payload['score']) && (float) $payload['score'] < $minimumScore) {
            return false;
        }

        return true;
    }

    private function containsSuspiciousContent(string $message): bool
    {
        $lowerMessage = Str::lower($message);
        $linkCount = preg_match_all('/https?:\/\//i', $message);

        if ($linkCount !== false && $linkCount > 3) {
            return true;
        }

        $spamTerms = [
            'viagra',
            'casino',
            'crypto investment',
            'loan approved',
            'work from home',
            'click here',
            'free money',
        ];

        foreach ($spamTerms as $term) {
            if (Str::contains($lowerMessage, $term)) {
                return true;
            }
        }

        return false;
    }
}
