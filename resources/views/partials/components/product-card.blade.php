<div class="product-card">
    <a href="{{ route('products.show', $product) }}" class="product-image">
        @php
            $image = $product->mainImage?->path ?? $product->image_path;
            $alt = $product->mainImage?->alt ?? $product->name;
        @endphp
        @if ($image)
            <img src="{{ asset('storage/' . $image) }}" alt="{{ $alt }}"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">

            <div class="product-image-fallback" style="display:none;">
                <i class="ri-image-fill"></i>
                <span>Imagen no disponible</span>
            </div>
        @else
            <div class="product-image-fallback">
                <i class="ri-image-fill"></i>
                <span>Imagen no disponible</span>
            </div>
        @endif
    </a>

    <div class="product-details">
        <div class="flex justify-between">
            <p class="product-brand">{{ $product->brand?->name ?? 'Sin marca' }}
            </p>
            <p class="product-rating">
                <i class="ri-star-fill"></i>
                <span>4.5 (128)</span>
            </p>
        </div>
        <h3 class="product-name">{{ $product->name }}</h3>
        <div class="flex w-full flex-col">
            <div class="product-pricing">
                @if (!is_null($product->discount) && (float) $product->discount > 0)
                    @php
                        $discountPercent = min(max((float) $product->discount, 0), 100);
                        $discounted = max((float) $product->price * (1 - $discountPercent / 100), 0);
                    @endphp
                    <span class="product-price">S/.{{ number_format($discounted, 2) }}</span>
                    <span class="product-price-original">S/.{{ number_format($product->price, 2) }}</span>
                @else
                    <span class="product-price">S/.{{ number_format($product->price, 2) }}</span>
                @endif
            </div>
        </div>
    </div>
    <livewire:site.add-to-wishlist-card :product-id="$product->id" :key="'wishlist-card-' . $product->id" />
    @if ($product->discount)
        <span class="product-badge">-{{ number_format($product->discount, 0) }}%
            OFF</span>
    @endif
</div>
