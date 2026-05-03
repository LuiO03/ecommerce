	@section('title', 'Productos')

	<x-admin-layout :showMobileFab="true">
	<x-slot name="title">
		<div class="page-icon card-danger">
			<i class="ri-box-3-line"></i>
		</div>
		Lista de Productos
	</x-slot>

	<x-slot name="action">
		@can('productos.export')
		<div class="export-menu-container">
			<button type="button" class="boton-form boton-action" id="exportMenuBtn">
				<span class="boton-form-icon"><i class="ri-download-2-fill"></i></span>
				<span class="boton-form-text">Exportar</span>
				<i class="ri-arrow-down-s-line boton-form-icon"></i>
			</button>
			<div class="export-dropdown" id="exportDropdown">
				<button type="button" class="export-option" id="exportAllExcel">
					<i class="ri-file-excel-2-fill"></i>
					<span>Exportar todo a Excel</span>
				</button>
				<button type="button" class="export-option" id="exportAllCsv">
					<i class="ri-file-text-fill"></i>
					<span>Exportar todo a CSV</span>
				</button>
				<button type="button" class="export-option" id="exportAllPdf">
					<i class="ri-file-pdf-2-fill"></i>
					<span>Exportar todo a PDF</span>
				</button>
			</div>
		</div>
		@endcan

        <button class="boton-form boton-action" title="Buscar o filtrar posts" id="toggleFiltersBtn">
            <span class="boton-form-icon">
                <i class="ri-search-eye-fill"></i>
            </span>
            <span class="boton-form-text">
                Buscar o filtrar
            </span>
        </button>

		@can('productos.create')
		<a href="{{ route('admin.products.create') }}" class="boton-form boton-accent">
			<span class="boton-form-icon"><i class="ri-add-box-fill"></i></span>
			<span class="boton-form-text">Crear Producto</span>
		</a>
		@endcan
	</x-slot>

	<div class="actions-container">
        <aside class="tabla-filtros">
            <span class="tabla-filtros-title">
                Buscar
            </span>
            <article class="tabla-buscador">
                <i class="ri-search-eye-line buscador-icon"></i>
                <input type="text" id="customSearch" placeholder="Buscar productos por nombre o SKU" autocomplete="off" />
                <button type="button" id="clearSearch" class="buscador-clear" title="Limpiar búsqueda">
                    <i class="ri-close-large-fill"></i>
                </button>
            </article>
            <span class="tabla-filtros-title">
                Aplicar filtros
            </span>
            <article class="tabla-select-wrapper">
                <div class="selector">
                    <select id="entriesSelect">
                        <option value="5">5/pág.</option>
                        <option value="10" selected>10/pág.</option>
                        <option value="25">25/pág.</option>
                        <option value="50">50/pág.</option>
                    </select>
                    <i class="ri-arrow-down-s-line selector-icon"></i>
                </div>
            </article>

            <article class="tabla-select-wrapper">
                <div class="selector">
                    <select id="sortFilter">
                        <option value="">Ordenar por</option>
                        <option value="name-asc">Nombre (A-Z)</option>
                        <option value="name-desc">Nombre (Z-A)</option>
                        <option value="date-desc">Más recientes</option>
                        <option value="date-asc">Más antiguos</option>
                    </select>
                    <i class="ri-sort-asc selector-icon"></i>
                </div>
            </article>

            <article class="tabla-select-wrapper">
                <div class="selector">
                    <select id="statusFilter">
                        <option value="">Todos los estados</option>
                        <option value="1">Activos</option>
                        <option value="0">Inactivos</option>
                    </select>
                    <i class="ri-filter-3-line selector-icon"></i>
                </div>
            </article>

            <article class="tabla-select-wrapper">
                <div class="selector">
                    <select id="categoryFilter">
                        <option value="">Todas las categorías</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <i class="ri-archive-stack-line selector-icon"></i>
                </div>
            </article>

			<article class="tabla-select-wrapper">
				<div class="selector">
					<select id="brandFilter">
						<option value="">Todas las marcas</option>
						@foreach($brands as $brand)
							<option value="{{ $brand->id }}">{{ $brand->name }}</option>
						@endforeach
					</select>
					<i class="ri-bookmark-3-line selector-icon"></i>
				</div>
			</article>

            <article class="tabla-select-wrapper" id="minStockFilterWrapper">
                <button type="button" id="minStockFilterBtn" class="" title="Mostrar solo productos con stock bajo el mínimo">
                    <i class="ri-alert-line" style="font-size:18px;"></i>
                    <span>Stock bajo</span>
                </button>
            </article>

            <!-- Botón para limpiar filtros -->
            <button type="button" id="clearFiltersBtn" class="boton-clear-filters"
                title="Limpiar todos los filtros">
                <span class="boton-icon"><i class="ri-filter-off-line"></i></span>
                <span class="boton-text">Limpiar filtros</span>
            </button>
            <button class="boton-form boton-accent" title="Aplicar filtros y búsqueda" id="applyFiltersBtn">
                <span class="boton-form-icon">
                    <i class="ri-filter-fill"></i>
                </span>
                <span class="boton-form-text">
                    Mostrar resultados
                </span>
            </button>
        </aside>

		@canany(['productos.export', 'productos.delete'])
		<div class="selection-bar" id="selectionBar">
			@can('productos.export')
			<div class="selection-actions">
				<button id="exportSelectedExcel" class="boton-selection boton-success" title="Exportar registros seleccionados a Excel">
					<span class="boton-selection-icon"><i class="ri-file-excel-2-fill"></i></span>
                    <span class="boton-selection-text">Excel</span>
                    <span class="boton-selection-dot">•</span>
                    <span class="selection-badge" id="excelBadge">0</span>
				</button>

                <button id="exportSelectedPdf" class="boton-selection boton-danger" title="Exportar registros seleccionados a PDF">
					<span class="boton-selection-icon"><i class="ri-file-pdf-2-fill"></i></span>
					<span class="boton-selection-text">PDF</span>
                    <span class="boton-selection-dot">•</span>
					<span class="selection-badge" id="pdfBadge">0</span>
				</button>

				<button id="exportSelectedCsv" class="boton-selection boton-orange" title="Exportar registros seleccionados a CSV">
					<span class="boton-selection-icon"><i class="ri-file-text-fill"></i></span>
                    <span class="boton-selection-text">CSV</span>
                    <span class="boton-selection-dot">•</span>
                    <span class="selection-badge" id="csvBadge">0</span>
				</button>


			</div>
			@endcan

			@can('productos.delete')
			<button id="deleteSelected" class="boton-selection boton-danger" title="Eliminar registros seleccionados">
				<span class="boton-selection-icon"><i class="ri-delete-bin-fill"></i></span>
				<span class="boton-selection-text">Eliminar</span>
                <span class="boton-selection-dot">•</span>
				<span class="selection-badge" id="deleteBadge">0</span>
			</button>
			@endcan

			<div class="selection-info">
				<span id="selectionCount">0 seleccionados</span>
				<button class="selection-close" id="clearSelection" title="Limpiar selección">
					<i class="ri-close-large-fill"></i>
				</button>
			</div>
		</div>
		@endcanany

		<div class="tabla-wrapper">
			<table id="tabla" class="tabla-general display">
				<thead>
					<tr>
						<th class="control"></th>
						@canany(['productos.export', 'productos.delete'])
						<th class="column-check-th column-not-order">
							<div><input type="checkbox" id="checkAll"></div>
						</th>
						@endcanany
						<th class="column-id-th">ID</th>
						<th class="column-name-th">Nombre</th>
						<th class="column-sku-th">SKU</th>
						<th class="column-brand-th">Marca</th>
						<th class="column-category-th">Categoría</th>
						<th class="column-price-th">Precio</th>
						<th class="column-discount-th">Desc.</th>
						<th class="column-variants-th">Variantes</th>
						<th class="column-stock-th">Stock</th>
						<th class="column-status-th">Estado</th>
						<th class="column-date-th">Creado</th>
						<th class="column-actions-th column-not-order">Acciones</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($products as $product)

						<tr data-id="{{ $product->id }}" data-name="{{ $product->name }}">
							<td class="control"></td>
							@canany(['productos.export', 'productos.delete'])
							<td class="column-check-td">
								<div>
									<input type="checkbox" class="check-row" value="{{ $product->id }}">
								</div>
							</td>
							@endcanany
							<td class="column-id-td">{{ $product->id }}</td>
							<td class="column-name-td">
								<div class="tabla-name-wrapper">
									<span class="tabla-name-main">{{ $product->name }}</span>
								</div>
							</td>
							<td class="column-sku-td">
								<span>{{ $product->sku }}</span>
							</td>
							<td class="column-brand-td" data-brand-id="{{ $product->brand_id ?? '' }}">
								@if($product->brand)
									{{ $product->brand->name }}
								@else
									<span class="badge badge-gray">
										<i class="ri-bookmark-2-line"></i>
										Sin marca
									</span>
								@endif
							</td>
							<td class="column-category-td" data-category-id="{{ $product->category_id ?? '' }}">
								@if($product->category)
										{{ $product->category->name }}
								@else
									<span class="badge badge-gray">
										<i class="ri-folder-unknow-line"></i>
										Sin categoría
									</span>
								@endif
							</td>
							<td class="column-price-td">
								<span>{{ number_format($product->price, 2) }}</span>
							</td>
							<td class="column-discount-td">
								@if(!is_null($product->discount) && (float) $product->discount > 0)
										{{ number_format($product->discount, 1) }} %
								@else
									<span class="text-muted-td">Sin descuento</span>
								@endif
							</td>
							<td class="column-variants-td">
                                {{ $product->variants_count }}
							</td>
							<td class="column-stock-td">
								@php
									$stockTotal = (int) ($product->variants_stock_sum ?? 0);
									$minStock = method_exists($product, 'getMinStock') ? $product->getMinStock() : (property_exists($product, 'min_stock') ? ($product->min_stock ?? config('products.min_stock', 10)) : config('products.min_stock', 10));
								@endphp
                                @if($stockTotal < $minStock)
                                    <span class="badge badge-danger">
                                        <i class="ri-close-circle-fill"></i>
                                        {{ $stockTotal }}
                                    </span>
                                @else
                                    <span class="badge badge-success">
                                        <i class="ri-checkbox-circle-fill"></i>
                                        {{ $stockTotal }}
                                    </span>
                                @endif
							</td>
                            <td class="column-status-td" data-status="{{ $product->status ? 1 : 0 }}">
							    @can('productos.update-status')
                                    <label class="switch-tabla">
                                        <input type="checkbox" class="switch-status" data-id="{{ $product->id }}" data-key="{{ $product->slug }}" {{ $product->status ? 'checked' : '' }}>
                                        <span class="slider"></span>
                                    </label>
                                @else
                                    @if ($product->status)
                                        <span class="badge badge-success">
                                            <i class="ri-checkbox-circle-fill"></i>
                                            Activo
                                        </span>
                                    @else
                                        <span class="badge badge-danger">
                                            <i class="ri-close-circle-fill"></i>
                                            Inactivo
                                        </span>
                                    @endif
                                @endcan
                            </td>
							<td class="column-date-td">
								<span class="{{ $product->created_at ? '' : 'text-muted-td' }}">
									{{ $product->created_at ? $product->created_at->format('d/m/Y H:i') : 'Sin fecha' }}
								</span>
							</td>
							<td class="column-actions-td">
                                <button class="boton-show-actions">
                                    <i class="ri-more-fill"></i>
                                </button>
								<div class="tabla-botones">
									<button class="boton-sm boton-info btn-ver-producto" data-slug="{{ $product->slug }}">
										<i class="ri-eye-2-fill"></i>
                                        <span class="boton-sm-text">Ver Producto</span>
									</button>
									@can('productos.edit')
									<a href="{{ route('admin.products.edit', $product) }}" class="boton-sm boton-warning">
										<i class="ri-edit-circle-fill"></i>
										<span class="boton-sm-text">Editar Producto</span>
									</a>
									@endcan
									@can('productos.delete')
									<form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="delete-form" data-entity="producto">
										@csrf
										@method('DELETE')
										<button type="submit" class="boton-sm boton-danger">
											<i class="ri-delete-bin-2-fill"></i>
											<span class="boton-sm-text">Eliminar Producto</span>
										</button>
									</form>
									@endcan
								</div>
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>

		<div class="tabla-footer">
			<div id="tableInfo" class="tabla-info"></div>
			<div id="tablePagination" class="tabla-paginacion"></div>
		</div>
	</div>

	@push('scripts')
		<script>
			$(document).ready(function() {
				const tableManager = new DataTableManager('#tabla', {
					moduleName: 'products',
					entityNameSingular: 'producto',
					entityNamePlural: 'productos',
					deleteRoute: '/admin/products',
					statusRoute: '/admin/products/{id}/status',
					exportRoutes: {
						excel: '/admin/products/export/excel',
						csv: '/admin/products/export/csv',
						pdf: '/admin/products/export/pdf'
					},
					csrfToken: '{{ csrf_token() }}',
					pageLength: 10,
					lengthMenu: [5, 10, 25, 50],
					callbacks: {
						onDraw: () => {
							console.log('🔄 Tabla de productos redibujada');
						},
						onStatusChange: (id, status) => {
							console.log(`✅ Producto ${id} -> ${status ? 'Activo' : 'Inactivo'}`);
						},
						onDelete: () => {
							console.log('🗑️ Productos eliminados');
						},
						onExport: (type, format, count) => {
							console.log(`📤 Exportación de ${type} en ${format} (${count || 'todos'})`);
						}
					}
				});

								let currentCategoryFilter = '';
								let currentBrandFilter = '';


				let minStockFilterActive = false;

				$.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
					if (settings.nTable.id !== 'tabla') return true;

					// Filtro por categoría
					if (currentCategoryFilter) {
						const row = tableManager.table.row(dataIndex).node();
						const rowCategoryId = $(row).find('.column-category-td').attr('data-category-id') || '';
						if (rowCategoryId !== currentCategoryFilter) {
							return false;
						}
					}

									// Filtro por marca
									if (currentBrandFilter) {
										const row = tableManager.table.row(dataIndex).node();
										const rowBrandId = $(row).find('.column-brand-td').attr('data-brand-id') || '';
										if (rowBrandId !== currentBrandFilter) {
											return false;
										}
									}

					// Filtro por stock bajo mínimo
					if (minStockFilterActive) {
						const row = tableManager.table.row(dataIndex).node();
						const badge = $(row).find('.column-stock-td .badge');
						// badge-danger indica stock bajo mínimo
						if (!badge.hasClass('badge-danger')) {
							return false;
						}
					}

					return true;
				});

				$('#categoryFilter').on('change', function() {
					currentCategoryFilter = this.value;
					tableManager.table.draw();
					tableManager.checkFiltersActive();
					console.log(`🔍 Filtro categoría: ${currentCategoryFilter || 'Todas'}`);
				});

								$('#brandFilter').on('change', function() {
									currentBrandFilter = this.value;
									tableManager.table.draw();
									tableManager.checkFiltersActive();
									console.log(`🔍 Filtro marca: ${currentBrandFilter || 'Todas'}`);
								});

				// Botón visual para stock bajo mínimo
				$('#minStockFilterBtn').on('click', function() {
					minStockFilterActive = !minStockFilterActive;
					$('#minStockFilterWrapper').toggleClass('filter-active', minStockFilterActive);
					tableManager.table.draw();
					tableManager.checkFiltersActive();
					if (minStockFilterActive) {
						$(this).attr('title', 'Ver todos los productos');
					} else {
						$(this).attr('title', 'Mostrar solo productos con stock bajo el mínimo');
					}
				});

				@if (Session::has('highlightRow'))
					(function() {
						const navEntries = (typeof performance !== 'undefined' && typeof performance.getEntriesByType === 'function')
							? performance.getEntriesByType('navigation')
							: [];
						const legacyNav = (typeof performance !== 'undefined' && performance.navigation)
							? performance.navigation.type
							: null;
						const navType = navEntries.length ? navEntries[0].type : legacyNav;
						const isBackNavigation = navType === 'back_forward' || navType === 2;

						if (isBackNavigation) {
							return;
						}

						const highlightId = {{ Session::get('highlightRow') }};
						setTimeout(() => {
							const row = $(`#tabla tbody tr[data-id="${highlightId}"]`);
							if (row.length) {
								row.addClass('row-highlight');
								row[0].scrollIntoView({
									behavior: 'smooth',
									block: 'center'
								});

								setTimeout(() => {
									row.removeClass('row-highlight');
								}, 3000);
							}
						}, 150);
					})();
				@endif
			});
		</script>
	@endpush

	@include('admin.products.modals.show-modal-product')
</x-admin-layout>
