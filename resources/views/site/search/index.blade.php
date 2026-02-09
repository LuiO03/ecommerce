<x-app-layout>
    <section class="site-container">
        <div class="products-header">
            <div>
                <h1>Resultados de busqueda</h1>
                <p class="products-count">
                    @if ($query !== '')
                        {{ $products->count() }} producto{{ $products->count() === 1 ? '' : 's' }} encontrados
                    @else
                        Ingresa un termino para buscar.
                    @endif
                </p>
            </div>
        </div>

        @if ($query !== '')
            @if ($products->isNotEmpty())
                <div class="products-grid">
                    @foreach ($products as $product)
                        <article class="product-card">
                            <div class="product-image">
                                @if ($product->mainImage)
                                    <img src="{{ asset('storage/' . $product->mainImage->path) }}"
                                        alt="{{ $product->mainImage->alt ?? $product->name }}"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="product-image-fallback" style="display: none;">
                                        <i class="ri-image-line"></i>
                                        <span>Imagen no disponible</span>
                                    </div>
                                @elseif ($product->image_path)
                                    <img src="{{ asset('storage/' . $product->image_path) }}"
                                        alt="{{ $product->name }}"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="product-image-fallback" style="display: none;">
                                        <i class="ri-image-line"></i>
                                        <span>Imagen no disponible</span>
                                    </div>
                                @else
                                    <div class="product-image-fallback">
                                        <i class="ri-image-line"></i>
                                        <span>Imagen no disponible</span>
                                    </div>
                                @endif

                                @if (!is_null($product->discount) && (float) $product->discount > 0)
                                    <span class="product-badge">-{{ number_format($product->discount, 0) }}% OFF</span>
                                @endif
                            </div>

                            <div class="product-details">
                                <div class="product-content">
                                    <p class="product-brand">{{ $product->category?->name ?? 'Sin categoria' }}</p>
                                    <h3 class="product-name">{{ $product->name }}</h3>
                                    <div class="flex w-full flex-col">
                                        <div class="product-pricing">
                                            @if (!is_null($product->discount) && (float) $product->discount > 0)
                                                @php
                                                    $discountPercent = min(max((float) $product->discount, 0), 100);
                                                    $discounted = max((float) $product->price * (1 - ($discountPercent / 100)), 0);
                                                @endphp
                                                <span class="product-price">S/.{{ number_format($discounted, 2) }}</span>
                                                <span class="product-price-original">S/.{{ number_format($product->price, 2) }}</span>
                                            @else
                                                <span class="product-price">S/.{{ number_format($product->price, 2) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="product-footer">
                                    <a href="#" class="product-btn product-btn-primary" aria-label="Ver detalles del producto">
                                        <i class="ri-eye-line"></i>
                                        <span>Ver</span>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="filters-empty">
                    <i class="ri-box-3-line"></i>
                    <span>No hay productos para esta busqueda.</span>
                </div>
            @endif

            @if ($categories->isNotEmpty())
                <div class="site-search-categories">
                    <h2>Categorias relacionadas</h2>
                    <div class="site-search-category-list">
                        @foreach ($categories as $category)
                            <div class="site-search-category">
                                <span>{{ $category->name }}</span>
                                <span class="site-search-category-family">{{ $category->family?->name ?? 'Sin familia' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </section>
</x-app-layout>
