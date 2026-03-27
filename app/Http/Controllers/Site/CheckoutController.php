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
        // Token de seguridad para autorizar la transacción
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

        // Extraer estructuras de datos y código de acción
        $dataMap = $response['dataMap'] ?? [];
        $data = $response['data'] ?? [];

        $actionCode = $dataMap['ACTION_CODE']
            ?? $dataMap['ACTIONCODE']
            ?? ($data['ACTION_CODE'] ?? ($data['ACTIONCODE'] ?? null));

        // Mapear códigos de acción de Niubiz a mensajes amigables
        $friendlyMessages = [
            '101' => 'Tu tarjeta está vencida. Prueba con otra tarjeta o actualiza los datos.',
            '102' => 'Esta operación no está permitida para tu tarjeta. Usa otro medio de pago.',
            '113' => 'El monto no está permitido para esta tarjeta. Intenta con un monto menor u otra tarjeta.',
            '116' => 'Fondos insuficientes. Revisa tu saldo o utiliza otra tarjeta.',
            '118' => 'Tu tarjeta no es válida o no está registrada. Revisa los datos o usa otra tarjeta.',
            '129' => 'La tarjeta no está operativa (por ejemplo, error en el CVV). Verifica los datos ingresados.',
            '180' => 'La transacción fue considerada inválida por el emisor. Usa otro medio de pago.',
            '190' => 'La transacción fue rechazada por el emisor. Contacta a tu banco si el problema persiste.',
            '191' => 'Debes contactar a tu banco para autorizar este tipo de operación.',
            '207' => 'La tarjeta fue reportada como perdida. Usa otro medio de pago.',
            '208' => 'La tarjeta fue reportada como perdida. Usa otro medio de pago.',
            '209' => 'La tarjeta fue reportada como robada. Usa otro medio de pago.',
            '401' => 'La tienda no está habilitada temporalmente para procesar pagos. Inténtalo más tarde.',
            '476' => 'Esta operación ya fue procesada previamente en un depósito.',
            '479' => 'El comercio configurado para este pago no es válido. Inténtalo más tarde o contacta soporte.',
            '666' => 'Hay problemas de comunicación con el banco o procesador. Inténtalo nuevamente en unos minutos.',
            '668' => 'Hay problemas de comunicación con el sistema antifraude. Inténtalo nuevamente en unos minutos.',
            '670' => 'La transacción fue denegada por posible fraude. Te recomendamos contactar a tu banco.',
            '678' => 'Hubo un error en la autenticación de la tarjeta. Intenta nuevamente o usa otra tarjeta.',
            '754' => 'El comercio configurado para este pago no es válido. Inténtalo más tarde o contacta soporte.',
        ];

        $friendlyMessage = $actionCode && isset($friendlyMessages[$actionCode])
            ? $friendlyMessages[$actionCode]
            : null;

        // Datos de tarjeta enmascarados y marca (priorizar dataMap y luego data)
        $brand = $dataMap['BRAND']
            ?? ($dataMap['BRAND_NAME'] ?? ($data['BRAND'] ?? ($data['BRAND_NAME'] ?? null)));

        $cardMasked = $dataMap['CARD'] ?? ($data['CARD'] ?? null); // ej. 455170******8059
        $cardLast4 = $cardMasked ? substr($cardMasked, -4) : null;

        // Fecha/hora de la transacción (formato Niubiz: dmyHis)
        $transactionDateRaw = $dataMap['TRANSACTION_DATE'] ?? ($data['TRANSACTION_DATE'] ?? null);
        $transactionDateFormatted = null;
        if (!empty($transactionDateRaw) && strlen($transactionDateRaw) === 12) {
            try {
                $transactionDateFormatted = \Carbon\Carbon::createFromFormat('dmyHis', $transactionDateRaw)
                    ->format('d/m/Y H:i:s');
            } catch (\Exception $e) {
                $transactionDateFormatted = $transactionDateRaw;
            }
        }

        // Guardar en sesión todos los datos que necesita la vista de checkout
        session()->flash('niubiz', [
            'response' => $response,
            'purchaseNumber' => $request->purchaseNumber,
            'actionCode' => $actionCode,
            'friendlyMessage' => $friendlyMessage,
            'brand' => $brand,
            'cardLast4' => $cardLast4,
            'transactionDate' => $transactionDateFormatted,
        ]);

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

        return redirect()->route('checkout.index');
    }

    public function success()
    {
        $niubiz = session('niubiz');
        $response = $niubiz['response'] ?? null;

        return view('site.checkout.success', compact('niubiz', 'response'));
    }

}
