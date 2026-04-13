<x-app-layout>
    @section('title', 'Carrito de compras')
    @include('partials.site.breadcrumb', [
        'items' => $breadcrumbItems ?? [['label' => 'Carrito de compras', 'icon' => 'ri-shopping-cart-fill']],
    ])

    @php
        $items = $cart?->items ?? collect();
        $hasItems = $items->isNotEmpty();
        $itemsCount = $items->count();
        $itemsQuantity = $items->sum('quantity');
        $subtotal = 0;
    @endphp

    <section class="site-container cart-page">
        <header class="cart-header">
            <div class="section-header">
                <h1 class="section-title">Mi carrito</h1>
                <p class="section-subtitle">Revisa tus productos antes de confirmar tu compra.</p>
            </div>
            @if ($hasItems)
                <div class="flex gap-1">
                    <div class="cart-header-summary">
                        <span class="cart-pill">
                            <i class="ri-shopping-cart-line"></i>
                            {{ $itemsQuantity }} {{ $itemsQuantity === 1 ? 'unidad' : 'unidades' }}
                        </span>
                    </div>
                    <div>
                        <form id="cart-clear-form" method="POST" action="{{ route('carts.destroy') }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="cart-pill cart-clear-btn" id="cart-clear-btn">
                                <i class="ri-close-large-line"></i>
                                Limpiar Carrito
                            </button>
                        </form>
                    </div>
                </div>
            @endif

        </header>

        @if (!Auth::check())
            <div class="card-empty">
                <div class="cart-empty-icon">
                    <i class="ri-user-3-line"></i>
                </div>
                <h2 class="card-title">Inicia sesión para ver tu carrito</h2>
                <p>
                    Para guardar y recuperar tus productos en el carrito, inicia sesión con tu cuenta.
                </p>
                <a href="{{ route('login') }}" class="boton-form boton-success py-3 px-5">
                    <span class="boton-form-icon"><i class="ri-login-box-line"></i></span>
                    <span class="boton-form-text">Iniciar sesión</span>
                </a>
            </div>
        @elseif (!$hasItems)
            <div class="card-empty">
                <div class="cart-empty-icon">
                    <i class="ri-shopping-cart-line"></i>
                </div>
                <h2 class="card-title">Tu carrito está vacío</h2>
                <p>
                    Aún no has agregado productos. Explora nuestro catálogo y empieza tu compra.
                </p>
                <a href="{{ route('site.home') }}" class="boton-form boton-success py-3 px-5">
                    <span class="boton-form-icon"><i class="ri-store-2-fill"></i></span>
                    <span class="boton-form-text">Ir a la tienda</span>
                </a>
            </div>
        @else
            <div class="cart-layout">
                <div class="cart-items">
                    @foreach ($items as $item)
                        @php
                            $product = $item->product;
                            if (!$product) {
                                continue;
                            }

                            $variant = $item->variant;
                            // Imagen priorizando variante si tiene, luego producto
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
                            $colorFeatures = [];

                            // Máxima cantidad permitida en el contador del carrito (por defecto amplio)
                            $maxQuantity = 999;

                            if ($variant && $variant->features->isNotEmpty()) {
                                foreach ($variant->features as $feature) {
                                    $option = $feature->option;

                                    // Opciones de color: se mostrarán como círculos de color
                                    if ($option && method_exists($option, 'isColor') && $option->isColor()) {
                                        $colorFeatures[] = $feature;
                                        continue;
                                    }

                                    $optionName = $option->name ?? ($option->slug ?? null);
                                    $label = $optionName ? $optionName . ': ' . $feature->value : $feature->value;
                                    $variantLabels[] = $label;
                                }

                                // Si la línea tiene variante con stock positivo, usamos ese stock como máximo
                                $variantStock = (int) $variant->stock;
                                if ($variantStock > 0) {
                                    $maxQuantity = $variantStock;
                                }
                            }
                        @endphp

                        <article class="cart-item">
                            <a href="{{ route('products.show', $product) }}" class="cart-item-thumb">
                                @if ($image)
                                    <img src="{{ asset('storage/' . $image->path) }}"
                                        alt="{{ $image->alt ?? $product->name }}" loading="lazy">
                                @else
                                    <div class="cart-thumb-fallback">
                                        <i class="ri-image-line"></i>
                                        <span>Imagen no disponible</span>
                                    </div>
                                @endif
                            </a>

                            <div class="cart-item-main">
                                <div class="cart-item-header">
                                    <div>
                                        <a href="{{ route('products.show', $product) }}" class="cart-item-name">
                                            {{ $product->name }}
                                        </a>
                                        <p class="cart-item-category">
                                            {{ $product->category?->name ?? 'Sin categoría' }}
                                        </p>
                                    </div>

                                    <div class="cart-item-price-block">
                                        <span class="cart-item-price-current">
                                            S/.{{ number_format($discounted, 2) }}
                                        </span>
                                        @if ($hasDiscount)
                                            <span class="cart-item-price-original">
                                                S/.{{ number_format($basePrice, 2) }}
                                            </span>
                                        @endif
                                        @if (!empty($variantLabels) || !empty($colorFeatures))
                                            <p class="cart-item-variant">
                                                @foreach ($colorFeatures as $feature)
                                                    @php
                                                        $rawHex = (string) ($feature->description ?? '');
                                                        $normalized = ltrim($rawHex, '#');
                                                        $displayColor = $normalized !== '' ? '#' . $normalized : '#000000';
                                                        $colorName = trim((string) ($feature->value ?? ''));
                                                    @endphp
                                                    <span class="cart-item-color-pill"
                                                        title="Color {{ $colorName !== '' ? $colorName : $displayColor }}">
                                                        <span class="cart-item-color-dot"
                                                            style="background-color: {{ $displayColor }};"></span>
                                                    </span>
                                                @endforeach

                                                @foreach ($variantLabels as $label)
                                                    <span class="cart-item-variant-pill">{{ $label }}</span>
                                                @endforeach
                                            </p>
                                        @endif
                                    </div>
                                    <form method="POST" action="{{ route('carts.items.update', $item) }}"
                                        class="cart-item-quantity-form">
                                        @csrf
                                        @method('PATCH')
                                        <div class="quantity-counter" data-quantity-root
                                            data-max-quantity="{{ $maxQuantity }}">
                                            <button class="quantity-btn quantity-btn--minus" type="button"
                                                data-quantity-decrement aria-label="Disminuir cantidad">
                                                <i class="ri-subtract-line"></i>
                                            </button>
                                            <div class="quantity-value" data-quantity-value>{{ $item->quantity }}
                                            </div>
                                            <button class="quantity-btn quantity-btn--plus" type="button"
                                                data-quantity-increment aria-label="Aumentar cantidad">
                                                <i class="ri-add-line"></i>
                                            </button>
                                            <input type="hidden" name="quantity" value="{{ $item->quantity }}"
                                                data-quantity-input>
                                        </div>
                                        <button type="submit" class="cart-item-quantity-submit">
                                            <i class="ri-refresh-line"></i>
                                            <span>Actualizar</span>
                                        </button>
                                    </form>
                                </div>

                                <div class="cart-item-meta">
                                    <form method="POST" action="{{ route('carts.items.destroy', $item) }}"
                                        class="cart-item-remove-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="cart-item-remove-btn"
                                            title="Eliminar del carrito" aria-label="Eliminar del carrito">
                                            <i class="ri-close-large-fill"></i>
                                        </button>
                                    </form>

                                    <div class="cart-item-line-total">
                                        <span class="cart-item-line-label">Subtotal</span>
                                        <span class="cart-item-line-value">
                                            S/.{{ number_format($lineTotal, 2) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <aside class="cart-summary">
                    <h2 class="cart-summary-title">Resumen del pedido</h2>
                    <dl class="cart-summary-list">
                        <div class="cart-summary-row">
                            <dt>Productos</dt>
                            <dd>{{ $itemsCount }}</dd>
                        </div>
                        <div class="cart-summary-row">
                            <dt>Unidades totales</dt>
                            <dd>{{ $itemsQuantity }}</dd>
                        </div>
                    </dl>

                    <p class="cart-summary-note">
                        Los precios y descuentos se confirmarán al momento de completar el pago.
                    </p>

                    <div class="cart-summary-actions">
                        <a href="{{ route('checkout.index') }}" class="site-btn site-btn-primary">
                            Continuar con la compra
                        </a>
                        <a href="{{ route('site.home') }}" class="site-btn site-btn-outline">
                            Seguir comprando
                        </a>
                    </div>
                </aside>
            </div>
        @endif
    </section>
    @push('js')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const clearBtn = document.getElementById('cart-clear-btn');
                const clearForm = document.getElementById('cart-clear-form');

                if (!clearBtn || !clearForm || typeof window.showConfirm !== 'function') {
                    return;
                }

                clearBtn.addEventListener('click', (event) => {
                    event.preventDefault();

                    window.showConfirm({
                        type: 'danger',
                        header: 'Vaciar carrito',
                        title: '¿Vaciar todo tu carrito?',
                        message: 'Se eliminarán todos los productos de tu carrito.<br>Esta acción no se puede deshacer.',
                        confirmText: 'Sí, vaciar carrito',
                        cancelText: 'No, mantener',
                        onConfirm: () => clearForm.submit(),
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>
