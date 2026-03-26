<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Mail\OrderSummary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{

    public function index()
    {
        $userId = Auth::id();
        $cart = null;
        $session_token = null;
        $subtotal = 0.0;
        $shipping = 5.0;
        $amount = 0.0;

        if ($userId) {
            $cart = Cart::with([
                'items.product.images',
                'items.product.category',
                'items.variant.features.option',
            ])
                ->where('user_id', $userId)
                ->where('is_active', true)
                ->first();

            if ($cart && $cart->items->isNotEmpty()) {
                $subtotal = $cart->total_price;
                $amount = $subtotal + $shipping;

                $access_token = $this->generateAccessToken();
                if ($access_token) {
                    $session_token = $this->generateSessionToken($access_token, $amount, $cart);
                }
            }
        }

        return view('site.checkout.index', compact('cart', 'session_token', 'subtotal', 'shipping', 'amount'));
    }

    public function generateAccessToken(): ?string
    {
        $url_api = config('services.niubiz.url_api') . '/api.security/v1/security';
        $password = config('services.niubiz.password');
        $user_niubiz = config('services.niubiz.user');

        $auth = base64_encode($user_niubiz . ':' . $password);

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $auth,
        ])->get($url_api);

        if (!$response->successful()) {
            return null;
        }

        return $response->body();
    }

    public function generateSessionToken(string $accessToken, float $amount, ?Cart $cart = null): ?string
    {
        $user = Auth::user();
        $merchantId = config('services.niubiz.merchant_id');
        $url_api = config('services.niubiz.url_api') . "/api.ecommerce/v2/ecommerce/token/session/{$merchantId}";
        $cart = $cart ?: Cart::with(['items.product', 'items.variant'])
            ->where('user_id', Auth::id())
            ->where('is_active', true)
            ->first();

        $response = Http::withHeaders([
            'Authorization' => $accessToken,
            'Content-Type' => 'application/json',
        ])->post($url_api, [
            'channel' => 'web',
            'amount' => $amount,
            'antifraud' => [
                'clientIp' => request()->ip(),
                'merchantDefineData' => [
                    'MDD4'  => (string) ($user->id ?? 0),                  // ID cliente interno
                    'MDD21' => $user->email ?? '',                        // Email
                    'MDD32' => $user->document_number ?? '',              // Nro documento
                    'MDD33' => $user->phone ?? '',                        // Teléfono
                    'MDD75' => (string) $amount,                          // Monto total
                    'MDD76' => 'PEN',                                     // Moneda
                    'MDD77' => (string) $cart->items_count,               // Cant. líneas
                    'MDD89' => now()->diffInDays($user->created_at ?? now()), // Antigüedad
                ],
            ],
        ]);

        if (!$response->successful()) {
            return null;
        }

        $data = $response->json();

        return $data['sessionKey'] ?? $data['token'] ?? null;
    }

    public function paid(Request $request)
    {

        // Aquí puedes validar el payload recibido y actualizar el estado del pedido en tu base de datos

        $access_token = $this->generateAccessToken();
        $merchantId = config('services.niubiz.merchant_id');
        $url_api = config('services.niubiz.url_api') . "/api.authorization/v3/authorization/ecommerce/{$merchantId}";

        $response = Http::withHeaders([
            'Authorization' => $access_token,
            'Content-Type'  => 'application/json',
        ])->post($url_api, [
            'channel'     => 'web',
            'captureType' => 'manual',
            'countable'   => true,
            'order'       => [
                'tokenId'        => $request->transactionToken,
                'purchaseNumber' => $request->purchaseNumber,
                'amount'         => $request->amount,
                'currency'       => 'PEN',
            ],
        ])->json();

        session()->flash('niubiz', [
            'response' => $response,
        ]);

        $actionCode = $response['dataMap']['ACTIONCODE']
            ?? $response['datamap']['ACTIONCODE']
            ?? null;

        if ($actionCode === '000') {
            // Enviar correo de resumen de compra al confirmar pago exitoso
            $user = Auth::user();

            if ($user) {
                $cart = Cart::with([
                    'items.product',
                    'items.variant',
                ])
                    ->where('user_id', $user->id)
                    ->where('is_active', true)
                    ->first();

                if ($cart && $cart->items->isNotEmpty()) {
                    $subtotal = $cart->total_price;
                    $shipping = 5.0; // Mantener consistente con index()
                    $amount = $subtotal + $shipping;

                    Mail::to($user->email)->send(new OrderSummary(
                        $user,
                        $cart,
                        $subtotal,
                        $shipping,
                        $amount,
                        $response,
                        $request->purchaseNumber,
                    ));
                }
            }

            return redirect()->route('checkout.success');
        }

        return redirect()->route('checkout.failure');
    }

    public function success()
    {
        $niubiz = session('niubiz');

        return view('site.checkout.success', compact('niubiz'));
    }

    public function failure()
    {
        $niubiz = session('niubiz');

        return view('site.checkout.failure', compact('niubiz'));
    }
}
