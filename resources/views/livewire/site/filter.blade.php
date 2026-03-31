<div class="site-container">
    <div class="products-layout">
        <aside class="products-sidebar">
            <section class="filters" aria-label="Filtros de productos">
                <div class="filters-header">
                    <div class="filters-title">
                        <i class="ri-filter-2-line"></i>
                        <span>Filtrar productos</span>
                    </div>
                    <button type="button" class="filters-clear" aria-label="Limpiar filtros" wire:click="clearFilters">
                        <i class="ri-refresh-line"></i>
                        <span>Limpiar</span>
                    </button>
                </div>

                <div class="filters-body">
                    @if (!empty($subcategories))
                        <section class="filters-subcategories" aria-label="Subcategorias">
                            <span class="filters-subcategories-title">Subcategorias</span>
                            <div class="filters-subcategories-chips">
                                @foreach ($subcategories as $subcategory)
                                    <a class="filter-chip" href="{{ route('categories.show', $subcategory) }}">
                                        <span>{{ $subcategory->name }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    @else
                        <div class="filters-empty">
                            <i class="ri-information-line"></i>
                            <span>No hay subcategorias para esta categoria.</span>
                        </div>
                    @endif

                    @forelse ($options as $option)
                        @php
                            $isColor = method_exists($option, 'isColor') && $option->isColor();
                        @endphp

                        <details class="filter-group" open>
                            <summary class="filter-group-title">
                                <span>{{ $option->name }}</span>
                                <i class="ri-arrow-down-s-line"></i>
                            </summary>
                            <div class="filter-group-content">
                                @forelse ($option->features as $feature)
                                    <label class="filter-item" wire:key="feature-{{ $feature->id }}">
                                        <span class="filter-facet-left">
                                            <input class="filter-facet-input" type="checkbox"
                                                value="{{ $feature->id }}" name="selectedFeatures[]"
                                                wire:model="selectedFeatures" />
                                            <span class="filter-facet-box" aria-hidden="true">
                                                <i class="ri-check-line"></i>
                                            </span>
                                            @if ($isColor)
                                                <span class="filter-facet-swatch"
                                                    style="--facet-color: {{ $feature->description }}"
                                                    title="{{ $feature->value }} ({{ $feature->description }})"
                                                    aria-label="{{ $feature->value }} ({{ $feature->description }})"></span>
                                                <span class="filter-facet-name">{{ $feature->value }}</span>
                                            @else
                                                <span class="filter-facet-size">{{ $feature->value }}</span>
                                            @endif
                                        </span>
                                        <span
                                            class="filter-facet-count">({{ $featureCounts[$feature->id] ?? 0 }})</span>
                                    </label>
                                @empty
                                    <div class="filter-empty">Sin opciones disponibles</div>
                                @endforelse
                            </div>
                        </details>
                    @empty
                        <div class="filters-empty">
                            <i class="ri-information-line"></i>
                            <span>No hay filtros para esta familia.</span>
                        </div>
                    @endforelse
                </div>

                <div class="filters-footer">
                    <button type="button" class="filters-apply" wire:click="applyFilters" wire:loading.attr="disabled">
                        <i class="ri-equalizer-line"></i>
                        <span wire:loading.remove>Aplicar filtros</span>
                        <span wire:loading>Aplicando...</span>
                    </button>
                </div>
            </section>
        </aside>

        <main class="products-main">
            <div class="mb-3">
                @if ($category)
                    <h1>{{ $family?->name }}</h1>
                    <h2>{{ $category->name }}</h2>
                    <p>{{ $category->description }}</p>
                @elseif ($family)
                    <h1>{{ $family->name }}</h1>
                    <p>{{ $family->description }}</p>
                @else
                    <h1>Todos los productos</h1>
                @endif
            </div>

            <div class="products-header">

                <div class="site-select">
                    <div class="site-select-trigger">
                        <i class="ri-sort-asc site-select-icon"></i>
                        <span>
                            @switch($sortBy)
                                @case('price-asc')
                                    Precio: Menor a Mayor
                                @break

                                @case('price-desc')
                                    Precio: Mayor a Menor
                                @break

                                @case('name-asc')
                                    Nombre: A-Z
                                @break

                                @case('name-desc')
                                    Nombre: Z-A
                                @break

                                @default
                                    Más recientes
                            @endswitch
                        </span>
                        <i class="ri-arrow-down-s-line"></i>
                    </div>
                    <div class="site-select-dropdown">
                        <div class="site-select-option {{ $sortBy === 'recent' ? 'active' : '' }}"
                            wire:click="updateSort('recent')">
                            <i class="ri-time-line"></i>
                            <span>Más recientes</span>
                        </div>
                        <div class="site-select-option {{ $sortBy === 'price-asc' ? 'active' : '' }}"
                            wire:click="updateSort('price-asc')">
                            <i class="ri-arrow-up-line"></i>
                            <span>Precio: Menor a Mayor</span>
                        </div>
                        <div class="site-select-option {{ $sortBy === 'price-desc' ? 'active' : '' }}"
                            wire:click="updateSort('price-desc')">
                            <i class="ri-arrow-down-line"></i>
                            <span>Precio: Mayor a Menor</span>
                        </div>
                        <div class="site-select-option {{ $sortBy === 'name-asc' ? 'active' : '' }}"
                            wire:click="updateSort('name-asc')">
                            <i class="ri-sort-asc"></i>
                            <span>Nombre: A-Z</span>
                        </div>
                        <div class="site-select-option {{ $sortBy === 'name-desc' ? 'active' : '' }}"
                            wire:click="updateSort('name-desc')">
                            <i class="ri-sort-desc"></i>
                            <span>Nombre: Z-A</span>
                        </div>
                    </div>
                </div>
            </div>
            @if ($products->isNotEmpty())
                <div class="products-results" data-infinite-products>
                    <div class="products-grid">
                        @foreach ($products as $product)
                            <article class="product-card">
                                <a href="{{ route('products.show', $product) }}" class="product-image">
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
                                        <span class="product-badge">-{{ number_format($product->discount, 0) }}%
                                            OFF</span>
                                    @endif
                                </a>

                                <div class="product-details">

                                    <div class="product-content">
                                        <div class="flex justify-between flex-wrap">
                                            <p class="product-brand">{{ $product->category?->name ?? 'Sin categoría' }}
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
                                                        $discounted = max(
                                                            (float) $product->price * (1 - $discountPercent / 100),
                                                            0,
                                                        );
                                                    @endphp
                                                    <span class="product-price">
                                                        S/.{{ number_format($discounted, 2) }}
                                                    </span>
                                                    <div class="product-price-info">
                                                        <span>Antes</span>
                                                        <span class="product-price-original">
                                                             S/.{{ number_format($product->price, 2) }}
                                                        </span>
                                                    </div>
                                                @else
                                                    <span class="product-price">
                                                        S/.{{ number_format($product->price, 2) }}
                                                    </span>
                                                @endif
                                            </div>

                                        </div>
                                    </div>

                                    <div class="product-footer">
                                        <livewire:site.add-to-wishlist-card :product-id="$product->id" :key="'wishlist-card-' . $product->id" />
                                        <button class="product-btn" aria-label="Agregar al carrito"
                                            title="Agregar al carrito">
                                            <i class="ri-shopping-bag-line"></i>
                                            Agregar
                                        </button>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    @if ($totalPages > 1)
                        @php
                            $pages = [];
                            $pages[] = 1;
                            $start = max(2, $currentPage - 1);
                            $end = min($totalPages - 1, $currentPage + 1);
                            if ($start > 2) {
                                $pages[] = '...';
                            }
                            for ($page = $start; $page <= $end; $page++) {
                                $pages[] = $page;
                            }
                            if ($end < $totalPages - 1) {
                                $pages[] = '...';
                            }
                            if ($totalPages > 1) {
                                $pages[] = $totalPages;
                            }
                        @endphp
                        <nav class="site-pagination" aria-label="Paginacion">
                            <button type="button" class="pagination-btn" wire:click="previousPage"
                                {{ $currentPage === 1 ? 'disabled' : '' }}>
                                <i class="ri-arrow-left-s-line"></i>
                                <span>Anterior</span>
                            </button>
                            <div class="pagination-list">
                                @foreach ($pages as $page)
                                    @if ($page === '...')
                                        <span class="pagination-ellipsis">...</span>
                                    @else
                                        <button type="button"
                                            class="pagination-page {{ $page === $currentPage ? 'is-active' : '' }}"
                                            wire:click="goToPage({{ $page }})">
                                            {{ $page }}
                                        </button>
                                    @endif
                                @endforeach
                            </div>
                            <button type="button" class="pagination-btn" wire:click="nextPage"
                                {{ $currentPage === $totalPages ? 'disabled' : '' }}>
                                <span>Siguiente</span>
                                <i class="ri-arrow-right-s-line"></i>
                            </button>
                        </nav>
                    @endif

                    @if ($hasMore)
                        <div class="filters-footer products-infinite">
                            <button type="button" class="filters-apply" data-load-more-button wire:click="loadMore"
                                wire:loading.attr="disabled">
                                <i class="ri-add-line"></i>
                                <span wire:loading.remove>Cargar más</span>
                                <span wire:loading>Cargando...</span>
                            </button>
                            <div class="products-infinite-sentinel" data-load-more-sentinel aria-hidden="true"></div>
                        </div>
                    @endif
                </div>
            @else
                <div class="filters-empty">
                    <i class="ri-box-3-line"></i>
                    <span>No hay productos para esta categoría o estos filtros.</span>
                </div>
            @endif
        </main>
    </div>
</div>
