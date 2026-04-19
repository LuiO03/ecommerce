<?php

namespace App\Services\Checkout\Gateways;

use App\Models\Cart;
use App\Services\Checkout\Gateways\Contracts\CheckoutPaymentGatewayInterface;
use App\Services\Checkout\Gateways\DTO\GatewayAuthorizationResult;
use App\Services\Checkout\Gateways\DTO\GatewaySessionResult;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MercadoPagoGatewayService implements CheckoutPaymentGatewayInterface
{
    public function code(): string
    {
        return 'mercadopago';
    }

    public function createSessionToken(float $amount, ?Cart $cart = null): GatewaySessionResult
    {
        if ($this->shouldSimulate()) {
            return new GatewaySessionResult(
                token: 'mp_dev_source_' . uniqid(),
                status: 200,
                message: null,
            );
        }

        // Mercado Pago no usa session token servidor como Niubiz.
        // El token/source_id se genera en frontend con la public key
        // y luego se autoriza en backend.
        return new GatewaySessionResult(
            token: '__MERCADOPAGO_FRONTEND_TOKEN_REQUIRED__',
            status: 200,
            message: null,
        );
    }

    public function authorize(array $payload): GatewayAuthorizationResult
    {
        if ($this->shouldSimulate()) {
            $purchaseNumber = (string) ($payload['purchase_number'] ?? now()->timestamp);
            $paymentId = 'dev_' . substr(md5((string) microtime(true)), 0, 18);

            return new GatewayAuthorizationResult(
                ok: true,
                response: [
                    'id' => $paymentId,
                    'status' => 'approved',
                    'status_detail' => 'accredited',
                    'purchase_order' => $purchaseNumber,
                    'payer' => [
                        'email' => $payload['customer_email'] ?? 'test@mercadopago.com',
                    ],
                    'payment_method_id' => 'visa',
                    'card' => [
                        'last_four_digits' => '1111',
                    ],
                ],
            );
        }

        $amount = (float) ($payload['amount'] ?? 0);
        $purchaseNumber = (string) ($payload['purchase_number'] ?? now()->timestamp);
        $transactionToken = $payload['transaction_token'] ?? null;
        $customerEmail = $payload['customer_email'] ?? 'noemail@example.com';

        if (!$transactionToken) {
            return new GatewayAuthorizationResult(
                ok: false,
                response: ['error' => 'Missing transaction token'],
            );
        }

        try {
            $baseUrl = config('services.mercadopago.base_url', 'https://api.mercadopago.com');
            $secretKey = config('services.mercadopago.secret_key');

            if (!$secretKey) {
                Log::warning('Mercado Pago secret key not configured');
                return new GatewayAuthorizationResult(
                    ok: false,
                    response: ['error' => 'Gateway not configured'],
                );
            }

            // Crear pago en Mercado Pago API
            $response = Http::timeout(20)
                ->retry(1, 300)
                ->withHeaders([
                    'Authorization' => "Bearer {$secretKey}",
                    'Content-Type' => 'application/json',
                    'X-Idempotency-Key' => md5($purchaseNumber . $customerEmail . (string) microtime(true)),
                ])
                ->post("{$baseUrl}/v1/payments", [
                    'transaction_amount' => $amount,
                    'token' => $transactionToken,
                    'description' => "Order #{$purchaseNumber}",
                    'installments' => 1,
                    'payment_method_id' => 'visa',
                    'payer' => [
                        'email' => $customerEmail,
                    ],
                    'external_reference' => $purchaseNumber,
                    'metadata' => [
                        'purchase_number' => $purchaseNumber,
                    ],
                ])
                ->json();

            Log::info('Mercado Pago authorization response', [
                'purchase_number' => $purchaseNumber,
                'status' => $response['status'] ?? null,
                'payment_id' => $response['id'] ?? null,
            ]);

            // Mercado Pago aprueba con status: 'approved' o 'pending_client_review'
            $isApproved = in_array($response['status'] ?? '', ['approved', 'authorized']);

            return new GatewayAuthorizationResult(
                ok: $isApproved,
                response: $response ?? [],
            );
        } catch (ConnectionException $e) {
            Log::error('Mercado Pago connection error', [
                'message' => $e->getMessage(),
                'purchase_number' => $purchaseNumber,
            ]);

            return new GatewayAuthorizationResult(
                ok: false,
                response: ['error' => 'Payment gateway connection failed'],
            );
        } catch (\Exception $e) {
            Log::error('Mercado Pago authorization error', [
                'message' => $e->getMessage(),
                'purchase_number' => $purchaseNumber,
            ]);

            return new GatewayAuthorizationResult(
                ok: false,
                response: ['error' => 'Payment processing failed'],
            );
        }
    }

    private function shouldSimulate(): bool
    {
        return !app()->isProduction() &&
               config('services.mercadopago.dev_simulation', false);
    }
}
