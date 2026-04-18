<?php

namespace App\Services\Checkout\Gateways;

use App\Models\Addresses;
use App\Models\Cart;
use App\Services\Checkout\Gateways\Contracts\CheckoutPaymentGatewayInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NiubizGatewayService implements CheckoutPaymentGatewayInterface
{
    public function code(): string
    {
        return 'niubiz';
    }

    public function createSessionToken(float $amount, ?Cart $cart = null): array
    {
        $accessTokenResult = $this->requestAccessToken();

        if (! $accessTokenResult['token']) {
            $accessStatus = (int) ($accessTokenResult['status'] ?? 0);
            $httpStatus = $accessStatus >= 400 && $accessStatus < 600 ? $accessStatus : 503;

            return [
                'token' => null,
                'status' => $httpStatus,
                'message' => $accessTokenResult['message'] ?? 'No se pudo obtener credenciales de Niubiz.',
            ];
        }

        $cart = $cart ?: Cart::with(['items.product', 'items.variant'])
            ->where('user_id', Auth::id())
            ->where('is_active', true)
            ->first();

        if (! $cart) {
            return [
                'token' => null,
                'status' => 422,
                'message' => 'No encontramos un carrito activo para generar la sesión de pago.',
            ];
        }

        $sessionToken = $this->generateSessionToken($accessTokenResult['token'], $amount, $cart);

        if (! $sessionToken) {
            return [
                'token' => null,
                'status' => 502,
                'message' => 'Niubiz no devolvió session token. Revisa credenciales, merchant id y conectividad.',
            ];
        }

        return [
            'token' => $sessionToken,
            'status' => 200,
            'message' => null,
        ];
    }

    public function authorize(array $payload): array
    {
        $accessTokenResult = $this->requestAccessToken();

        if (! $accessTokenResult['token']) {
            return [
                'ok' => false,
                'response' => [],
                'status' => (int) ($accessTokenResult['status'] ?? 503),
                'message' => $accessTokenResult['message'] ?? 'No se pudo autenticar con Niubiz.',
            ];
        }

        $merchantId = config('services.niubiz.merchant_id');
        $urlApi = config('services.niubiz.url_api') . "/api.authorization/v3/authorization/ecommerce/{$merchantId}";
        $countable = filter_var(config('services.niubiz.countable', true), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $countable = $countable === null ? true : $countable;

        try {
            $httpResponse = Http::withHeaders([
                'Authorization' => $accessTokenResult['token'],
                'Content-Type' => 'application/json',
            ])->connectTimeout(10)
                ->timeout(60)
                ->retry(2, 500)
                ->post($urlApi, [
                    'channel' => 'web',
                    'captureType' => 'manual',
                    'countable' => $countable,
                    'order' => [
                        'tokenId' => $payload['transaction_token'] ?? null,
                        'purchaseNumber' => $payload['purchase_number'] ?? null,
                        'amount' => $payload['amount'] ?? null,
                        'currency' => 'PEN',
                    ],
                    'dataMap' => [
                        'urlAddress' => $payload['site_url'] ?? null,
                        'serviceLocationCityName' => $payload['shipping_city'] ?? 'Lima',
                        'serviceLocationCountrySubdivisionCode' => 'LIM',
                        'serviceLocationCountryCode' => 'PER',
                        'serviceLocationPostalCode' => $payload['shipping_postal_code'] ?? '00000',
                    ],
                ]);

            if (! $httpResponse->successful()) {
                Log::warning('Niubiz authorization failed', [
                    'status' => $httpResponse->status(),
                    'response' => $httpResponse->body(),
                ]);

                return [
                    'ok' => false,
                    'response' => $httpResponse->json() ?? [],
                    'status' => $httpResponse->status(),
                    'message' => 'Niubiz no autorizó la operación.',
                ];
            }

            return [
                'ok' => true,
                'response' => $httpResponse->json() ?? [],
                'status' => $httpResponse->status(),
                'message' => null,
            ];
        } catch (ConnectionException $e) {
            Log::warning('Niubiz authorization connection failed', [
                'error' => $e->getMessage(),
                'url' => $urlApi,
            ]);

            return [
                'ok' => false,
                'response' => [],
                'status' => 503,
                'message' => 'No se pudo conectar con Niubiz para autorizar el pago.',
            ];
        }
    }

    private function requestAccessToken(): array
    {
        $urlApi = config('services.niubiz.url_api') . '/api.security/v1/security';
        $password = config('services.niubiz.password');
        $userNiubiz = config('services.niubiz.user');

        $auth = base64_encode($userNiubiz . ':' . $password);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $auth,
            ])->connectTimeout(10)
                ->timeout(60)
                ->retry(2, 500)
                ->get($urlApi);

            if (! $response->successful()) {
                Log::warning('Niubiz security auth failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                return [
                    'token' => null,
                    'status' => $response->status(),
                    'message' => 'Niubiz devolvió estado ' . $response->status() . ' al autenticar credenciales.',
                ];
            }

            return [
                'token' => $response->body(),
                'status' => $response->status(),
                'message' => null,
            ];
        } catch (ConnectionException $e) {
            Log::warning('Niubiz security connection failed', [
                'error' => $e->getMessage(),
                'url' => $urlApi,
            ]);

            return [
                'token' => null,
                'status' => 0,
                'message' => 'No se pudo conectar a Niubiz. Verifica DNS, red o proxy.',
            ];
        }
    }

    private function generateSessionToken(string $accessToken, float $amount, Cart $cart): ?string
    {
        $user = Auth::user();
        $merchantId = config('services.niubiz.merchant_id');
        $urlApi = config('services.niubiz.url_api') . "/api.ecommerce/v2/ecommerce/token/session/{$merchantId}";

        $clientIp = request()->ip();
        if ($clientIp === '::1') {
            $clientIp = '127.0.0.1';
        }

        $amountFormatted = number_format($amount, 2, '.', '');
        $customerEmail = (string) ($user->email ?? 'cliente@example.com');
        $customerIdentifier = (string) ($user->document_number ?? $customerEmail);
        $customerType = $user ? 'Registrado' : 'Invitado';
        $daysRegistered = max((int) now()->diffInDays($user->created_at ?? now()), 1);

        $address = Addresses::where('user_id', Auth::id())
            ->orderByDesc('id')
            ->first();

        $cardholderCity = (string) ($address?->district ?? 'Lima');
        $cardholderCountry = 'PE';
        $cardholderAddress = (string) ($address?->address_line ?? 'Sin direccion');
        $cardholderPostalCode = (string) ($address?->postal_code ?? '00000');
        $cardholderState = (string) ($address?->state_code ?? 'LIM');
        $cardholderPhoneNumber = (string) ($address?->receiver_phone ?? ($user->phone ?? '999999999'));

        try {
            $response = Http::withHeaders([
                'Authorization' => $accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->connectTimeout(10)
                ->timeout(60)
                ->retry(2, 500)
                ->post($urlApi, [
                    'channel' => 'web',
                    'amount' => (float) $amountFormatted,
                    'antifraud' => [
                        'clientIp' => $clientIp,
                        'merchantDefineData' => [
                            'MDD4' => $customerEmail,
                            'MDD32' => $customerIdentifier,
                            'MDD75' => $customerType,
                            'MDD77' => $daysRegistered,
                        ],
                    ],
                    'dataMap' => [
                        'cardholderCity' => $cardholderCity,
                        'cardholderCountry' => $cardholderCountry,
                        'cardholderAddress' => $cardholderAddress,
                        'cardholderPostalCode' => $cardholderPostalCode,
                        'cardholderState' => $cardholderState,
                        'cardholderPhoneNumber' => $cardholderPhoneNumber,
                    ],
                ]);
        } catch (ConnectionException $e) {
            Log::warning('Niubiz session token connection failed', [
                'error' => $e->getMessage(),
                'url' => $urlApi,
                'merchant_id' => $merchantId,
            ]);

            return null;
        }

        if (! $response->successful()) {
            Log::warning('Niubiz session token request failed', [
                'status' => $response->status(),
                'response' => $response->body(),
                'merchant_id' => $merchantId,
                'payload_summary' => [
                    'amount' => $amountFormatted,
                    'email' => $customerEmail,
                    'identifier' => $customerIdentifier,
                    'type' => $customerType,
                    'days_registered' => $daysRegistered,
                ],
            ]);

            return null;
        }

        $data = $response->json();

        return $data['sessionKey'] ?? $data['token'] ?? null;
    }
}
