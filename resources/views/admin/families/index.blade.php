@section('title', 'Familias')

<x-admin-layout :showMobileFab="true">
    <x-slot name="title">
        <div class="page-icon card-success">
            <i class="ri-apps-line"></i>
        </div>
        Lista de Familias
    </x-slot>

    <x-slot name="action">
        <!-- Menú desplegable de exportación -->
        @can('familias.export')
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
        @can('familias.create')
            <a href="{{ route('admin.families.create') }}" class="boton-form boton-accent">
                <span class="boton-form-icon"><i class="ri-add-box-fill"></i></span>
                <span class="boton-form-text">Crear Familia</span>
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
                <input type="text" id="customSearch" placeholder="Buscar familias por nombre" autocomplete="off" />
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
            <!-- Botón para limpiar filtros -->
            <button type="button" id="clearFiltersBtn" class="boton-clear-filters" title="Limpiar todos los filtros">
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

        <!-- Barra contextual de selección (oculta por defecto) -->
        @canany(['familias.export', 'familias.delete'])
            <div class="selection-bar" id="selectionBar">
                @can('familias.export')
                    <div class="selection-actions">
                        <button id="exportSelectedExcel" class="boton-selection boton-success">
                            <span class="boton-selection-icon">
                                <i class="ri-file-excel-2-fill"></i>
                            </span>
                            <span class="boton-selection-text">Excel</span>
                            <span class="boton-selection-dot">•</span>
                            <span class="selection-badge" id="excelBadge">0</span>
                        </button>
                        <button id="exportSelectedCsv" class="boton-selection boton-orange">
                            <span class="boton-selection-icon">
                                <i class="ri-file-text-fill"></i>
                            </span>
                            <span class="boton-selection-text">CSV</span>
                            <span class="boton-selection-dot">•</span>
                            <span class="selection-badge" id="csvBadge">0</span>
                        </button>
                        <button id="exportSelectedPdf" class="boton-selection boton-secondary">
                            <span class="boton-selection-icon">
                                <i class="ri-file-pdf-2-fill"></i>
                            </span>
                            <span class="boton-selection-text">PDF</span>
                            <span class="boton-selection-dot">•</span>
                            <span class="selection-badge" id="pdfBadge">0</span>
                        </button>
                    </div>
                @endcan
                @can('familias.delete')
                    <button id="deleteSelected" class="boton-selection boton-danger">
                        <span class="boton-selection-icon">
                            <i class="ri-delete-bin-fill"></i>
                        </span>
                        <span class="boton-selection-text">Eliminar</span>
                        <span class="boton-selection-dot">•</span>
                        <span class="selection-badge" id="deleteBadge">0</span>
                    </button>
                @endcan
                <div class="selection-info">
                    <span id="selectionCount">0 seleccionados</span>
                    <button class="selection-close" id="clearSelection" title="Deseleccionar todo">
                        <i class="ri-close-large-fill"></i>
                    </button>
                </div>
            </div>
        @endcanany
        <!-- === Tabla === -->
        <div class="tabla-wrapper">
            <table id="tabla" class="tabla-general display">
                <thead>
                    <tr>
                        <th class="control"></th>
                        @canany(['familias.export', 'familias.delete'])
                            <th class="column-check-th column-not-order">
                                <div>
                                    <input type="checkbox" id="checkAll" name="checkAll">
                                </div>
                            </th>
                        @endcanany
                        <th class="column-id-th">ID</th>
                        <th class="column-name-th">Nombre</th>
                        <th class="column-description-th">Descripción</th>
                        <th class="column-categories-th">Categorías</th>
                        <th class="column-status-th">Estado</th>
                        <th class="column-date-th">Creado</th>
                        <th class="column-actions-th column-not-order">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($families as $family)
                        <tr data-id="{{ $family->id }}" data-name="{{ $family->name }}">
                            <td class="control" title="Expandir detalles"></td>
                            @canany(['familias.export', 'familias.delete'])
                                <td class="column-check-td">
                                    <div>
                                        <input type="checkbox" class="check-row" id="check-row-{{ $family->id }}"
                                            name="families[]" value="{{ $family->id }}">
                                    </div>
                                </td>
                            @endcanany
                            <td class="column-id-td">
                                <span class="id-text">{{ $family->id }}</span>
                            </td>
                            <td class="column-name-td">{{ $family->name }}</td>
                            <td class="column-description-td">
                                <span class="{{ $family->description ? '' : 'text-muted-td' }}">
                                    {{ $family->description ?? 'Sin descripción' }}
                                </span>
                            </td>
                            <td class="column-categories-td">
                                <span class="badge badge-primary"
                                    title="{{ $family->categories_count }} {{ Str::plural('categoría', $family->categories_count) }} relacionada(s)">
                                    <i class="ri-price-tag-3-fill"></i>
                                    {{ $family->categories_count }}
                                </span>
                            </td>
                            <td class="column-status-td" data-status="{{ $family->status ? 1 : 0 }}">
                                @can('familias.update-status')
                                    <label class="switch-tabla">
                                        <input type="checkbox" class="switch-status" data-id="{{ $family->id }}"
                                            {{ $family->status ? 'checked' : '' }}>
                                        <span class="slider"></span>
                                    </label>
                                @else
                                    @if ($family->status)
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
                                <span class="{{ $family->created_at ? '' : 'text-muted-td' }}">
                                    {{ $family->created_at ? $family->created_at->format('d/m/Y H:i') : 'Sin fecha' }}
                                </span>
                            </td>
                            <td class="column-actions-td">
                                <button class="boton-show-actions">
                                    <i class="ri-more-fill"></i>
                                </button>
                                <div class="tabla-botones">
                                    <button class="boton-sm boton-info btn-ver-familia"
                                        data-slug="{{ $family->slug }}" title="Ver Familia">
                                        <i class="ri-eye-2-fill"></i>
                                        <span class="boton-sm-text">Ver Familia</span>
                                    </button>
                                    @can('familias.edit')
                                        <a href="{{ route('admin.families.edit', $family) }}" title="Editar Familia"
                                            class="boton-sm boton-warning">
                                            <i class="ri-edit-circle-fill"></i>
                                            <span class="boton-sm-text">Editar Familia</span>
                                        </a>
                                    @endcan
                                    @can('familias.delete')
                                        <form action="{{ route('admin.families.destroy', $family) }}" method="POST"
                                            class="delete-form" data-entity="familia">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Eliminar Familia"
                                                class="boton-sm boton-danger">
                                                <i class="ri-delete-bin-2-fill"></i>
                                                <span class="boton-sm-text">Eliminar Familia</span>
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

        <!-- === Footer: info + paginación === -->
        <div class="tabla-footer">
            <div id="tableInfo" class="tabla-info"></div>
            <div id="tablePagination" class="tabla-paginacion"></div>
        </div>
    </div>
    @push('scripts')
        <script>
            $(document).ready(function() {
                // ========================================
                // 📊 INICIALIZACIÓN CON DATATABLEMANAGER
                // ========================================
                const tableManager = new DataTableManager('#tabla', {
                    moduleName: 'families',
                    entityNameSingular: 'familia',
                    entityNamePlural: 'familias',
                    deleteRoute: '/admin/families',
                    statusRoute: '/admin/families/{id}/status',
                    exportRoutes: {
                        excel: '/admin/families/export/excel',
                        csv: '/admin/families/export/csv',
                        pdf: '/admin/families/export/pdf'
                    },
                    csrfToken: '{{ csrf_token() }}',

                    // Configuración de DataTable
                    pageLength: 10,
                    lengthMenu: [5, 10, 25, 50],

                    // Características (todas activadas por defecto)
                    features: {
                        selection: true,
                        export: true,
                        filters: true,
                        statusToggle: true,
                        responsive: true,
                        customPagination: true
                    },

                    // Callbacks personalizados (opcional)
                    callbacks: {
                        onDraw: () => {
                            console.log('🔄 Tabla redibujada');
                        },
                        onStatusChange: (id, status, response) => {
                            console.log(
                                `✅ Estado actualizado: ID ${id} -> ${status ? 'Activo' : 'Inactivo'}`);
                        },
                        onDelete: () => {
                            console.log('🗑️ Registros eliminados');
                        },
                        onExport: (type, format, count) => {
                            console.log(
                                `📤 Exportación: ${type} (${format}) - ${count || 'todos'} registros`);
                        }
                    }
                });

                // ========================================
                // 🎨 RESALTAR FILA CREADA/EDITADA
                // ========================================
                @if (Session::has('highlightRow'))
                    (function() {
                        const navEntries = (typeof performance !== 'undefined' && typeof performance
                                .getEntriesByType === 'function') ?
                            performance.getEntriesByType('navigation') : [];
                        const legacyNav = (typeof performance !== 'undefined' && performance.navigation) ?
                            performance.navigation.type :
                            null;
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

                                // Scroll suave hacia la fila
                                row[0].scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'center'
                                });

                                // Remover la clase después de la animación
                                setTimeout(() => {
                                    row.removeClass('row-highlight');
                                }, 3000);
                            }
                        }, 100);
                    })();
                @endif

                // ========================================
                // 🛠️ API DISPONIBLES (Ejemplos de uso)
                // ========================================
                // tableManager.getTable() - Obtiene instancia DataTable
                // tableManager.getSelectedItems() - Obtiene Map de items seleccionados
                // tableManager.refresh() - Refresca la tabla
                // tableManager.clearSelection() - Limpia selección
                // tableManager.destroy() - Destruye la instancia
            });
        </script>
    @endpush
    @include('admin.families.modals.show-modal-family')
</x-admin-layout>
