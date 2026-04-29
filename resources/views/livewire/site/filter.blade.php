<div class="site-container">
    <div class="products-layout {{ !empty($search) ? 'products-layout--full' : '' }}">
        @if (empty($search))
            <aside class="products-sidebar">
                <section class="filters" aria-label="Filtros de productos">
                    <div class="filters-header">
                        <div class="filters-title">
                            <span>Filtrar productos</span>
                        </div>
                        <button type="button" class="filters-clear" aria-label="Limpiar filtros"
                            wire:click="clearFilters">
                            <i class="ri-refresh-line"></i>
                            <span>Limpiar</span>
                        </button>
                    </div>

                    <div class="filters-body">
                        @if ($subcategories && $subcategories->isNotEmpty())
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
                            <div class="card-empty">
                                <div class="card-empty-icon card-warning">
                                    <i class="ri-price-tag-3-fill"></i>
                                </div>
                                <h2 class="card-title">Sin subcategorias</h2>
                                <span>No hay subcategorias para esta categoria.</span>
                            </div>
                        @endif

                        @forelse ($options as $option)
                            @php
                                $selectedCount = $selectedFeaturesByOption[$option->id] ?? 0;
                                $isColorOption = method_exists($option, 'isColor') && $option->isColor();
                            @endphp
                            <details class="filter-group" open>
                                <summary class="filter-group-title">
                                    <span class="filters-name">{{ $option->name }}</span>
                                    @if ($selectedCount > 0)
                                        <span class="filters-badge">{{ $selectedCount }}</span>
                                    @endif
                                    <i class="ri-arrow-down-s-line"></i>
                                </summary>
                                <div class="filter-group-content">
                                    @foreach ($option->features as $feature)
                                        @php
                                            // Para color: value = nombre, description = HEX
                                            $rawHex = $isColorOption
                                                ? (string) ($feature->description ?? ($feature->value ?? ''))
                                                : null;
                                            $normalized = $rawHex !== null ? ltrim($rawHex, '#') : null;
                                            $displayColor =
                                                $normalized !== null && $normalized !== '' ? '#' . $normalized : null;
                                        @endphp
                                        <label class="filter-item {{ $isColorOption ? 'filter-item--color' : '' }}">
                                            <input type="checkbox" wire:model.defer="selectedFeatures"
                                                value="{{ $feature->id }}">
                                            @if ($isColorOption && $displayColor)
                                                <span class="filter-color-dot"
                                                    style="--filter-color: {{ $displayColor }};"></span>
                                            @endif
                                            <span class="filter-item-label">{{ $feature->value }}</span>
                                            @if (isset($featureCounts[$feature->id]))
                                                <span class="filters-count">{{ $featureCounts[$feature->id] }}</span>
                                            @endif
                                        </label>
                                    @endforeach
                                </div>
                            </details>
                        @empty
                            <div class="card-empty">
                                <div class="card-empty-icon card-info">
                                    <i class="ri-checkbox-multiple-blank-fill"></i>
                                </div>
                                <h2 class="card-title">Sin opciones</h2>
                                <span>No hay opciones configuradas para esta categoría.</span>
                            </div>
                        @endforelse

                        @if (count($options) > 0)
                            <div class="filters-footer">
                                <button type="button" class="filters-apply" wire:click="applyFilters">
                                    <i class="ri-check-line"></i>
                                    <span>Aplicar filtros</span>
                                </button>
                            </div>
                        @endif
                    </div>
                </section>
            </aside>
        @endif

        <main class="products-main">
            <div class="products-header">
                <div>
                    <h1 class="products-title">
                        @if (!empty($search))
                            Resultados de búsqueda
                        @elseif ($category)
                            {{ $category->name }}
                        @elseif ($family)
                            {{ $family->name }}
                        @else
                            Todos los productos
                        @endif
                    </h1>
                    <p class="products-count">
                        @if (!empty($search))
                            @if ($totalProducts > 0)
                                {{ $totalProducts }}
                                {{ Str::plural('producto', $totalProducts) }}
                                encontrado{{ $totalProducts === 1 ? '' : 's' }} para
                                "{{ $search }}"
                            @else
                                No se encontraron productos para "{{ $search }}"
                            @endif
                        @else
                            {{ $totalProducts }}
                            {{ Str::plural('producto', $totalProducts) }}
                            encontrado{{ $totalProducts === 1 ? '' : 's' }}
                        @endif
                    </p>
                </div>

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

                                    @if ($product->discount)
                                        <span class="product-badge">-{{ number_format($product->discount, 0) }}%
                                            OFF</span>
                                    @endif
                                </a>

                                <div class="product-details">
                                    <div class="flex justify-between flex-wrap">
                                        <p class="product-brand">{{ $product->category?->name ?? 'Sin categoría' }}</p>
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
                                                <span
                                                    class="product-price">S/.{{ number_format($discounted, 2) }}</span>
                                                <span
                                                    class="product-price-original">S/.{{ number_format($product->price, 2) }}</span>
                                            @else
                                                <span
                                                    class="product-price">S/.{{ number_format($product->price, 2) }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="product-footer">
                                        <livewire:site.add-to-wishlist-card :product-id="$product->id" :key="'wishlist-card-' . $product->id" />
                                    </div>
                                </div>
                            </div>
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
                <div class="card-empty">
                    <div class="card-empty-icon card-danger">
                        <i class="ri-close-large-fill"></i>
                    </div>
                    @if (!empty($search))
                        <h2 class="card-title">No se encontraron productos</h2>
                        <p>No se encontraron resultados para "{{ $search }}".</p>
                    @else
                        <h2 class="card-title">No hay productos disponibles</h2>
                        <p>No hay productos para esta categoría o estos filtros.</p>
                    @endif
                </div>
            @endif
        </main>
    </div>
</div>
