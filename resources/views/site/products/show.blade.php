<x-app-layout>
    @section('title', $product->name)
    @include('partials.site.breadcrumb', [
        'items' => $breadcrumbItems,
    ])

    @php
        $images = $product->images->sortBy('order')->values();
        $mainImage = $images->firstWhere('is_main', true) ?? $images->first();
        $discountPercent = !is_null($product->discount) ? min(max((float) $product->discount, 0), 100) : 0;
        $hasDiscount = $discountPercent > 0;
        $discounted = $hasDiscount
            ? max((float) $product->price * (1 - $discountPercent / 100), 0)
            : (float) $product->price;
    @endphp

    <section class="site-container product-detail">
        <div class="product-detail-hero" data-product-gallery>
            <div class="product-media">
                <div class="@if ($images->isNotEmpty()) product-gallery @else product-gallery-empty @endif">

                    @if ($images->isNotEmpty())
                        <div class="product-gallery-thumbs" role="list">
                            @foreach ($images as $index => $image)
                                <button class="product-thumb" type="button" aria-label="Ver imagen"
                                    data-index="{{ $index }}">
                                    <img src="{{ asset('storage/' . $image->path) }}"
                                        alt="{{ $image->alt ?? $product->name }}" loading="lazy">
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <div class="product-gallery-main">
                        <div class="product-gallery-track">
                            @forelse ($images as $image)
                                <div class="product-gallery-slide">
                                    <img src="{{ asset('storage/' . $image->path) }}"
                                        alt="{{ $image->alt ?? $product->name }}"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="product-media-fallback" style="display: none;">
                                        <i class="ri-image-fill"></i>
                                        <span>Imagen no disponible</span>
                                    </div>
                                </div>
                            @empty
                                <div class="product-gallery-slide product-media-fallback">
                                    <i class="ri-image-fill"></i>
                                    <span>Imagen no disponible</span>
                                </div>
                            @endforelse
                        </div>

                    </div>

                    @if ($images->isNotEmpty())
                        <div class="product-gallery-controls" aria-label="Controles de galeria">
                            <button class="gallery-action gallery-expand" type="button" aria-label="Agrandar imagen"
                                title="Agrandar imagen" data-gallery-expand>
                                <i class="ri-fullscreen-line"></i>
                            </button>

                            @if ($images->count() > 1)
                                <div class="gallery-nav-stack">
                                    <button class="gallery-nav gallery-prev" type="button" aria-label="Anterior">
                                        <i class="ri-arrow-left-s-line"></i>
                                    </button>
                                    <button class="gallery-nav gallery-next" type="button" aria-label="Siguiente">
                                        <i class="ri-arrow-right-s-line"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="product-summary" data-variant-root data-base-price="{{ $product->price }}"
                data-discount="{{ $discountPercent }}" data-has-variants="{{ $hasActiveVariants ? '1' : '0' }}"
                data-has-available-variants="{{ $hasAvailableVariants ? '1' : '0' }}">
                <div class="product-summary-header">
                    <a href="{{ route('brands.show', $product->brand) }}" class="product-brand">
                        {{ $product->brand?->name ?? 'Sin marca' }}
                    </a>
                    <h1 style="line-height: 1;">{{ $product->name }}</h1>
                    <p class="product-sku">SKU: {{ $product->sku }}</p>
                </div>
                <div class="product-price">
                    @if ($hasDiscount)
                        <span class="product-price-original" data-price-original>
                            S/.{{ number_format($product->price, 2) }}
                        </span>
                    @endif
                    <span class="product-price-current" data-price-current>
                        S/.{{ number_format($discounted, 2) }}
                    </span>
                    @if ($hasDiscount)
                        <span class="product-discount-badge">-{{ number_format($discountPercent, 0) }}% OFF</span>
                    @endif
                </div>
                <hr class="w-full my-0 border-default">
                <div class="product-status-stock">
                    <div class="product-meta">
                        <span class="product-status {{ $product->status ? 'is-active' : 'is-inactive' }}">
                            <i class="ri-circle-fill"></i>
                            {{ $product->status ? 'Producto Disponible' : 'Producto No Disponible' }}
                        </span>
                    </div>
                    <span class="product-stock" data-stock>
                        <i class="ri-stack-line"></i>
                        @if (!$hasActiveVariants)
                            Sin stock disponible
                        @elseif($hasActiveVariants && !$hasAvailableVariants)
                            Sin stock disponible
                        @elseif($hasActiveVariants)
                            Selecciona una opcion para ver stock
                        @else
                            Stock disponible
                        @endif
                    </span>
                </div>
                @if ($variantOptions->isNotEmpty())
                    <div class="product-variants">
                        @foreach ($variantOptions as $option)
                            <div class="variant-group" data-option-id="{{ $option->option_id }}"
                                data-option-slug="{{ $option->slug }}">
                                <h4 class="subtitle-variant-product">
                                    {{ $option->name }}:
                                    <span class="subtitle-variant-selected"></span>
                                </h4>
                                <div class="variant-values">
                                    @foreach ($option->features as $feature)
                                        @if ($option->is_color)
                                            <button type="button"
                                                class="variant-value {{ $option->is_color ? 'is-color' : 'is-size' }}"
                                                data-feature-id="{{ $feature->id }}" aria-pressed="false"
                                                title="{{ $feature->value }}"
                                                aria-label="{{ $feature->value }}{{ $feature->description ? ' (' . $feature->description . ')' : '' }}">
                                                <span class="variant-swatch"
                                                    style="background-color: {{ $feature->description }}"></span>
                                            </button>
                                        @else
                                            <button type="button"
                                                class="variant-value {{ $option->is_color ? 'is-color' : 'is-size' }}"
                                                data-feature-id="{{ $feature->id }}" aria-pressed="false"
                                                title="{{ $feature->description ?? $feature->value }}"
                                                aria-label="{{ $feature->description ?? $feature->value }}">
                                                <span class="variant-size">{{ $feature->value }}</span>
                                            </button>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif


                <div class="product-actions">
                    <div class="quantity-counter" data-quantity-root>
                        <button class="quantity-btn quantity-btn--minus" type="button" data-quantity-decrement
                            aria-label="Disminuir cantidad">
                            <i class="ri-subtract-line"></i>
                        </button>
                        <div class="quantity-value" data-quantity-value>1</div>
                        <button class="quantity-btn quantity-btn--plus" type="button" data-quantity-increment
                            aria-label="Aumentar cantidad">
                            <i class="ri-add-line"></i>
                        </button>
                    </div>
                    <div class="product-action-buttons">
                        @livewire('site.add-to-cart', [
                            'productId' => $product->id,
                        ])

                        @livewire('site.add-to-wishlist', [
                            'productId' => $product->id,
                        ])
                    </div>
                </div>



                <div class="product-description">
                    <h3 class="subtitle-variant-product">Descripción</h3>
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

        @if ($variantsPayload->isNotEmpty())
            <script type="application/json" id="product-variants-data">
            @json($variantsPayload)
        </script>
        @endif
    </section>
    {{-- Similares (en show.blade.php, por ejemplo) --}}
    <livewire:site.product-list :category-id="$product->category_id" :exclude-id="$product->id" :limit="12" title="Productos similares" :scope="'family_fallback'" :strict="true"
        subtitle="Te pueden interesar estos productos de la misma categoría {{ $product->category->name }}" />

    <livewire:site.product-list :limit="12" order-by="cheap" title="¡Los más baratos!"
        subtitle="Aprovecha los mejores precios" />
</x-app-layout>
