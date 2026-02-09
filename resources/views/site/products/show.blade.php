<x-app-layout>
    @push('css')
        @vite(['resources/css/site/modules/product-details.css'])
    @endpush

    @include('partials.site.breadcrumb', [
        'items' => $breadcrumbItems,
    ])

    @php
        $images = $product->images->sortBy('order')->values();
        $mainImage = $images->firstWhere('is_main', true) ?? $images->first();
        $discountPercent = !is_null($product->discount) ? min(max((float) $product->discount, 0), 100) : 0;
        $hasDiscount = $discountPercent > 0;
        $discounted = $hasDiscount
            ? max((float) $product->price * (1 - ($discountPercent / 100)), 0)
            : (float) $product->price;
    @endphp

    <section class="site-container product-detail">
        <div class="product-detail-hero" data-product-gallery>
            <div class="product-media">
                <div class="product-gallery">
                    @if ($images->isNotEmpty())
                        <div class="product-gallery-thumbs" role="list">
                            @foreach ($images as $index => $image)
                                <button class="product-thumb" type="button" aria-label="Ver imagen"
                                    data-index="{{ $index }}">
                                    <img src="{{ asset('storage/' . $image->path) }}"
                                        alt="{{ $image->alt ?? $product->name }}"
                                        loading="lazy">
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <div class="product-gallery-main swiper">
                        <div class="swiper-wrapper">
                            @forelse ($images as $image)
                                <div class="swiper-slide product-gallery-slide">
                                    <img src="{{ asset('storage/' . $image->path) }}"
                                        alt="{{ $image->alt ?? $product->name }}"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="product-media-fallback" style="display: none;">
                                        <i class="ri-image-line"></i>
                                        <span>Imagen no disponible</span>
                                    </div>
                                </div>
                            @empty
                                <div class="product-gallery-slide product-media-fallback">
                                    <i class="ri-image-line"></i>
                                    <span>Imagen no disponible</span>
                                </div>
                            @endforelse
                        </div>

                        <button class="gallery-nav gallery-prev" type="button" aria-label="Anterior">
                            <i class="ri-arrow-left-s-line"></i>
                        </button>
                        <button class="gallery-nav gallery-next" type="button" aria-label="Siguiente">
                            <i class="ri-arrow-right-s-line"></i>
                        </button>

                        @if ($hasDiscount)
                            <span class="product-discount-badge">-{{ number_format($discountPercent, 0) }}%</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="product-summary" data-variant-root data-base-price="{{ $product->price }}"
                data-discount="{{ $discountPercent }}">
                <div class="product-summary-header">
                    <span class="product-category">
                        <i class="ri-price-tag-3-line"></i>
                        {{ $product->category?->name ?? 'Sin categoria' }}
                    </span>
                    <h1>{{ $product->name }}</h1>
                    <p class="product-sku">SKU: {{ $product->sku }}</p>
                </div>

                <div class="product-price">
                    <span class="product-price-current" data-price-current>
                        S/.{{ number_format($discounted, 2) }}
                    </span>
                    @if ($hasDiscount)
                        <span class="product-price-original" data-price-original>
                            S/.{{ number_format($product->price, 2) }}
                        </span>
                    @endif
                </div>

                <div class="product-meta">
                    <span class="product-status {{ $product->status ? 'is-active' : 'is-inactive' }}">
                        <i class="ri-circle-fill"></i>
                        {{ $product->status ? 'Disponible' : 'No disponible' }}
                    </span>
                    <span class="product-family">
                        <i class="ri-folder-3-line"></i>
                        {{ $product->category?->family?->name ?? 'Sin familia' }}
                    </span>
                    <span class="product-stock" data-stock>
                        <i class="ri-stack-line"></i>
                        Stock disponible
                    </span>
                </div>

                @if ($variantOptions->isNotEmpty())
                    <div class="product-variants">
                        <h2>Variantes disponibles</h2>
                        <p class="variant-helper" data-variant-helper>
                            Selecciona una opcion para ver disponibilidad y precio.
                        </p>
                        @foreach ($variantOptions as $option)
                            <div class="variant-group" data-option-id="{{ $option['option_id'] }}"
                                data-option-slug="{{ $option['slug'] }}">
                                <span class="variant-label">{{ $option['name'] }}</span>
                                <div class="variant-values">
                                    @foreach ($option['features'] as $feature)
                                        <button type="button"
                                            class="variant-value {{ $option['is_color'] ? 'is-color' : 'is-size' }}"
                                            data-feature-id="{{ $feature['id'] }}" aria-pressed="false">
                                            @if ($option['is_color'])
                                                <span class="variant-swatch"
                                                    style="--swatch-color: {{ $feature['value'] }}"></span>
                                                <span class="variant-name">
                                                    {{ $feature['description'] ?? $feature['value'] }}
                                                </span>
                                            @else
                                                <span class="variant-size">{{ $feature['value'] }}</span>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="product-actions">
                    <button class="product-action-btn product-action-primary" type="button">
                        <i class="ri-shopping-cart-2-line"></i>
                        <span>Agregar al carrito</span>
                    </button>
                    <button class="product-action-btn" type="button" aria-label="Agregar a favoritos">
                        <i class="ri-heart-line"></i>
                        <span>Favoritos</span>
                    </button>
                </div>

                <div class="product-description">
                    <h2>Descripcion</h2>
                    <p>{{ $product->description ?? 'Este producto no tiene descripcion adicional.' }}</p>
                </div>
            </div>
        </div>

        <div class="product-detail-footer">
            <a class="product-back-link" href="{{ route('categories.show', $product->category) }}">
                <i class="ri-arrow-left-line"></i>
                <span>Volver a la categoria</span>
            </a>
        </div>
    </section>

    @if ($variantsPayload->isNotEmpty())
        <script type="application/json" id="product-variants-data">
            @json($variantsPayload)
        </script>
    @endif
</x-app-layout>
