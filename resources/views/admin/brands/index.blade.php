@section('title', 'Marcas')

<x-admin-layout :showMobileFab="true">
    <x-slot name="title">
        <div class="page-icon card-warning">
            <i class="ri-bookmark-3-line"></i>
        </div>
        Lista de Marcas
    </x-slot>

    <x-slot name="action">
        @can('marcas.export')
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

        <button class="boton-form boton-action" title="Buscar o filtrar marcas" id="toggleFiltersBtn">
            <span class="boton-form-icon">
                <i class="ri-search-eye-fill"></i>
            </span>
            <span class="boton-form-text">
                Buscar o filtrar
            </span>
        </button>

        @can('marcas.create')
            <a href="{{ route('admin.brands.create') }}" class="boton-form boton-accent">
                <span class="boton-form-icon"><i class="ri-add-box-fill"></i></span>
                <span class="boton-form-text">Crear Marca</span>
            </a>
        @endcan
    </x-slot>

    <div class="actions-container">
        <aside class="tabla-filtros">
            <span class="tabla-filtros-title">Buscar</span>
            <article class="tabla-buscador">
                <i class="ri-search-eye-line buscador-icon"></i>
                <input type="text" id="customSearch" placeholder="Buscar marcas por nombre" autocomplete="off" />
                <button type="button" id="clearSearch" class="buscador-clear" title="Limpiar búsqueda">
                    <i class="ri-close-large-fill"></i>
                </button>
            </article>

            <span class="tabla-filtros-title">Aplicar filtros</span>

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

            <button type="button" id="clearFiltersBtn" class="boton-clear-filters" title="Limpiar todos los filtros">
                <span class="boton-icon"><i class="ri-filter-off-line"></i></span>
                <span class="boton-text">Limpiar filtros</span>
            </button>
            <button class="boton-form boton-accent" title="Aplicar filtros y búsqueda" id="applyFiltersBtn">
                <span class="boton-form-icon">
                    <i class="ri-filter-fill"></i>
                </span>
                <span class="boton-form-text">Mostrar resultados</span>
            </button>
        </aside>

        @canany(['marcas.export', 'marcas.delete'])
            <div class="selection-bar" id="selectionBar">
                @can('marcas.export')
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

                @can('marcas.delete')
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

                        @canany(['marcas.export', 'marcas.delete'])
                            <th class="column-check-th column-not-order">
                                <div><input type="checkbox" id="checkAll"></div>
                            </th>
                        @endcanany

                        <th class="column-id-th">ID</th>
                        <th class="column-name-th">Nombre</th>
                        <th class="column-description-th">Descripción</th>
                        <th class="column-products-th">Productos</th>
                        <th class="column-status-th">Estado</th>
                        <th class="column-date-th">Creado</th>
                        <th class="column-actions-th column-not-order">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($brands as $brand)
                        <tr data-id="{{ $brand->id }}" data-name="{{ $brand->name }}">
                            <td class="control"></td>

                            @canany(['marcas.export', 'marcas.delete'])
                                <td class="column-check-td">
                                    <div>
                                        <input type="checkbox" class="check-row" value="{{ $brand->id }}">
                                    </div>
                                </td>
                            @endcanany

                            <td class="column-id-td">{{ $brand->id }}</td>

                            <td class="column-name-td">
                                {{ $brand->name }}
                            </td>

                            <td class="column-description-td">
                                <span class="{{ $brand->description ? '' : 'text-muted-td' }}">
                                    {{ $brand->description ?? 'Sin descripción' }}
                                </span>
                            </td>

                            <td class="column-products-td">
                                @if ($brand->products_count > 0)
                                    <span class="badge badge-primary" title="{{ $brand->products_count }} {{ Str::plural('producto', $brand->products_count) }}">
                                        <i class="ri-box-3-fill"></i>
                                        {{ $brand->products_count }}
                                    </span>
                                @else
                                    <span class="badge badge-danger" title="No tiene productos">
                                        <i class="ri-box-3-fill"></i>
                                        0
                                    </span>
                                @endif
                            </td>

                            <td class="column-status-td" data-status="{{ $brand->status ? 1 : 0 }}">
                                @can('marcas.update-status')
                                    <label class="switch-tabla">
                                        <input type="checkbox" class="switch-status" data-id="{{ $brand->id }}" {{ $brand->status ? 'checked' : '' }}>
                                        <span class="slider"></span>
                                    </label>
                                @else
                                    @if ($brand->status)
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
                                <span class="{{ $brand->created_at ? '' : 'text-muted-td' }}">
                                    {{ $brand->created_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}
                                </span>
                            </td>

                            <td class="column-actions-td">
                                <button class="boton-show-actions">
                                    <i class="ri-more-fill"></i>
                                </button>

                                <div class="tabla-botones">
                                    <button type="button" class="boton-sm boton-info btn-ver-marca" data-slug="{{ $brand->slug }}">
                                        <i class="ri-eye-2-fill"></i>
                                        <span class="boton-sm-text">Ver</span>
                                    </button>

                                    @can('marcas.edit')
                                        <a href="{{ route('admin.brands.edit', $brand) }}" class="boton-sm boton-warning">
                                            <i class="ri-edit-circle-fill"></i>
                                            <span class="boton-sm-text">Editar</span>
                                        </a>
                                    @endcan

                                    @can('marcas.delete')
                                        <form action="{{ route('admin.brands.destroy', $brand) }}" method="POST" class="delete-form" data-entity="marca">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="boton-sm boton-danger">
                                                <i class="ri-delete-bin-2-fill"></i>
                                                <span class="boton-sm-text">Eliminar</span>
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

    @include('admin.brands.modals.show-modal-brand')

    @push('scripts')
        <script>
            $(document).ready(function() {
                const tableManager = new DataTableManager('#tabla', {
                    moduleName: 'brands',
                    entityNameSingular: 'marca',
                    entityNamePlural: 'marcas',
                    deleteRoute: '/admin/brands',
                    statusRoute: '/admin/brands/{id}/status',
                    exportRoutes: {
                        excel: '/admin/brands/export/excel',
                        csv: '/admin/brands/export/csv',
                        pdf: '/admin/brands/export/pdf'
                    },
                    csrfToken: '{{ csrf_token() }}',
                    pageLength: 10,
                    lengthMenu: [5, 10, 25, 50],
                    callbacks: {
                        onDraw: () => {
                            console.log('🔄 Tabla de marcas redibujada');
                        },
                        onStatusChange: (id, status) => {
                            console.log(`✅ Marca ${id} -> ${status ? 'Activo' : 'Inactivo'}`);
                        },
                        onDelete: () => {
                            console.log('🗑️ Marcas eliminadas');
                        },
                        onExport: (type, format, count) => {
                            console.log(`📤 Exportación de ${type} en ${format} (${count || 'todos'})`);
                        }
                    }
                });

                $(document).on('click', '.btn-ver-marca', function() {
                    const slug = $(this).data('slug');
                    loadBrandModal(slug);
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

</x-admin-layout>
