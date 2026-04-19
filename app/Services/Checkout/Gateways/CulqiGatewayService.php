<?php

namespace App\Services\Checkout\Gateways;

use App\Models\Cart;
use App\Services\Checkout\Gateways\Contracts\CheckoutPaymentGatewayInterface;
use App\Services\Checkout\Gateways\DTO\GatewayAuthorizationResult;
use App\Services\Checkout\Gateways\DTO\GatewaySessionResult;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CulqiGatewayService implements CheckoutPaymentGatewayInterface
{
    public function code(): string
    {
        return 'culqi';
    }

    public function createSessionToken(float $amount, ?Cart $cart = null): GatewaySessionResult
    {
        if ($this->shouldSimulate()) {
            return new GatewaySessionResult(
                token: 'culqi-dev-source-' . uniqid(),
                status: 200,
                message: null,
            );
        }

        // Culqi no usa session token servidor como Niubiz. El token/source_id
        // se genera en frontend con la public key y luego se autoriza en backend.
        return new GatewaySessionResult(
            token: '__CULQI_FRONTEND_TOKEN_REQUIRED__',
            status: 200,
            message: null,
        );
    }

    public function authorize(array $payload): GatewayAuthorizationResult
    {
        if ($this->shouldSimulate()) {
            $purchaseNumber = (string) ($payload['purchase_number'] ?? now()->timestamp);
            $chargeId = 'ch_dev_' . substr(md5((string) microtime(true)), 0, 14);

            return new GatewayAuthorizationResult(
                ok: true,
                response: [
                    'id' => $chargeId,
                    'object' => 'charge',
                    'outcome' => [
                        'type' => 'venta_exitosa',
                        'merchant_message' => 'APROBADO (SIMULACION CULQI)',
                        'user_message' => 'Pago aprobado',
                    ],
                    'metadata' => [
                        'purchase_number' => $purchaseNumber,
                    ],
                    'source' => [
                        'id' => (string) ($payload['transaction_token'] ?? 'tok_test'),
                        'brand' => 'VISA',
                    ],
                    'status' => 'captured',
                ],
                status: 200,
                message: null,
            );
        }

        $secretKey = (string) config('services.culqi.secret_key', '');
        $baseUrl = rtrim((string) config('services.culqi.base_url', 'https://api.culqi.com'), '/');
        $sourceId = (string) ($payload['transaction_token'] ?? '');

        if ($secretKey === '' || $sourceId === '') {
            return new GatewayAuthorizationResult(
                ok: false,
                response: [],
                status: 422,
                message: 'Faltan credenciales de Culqi o source_id para autorizar el pago.',
            );
        }

        $amount = (float) ($payload['amount'] ?? 0);
        $amountInCents = (int) round($amount * 100);

        if ($amountInCents <= 0) {
            return new GatewayAuthorizationResult(
                ok: false,
                response: [],
                status: 422,
                message: 'El monto para autorizar en Culqi no es válido.',
            );
        }

        $purchaseNumber = (string) ($payload['purchase_number'] ?? now()->timestamp);
        $email = (string) ($payload['customer_email'] ?? 'comprador@example.com');

        try {
            $response = Http::withToken($secretKey)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->connectTimeout(10)
                ->timeout(20)
                ->retry(1, 300)
                ->post($baseUrl . '/v2/charges', [
                    'amount' => $amountInCents,
                    'currency_code' => 'PEN',
                    'email' => $email,
                    'source_id' => $sourceId,
                    'capture' => true,
                    'description' => 'Pedido #' . $purchaseNumber,
                    'metadata' => [
                        'purchase_number' => $purchaseNumber,
                    ],
                ]);
        } catch (ConnectionException $e) {
            Log::warning('Culqi authorization connection failed', [
                'error' => $e->getMessage(),
                'base_url' => $baseUrl,
            ]);

            return new GatewayAuthorizationResult(
                ok: false,
                response: [],
                status: 503,
                message: 'No se pudo conectar con Culqi para autorizar el pago.',
            );
        }

        $body = $response->json() ?? [];
        $outcomeType = mb_strtolower((string) ($body['outcome']['type'] ?? ''));
        $isApproved = $response->successful() && in_array($outcomeType, ['venta_exitosa', 'authorized'], true);

        if (! $isApproved) {
            Log::warning('Culqi authorization failed', [
                'status' => $response->status(),
                'response' => $body,
            ]);

            $message = (string) ($body['user_message']
                ?? $body['merchant_message']
                ?? $body['outcome']['user_message']
                ?? 'Culqi no autorizó la operación.');

            return new GatewayAuthorizationResult(
                ok: false,
                response: $body,
                status: $response->status(),
                message: $message,
            );
        }

        return new GatewayAuthorizationResult(
            ok: true,
            response: $body,
            status: $response->status(),
            message: null,
        );
    }

    private function shouldSimulate(): bool
    {
        return app()->environment('local')
            && filter_var((string) config('services.culqi.dev_simulation', false), FILTER_VALIDATE_BOOLEAN);
    }
}
