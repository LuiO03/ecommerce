
<x-admin-layout :showMobileFab="true">
    <x-slot name="title">
        <div class="page-icon card-success"><i class="ri-key-line"></i></div>
        Lista de Permisos
    </x-slot>
    <x-slot name="action">
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
        <a href="{{ route('admin.permissions.create') }}" class="boton boton-primary">
            <span class="boton-icon"><i class="ri-add-box-fill"></i></span>
            <span class="boton-text">Nuevo Permiso</span>
        </a>
    </x-slot>
    <div class="actions-container">
        <!-- === Controles personalizados === -->
        <div class="tabla-controles">
            <div class="tabla-buscador">
                <i class="ri-search-eye-line buscador-icon"></i>
                <input type="text" id="customSearch" placeholder="Buscar permisos por nombre" autocomplete="off" />
                <button type="button" id="clearSearch" class="buscador-clear" title="Limpiar búsqueda">
                    <i class="ri-close-circle-fill"></i>
                </button>
            </div>
            <div class="tabla-filtros">
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
            </div>
            <!-- Botón para limpiar filtros -->
            <button type="button" id="clearFiltersBtn" class="boton-clear-filters" title="Limpiar todos los filtros">
                <span class="boton-icon"><i class="ri-filter-off-line"></i></span>
                <span class="boton-text">Limpiar filtros</span>
            </button>
        </div>
        <!-- Barra contextual de selección (oculta por defecto) -->
        <div class="selection-bar" id="selectionBar">
            <div class="selection-actions">
                <button id="exportSelectedExcel" class="boton-selection boton-success">
                    <span class="boton-selection-icon">
                        <i class="ri-file-excel-2-fill"></i>
                    </span>
                    <span class="boton-selection-text">Excel</span>
                    <span class="selection-badge" id="excelBadge">0</span>
                </button>
                <button id="exportSelectedCsv" class="boton-selection boton-orange">
                    <span class="boton-selection-icon">
                        <i class="ri-file-text-fill"></i>
                    </span>
                    <span class="boton-selection-text">CSV</span>
                    <span class="selection-badge" id="csvBadge">0</span>
                </button>
                <button id="exportSelectedPdf" class="boton-selection boton-secondary">
                    <span class="boton-selection-icon">
                        <i class="ri-file-pdf-2-fill"></i>
                    </span>
                    <span class="boton-selection-text">PDF</span>
                    <span class="selection-badge" id="pdfBadge">0</span>
                </button>
            </div>
            <button id="deleteSelected" class="boton-selection boton-danger">
                <span class="boton-selection-icon">
                    <i class="ri-delete-bin-line"></i>
                </span>
                <span class="boton-selection-text">Eliminar</span>
                <span class="selection-badge" id="deleteBadge">0</span>
            </button>
            <div class="selection-info">
                <span id="selectionCount">0 seleccionados</span>
                <button class="selection-close" id="clearSelection" title="Deseleccionar todo">
                    <i class="ri-close-large-fill"></i>
                </button>
            </div>
        </div>
        <!-- === Tabla === -->
        <div class="tabla-wrapper">
            <table id="tabla" class="tabla-general display">
                <thead>
                    <tr>
                        <th class="control"></th>
                        <th class="column-check-th column-not-order">
                            <div>
                                <input type="checkbox" id="checkAll" name="checkAll">
                            </div>
                        </th>
                        <th class="column-id-th">ID</th>
                        <th class="column-name-th">Nombre</th>
                        <th class="column-description-th">Descripción</th>
                        <th class="column-guard-th">Guard</th>
                        <th class="column-roles-th">Roles asignados</th>
                        <th class="column-date-th">Creado</th>
                        <th class="column-actions-th column-not-order">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($permissions as $permission)
                        <tr data-id="{{ $permission->id }}" data-name="{{ $permission->name }}">
                            <td class="control" title="Expandir detalles"></td>
                            <td class="column-check-td">
                                <div>
                                    <input type="checkbox" class="check-row" id="check-row-{{ $permission->id }}" name="permissions[]" value="{{ $permission->id }}">
                                </div>
                            </td>
                            <td class="column-id-td">
                                <span class="id-text">{{ $permission->id }}</span>
                            </td>
                            <td class="column-name-td"><span class="badge badge-info">{{ $permission->name }}</span></td>
                            <td class="column-description-td">
                                <span class="text-gray-700">{{ $permission->description ?? 'Sin descripción' }}</span>
                            </td>
                            <td class="column-guard-td">{{ $permission->guard_name }}</td>
                            <td class="column-roles-td">
                                @if($permission->roles->count())
                                    @foreach($permission->roles as $role)
                                        <span class="badge badge-primary">{{ $role->name }}</span>
                                    @endforeach
                                @else
                                    <span class="text-gray-400">Ninguno</span>
                                @endif
                            </td>
                            <td class="column-date-td">{{ $permission->created_at ? $permission->created_at->format('d/m/Y H:i') : 'Sin fecha' }}</td>
                            <td class="column-actions-td">
                                <div class="tabla-botones">
                                    <a href="{{ route('admin.permissions.edit', $permission) }}" class="boton boton-warning" title="Editar">
                                        <span class="boton-icon"><i class="ri-edit-2-line"></i></span>
                                        <span class="boton-text">Editar</span>
                                    </a>
                                    <form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST" class="delete-form" data-entity="permiso">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="boton boton-danger" title="Eliminar">
                                            <span class="boton-text">Borrar</span>
                                            <span class="boton-icon"><i class="ri-delete-bin-6-line"></i></span>
                                        </button>
                                    </form>
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
                    moduleName: 'permissions',
                    entityNameSingular: 'permiso',
                    entityNamePlural: 'permisos',
                    deleteRoute: '/admin/permissions',
                    exportRoutes: {
                        excel: '/admin/permissions/export/excel',
                        csv: '/admin/permissions/export/csv',
                        pdf: '/admin/permissions/export/pdf'
                    },
                    csrfToken: '{{ csrf_token() }}',
                    pageLength: 10,
                    lengthMenu: [5, 10, 25, 50],
                    features: {
                        selection: true,
                        export: true,
                        filters: true,
                        responsive: true,
                        customPagination: true
                    },
                    callbacks: {
                        onDraw: () => {
                            console.log('🔄 Tabla redibujada');
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
                            row[0].scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                            setTimeout(() => {
                                row.removeClass('row-highlight');
                            }, 3000);
                        }
                    }, 100);
                @endif
            });
        </script>
    @endpush
</x-admin-layout>