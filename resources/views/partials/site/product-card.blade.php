<div class="product-card">
    <a href="{{ route('products.show', $product) }}" class="product-image">
        @php
            $image = $product->mainImage?->path ?? $product->image_path;
            $alt = $product->mainImage?->alt ?? $product->name;
        @endphp
        @if ($image)
            <img src="{{ asset('storage/' . $image) }}" alt="{{ $alt }}"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">

            <div class="product-card-image-fallback" style="display:none;">
                <i class="ri-image-fill"></i>
                <span>Imagen no disponible</span>
            </div>
        @else
            <div class="product-card-image-fallback">
                <i class="ri-image-fill"></i>
                <span>Imagen no disponible</span>
            </div>
        @endif
    </a>

    <div class="product-card-details">
        <div class="flex justify-between">
            <p class="product-brand">{{ $product->brand?->name ?? 'Sin marca' }}
            </p>
            <p class="product-card-rating">
                <i class="ri-star-fill"></i>
                <span>{{ number_format($product->rating_avg, 1) }}</span>
            </p>
        </div>

        @foreach ($product->variantOptions as $option)
            <div class="product-card-variant-group" data-option-id="{{ $option->option_id }}" data-option-slug="{{ $option->slug }}">
                <div class="product-card-variant-values">
                    @foreach ($option->features as $feature)
                        @if ($option->is_color)
                            <span class="product-card-variant-value {{ $option->is_color ? 'is-color' : 'is-size' }}"
                                title="{{ $feature->value }}">
                                <span class="variant-swatch"
                                    style="background-color: {{ $feature->description }}"></span>
                            </span>
                        @else
                            <span class="product-card-variant-value {{ $option->is_color ? 'is-color' : 'is-size' }}"
                                title="{{ $feature->description ?? $feature->value }}">
                                <span class="variant-size">{{ $feature->value }}</span>
                            </span>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach

        <h3 class="product-card-name">{{ $product->name }}</h3>
        <div class="flex w-full flex-col">
            <div class="product-card-pricing">
                @if (!is_null($product->discount) && (float) $product->discount > 0)
                    @php
                        $discountPercent = min(max((float) $product->discount, 0), 100);
                        $discounted = max((float) $product->price * (1 - $discountPercent / 100), 0);
                    @endphp
                    <span class="product-card-price">S/.{{ number_format($discounted, 2) }}</span>
                    <span class="product-card-price-original">S/.{{ number_format($product->price, 2) }}</span>
                @else
                    <span class="product-card-price">S/.{{ number_format($product->price, 2) }}</span>
                @endif
            </div>
        </div>
    </div>
    <livewire:site.add-to-wishlist-card :product-id="$product->id" :key="'wishlist-card-' . $product->id" />
    @if ($product->discount)
        <span class="product-card-badge">-{{ number_format($product->discount, 0) }}%
            OFF</span>
    @endif
</div>
