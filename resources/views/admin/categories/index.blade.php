<x-admin-layout :showMobileFab="true">
    <x-slot name="title">
        <div class="page-icon card-success">
            <i class="ri-stack-line"></i>
        </div>
        Lista de Categorías
    </x-slot>

    <x-slot name="action">
        <!-- Menú de exportación -->
        <div class="export-menu-container">
            <button type="button" class="boton-form boton-action" id="exportMenuBtn">
                <span class="boton-form-icon"><i class="ri-download-2-fill"></i></span>
                <span class="boton-form-text">Exportar</span>
                <i class="ri-arrow-down-s-line"></i>
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

        <a href="{{ route('admin.categories.create') }}" class="boton boton-primary">
            <span class="boton-icon"><i class="ri-add-box-fill"></i></span>
            <span class="boton-text">Crear Categoría</span>
        </a>
    </x-slot>

    <div class="actions-container">

        <!-- Controles -->
        <div class="tabla-controles">

            <div class="tabla-buscador">
                <i class="ri-search-eye-line buscador-icon"></i>
                <input type="text" id="customSearch" placeholder="Buscar categorías por nombre" autocomplete="off" />
                <button type="button" id="clearSearch" class="buscador-clear" title="Limpiar búsqueda">
                    <i class="ri-close-circle-fill"></i>
                </button>
            </div>

            <div class="tabla-filtros">
                <!-- Cantidad por página -->
                <div class="tabla-select-wrapper">
                    <div class="selector">
                        <select id="entriesSelect">
                            <option value="5">5/pág.</option>
                            <option value="10" selected>10/pág.</option>
                            <option value="25">25/pág.</option>
                            <option value="50">50/pág.</option>
                        </select>
                        <i class="ri-arrow-down-s-line selector-icon"></i>
                    </div>
                </div>

                <!-- Orden -->
                <div class="tabla-select-wrapper">
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
                </div>

                <!-- Estado -->
                <div class="tabla-select-wrapper">
                    <div class="selector">
                        <select id="statusFilter">
                            <option value="">Todos los estados</option>
                            <option value="1">Activos</option>
                            <option value="0">Inactivos</option>
                        </select>
                        <i class="ri-filter-3-line selector-icon"></i>
                    </div>
                </div>
            </div>

            <button type="button" id="clearFiltersBtn" class="boton-clear-filters">
                <span class="boton-icon"><i class="ri-filter-off-line"></i></span>
                <span class="boton-text">Limpiar filtros</span>
            </button>
        </div>

        <!-- Barra contextual -->
        <div class="selection-bar" id="selectionBar">
            <div class="selection-actions">

                <button id="exportSelectedExcel" class="boton-selection boton-success">
                    <span class="boton-selection-icon"><i class="ri-file-excel-2-fill"></i></span>
                    Excel
                    <span class="selection-badge" id="excelBadge">0</span>
                </button>

                <button id="exportSelectedCsv" class="boton-selection boton-orange">
                    <span class="boton-selection-icon"><i class="ri-file-text-fill"></i></span>
                    CSV
                    <span class="selection-badge" id="csvBadge">0</span>
                </button>

                <button id="exportSelectedPdf" class="boton-selection boton-secondary">
                    <span class="boton-selection-icon"><i class="ri-file-pdf-2-fill"></i></span>
                    PDF
                    <span class="selection-badge" id="pdfBadge">0</span>
                </button>

            </div>

            <button id="deleteSelected" class="boton-selection boton-danger">
                <span class="boton-selection-icon"><i class="ri-delete-bin-line"></i></span>
                Eliminar
                <span class="selection-badge" id="deleteBadge">0</span>
            </button>

            <div class="selection-info">
                <span id="selectionCount">0 seleccionados</span>
                <button class="selection-close" id="clearSelection">
                    <i class="ri-close-large-fill"></i>
                </button>
            </div>
        </div>

        <!-- Tabla -->
        <div class="tabla-wrapper">
            <table id="tabla" class="tabla-general display">
                <thead>
                    <tr>
                        <th class="control"></th>
                        <th class="column-check-th column-not-order">
                            <div><input type="checkbox" id="checkAll"></div>
                        </th>
                        <th class="column-id-th">ID</th>
                        <th class="column-name-th">Nombre</th>
                        <th class="column-description-th">Descripción</th>
                        <th class="column-family-th">Familia</th>
                        <th class="column-father-th">Padre</th>
                        <th class="column-status-th">Estado</th>
                        <th class="column-date-th">Fecha</th>
                        <th class="column-actions-th column-not-order">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($categories as $cat)
                        <tr data-id="{{ $cat->id }}" data-name="{{ $cat->name }}">
                            <td class="control"></td>
                            <td class="column-check-td">
                                <div>
                                    <input type="checkbox" class="check-row"
                                        value="{{ $cat->id }}">
                                </div>
                            </td>
                            <td class="column-id-td">{{ $cat->id }}</td>
                            <td class="column-name-td">{{ $cat->name }}</td>
                            <td class="column-description-td">{{ $cat->description }}</td>
                            <td class="column-family-td">{{ $cat->family?->name ?? 'Sin Familia' }}</td>
                            <td class="column-father-td">{{ $cat->parent?->name ?? 'Sin Padre' }}</td>
                            <td class="column-status-td">
                                <label class="switch-tabla">
                                    <input type="checkbox" class="switch-status"
                                        data-id="{{ $cat->id }}"
                                        {{ $cat->status ? 'checked' : '' }}>
                                    <span class="slider"></span>
                                </label>
                            </td>

                            <td class="column-date-td">
                                {{ $cat->created_at ? $cat->created_at->format('d/m/Y H:i') : 'Sin fecha' }}
                            </td>
                            <td class="column-actions-td">
                                <div class="tabla-botones">
                                    <button class="boton boton-info" data-id="{{ $cat->id }}">
                                        <span class="boton-text">Ver</span>
                                        <span class="boton-icon"><i class="ri-eye-2-fill"></i></span>
                                    </button>
                                    <a href="{{ route('admin.categories.edit', $cat) }}"
                                        class="boton boton-warning">
                                        <span class="boton-icon"><i class="ri-quill-pen-fill"></i></span>
                                        <span class="boton-text">Editar</span>
                                    </a>
                                    <form action="{{ route('admin.categories.destroy', $cat) }}"
                                        method="POST" class="delete-form"
                                        data-entity="categoría">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="boton boton-danger">
                                            <span class="boton-text">Borrar</span>
                                            <span class="boton-icon"><i class="ri-delete-bin-2-fill"></i></span>
                                        </button>
                                    </form>

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
                            console.log(`✅ Estado actualizado: ID ${id} -> ${status ? 'Activo' : 'Inactivo'}`);
                        },
                        onDelete: () => {
                            console.log('🗑️ Registros eliminados');
                        },
                        onExport: (type, format, count) => {
                            console.log(`📤 Exportación: ${type} (${format}) - ${count || 'todos'} registros`);
                        }
                    }
                });

                // ========================================
                // 🎨 RESALTAR FILA CREADA/EDITADA
                // ========================================
                @if (Session::has('highlightRow'))
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
</x-admin-layout>
