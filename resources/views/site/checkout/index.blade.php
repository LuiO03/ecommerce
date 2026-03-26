<x-app-layout>
    @include('partials.site.breadcrumb', [
        'items' => $breadcrumbItems ?? [
            [
                'label' => 'Carrito de compras',
                'icon' => 'ri-shopping-cart-fill',
                'url' => route('carts.show'),
            ],
            [
                'label' => 'Envío',
                'icon' => 'ri-truck-fill',
                'url' => route('shipping.index'),
            ],
            [
                'label' => 'Pago',
                'icon' => 'ri-wallet-3-fill',
            ],
        ],
    ])

    @php
        $items = $cart?->items ?? collect();
        $hasItems = $items->isNotEmpty();
        $itemsCount = $items->count();
        $itemsQuantity = $items->sum('quantity');
    @endphp

    <section class="site-container checkout-page">
        <div class="section-header">
            <h1>Pago</h1>
            <p>
                Aquí puedes revisar tu resumen de compra y seleccionar tu método de pago.
            </p>
        </div>

        @if (!Auth::check() || !$hasItems)
            <div class="card-empty">
                <div class="cart-empty-icon">
                    <i class="ri-shopping-cart-line"></i>
                </div>
                <h2 class="card-title">Tu carrito está vacío</h2>
                <p>
                    Aún no has agregado productos. Explora nuestro catálogo y empieza tu compra.
                </p>
                <a href="{{ route('welcome.index') }}" class="boton-form boton-success py-3 px-5">
                    <span class="boton-form-icon"><i class="ri-store-2-fill"></i></span>
                    <span class="boton-form-text">Ir a la tienda</span>
                </a>
            </div>
        @else
            <div class="checkout-layout">
                <div class="checkout-main">
                    <h2 class="checkout-main-title">Método de pago</h2>

                    <form class="payment-methods-form">
                        <div class="payment-method-option">
                            <input type="radio" value="card" name="payment_method" id="payment_method_card"
                                class="payment-method-radio" checked>
                            <label for="payment_method_card" class="payment-method-card">
                                <div class="payment-method-header">
                                    <div class="payment-method-icon">
                                        <i class="ri-bank-card-line"></i>
                                    </div>
                                    <div class="payment-method-text">
                                        <span class="card-title">Tarjeta de crédito/débito</span>
                                        <span class="payment-method-helper">Paga con Visa, Mastercard u otras
                                            tarjetas.</span>
                                    </div>
                                </div>
                                <img class="payment-method-img" src="{{ asset('images/checkout/cards_pay.png') }}"
                                    alt="Formas de pago con tarjeta">
                            </label>


                            <div class="payment-method-body">
                                <p class="input-help-text mb-2">
                                    Luego de hacer click en "Pagar ahora" se abrira el checkout de Niubiz para que
                                    completes los datos de tu tarjeta y finalices tu compra de forma segura.
                                </p>
                                <ul class="payment-method-info">
                                    <li>Aceptamos Visa, Mastercard, American Express y otras tarjetas.</li>
                                    <li>El pago se procesa de forma segura a través de Niubiz.</li>
                                    <li>Tiempo de validación del pago: 5-15 minutos hábiles.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="payment-method-option">
                            <input type="radio" value="bank" name="payment_method" id="payment_method_bank"
                                class="payment-method-radio">
                            <label for="payment_method_bank" class="payment-method-card">
                                <div class="payment-method-header">
                                    <div class="payment-method-icon">
                                        <i class="ri-exchange-dollar-line"></i>
                                    </div>
                                    <div class="payment-method-text">
                                        <span class="card-title">Depósito bancario o Yape</span>
                                        <span class="payment-method-helper">Transfiere desde tu banco o paga con
                                            Yape.</span>
                                    </div>
                                </div>
                                <img class="payment-method-img" src="{{ asset('images/checkout/yape-pay.png') }}"
                                    alt="Depósito bancario o Yape">
                            </label>

                            <div class="payment-method-body">
                                <p class="input-help-text mb-2">
                                    Al confirmar tu pedido, te mostraremos los datos de la cuenta bancaria o el número
                                    de
                                    Yape para completar el pago.
                                </p>
                                <ul class="payment-method-info">
                                    <li>Tiempo de validación del pago: 5-15 minutos hábiles.</li>
                                    <li>Envía el comprobante para acelerar la confirmación.</li>
                                </ul>
                            </div>
                        </div>
                    </form>
                </div>

                <aside class="checkout-summary">
                    <h2 class="checkout-summary-title">Resumen de compra</h2>
                    <div class="checkout-summary-body">
                        @foreach ($items as $item)
                            @php
                                $product = $item->product;
                                if (!$product) {
                                    continue;
                                }

                                $variant = $item->variant;
                                $image = $variant?->images->first() ?? $product->images->sortBy('order')->first();

                                $discountPercent = !is_null($product->discount)
                                    ? min(max((float) $product->discount, 0), 100)
                                    : 0;
                                $hasDiscount = $discountPercent > 0;

                                $basePrice =
                                    $variant && $variant->price && $variant->price > 0
                                        ? (float) $variant->price
                                        : (float) $product->price;
                                $discounted = $hasDiscount
                                    ? max($basePrice * (1 - $discountPercent / 100), 0)
                                    : $basePrice;

                                $lineTotal = $discounted * (int) $item->quantity;

                                $variantLabels = [];

                                if ($variant && $variant->features->isNotEmpty()) {
                                    foreach ($variant->features as $feature) {
                                        $option = $feature->option;
                                        $optionName = $option->name ?? ($option->slug ?? null);
                                        $label = $optionName ? $optionName . ': ' . $feature->value : $feature->value;
                                        $variantLabels[] = $label;
                                    }
                                }
                            @endphp

                            <article class="checkout-summary-item">
                                <div class="checkout-summary-thumb">
                                    @if ($image)
                                        <img src="{{ asset('storage/' . $image->path) }}"
                                            alt="{{ $image->alt ?? $product->name }}" loading="lazy">
                                    @else
                                        <div class="checkout-thumb-fallback">
                                            <i class="ri-image-line"></i>
                                        </div>
                                    @endif
                                </div>

                                <div class="checkout-summary-main">
                                    <div class="checkout-summary-name">{{ $product->name }}</div>
                                    <div class="checkout-summary-meta">
                                        <span
                                            class="checkout-summary-price">S/.{{ number_format($discounted, 2) }}</span>
                                        @if ($hasDiscount)
                                            <span
                                                class="checkout-summary-price-original">S/.{{ number_format($basePrice, 2) }}</span>
                                        @endif
                                        @if (!empty($variantLabels))
                                            <span class="checkout-summary-variant">
                                                {{ implode(' · ', $variantLabels) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="checkout-summary-qty">
                                    <span class="checkout-summary-qty-label">Cant.</span>
                                    <span class="checkout-summary-qty-value">x{{ $item->quantity }}</span>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="checkout-summary-footer">
                        <div class="checkout-summary-row">
                            <span>Total productos</span>
                            <span>{{ $itemsCount }}</span>
                        </div>
                        <div class="checkout-summary-row">
                            <span>Unidades totales</span>
                            <span>{{ $itemsQuantity }}</span>
                        </div>
                    </div>
                    <hr class="w-full my-0 border-default">
                    <div class="checkout-summary-footer">
                        <div class="checkout-summary-row">
                            <span>Subtotal</span>
                            <span>S/. {{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="checkout-summary-row">
                            <span>Precio de envío</span>
                            <span>S/. {{ number_format($shipping, 2) }}</span>
                        </div>
                        <div class="checkout-summary-row checkout-summary-row--total">
                            <span>Total a pagar</span>
                            <span>S/. {{ number_format($amount, 2) }}</span>
                        </div>
                    </div>

                    <div class="checkout-summary-actions">
                        <button type="button" id="payButton" class="boton-form boton-primary w-full py-3" onclick="VisanetCheckout.open()">
                            <span class="boton-form-icon"><i class="ri-wallet-3-fill"></i>
                            </span>
                            <span class="boton-form-text">Pagar ahora</span>
                        </button>
                    </div>
                </aside>
            </div>
        @endif
    </section>
    @push('js')
        <script type="text/javascript" src="{{ config('services.niubiz.url_js') }}"></script>
        </script>
        <script type="text/javascript">

            let merchantId = '{{ config('services.niubiz.merchant_id') }}';
            let purchaseNumber = Math.floor(Math.random() * 1000000000);
            let amount = '{{ number_format($amount, 2, '.', '') }}';
            let action = '{{ route('checkout.paid') }}?amount=' + amount + '&purchaseNumber=' + purchaseNumber;

            document.addEventListener('DOMContentLoaded', function() {
                VisanetCheckout.configure({
                    sessiontoken: '{{ $session_token }}',
                    channel: 'web',
                    merchantid: merchantId,
                    purchasenumber: purchaseNumber,
                    amount: amount,
                    expirationminutes: '20',
                    timeouturl: 'about:blank',
                    merchantlogo: 'img/comercio.png',
                    formbuttoncolor: '#000000',
                    action: action,
                    complete: function(params) {
                        alert(JSON.stringify(params));
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>
