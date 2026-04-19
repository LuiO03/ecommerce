<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\CheckoutPaidRequest;
use App\Models\Cart;
use App\Models\Addresses;
use App\Models\CompanySetting;
use App\Models\PaymentAttempt;
use App\Mail\OrderSummary;
use App\Services\Checkout\Gateways\DTO\GatewayAuthorizationResult;
use App\Services\Checkout\OrderPlacementService;
use App\Services\Checkout\PaymentGatewayManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\LaravelPdf\Facades\Pdf;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly OrderPlacementService $orderPlacementService,
        private readonly PaymentGatewayManager $paymentGatewayManager,
    )
    {
    }

    public function index()
    {
        $userId = Auth::id();
        $cart = null;
        $session_token = null;
        $subtotal = 0.0;
        $shipping = 5.0;
        $amount = 0.0;
        $addresses = collect();

        if ($userId) {
            $cart = Cart::with([
                'items.product.images',
                'items.product.category',
                'items.variant.features.option',
            ])
                ->where('user_id', $userId)
                ->where('is_active', true)
                ->first();

            // Direcciones del usuario para selección rápida en checkout
            $addresses = Addresses::where('user_id', $userId)
                ->orderByDesc('id')
                ->get();

            if ($cart && $cart->items->isNotEmpty()) {
                $subtotal = $cart->total_price;
                $amount = $subtotal + $shipping;
            }
        }

        return view('site.checkout.index', compact('cart', 'session_token', 'subtotal', 'shipping', 'amount', 'addresses'));
    }

    public function paid(CheckoutPaidRequest $request)
    {
        $deliveryType = $request->query('delivery_type', 'delivery');
        $selectedAddressId = $request->query('address_id');
        $selectedStoreId = $request->query('store_id');
        $requestedPaymentMethod = mb_strtolower((string) $request->query('payment_method', 'niubiz'));
        $allowedPaymentMethods = ['niubiz', 'culqi', 'mercadopago', 'pagoefectivo', 'yape'];
        $paymentMethod = in_array($requestedPaymentMethod, $allowedPaymentMethods, true)
            ? $requestedPaymentMethod
            : 'niubiz';

        $user = Auth::user();
        if (! $user) {
            return redirect()->route('checkout.index');
        }

        $cart = Cart::with(['items.product', 'items.variant'])
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if (! $cart || $cart->items->isEmpty()) {
            return redirect()->route('checkout.index');
        }

        $shipping = 5.0;
        $subtotal = (float) $cart->total_price;
        $calculatedAmount = $subtotal + $shipping;

        $idempotencyKey = (string) ($request->input('idempotency_key')
            ?? $request->query('idempotency_key')
            ?? $request->purchaseNumber);

        if ($idempotencyKey === '') {
            $idempotencyKey = hash('sha256', implode('|', [
                $user->id,
                (string) $request->purchaseNumber,
                (string) $request->transactionToken,
                $paymentMethod,
            ]));
        }

        $correlationId = $idempotencyKey;

        Log::info('Checkout paid request started', [
            'correlation_id' => $correlationId,
            'user_id' => $user->id,
            'payment_method' => $paymentMethod,
            'purchase_number' => (string) $request->purchaseNumber,
            'delivery_type' => $deliveryType,
        ]);

        $attempt = PaymentAttempt::where('idempotency_key', $idempotencyKey)->first();

        if ($attempt && $attempt->status === 'approved') {
            return redirect()->route('checkout.success');
        }

        if ($attempt && $attempt->status === 'processing') {
            return redirect()->route('checkout.index', [
                'payment_method' => $paymentMethod,
            ]);
        }

        if (! $attempt) {
            $attempt = PaymentAttempt::create([
                'idempotency_key' => $idempotencyKey,
                'user_id' => $user->id,
                'payment_method' => $paymentMethod,
                'purchase_number' => (string) $request->purchaseNumber,
                'request_hash' => hash('sha256', json_encode([
                    'purchaseNumber' => $request->purchaseNumber,
                    'delivery_type' => $deliveryType,
                    'address_id' => $selectedAddressId,
                    'store_id' => $selectedStoreId,
                    'payment_method' => $paymentMethod,
                ])),
                'status' => 'processing',
            ]);
        }

        $gateway = $this->paymentGatewayManager->resolve($paymentMethod);
        if (! $gateway) {
            $attempt->update([
                'status' => 'failed',
                'result_payload' => [
                    'message' => 'Gateway no disponible para el método seleccionado.',
                ],
            ]);

            Log::warning('Checkout gateway not available', [
                'correlation_id' => $correlationId,
                'payment_method' => $paymentMethod,
                'user_id' => $user->id,
            ]);

            return redirect()->route('checkout.index', [
                'payment_method' => $paymentMethod,
            ]);
        }

        $siteUrl = rtrim((string) config('app.url'), '/');
        $address = null;
        $shippingAddress = 'Recojo en tienda';
        $shippingCity = null;
        $shippingPostalCode = null;

        if ($deliveryType === 'delivery') {
            if ($selectedAddressId) {
                $address = Addresses::where('user_id', $user->id)
                    ->where('id', $selectedAddressId)
                    ->first();
            }

            if (! $address) {
                $address = Addresses::where('user_id', $user->id)
                    ->orderByDesc('id')
                    ->first();
            }

            $shippingAddress = $address?->address_line ?? 'Sin dirección registrada';
            $shippingCity = $address?->district ?? null;
            $shippingPostalCode = $address?->postal_code ?? null;
        } else {
            $stores = [
                'store_central' => [
                    'name'    => 'Tienda Central',
                    'address' => 'Av. Principal 123, Miraflores',
                    'city'    => 'Miraflores',
                    'postal'  => '15074',
                ],
                'store_sucursal_1' => [
                    'name'    => 'Sucursal Norte',
                    'address' => 'Av. Las Flores 456, Los Olivos',
                    'city'    => 'Los Olivos',
                    'postal'  => '15301',
                ],
            ];

            $store = $stores[$selectedStoreId] ?? null;

            if ($store) {
                $shippingAddress = $store['name'] . ' - ' . $store['address'];
                $shippingCity = $store['city'];
                $shippingPostalCode = $store['postal'];
            }
        }

        $authorization = $gateway->authorize([
            'transaction_token' => $request->transactionToken,
            'purchase_number' => $request->purchaseNumber,
            'amount' => $calculatedAmount,
            'customer_email' => (string) $user->email,
            'site_url' => $siteUrl,
            'shipping_city' => $shippingCity,
            'shipping_postal_code' => $shippingPostalCode,
            'correlation_id' => $correlationId,
        ]);

        if (!($authorization instanceof GatewayAuthorizationResult)) {
            $attempt->update([
                'status' => 'failed',
                'result_payload' => [
                    'message' => 'Respuesta de gateway inválida.',
                ],
            ]);

            Log::warning('Checkout gateway returned invalid authorization result', [
                'correlation_id' => $correlationId,
                'payment_method' => $paymentMethod,
                'user_id' => $user->id,
            ]);

            return redirect()->route('checkout.index', [
                'payment_method' => $paymentMethod,
            ]);
        }

        $response = $authorization->response;
        if (!$authorization->ok && !$authorization->hasResponse()) {
            $attempt->update([
                'status' => 'failed',
                'result_payload' => [
                    'message' => $authorization->message ?? 'No se pudo autorizar el pago.',
                    'response' => $response,
                ],
            ]);

            Log::warning('Checkout authorization failed without response payload', [
                'correlation_id' => $correlationId,
                'payment_method' => $paymentMethod,
                'purchase_number' => (string) $request->purchaseNumber,
                'status' => $authorization->status,
                'message' => $authorization->message,
            ]);

            return redirect()->route('checkout.index', [
                'payment_method' => $paymentMethod,
            ]);
        }

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

        // Guardar en sesión datos de error solo para Niubiz
        if ($paymentMethod === 'niubiz') {
            session()->flash('niubiz', [
                'response' => $response,
                'purchaseNumber' => $request->purchaseNumber,
                'actionCode' => $actionCode,
                'friendlyMessage' => $friendlyMessage,
                'brand' => $brand,
                'cardLast4' => $cardLast4,
                'transactionDate' => $transactionDateFormatted,
            ]);
        }

        $transactionStatus = strtoupper((string) ($dataMap['STATUS'] ?? $data['STATUS'] ?? ''));
        $culqiOutcomeType = mb_strtolower((string) ($response['outcome']['type'] ?? ''));
        $culqiStatus = mb_strtolower((string) ($response['status'] ?? ''));
        $mercadoPagoStatus = mb_strtolower((string) ($response['status'] ?? ''));

        $paymentApproved = in_array((string) $actionCode, ['000', '010'], true)
            || in_array($transactionStatus, ['AUTHORIZED', 'AUTHORIZED AND COMPLETED WITH SUCCESS'], true)
            || ($paymentMethod === 'culqi' && ($authorization->ok
                || in_array($culqiOutcomeType, ['venta_exitosa', 'authorized'], true)
                || in_array($culqiStatus, ['captured', 'paid'], true)))
            || ($paymentMethod === 'mercadopago' && $authorization->ok
                && in_array($mercadoPagoStatus, ['approved', 'authorized'], true));

        // Guardar orden en base de datos solo si el pago fue exitoso
        if ($paymentApproved) {
            if ($user) {
                if ($cart && $cart->items->isNotEmpty()) {
                    $amount = $calculatedAmount;

                    // Datos de flujo de checkout (tipo de entrega y selección de dirección/tienda)
                    $shippingPhone = $deliveryType === 'delivery'
                        ? ($address?->receiver_phone ?? ($user->phone ?? null))
                        : ($user->phone ?? null);

                    // Identificador de pago del gateway (si está disponible)
                    $paymentId = $dataMap['TRANSACTION_ID']
                        ?? ($data['TRANSACTION_ID']
                            ?? ($response['order']['transactionId']
                                ?? ($response['id'] ?? null)));

                    // 1) REGISTRO DEL PEDIDO + ÍTEMS EN BASE DE DATOS
                    // -----------------------------------------------------------------
                    try {
                        $order = $this->orderPlacementService->placePaidOrder($user, $cart, [
                            'purchase_number' => $request->purchaseNumber,
                            'total' => $amount,
                            'subtotal' => $subtotal,
                            'shipping_cost' => $shipping,
                            'delivery_type' => $deliveryType,
                            'address_id' => $deliveryType === 'delivery' ? $address?->id : null,
                            'pickup_store_code' => $deliveryType === 'pickup' ? $selectedStoreId : null,
                            'shipping_address' => $shippingAddress,
                            'shipping_city' => $shippingCity,
                            'shipping_phone' => $shippingPhone,
                            'payment_method' => $paymentMethod,
                            'payment_id' => $paymentId,
                            'payment_response' => $response,
                        ]);
                    } catch (\RuntimeException $e) {
                        $attempt->update([
                            'status' => 'conflict',
                            'result_payload' => [
                                'purchaseNumber' => $request->purchaseNumber,
                                'actionCode' => $actionCode,
                                'message' => $e->getMessage(),
                            ],
                        ]);

                        Log::warning('Checkout order placement conflict', [
                            'correlation_id' => $correlationId,
                            'user_id' => $user->id,
                            'purchase_number' => (string) $request->purchaseNumber,
                            'message' => $e->getMessage(),
                        ]);

                        return redirect()->route('checkout.index', [
                            'payment_method' => $paymentMethod,
                        ]);
                    }

                    $attempt->update([
                        'status' => 'approved',
                        'order_id' => $order->id,
                        'payment_record_id' => $order->load('latestPayment')->latestPayment?->id,
                        'result_payload' => [
                            'purchaseNumber' => $request->purchaseNumber,
                            'actionCode' => $actionCode,
                            'paymentId' => $paymentId,
                        ],
                    ]);

                    Log::info('Checkout payment approved and order created', [
                        'correlation_id' => $correlationId,
                        'user_id' => $user->id,
                        'order_id' => $order->id,
                        'payment_id' => $paymentId,
                        'payment_method' => $paymentMethod,
                    ]);

                    // 2) GENERACIÓN Y ALMACENAMIENTO DE LA BOLETA PDF
                    // -----------------------------------------------------------------
                    try {
                        $orderForPdf = $order->load([
                            'user',
                            'items.product',
                            'items.variant.features.option',
                        ]);

                        $companyInfo = CompanySetting::first();

                        $relativePath = 'orders/documents/order-' . $orderForPdf->id . '-' . now()->format('YmdHis') . '.pdf';

                        // Asegurar directorio en disco public
                        Storage::disk('public')->makeDirectory('orders/documents');

                        // Ruta absoluta basada en el disco public (más robusta que concatenar storage_path)
                        $fullPath = Storage::disk('public')->path($relativePath);

                        Pdf::view('admin.export.order-invoice', [
                            'order' => $orderForPdf,
                            'companyInfo' => $companyInfo,
                        ])->format('a4')->save($fullPath);

                        $orderForPdf->update(['pdf_path' => $relativePath]);
                    } catch (\Throwable $e) {
                        // Si falla la generación del PDF, no interrumpimos el flujo de compra
                    }

                    // 3) ENVÍO DEL CORREO DE RESUMEN DE COMPRA AL CLIENTE
                    // -----------------------------------------------------------------
                    try {
                        Mail::to($user->email)->send(new OrderSummary(
                            $user,
                            $cart,
                            $subtotal,
                            $shipping,
                            $amount,
                            $response,
                            $request->purchaseNumber,
                        ));
                    } catch (\Throwable $e) {
                        report($e); // Si falla el envío del correo, no se interrumpe el flujo de compra
                    }
                }
            }

            return redirect()->route('checkout.success');
        }

        $attempt->update([
            'status' => 'failed',
            'result_payload' => [
                'purchaseNumber' => $request->purchaseNumber,
                'actionCode' => $actionCode,
                'response' => $response,
            ],
        ]);

        Log::info('Checkout payment failed', [
            'correlation_id' => $correlationId,
            'user_id' => $user->id,
            'payment_method' => $paymentMethod,
            'purchase_number' => (string) $request->purchaseNumber,
            'action_code' => $actionCode,
        ]);

        return redirect()->route('checkout.index');
    }

    public function refreshSessionToken(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Debes iniciar sesión para continuar con el pago.',
            ], 401);
        }

        $amount = 0.0;
        $correlationId = (string) $request->header('X-Correlation-Id', Str::uuid()->toString());
        $requestedPaymentMethod = mb_strtolower((string) $request->input('payment_method', 'niubiz'));
        $gateway = $this->paymentGatewayManager->resolve($requestedPaymentMethod);

        Log::info('Checkout session token refresh requested', [
            'correlation_id' => $correlationId,
            'user_id' => Auth::id(),
            'payment_method' => $requestedPaymentMethod,
        ]);

        if (! $gateway) {
            Log::warning('Checkout session token gateway not available', [
                'correlation_id' => $correlationId,
                'user_id' => Auth::id(),
                'payment_method' => $requestedPaymentMethod,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'El método de pago seleccionado aún no está disponible.',
            ], 422);
        }

        $cart = Cart::with([
            'items.product',
            'items.variant',
        ])
            ->where('user_id', Auth::id())
            ->where('is_active', true)
            ->first();

        if (! $cart || $cart->items->isEmpty()) {
            Log::warning('Checkout session token refresh without active cart', [
                'correlation_id' => $correlationId,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'No encontramos un carrito activo para generar la sesión de pago.',
            ], 422);
        }

        $shipping = 5.0;
        $subtotal = (float) $cart->total_price;
        $amount = $subtotal + $shipping;

        try {
            $sessionTokenResult = $gateway->createSessionToken($amount, $cart);
        } catch (\Throwable $e) {
            Log::error('Checkout session token refresh crashed', [
                'correlation_id' => $correlationId,
                'user_id' => Auth::id(),
                'payment_method' => $requestedPaymentMethod,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Niubiz tardó demasiado en responder. Intenta nuevamente en unos segundos.',
            ], 503);
        }

        $sessionToken = $sessionTokenResult->token;

        if (! $sessionToken) {
            Log::warning('Checkout session token refresh failed', [
                'correlation_id' => $correlationId,
                'user_id' => Auth::id(),
                'payment_method' => $requestedPaymentMethod,
                'status' => $sessionTokenResult->status,
                'message' => $sessionTokenResult->message,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $sessionTokenResult->message ?? 'No se pudo generar el token de sesión.',
            ], (int) ($sessionTokenResult->status ?? 502));
        }

        Log::info('Checkout session token refresh succeeded', [
            'correlation_id' => $correlationId,
            'user_id' => Auth::id(),
            'payment_method' => $requestedPaymentMethod,
        ]);

        return response()->json([
            'status' => 'success',
            'session_token' => $sessionToken,
        ]);
    }

    public function success()
    {
        $niubiz = session('niubiz');
        $response = $niubiz['response'] ?? null;

        return view('site.checkout.success', compact('niubiz', 'response'));
    }

}
