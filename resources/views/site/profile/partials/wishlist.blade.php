<div class="profile-section">
    <div class="card-header">
        <span class="card-title">Mis favoritos</span>
        <p class="card-description">Productos que te interesan para comprar más adelante.</p>
    </div>

    @if(!isset($wishlistItems) || $wishlistItems->isEmpty())
        <div class="card-empty">
            <div class="wishlist-empty-icon">
                <i class="ri-heart-3-line"></i>
            </div>
            <h3 class="card-title">No tienes productos en favoritos</h3>
            <p>Agrega productos a tu lista de deseos para verlos aquí.</p>
            <a href="{{ route('welcome.index') }}" class="boton-form boton-success py-3 px-5">
                <span class="boton-form-icon"><i class="ri-store-2-fill"></i></span>
                <span class="boton-form-text">Ir a la tienda</span>
            </a>
        </div>
    @else
        <div class="profile-grid wishlist-grid-compact">
            @foreach ($wishlistItems as $wishlist)
                @php
                    $product = $wishlist->product;
                    if (! $product) {
                        continue;
                    }
                    $image = $product->images->sortBy('order')->first();
                    $discountPercent = ! is_null($product->discount)
                        ? min(max((float) $product->discount, 0), 100)
                        : 0;
                    $hasDiscount = $discountPercent > 0;
                    $discounted = $hasDiscount
                        ? max((float) $product->price * (1 - $discountPercent / 100), 0)
                        : (float) $product->price;
                @endphp

                <article class="wishlist-compact-card">
                    <a href="{{ route('products.show', $product) }}" class="wishlist-compact-thumb">
                        @if ($image)
                            <img src="{{ asset('storage/' . $image->path) }}" alt="{{ $image->alt ?? $product->name }}" loading="lazy">
                        @else
                            <div class="wishlist-thumb-fallback">
                                <i class="ri-image-line"></i>
                            </div>
                        @endif
                    </a>
                    <div class="wishlist-compact-body">
                        <a href="{{ route('products.show', $product) }}" class="wishlist-compact-title">
                            {{ $product->name }}
                        </a>
                        <div class="wishlist-compact-price">
                            <span class="wishlist-price-current">S/.{{ number_format($discounted, 2) }}</span>
                        </div>
                    </div>
                </article>

            @endforeach
        </div>
    @endif
</div>
