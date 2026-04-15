@section('title', 'Categorías')

<x-admin-layout :showMobileFab="true">
    <x-slot name="title">
        <div class="page-icon card-success">
            <i class="ri-price-tag-3-line"></i>
        </div>
        Lista de Categorías
    </x-slot>


    <x-slot name="action">
        <!-- Menú de exportación -->
        @can('categorias.export')
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
        @can('categorias.manage-tree')
            <a href="{{ route('admin.categories.hierarchy') }}" class="boton-form boton-action">
                <span class="boton-form-icon"><i class="ri-node-tree"></i></span>
                <span class="boton-form-text">Gestor Jerárquico</span>
            </a>
        @endcan
        <button class="boton-form boton-action" title="Buscar o filtrar posts" id="toggleFiltersBtn">
            <span class="boton-form-icon">
                <i class="ri-search-eye-fill"></i>
            </span>
            <span class="boton-form-text">
                Buscar o filtrar
            </span>
        </button>
        @can('categorias.create')
            <a href="{{ route('admin.categories.create') }}" class="boton-form boton-accent">
                <span class="boton-form-icon"><i class="ri-add-box-fill"></i></span>
                <span class="boton-form-text">Crear Categoría</span>
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
                <input type="text" id="customSearch" placeholder="Buscar categorías por nombre" autocomplete="off" />
                <button type="button" id="clearSearch" class="buscador-clear" title="Limpiar búsqueda">
                    <i class="ri-close-circle-fill"></i>
                </button>
            </article>
            <span class="tabla-filtros-title">
                Aplicar filtros
            </span>
            <!-- Cantidad -->
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

            <!-- Orden -->
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

            <!-- Estado -->
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

            <!-- Familia -->
            <article class="tabla-select-wrapper">
                <div class="selector">
                    <select id="familyFilter">
                        <option value="">Todas las familias</option>
                        @foreach ($families as $family)
                            <option value="{{ $family->id }}">{{ $family->name }}</option>
                        @endforeach
                    </select>
                    <i class="ri-folder-3-line selector-icon"></i>
                </div>
            </article>

            <!-- Nivel -->
            <article class="tabla-select-wrapper">
                <div class="selector">
                    <select id="levelFilter">
                        <option value="">Todos los niveles</option>
                        <option value="root">Raíz (sin padre)</option>
                        <option value="subcategory">Subcategoría</option>
                    </select>
                    <i class="ri-node-tree selector-icon"></i>
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
                <span class="boton-form-text">
                    Mostrar resultados
                </span>
            </button>
        </aside>

        @canany(['categorias.export', 'categorias.delete'])
            <!-- Barra contextual -->
            <div class="selection-bar" id="selectionBar">
                @can('categorias.export')
                    <div class="selection-actions">
                        <button id="exportSelectedExcel" class="boton-selection boton-success">
                            <span class="boton-selection-icon"><i class="ri-file-excel-2-fill"></i></span>
                            <span class="boton-selection-text">Excel</span>
                            <span class="boton-selection-dot">•</span>
                            <span class="selection-badge" id="excelBadge">0</span>
                        </button>
                        <button id="exportSelectedCsv" class="boton-selection boton-orange">
                            <span class="boton-selection-icon"><i class="ri-file-text-fill"></i></span>
                            <span class="boton-selection-text">CSV</span>
                            <span class="boton-selection-dot">•</span>
                            <span class="selection-badge" id="csvBadge">0</span>
                        </button>
                        <button id="exportSelectedPdf" class="boton-selection boton-secondary">
                            <span class="boton-selection-icon"><i class="ri-file-pdf-2-fill"></i></span>
                            <span class="boton-selection-text">PDF</span>
                            <span class="boton-selection-dot">•</span>
                            <span class="selection-badge" id="pdfBadge">0</span>
                        </button>
                    </div>
                @endcan
                @can('categorias.delete')
                    <button id="deleteSelected" class="boton-selection boton-danger">
                        <span class="boton-selection-icon"><i class="ri-delete-bin-line"></i></span>
                        <span class="boton-selection-text">Eliminar</span>
                        <span class="boton-selection-dot">•</span>
                        <span class="selection-badge" id="deleteBadge">0</span>
                    </button>
                @endcan
                <div class="selection-info">
                    <span id="selectionCount">0 seleccionados</span>
                    <button class="selection-close" id="clearSelection">
                        <i class="ri-close-large-fill"></i>
                    </button>
                </div>
            </div>
        @endcanany

        <!-- Tabla -->
        <div class="tabla-wrapper">
            <table id="tabla" class="tabla-general display">
                <thead>
                    <tr>
                        <th class="control"></th>
                        @canany(['categorias.export', 'categorias.delete'])
                            <th class="column-check-th column-not-order">
                                <div><input type="checkbox" id="checkAll"></div>
                            </th>
                        @endcanany
                        <th class="column-id-th">ID</th>
                        <th class="column-name-th">Nombre</th>
                        <th class="column-description-th">Descripción</th>
                        <th class="column-family-th">Familia</th>
                        <th class="column-father-th">Padre</th>
                        @can('categorias.update-status')
                            <th class="column-status-th">Estado</th>
                        @endcan
                        <th class="column-date-th">Creado</th>
                        <th class="column-actions-th column-not-order">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($categories as $cat)
                        <tr data-id="{{ $cat->id }}" data-name="{{ $cat->name }}">
                            <td class="control"></td>
                            <td class="column-check-td">
                                <div>
                                    <input type="checkbox" class="check-row" value="{{ $cat->id }}">
                                </div>
                            </td>
                            <td class="column-id-td">{{ $cat->id }}</td>
                            <td class="column-name-td">{{ $cat->name }}</td>
                            <td class="column-description-td">
                                <span class="{{ $cat->description ? '' : 'text-muted-td' }}">
                                    {{ $cat->description ?? 'Sin descripción' }}
                                </span>
                            </td>
                            <td class="column-family-td" data-family-id="{{ $cat->family_id ?? '' }}">
                                @if ($cat->family)
                                    <span class="badge badge-info">
                                        <i class="ri-archive-stack-line"></i>
                                        {{ $cat->family->name }}
                                    </span>
                                @else
                                    <span class="badge badge-gray">
                                        <i class="ri-folder-unknow-line"></i>
                                        Sin Familia
                                    </span>
                                @endif
                            </td>
                            <td class="column-parent-td"
                                data-search="{{ $cat->parent ? $cat->parent->name : 'Categoría Raíz' }}">
                                @if ($cat->parent)
                                    <span class="badge badge-secondary">
                                        <i class="ri-node-tree"></i>
                                        {{ $cat->parent->name }}
                                    </span>
                                @else
                                    <span class="badge badge-gray">
                                        <i class="ri-git-branch-line"></i>
                                        Categoría Raíz
                                    </span>
                                @endif
                            </td>
                            @can('categorias.update-status')
                                <td class="column-status-td">
                                    <label class="switch-tabla">
                                        <input type="checkbox" class="switch-status" data-id="{{ $cat->id }}"
                                            {{ $cat->status ? 'checked' : '' }}>
                                        <span class="slider"></span>
                                    </label>
                                </td>
                            @endcan
                            <td class="column-date-td">
                                <span class="{{ $cat->created_at ? '' : 'text-muted-td' }}">
                                    {{ $cat->created_at ? $cat->created_at->format('d/m/Y H:i') : 'Sin fecha' }}
                                </span>
                            </td>
                            <td class="column-actions-td">
                                <div class="tabla-botones">
                                    <button class="boton-sm boton-info btn-ver-categoria"
                                        data-slug="{{ $cat->slug }}">
                                        <i class="ri-eye-2-fill"></i>
                                    </button>
                                    @can('categorias.edit')
                                        <a href="{{ route('admin.categories.edit', $cat) }}"
                                            class="boton-sm boton-warning">
                                            <i class="ri-edit-circle-fill"></i>
                                        </a>
                                    @endcan
                                    @can('categorias.delete')
                                        <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST"
                                            class="delete-form" data-entity="categoría">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="boton-sm boton-danger">
                                                <i class="ri-delete-bin-2-fill"></i>
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
                // ========================================
                // 📊 INICIALIZACIÓN CON DATATABLEMANAGER
                // ========================================
                const tableManager = new DataTableManager('#tabla', {

                    moduleName: 'categories',
                    entityNameSingular: 'categoría',
                    entityNamePlural: 'categorías',
                    deleteRoute: '/admin/categories',
                    statusRoute: '/admin/categories/{id}/status',
                    exportRoutes: {
                        excel: '/admin/categories/export/excel',
                        csv: '/admin/categories/export/csv',
                        pdf: '/admin/categories/export/pdf'
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
                // 🔍 FILTROS PERSONALIZADOS
                // ========================================

                // Variables globales para los filtros
                let currentFamilyFilter = '';
                let currentLevelFilter = '';

                // Función de filtrado personalizado para DataTables
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    // Solo aplicar a esta tabla
                    if (settings.nTable.id !== 'tabla') return true;

                    const row = tableManager.table.row(dataIndex).node();

                    // Filtro por Familia
                    if (currentFamilyFilter !== '') {
                        const rowFamilyId = $(row).find('.column-family-td').attr('data-family-id');
                        if (rowFamilyId !== currentFamilyFilter) {
                            return false;
                        }
                    }

                    // Filtro por Nivel
                    if (currentLevelFilter !== '') {
                        const searchValue = $(row).find('.column-parent-td').attr('data-search');

                        if (currentLevelFilter === 'root' && searchValue !== 'Sin padre') {
                            return false;
                        }
                        if (currentLevelFilter === 'subcategory' && searchValue === 'Sin padre') {
                            return false;
                        }
                    }

                    return true;
                });

                // Filtro por Familia
                $('#familyFilter').on('change', function() {
                    currentFamilyFilter = this.value;
                    tableManager.table.draw();

                    // Actualizar estado de filtros activos
                    tableManager.checkFiltersActive();

                    console.log(`🔍 Filtro Familia: ${currentFamilyFilter || 'Todas'}`);
                });

                // Filtro por Nivel (Raíz/Subcategoría)
                $('#levelFilter').on('change', function() {
                    currentLevelFilter = this.value;
                    tableManager.table.draw();

                    // Actualizar estado de filtros activos
                    tableManager.checkFiltersActive();

                    console.log(`🔍 Filtro Nivel: ${currentLevelFilter || 'Todos'}`);
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

                                row[0].scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'center'
                                });

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
    @include('admin.categories.modals.show-modal-category')
</x-admin-layout>
