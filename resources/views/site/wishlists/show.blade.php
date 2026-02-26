<x-app-layout>
    <section class="site-container wishlist-page">
        <header class="wishlist-header">
            <div class="section-header">
                <h1 class="section-title">Mis favoritos</h1>
                <h5 class="section-subtitle">
                    Productos guardados para comprar más tarde.
                </h5>
            </div>
            @if ($wishlists->isNotEmpty())
                <span class="wishlist-count">{{ $wishlists->count() }} productos</span>
            @endif
        </header>

        @if ($wishlists->isEmpty())
            <div class="wishlist-empty">
                <i class="ri-heart-fill wishlist-empty-icon"></i>
                <h2 class="card-title">No tienes productos en tu lista de deseos</h2>
                <p>Explora el catálogo y guarda tus productos favoritos para verlos aquí.</p>
                <a href="{{ route('welcome.index') }}" class="boton-form boton-success py-3 px-5">
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
                                        <i class="ri-image-line"></i>
                                        <span>Imagen no disponible</span>
                                    </div>
                                @endif
                            </a>

                            <div class="wishlist-info">
                                <a href="{{ route('products.show', $product) }}" class="wishlist-product-name">
                                    {{ $product->name }}
                                </a>
                                <p class="wishlist-product-category">
                                    {{ $product->category?->name ?? 'Sin categoría' }}
                                </p>
                                <div class="wishlist-price">
                                    <span class="wishlist-price-current">
                                        S/.{{ number_format($discounted ?? $product->price, 2) }}
                                    </span>
                                </div>
                                <div class="wishlist-actions">
                                    <div class="wishlist-buttons">

                                        <form method="POST" action="{{ route('wishlists.destroy', $wishlist) }}"
                                            class="wishlist-delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="boton-form boton-danger"
                                                title="Eliminar de favoritos" aria-label="Eliminar de favoritos">
                                                <span class="boton-form-icon"><i
                                                        class="ri-delete-bin-2-fill"></i></span>
                                                <span class="boton-form-text">Eliminar</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        </article>
                    @endforeach
                </div>
            </div>
        @endif
    </section>
</x-app-layout>
