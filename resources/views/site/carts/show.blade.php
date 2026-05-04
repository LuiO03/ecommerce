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
    @endphp

    <section class="site-container cart-page">

        @if (!Auth::check())
            <div class="card-empty">
                <div class="card-empty-icon card-purple">
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
                <div class="card-empty-icon card-purple">
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

                <div class="cart-container">
                    <header class="cart-header">
                        <div class="section-header">
                            <h1 class="section-title">Mi carrito</h1>
                            <p class="section-subtitle">Revisa tus productos antes de confirmar tu compra.</p>
                        </div>
                        @if ($hasItems)
                            <div class="flex gap-1">
                                <span class="cart-pill">
                                    <i class="ri-shopping-cart-line"></i>
                                    {{ $itemsQuantity }} {{ $itemsQuantity === 1 ? 'unidad' : 'unidades' }}
                                </span>
                                <form id="cart-clear-form" method="POST" action="{{ route('carts.destroy') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="cart-pill cart-clear-btn" id="cart-clear-btn">
                                        <i class="ri-close-large-line"></i>
                                        Limpiar Carrito
                                    </button>
                                </form>
                            </div>
                        @endif

                    </header>
                    <div class="cart-items">
                        @foreach ($items as $item)
                            @php
                                $product = $item->product;
                                if (!$product) {
                                    continue;
                                }

                                $image = $item->getImage();
                                $discountPercent = $item->getDiscountPercent();
                                $hasDiscount = $item->hasDiscount();
                                $basePrice = $item->getBasePrice();
                                $discounted = $item->getDiscountedPrice();
                                $lineTotal = $item->getLineTotal();
                                $maxQuantity = $item->getMaxQuantity();
                                $variantLabels = $item->getVariantLabels();
                                $colorFeatures = $item->getColorFeatures();
                            @endphp

                            <article class="cart-item">

                                {{-- Imagen --}}
                                <a href="{{ route('products.show', $product) }}" class="cart-item-thumb">
                                    @if ($image)
                                        <img src="{{ asset('storage/' . $image->path) }}"
                                            alt="{{ $image->alt ?? $product->name }}" loading="lazy">
                                    @else
                                        <div class="cart-thumb-fallback">
                                            <i class="ri-image-fill"></i>
                                            <span>Sin imagen</span>
                                        </div>
                                    @endif
                                </a>

                                {{-- Contenido --}}
                                <div class="cart-item-content">

                                    {{-- TOP: info + precio --}}
                                    <div class="cart-item-top">
                                        <div class="cart-item-info">
                                            <a href="{{ route('products.show', $product) }}" class="cart-item-name">
                                                {{ $product->name }}
                                            </a>

                                            <p class="cart-item-brand">
                                                {{ $product->brand?->name ?? 'Sin marca' }} |
                                                {{ $product->category?->name ?? 'Sin categoría' }}
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Variantes --}}
                                    <div class="cart-item-center">
                                        @if (!empty($variantLabels) || !empty($colorFeatures))
                                            <div class="cart-item-variants">

                                                @foreach ($colorFeatures as $feature)
                                                    @php
                                                        $color = '#' . ltrim((string) $feature->description, '#');
                                                        $name = $feature->value ?? $color;
                                                    @endphp

                                                    <span class="variant-pill" title="{{ $name }}">
                                                        <span class="dot"
                                                            style="background: {{ $color }}"></span>
                                                        {{ $name }}
                                                    </span>
                                                @endforeach

                                                @foreach ($variantLabels as $label)
                                                    <span class="variant-pill">{{ $label }}</span>
                                                @endforeach

                                            </div>
                                        @endif
                                        {{-- Cantidad --}}
                                        <form method="POST" action="{{ route('carts.items.update', $item) }}"
                                            class="cart-qty-form">
                                            @csrf
                                            @method('PATCH')

                                            <div class="qty-control" data-max="{{ $maxQuantity }}">
                                                <button type="button" class="qty-btn" data-dec>-</button>

                                                <span class="qty-value" data-value>
                                                    {{ $item->quantity }}
                                                </span>

                                                <button type="button" class="qty-btn" data-inc>+</button>

                                                <input type="hidden" name="quantity" value="{{ $item->quantity }}">
                                            </div>
                                        </form>

                                        {{-- Eliminar --}}
                                        <form method="POST" action="{{ route('carts.items.destroy', $item) }}">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn-remove" title="Eliminar producto">
                                                <i class="ri-close-line"></i> Eliminar
                                            </button>
                                        </form>
                                    </div>

                                    {{-- ACCIONES --}}
                                    <div class="cart-item-actions">
                                        {{-- Precio --}}
                                        <div class="cart-item-price">
                                            <span class="price-current">
                                                S/.{{ number_format($discounted, 2) }}
                                            </span>

                                            @if ($hasDiscount)
                                                <span class="price-old">
                                                    S/.{{ number_format($basePrice, 2) }}
                                                </span>
                                            @endif
                                        </div>
                                        {{-- Subtotal --}}
                                        <div class="cart-item-subtotal">
                                            <span>Subtotal: </span>
                                            <strong>S/.{{ number_format($lineTotal, 2) }}</strong>
                                        </div>



                                    </div>

                                </div>
                            </article>

                            <hr class="w-full my-0 border-default">
                        @endforeach
                    </div>
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
