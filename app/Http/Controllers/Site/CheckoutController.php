<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Addresses;
use App\Models\CompanySetting;
use App\Mail\OrderSummary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelPdf\Facades\Pdf;

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
                ->orderByDesc('is_default')
                ->orderByDesc('id')
                ->get();

            if ($cart && $cart->items->isNotEmpty()) {
                $subtotal = $cart->total_price;
                $amount = $subtotal + $shipping;

                $access_token = $this->generateAccessToken();
                if ($access_token) {
                    $session_token = $this->generateSessionToken($access_token, $amount, $cart);
                }
            }
        }

        return view('site.checkout.index', compact('cart', 'session_token', 'subtotal', 'shipping', 'amount', 'addresses'));
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

        // Guardar orden en base de datos solo si el pago fue exitoso
        if ($actionCode === '000') {
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
                    $deliveryType = $request->query('delivery_type', 'delivery');
                    $selectedAddressId = $request->query('address_id');
                    $selectedStoreId = $request->query('store_id');

                    // Buscar dirección de envío: primero la predeterminada, luego cualquier otra
                    $address = null;

                    if ($deliveryType === 'delivery') {
                        if ($selectedAddressId) {
                            $address = Addresses::where('user_id', $user->id)
                                ->where('id', $selectedAddressId)
                                ->first();
                        }

                        if (! $address) {
                            $address = Addresses::where('user_id', $user->id)
                                ->orderByDesc('is_default')
                                ->orderByDesc('id')
                                ->first();
                        }

                        $shippingAddress = $address?->address_line ?? 'Sin dirección registrada';
                        $shippingCity    = $address?->district;
                        $shippingPhone   = $address?->receiver_phone ?? ($user->phone ?? null);
                    } else {
                        // Recojo en tienda: mapear tienda seleccionada a una dirección descriptiva
                        $stores = [
                            'store_central' => [
                                'name'    => 'Tienda Central',
                                'address' => 'Av. Principal 123, Miraflores',
                                'city'    => 'Miraflores, Lima',
                            ],
                            'store_sucursal_1' => [
                                'name'    => 'Sucursal Norte',
                                'address' => 'Av. Las Flores 456, Los Olivos',
                                'city'    => 'Los Olivos, Lima',
                            ],
                        ];

                        $store = $stores[$selectedStoreId] ?? null;

                        $shippingAddress = $store
                            ? ($store['name'] . ' - ' . $store['address'])
                            : 'Recojo en tienda';
                        $shippingCity  = $store['city'] ?? null;
                        $shippingPhone = $user->phone ?? null;
                    }

                    // Identificador de pago del gateway (si está disponible)
                    $paymentId = $dataMap['TRANSACTION_ID']
                        ?? ($data['TRANSACTION_ID'] ?? ($response['order']['transactionId'] ?? null));

                    // 1) REGISTRO DEL PEDIDO + ÍTEMS EN BASE DE DATOS
                    // -----------------------------------------------------------------
                    $order = DB::transaction(function () use (
                        $user,
                        $cart,
                        $subtotal,
                        $shipping,
                        $amount,
                            $deliveryType,
                        $shippingAddress,
                        $shippingCity,
                        $shippingPhone,
                        $paymentId,
                        $request
                    ) {
                        $order = Order::create([
                            'user_id'          => $user->id,
                            'order_number'     => $request->purchaseNumber,
                            'total'            => $amount,
                            'subtotal'         => $subtotal,
                            'shipping_cost'    => $shipping,
                                'delivery_type'    => $deliveryType === 'pickup' ? 'pickup' : 'delivery',
                            'status'           => 'pending',
                            'shipping_address' => $shippingAddress,
                            'shipping_city'    => $shippingCity,
                            'shipping_phone'   => $shippingPhone,
                            'payment_method'   => 'niubiz',
                            'payment_id'       => $paymentId,
                            'payment_status'   => 'paid',
                        ]);

                        foreach ($cart->items as $item) {
                            $product = $item->product;

                            if (! $product) {
                                continue;
                            }

                            $variant = $item->variant;

                            $discountPercent = ! is_null($product->discount)
                                ? min(max((float) $product->discount, 0), 100)
                                : 0.0;
                            $hasDiscount = $discountPercent > 0;

                            $basePrice = ($variant && $variant->price && $variant->price > 0)
                                ? (float) $variant->price
                                : (float) $product->price;

                            $unitPrice = $hasDiscount
                                ? max($basePrice * (1 - $discountPercent / 100), 0)
                                : $basePrice;

                            $lineTotal = $unitPrice * (int) $item->quantity;

                            // Crear ítem de orden
                            OrderItem::create([
                                'order_id'   => $order->id,
                                'product_id' => $product->id,
                                'variant_id' => $variant?->id,
                                'quantity'   => $item->quantity,
                                'unit_price' => $unitPrice,
                                'line_total' => $lineTotal,
                            ]);

                            // Descontar stock de la variante asociada (si aplica)
                            if ($variant) {
                                $currentStock = (int) ($variant->stock ?? 0);

                                // Solo gestionar stock cuando es un número positivo
                                if ($currentStock > 0) {
                                    $newStock = max($currentStock - (int) $item->quantity, 0);

                                    $variant->update([
                                        'stock' => $newStock,
                                        'updated_by' => $user->id,
                                    ]);
                                }
                            }
                        }

                        // Marcar el carrito como inactivo una vez registrados todos los ítems
                        $cart->update(['is_active' => false]);
                        return $order;
                    });

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

    public function success()
    {
        $niubiz = session('niubiz');
        $response = $niubiz['response'] ?? null;

        return view('site.checkout.success', compact('niubiz', 'response'));
    }

}
