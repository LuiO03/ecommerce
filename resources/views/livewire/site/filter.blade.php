<div class="site-container">
    <div class="products-layout {{ !empty($search) ? 'products-layout--full' : '' }}">
        @if (empty($search))
            <aside class="products-sidebar">
                <section class="filters" aria-label="Filtros de productos">
                    <div class="filters-header">
                        <div class="filters-title">
                            <span>Filtrar productos</span>
                        </div>
                    </div>

                    @php
                        $hasSubcategories = $subcategories && $subcategories->isNotEmpty();
                        $hasBrands = $brands && $brands->isNotEmpty();
                        $hasOptions = $options && $options->isNotEmpty();

                        $hasAnyFilter = $hasSubcategories || $hasBrands || $hasOptions;
                    @endphp

                    <div class="filters-body">
                        @if ($hasAnyFilter)
                            {{-- 🔷 Subcategorías --}}
                            @if ($hasSubcategories)
                                <section class="filters-subcategories" aria-label="Subcategorías">
                                    <summary class="filter-group-title">
                                        <span class="filters-name">Subcategorías</span>
                                    </summary>
                                    <div class="filters-subcategories-chips">
                                        @foreach ($subcategories as $subcategory)
                                            <a class="filter-chip" href="{{ route('categories.show', $subcategory) }}">
                                                <span>{{ $subcategory->name }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </section>
                            @endif
                            {{-- 🔷 Marcas --}}
                            @if ($hasBrands)
                                <details class="filter-group" aria-label="Marcas" open>

                                    <summary class="filter-group-title">
                                        <span class="filters-name">Marcas</span>

                                        <i class="ri-arrow-down-s-line"></i>
                                    </summary>

                                    <div class="filter-group-content">
                                        @foreach ($brands as $brand)
                                            <label
                                                class="filter-item {{ in_array($brand->id, $selectedBrands ?? []) ? 'is-active' : '' }}">
                                                <input type="checkbox" wire:model.defer="selectedBrands"
                                                    value="{{ $brand->id }}">

                                                <span class="filter-item-label">
                                                    {{ $brand->name }}
                                                </span>

                                                <span class="filters-count">
                                                    {{ $brand->products_count }}
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </details>
                            @endif
                            {{-- 🔷 Opciones (features) --}}
                            @if ($hasOptions)
                                @foreach ($options as $option)
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
                                                    $rawHex = $isColorOption
                                                        ? (string) ($feature->description ?? ($feature->value ?? ''))
                                                        : null;

                                                    $normalized = $rawHex !== null ? ltrim($rawHex, '#') : null;

                                                    $displayColor = $normalized ? '#' . $normalized : null;
                                                @endphp

                                                <label
                                                    class="filter-item {{ in_array($feature->id, $selectedFeatures ?? []) ? 'is-active' : '' }}">
                                                    <input type="checkbox" wire:model.defer="selectedFeatures"
                                                        value="{{ $feature->id }}">

                                                    @if ($isColorOption && $displayColor)
                                                        <span class="filter-color-dot"
                                                            style="--filter-color: {{ $displayColor }};"></span>
                                                    @endif

                                                    <span class="filter-item-label">
                                                        {{ $feature->value }}
                                                    </span>

                                                    @if (isset($featureCounts[$feature->id]))
                                                        <span class="filters-count">
                                                            {{ $featureCounts[$feature->id] }}
                                                        </span>
                                                    @endif
                                                </label>
                                            @endforeach
                                        </div>
                                    </details>
                                @endforeach
                            @endif

                            {{-- 🔷 Precio --}}
                            <section class="filters-price" aria-label="Filtrar por precio">
                                <div class="filter-group-title filters-price-title">
                                    <span class="filters-name">Precio</span>
                                    <i class="ri-price-tag-3-line"></i>
                                </div>

                                <div class="filters-price-range">
                                    <label class="filters-price-field">
                                        <span>Desde</span>
                                        <input type="number" min="0" step="0.01" inputmode="decimal"
                                            placeholder="0.00" wire:model.defer="priceMin" data-price-manual>
                                    </label>

                                    <label class="filters-price-field">
                                        <span>Hasta</span>
                                        <input type="number" min="0" step="0.01" inputmode="decimal"
                                            placeholder="0.00" wire:model.defer="priceMax" data-price-manual>
                                    </label>
                                </div>

                                <div class="filters-price-presets" role="radiogroup"
                                    aria-label="Rangos predefinidos de precio">
                                    <label
                                        class="filters-price-preset {{ $selectedPriceRange === '' ? 'is-active' : '' }}">
                                        <input type="radio" wire:model.defer="selectedPriceRange" value="" data-price-preset>
                                        <span>Todos</span>
                                    </label>
                                    <label
                                        class="filters-price-preset {{ $selectedPriceRange === '50-100' ? 'is-active' : '' }}">
                                        <input type="radio" wire:model.defer="selectedPriceRange" value="50-100" data-price-preset>
                                        <span>S/. 50 a 100</span>
                                    </label>
                                    <label
                                        class="filters-price-preset {{ $selectedPriceRange === '100-200' ? 'is-active' : '' }}">
                                        <input type="radio" wire:model.defer="selectedPriceRange" value="100-200" data-price-preset>
                                        <span>S/. 100 a 200</span>
                                    </label>
                                    <label
                                        class="filters-price-preset {{ $selectedPriceRange === '200-500' ? 'is-active' : '' }}">
                                        <input type="radio" wire:model.defer="selectedPriceRange" value="200-500" data-price-preset>
                                        <span>S/. 200 a 500</span>
                                    </label>
                                    <label
                                        class="filters-price-preset {{ $selectedPriceRange === '500-1000' ? 'is-active' : '' }}">
                                        <input type="radio" wire:model.defer="selectedPriceRange" value="500-1000" data-price-preset>
                                        <span>S/. 500 a 1000</span>
                                    </label>
                                    <label
                                        class="filters-price-preset {{ $selectedPriceRange === '1000+' ? 'is-active' : '' }}">
                                        <input type="radio" wire:model.defer="selectedPriceRange" value="1000+" data-price-preset>
                                        <span>S/. 1000 a más</span>
                                    </label>
                                </div>
                            </section>
                        @else
                            {{-- 🔴 Fallback único --}}
                            <div class="card-empty">
                                <div class="card-empty-icon card-info">
                                    <i class="ri-filter-off-line"></i>
                                </div>

                                <h2 class="card-title">Sin filtros disponibles</h2>

                                <span>
                                    Esta categoría no tiene filtros configurados actualmente.
                                </span>
                            </div>

                        @endif
                    </div>
                    {{-- 🔷 Footer (solo si hay filtros) --}}
                    <div class="filters-footer">
                        <button type="button" class="site-btn site-btn-outline" aria-label="Limpiar filtros"
                            wire:click="clearFilters" wire:loading.attr="disabled" wire:target="clearFilters">
                            <i class="ri-refresh-line" wire:loading.remove wire:target="clearFilters"></i>
                            <i class="ri-loader-4-line button-loading-icon" wire:loading wire:target="clearFilters"
                                aria-hidden="true"></i>
                            <span>Limpiar</span>
                        </button>
                        <button type="button" class="site-btn site-btn-primary" wire:click="applyFilters"
                            wire:loading.attr="disabled" wire:target="applyFilters">
                            <i class="ri-check-line" wire:loading.remove wire:target="applyFilters"></i>
                            <i class="ri-loader-4-line button-loading-icon" wire:loading wire:target="applyFilters"
                                aria-hidden="true"></i>
                            <span>Aplicar filtros</span>
                        </button>
                    </div>
                </section>
            </aside>
        @endif

        <main class="products-main">
            <div class="products-header">
                <div>
                    <h1 class="products-title">
                        {{ $this->pageTitle }}
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
                            wire:click="updateSort('recent')" wire:loading.attr="disabled" wire:target="updateSort">
                            <i class="ri-time-line" wire:loading.remove wire:target="updateSort"></i>
                            <i class="ri-loader-4-line button-loading-icon" wire:loading wire:target="updateSort"
                                aria-hidden="true"></i>
                            <span>Más recientes</span>
                        </div>
                        <div class="site-select-option {{ $sortBy === 'price-asc' ? 'active' : '' }}"
                            wire:click="updateSort('price-asc')" wire:loading.attr="disabled"
                            wire:target="updateSort">
                            <i class="ri-arrow-up-line" wire:loading.remove wire:target="updateSort"></i>
                            <i class="ri-loader-4-line button-loading-icon" wire:loading wire:target="updateSort"
                                aria-hidden="true"></i>
                            <span>Precio: Menor a Mayor</span>
                        </div>
                        <div class="site-select-option {{ $sortBy === 'price-desc' ? 'active' : '' }}"
                            wire:click="updateSort('price-desc')" wire:loading.attr="disabled"
                            wire:target="updateSort">
                            <i class="ri-arrow-down-line" wire:loading.remove wire:target="updateSort"></i>
                            <i class="ri-loader-4-line button-loading-icon" wire:loading wire:target="updateSort"
                                aria-hidden="true"></i>
                            <span>Precio: Mayor a Menor</span>
                        </div>
                        <div class="site-select-option {{ $sortBy === 'name-asc' ? 'active' : '' }}"
                            wire:click="updateSort('name-asc')" wire:loading.attr="disabled"
                            wire:target="updateSort">
                            <i class="ri-sort-asc" wire:loading.remove wire:target="updateSort"></i>
                            <i class="ri-loader-4-line button-loading-icon" wire:loading wire:target="updateSort"
                                aria-hidden="true"></i>
                            <span>Nombre: A-Z</span>
                        </div>
                        <div class="site-select-option {{ $sortBy === 'name-desc' ? 'active' : '' }}"
                            wire:click="updateSort('name-desc')" wire:loading.attr="disabled"
                            wire:target="updateSort">
                            <i class="ri-sort-desc" wire:loading.remove wire:target="updateSort"></i>
                            <i class="ri-loader-4-line button-loading-icon" wire:loading wire:target="updateSort"
                                aria-hidden="true"></i>
                            <span>Nombre: Z-A</span>
                        </div>
                    </div>
                </div>
            </div>

            @if ($products->isNotEmpty())
                <div class="products-results products-results--loading-zone">
                    <div class="products-loading-overlay" wire:loading.flex
                        wire:target="applyFilters,clearFilters,goToPage,nextPage,previousPage,updateSort">
                        <div class="products-loading-card" role="status" aria-live="polite">
                            <i class="ri-loader-4-line products-loading-icon"></i>
                            <span>Cargando productos...</span>
                        </div>
                    </div>

                    <div class="products-grid">
                        @foreach ($products as $product)
                            @include('partials.components.product-card', ['product' => $product])
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
                                {{ $currentPage === 1 ? 'disabled' : '' }} wire:loading.attr="disabled"
                                wire:target="previousPage">
                                <i class="ri-arrow-left-s-line" wire:loading.remove wire:target="previousPage"></i>
                                <i class="ri-loader-4-line button-loading-icon" wire:loading
                                    wire:target="previousPage" aria-hidden="true"></i>
                                <span>Anterior</span>
                            </button>
                            <div class="pagination-list">
                                @foreach ($pages as $page)
                                    @if ($page === '...')
                                        <span class="pagination-ellipsis">...</span>
                                    @else
                                        <button type="button"
                                            class="pagination-page {{ $page === $currentPage ? 'is-active' : '' }}"
                                            wire:click="goToPage({{ $page }})" wire:loading.attr="disabled"
                                            wire:target="goToPage">
                                            <span wire:loading.remove
                                                wire:target="goToPage">{{ $page }}</span>
                                            <i class="ri-loader-4-line button-loading-icon" wire:loading
                                                wire:target="goToPage" aria-hidden="true"></i>
                                        </button>
                                    @endif
                                @endforeach
                            </div>
                            <button type="button" class="pagination-btn" wire:click="nextPage"
                                {{ $currentPage === $totalPages ? 'disabled' : '' }} wire:loading.attr="disabled"
                                wire:target="nextPage">
                                <span>Siguiente</span>
                                <i class="ri-arrow-right-s-line" wire:loading.remove wire:target="nextPage"></i>
                                <i class="ri-loader-4-line button-loading-icon" wire:loading wire:target="nextPage"
                                    aria-hidden="true"></i>
                            </button>
                        </nav>
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
