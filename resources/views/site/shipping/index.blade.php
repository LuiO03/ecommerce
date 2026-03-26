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
            ],
        ],
    ])

    @php
        $items = $cart?->items ?? collect();
        $hasItems = $items->isNotEmpty();
        $itemsCount = $items->count();
        $itemsQuantity = $items->sum('quantity');
        $subtotal = 0;
    @endphp

    <section class="site-container shipping-page">
        <div class="section-header">
            <h1>Direcciones de envío</h1>
            <p>
                Aquí puedes gestionar tus direcciones de envío y revisar el resumen de tu compra.
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
            <div class="shipping-layout">
                <div class="shipping-main">
                    @livewire('site.shipping-addresses')
                </div>

                <aside class="shipping-summary">
                    <h2 class="shipping-summary-title">Resumen de compra</h2>
                    <div class="shipping-summary-body">
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
                                $discounted = $hasDiscount ? max($basePrice * (1 - $discountPercent / 100), 0) : $basePrice;

                                $lineTotal = $discounted * (int) $item->quantity;
                                $subtotal += $lineTotal;

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

                            <article class="shipping-summary-item">
                                <div class="shipping-summary-thumb">
                                    @if ($image)
                                        <img src="{{ asset('storage/' . $image->path) }}"
                                            alt="{{ $image->alt ?? $product->name }}" loading="lazy">
                                    @else
                                        <div class="checkout-thumb-fallback">
                                            <i class="ri-image-line"></i>
                                        </div>
                                    @endif
                                </div>

                                <div class="shipping-summary-main">
                                    <div class="shipping-summary-name">{{ $product->name }}</div>
                                    <div class="shipping-summary-meta">
                                        <span class="shipping-summary-price">S/.{{ number_format($discounted, 2) }}</span>
                                        @if ($hasDiscount)
                                            <span class="shipping-summary-price-original">S/.{{ number_format($basePrice, 2) }}</span>
                                        @endif
                                        @if (!empty($variantLabels))
                                            <span class="shipping-summary-variant">
                                                {{ implode(' · ', $variantLabels) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="shipping-summary-qty">
                                    <span class="shipping-summary-qty-label">Cant.</span>
                                    <span class="shipping-summary-qty-value">x{{ $item->quantity }}</span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    <hr class="w-full my-0 border-default">
                    <div class="shipping-summary-footer">
                        <div class="shipping-summary-row">
                            <span>Total productos</span>
                            <span>{{ $itemsCount }}</span>
                        </div>
                        <div class="shipping-summary-row shipping-summary-row--total">
                            <span>Total a pagar</span>
                            <span>S/. {{ number_format($subtotal, 2) }}</span>
                        </div>
                    </div>
                    <div class="cart-summary-actions">
                        <a href=" {{ route('checkout.index') }}" class="site-btn site-btn-primary">
                            Continuar con el pago
                        </a>
                    </div>
                </aside>
            </div>
        @endif
    </section>
</x-app-layout>
