<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    private const MAX_ATTEMPTS_PER_MINUTE = 3;

    public function show()
    {
        return view('site.contact.index', [
            'contactIdempotencyKey' => (string) Str::uuid(),
            'recaptchaSiteKey' => (string) config('services.recaptcha.site_key', ''),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $ip = (string) $request->ip();
        $emailForRateLimit = Str::lower((string) $request->input('email', ''));

        if ($emailForRateLimit !== '' && $this->isRateLimited($ip, $emailForRateLimit)) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'form' => 'Has alcanzado el limite de envios por minuto. Intenta nuevamente en unos segundos.',
                ],
            ], 429);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'email' => ['required', 'email:rfc,dns', 'max:160'],
            'topic' => ['required', Rule::in(['order', 'product', 'account', 'billing', 'other'])],
            'order_number' => ['nullable', 'string', 'max:120'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
            'idempotency_key' => ['required', 'uuid', 'max:64'],
            'website' => ['nullable', 'max:0'],
            'recaptcha_token' => ['nullable', 'string', 'max:2048'],
        ], [
            'website.max' => 'No se pudo validar el envio.',
        ]);

        $validator->after(function ($validator) use ($request) {
            $message = (string) $request->input('message', '');

            if ($this->containsSuspiciousContent($message)) {
                $validator->errors()->add('message', 'El contenido del mensaje parece sospechoso. Ajusta tu texto e intenta de nuevo.');
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
        $existingMessage = ContactMessage::query()
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if ($existingMessage) {
            return response()->json([
                'success' => true,
                'message' => 'Tu mensaje ya ha sido recibido. Nos pondremos en contacto contigo pronto.',
            ]);
        }

        ContactMessage::create([
            'name' => (string) $request->string('name'),
            'email' => Str::lower((string) $request->string('email')),
            'topic' => (string) $request->string('topic'),
            'order_number' => $request->filled('order_number') ? (string) $request->string('order_number') : null,
            'message' => (string) $request->string('message'),
            'status' => 'new',
            'idempotency_key' => $idempotencyKey,
            'ip_address' => $ip,
            'user_agent' => Str::limit((string) $request->userAgent(), 255),
            'submitted_at' => now(),
        ]);

        $this->hitRateLimit($ip, Str::lower((string) $request->string('email')));
        $this->registerLastSubmission($ip, Str::lower((string) $request->string('email')));

        return response()->json([
            'success' => true,
        ]);
    }

    private function flattenErrors(array $errors): array
    {
        $flattened = [];

        foreach ($errors as $field => $messages) {
            $flattened[$field] = is_array($messages) ? (string) ($messages[0] ?? 'Dato invalido.') : (string) $messages;
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
        return 'contact:submit:ip:' . $ip;
    }

    private function rateLimitEmailKey(string $email): string
    {
        return 'contact:submit:email:' . $email;
    }

    private function registerLastSubmission(string $ip, string $email): void
    {
        $timestamp = now()->toIso8601String();

        Cache::put('contact:last_submission:ip:' . $ip, $timestamp, now()->addDay());
        Cache::put('contact:last_submission:email:' . $email, $timestamp, now()->addDay());
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
