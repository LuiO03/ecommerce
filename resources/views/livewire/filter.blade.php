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

				<div class="filters-search">
					<i class="ri-search-2-line"></i>
					<input type="search" placeholder="Buscar filtro..." aria-label="Buscar filtro"
						wire:model.debounce.300ms="search" />
				</div>

				<div class="filters-body">
					@forelse ($options as $option)
						@php
							$searchTerm = trim($search);
							$isColor = method_exists($option, 'isColor') && $option->isColor();
							$matchesOption = $searchTerm !== '' && \Illuminate\Support\Str::contains(
								mb_strtolower($option->name),
								mb_strtolower($searchTerm)
							);
							$filteredFeatures = $searchTerm === ''
								? $option->features
								: $option->features->filter(fn ($feature) => \Illuminate\Support\Str::contains(
									mb_strtolower($feature->value),
									mb_strtolower($searchTerm)
								));
							$featuresToShow = $matchesOption ? $option->features : $filteredFeatures;
						@endphp

						@if ($searchTerm !== '' && !$matchesOption && $filteredFeatures->isEmpty())
							@continue
						@endif

						<details class="filter-group" open>
							<summary class="filter-group-title">
								<span>{{ $option->name }}</span>
								<i class="ri-arrow-down-s-line"></i>
							</summary>
							<div class="filter-group-content">
								@forelse ($featuresToShow as $feature)
									<label class="filter-item">
										<span class="filter-facet-left">
											<input class="filter-facet-input" type="checkbox" value="{{ $feature->id }}"
												wire:model="selectedFeatures" />
											@if ($isColor)
												<span class="filter-facet-swatch"
													style="--facet-color: {{ $feature->value }}"
													title="{{ $feature->value }}"
													aria-label="{{ $feature->value }}"></span>
												<span class="filter-facet-name">{{ $feature->description }}</span>
											@else
												<span class="filter-facet-size">{{ $feature->value }}</span>
											@endif
										</span>
										<span class="filter-facet-count">({{ $featureCounts[$feature->id] ?? 0 }})</span>
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
			<div class="mb-6">
				<h1>{{ $family->name }}</h1>
				<div class="products-header">
					<p class="products-count">
						{{ count($products) }} producto{{ count($products) === 1 ? '' : 's' }} encontrados
					</p>

					<div class="site-select">
						<div class="site-select-trigger">
							<i class="ri-sort-asc site-select-icon"></i>
							<span>
								@switch($sortBy)
									@case('price-asc') Precio: Menor a Mayor @break
									@case('price-desc') Precio: Mayor a Menor @break
									@case('name-asc') Nombre: A-Z @break
									@case('name-desc') Nombre: Z-A @break
									@default Más recientes
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
			</div>

			@if (!empty($products))
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

								@if ($product->discount)
									<span class="product-badge">-{{ $product->discount }}%</span>
								@endif
							</div>

							<div class="product-content">
								<p class="product-brand">{{ $product->category?->name ?? 'Sin categoría' }}</p>
								<h3 class="product-name">{{ $product->name }}</h3>
								<div class="flex w-full justify-between flex-wrap">
									<div>
										<span class="product-price">S/.{{ number_format($product->price, 2) }}</span>
									</div>
									<p class="product-rating">
										<i class="ri-star-fill"></i>
										<span>4.5 (128 reseñas)</span>
									</p>
								</div>
							</div>

							<div class="product-footer">
								<button class="product-btn" aria-label="Agregar a favoritos"
									title="Agregar a favoritos">
									<i class="ri-heart-line"></i>
								</button>
								<a href="#" class="product-btn product-btn-primary"
									aria-label="Ver detalles del producto">
									<i class="ri-eye-line"></i>
									<span>Ver</span>
								</a>
								<button class="product-btn" aria-label="Agregar al carrito"
									title="Agregar al carrito">
									<i class="ri-shopping-cart-2-line"></i>
								</button>
							</div>
						</article>
					@endforeach
				</div>

				@if ($hasMore)
					<div class="filters-footer">
						<button type="button" class="filters-apply" wire:click="loadMore" wire:loading.attr="disabled">
							<i class="ri-add-line"></i>
							<span wire:loading.remove>Cargar más</span>
							<span wire:loading>Cargando...</span>
						</button>
					</div>
				@endif
			@else
				<div class="filters-empty">
					<i class="ri-box-3-line"></i>
					<span>No hay productos para estos filtros.</span>
				</div>
			@endif
		</main>
	</div>
</div>
