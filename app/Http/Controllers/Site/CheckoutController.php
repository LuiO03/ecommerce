<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Addresses;
use App\Models\CompanySetting;
use App\Mail\OrderSummary;
use App\Services\Checkout\OrderPlacementService;
use App\Services\Checkout\PaymentGatewayManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

    public function paid(Request $request)
    {
        $deliveryType = $request->query('delivery_type', 'delivery');
        $selectedAddressId = $request->query('address_id');
        $selectedStoreId = $request->query('store_id');
        $requestedPaymentMethod = mb_strtolower((string) $request->query('payment_method', 'niubiz'));
        $allowedPaymentMethods = ['niubiz', 'pagoefectivo', 'yape'];
        $paymentMethod = in_array($requestedPaymentMethod, $allowedPaymentMethods, true)
            ? $requestedPaymentMethod
            : 'niubiz';

        $gateway = $this->paymentGatewayManager->resolve($paymentMethod);
        if (! $gateway) {
            return redirect()->route('checkout.index', [
                'payment_method' => $paymentMethod,
            ]);
        }

        $siteUrl = rtrim((string) config('app.url'), '/');
        $address = null;
        $shippingAddress = 'Recojo en tienda';
        $shippingCity = null;
        $shippingPostalCode = null;

        if (Auth::check()) {
            $user = Auth::user();

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
        }

        $authorization = $gateway->authorize([
            'transaction_token' => $request->transactionToken,
            'purchase_number' => $request->purchaseNumber,
            'amount' => $request->amount,
            'site_url' => $siteUrl,
            'shipping_city' => $shippingCity,
            'shipping_postal_code' => $shippingPostalCode,
        ]);

        $response = $authorization['response'] ?? [];
        if (!($authorization['ok'] ?? false) && empty($response)) {
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

        $transactionStatus = strtoupper((string) ($dataMap['STATUS'] ?? $data['STATUS'] ?? ''));
        $paymentApproved = in_array((string) $actionCode, ['000', '010'], true)
            || in_array($transactionStatus, ['AUTHORIZED', 'AUTHORIZED AND COMPLETED WITH SUCCESS'], true);

        // Guardar orden en base de datos solo si el pago fue exitoso
        if ($paymentApproved) {
            $user = Auth::user();

            if ($user) {
                $cart = Cart::with(['items.product', 'items.variant'])
                    ->where('user_id', $user->id)
                    ->where('is_active', true)
                    ->first();

                if ($cart && $cart->items->isNotEmpty()) {
                    $subtotal = $cart->total_price;
                    $shipping = 5.0; // Mantener consistente con index()
                    $amount   = $subtotal + $shipping;

                    // Datos de flujo de checkout (tipo de entrega y selección de dirección/tienda)
                    $shippingPhone = $deliveryType === 'delivery'
                        ? ($address?->receiver_phone ?? ($user->phone ?? null))
                        : ($user->phone ?? null);

                    // Identificador de pago del gateway (si está disponible)
                    $paymentId = $dataMap['TRANSACTION_ID']
                        ?? ($data['TRANSACTION_ID'] ?? ($response['order']['transactionId'] ?? null));

                    // 1) REGISTRO DEL PEDIDO + ÍTEMS EN BASE DE DATOS
                    // -----------------------------------------------------------------
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

        $amount = (float) $request->input('amount', 0);
        $requestedPaymentMethod = mb_strtolower((string) $request->input('payment_method', 'niubiz'));
        $gateway = $this->paymentGatewayManager->resolve($requestedPaymentMethod);

        if (! $gateway) {
            return response()->json([
                'status' => 'error',
                'message' => 'El método de pago seleccionado aún no está disponible.',
            ], 422);
        }

        if ($amount <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'El monto del pedido no es válido.',
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
            return response()->json([
                'status' => 'error',
                'message' => 'No encontramos un carrito activo para generar la sesión de pago.',
            ], 422);
        }

        $sessionTokenResult = $gateway->createSessionToken($amount, $cart);
        $sessionToken = $sessionTokenResult['token'] ?? null;

        if (! $sessionToken) {
            return response()->json([
                'status' => 'error',
                'message' => $sessionTokenResult['message'] ?? 'No se pudo generar el token de sesión.',
            ], (int) ($sessionTokenResult['status'] ?? 502));
        }

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
