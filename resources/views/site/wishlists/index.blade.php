<x-app-layout>
    @section('title', 'Mis favoritos')
    @include('partials.site.breadcrumb', [
        'items' => $breadcrumbItems ?? [['label' => 'Mis favoritos', 'icon' => 'ri-heart-fill']],
    ])
    <section class="site-container wishlist-page">
        <header class="wishlist-header">
            <div class="section-header">
                <h1 class="section-title">Mis favoritos</h1>
                <p>
                    Productos guardados para comprar más tarde.
                </p>
            </div>
            @if ($wishlists->isNotEmpty())
                <div class="flex gap-1">
                    <div class="cart-header-summary">
                        <span class="cart-pill">
                            <i class="ri-heart-line"></i>
                            {{ $wishlists->count() }} {{ $wishlists->count() === 1 ? 'producto' : 'productos' }}
                        </span>
                    </div>
                    <div>
                        <form id="wishlist-clear-form" method="POST" action="{{ route('wishlists.clear') }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="cart-pill cart-clear-btn" id="wishlist-clear-btn">
                                <i class="ri-close-large-line"></i>
                                Limpiar lista de deseos
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </header>

        @if (!Auth::check())
            <div class="card-empty">
                <div class="card-empty-icon card-pink">
                    <i class="ri-user-3-line"></i>
                </div>
                <h2 class="card-title">Inicia sesión para ver tu lista de deseos</h2>
                <p>
                    Inicia sesión para guardar tus productos favoritos y acceder a ellos en cualquier momento.
                </p>
                <a href="{{ route('login') }}" class="boton-form boton-success py-3 px-5">
                    <span class="boton-form-icon"><i class="ri-login-box-line"></i></span>
                    <span class="boton-form-text">Iniciar sesión</span>
                </a>
            </div>
        @elseif($wishlists->isEmpty())
            <div class="card-empty">
                <div class="card-empty-icon card-pink">
                    <i class="ri-heart-fill "></i>
                </div>
                <h2 class="card-title">No tienes productos en tu lista de deseos</h2>
                <p>Explora el catálogo y guarda tus productos favoritos para verlos aquí.</p>
                <a href="{{ route('site.home') }}" class="boton-form boton-success py-3 px-5">
                    <span class="boton-form-icon"><i class="ri-store-2-fill"></i></span>
                    <span class="boton-form-text">Ir a la tienda</span>
                </a>
            </div>
        @else
            <div class="wishlist-layout">
                <div class="wishlist-items">
                    @foreach ($wishlists as $wishlist)
                        @php
                            $product = $wishlist->product;
                            if (!$product) {
                                continue;
                            }
                            $image = $product->images->sortBy('order')->first();
                            $discountPercent = !is_null($product->discount)
                                ? min(max((float) $product->discount, 0), 100)
                                : 0;
                            $hasDiscount = $discountPercent > 0;
                            $discounted = $hasDiscount
                                ? max((float) $product->price * (1 - $discountPercent / 100), 0)
                                : (float) $product->price;
                        @endphp

                        <article class="wishlist-item">
                            <a href="{{ route('products.show', $product) }}" class="wishlist-thumb">
                                @if ($image)
                                    <img src="{{ asset('storage/' . $image->path) }}"
                                        alt="{{ $image->alt ?? $product->name }}" loading="lazy">
                                @else
                                    <div class="wishlist-thumb-fallback">
                                        <i class="ri-image-fill"></i>
                                        <span>Imagen no disponible</span>
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('wishlists.destroy', $wishlist) }}"
                                    class="card-delete-btn">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Eliminar de favoritos"
                                        aria-label="Eliminar de favoritos">
                                        <i class="ri-close-large-fill"></i>
                                    </button>
                                </form>
                            </a>

                            <div class="wishlist-info">
                                <p class="wishlist-product-category">
                                    {{ $product->brand?->name ?? 'Sin marca' }}
                                </p>
                                <a href="{{ route('products.show', $product) }}" class="wishlist-product-name">
                                    {{ $product->name }}
                                </a>
                                <div class="wishlist-price">
                                    <div class="cart-item-price">
                                        <span class="price-current">
                                            S/.{{ number_format($discounted ?? $product->price, 2) }}
                                        </span>

                                        @if ($hasDiscount)
                                            <span class="price-old">
                                                S/.{{ number_format($product->price, 2) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                        </article>
                    @endforeach
                </div>
            </div>
        @endif
    </section>
    @push('js')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const clearBtn = document.getElementById('wishlist-clear-btn');
                const clearForm = document.getElementById('wishlist-clear-form');

                if (!clearBtn || !clearForm || typeof window.showConfirm !== 'function') {
                    return;
                }

                clearBtn.addEventListener('click', (event) => {
                    event.preventDefault();

                    window.showConfirm({
                        type: 'danger',
                        header: 'Vaciar lista de deseos',
                        title: '¿Vaciar toda tu lista de deseos?',
                        message: 'Se eliminarán todos los productos de tu lista de deseos.<br>Esta acción no se puede deshacer.',
                        confirmText: 'Sí, vaciar lista',
                        cancelText: 'No, mantener',
                        onConfirm: () => clearForm.submit(),
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>
