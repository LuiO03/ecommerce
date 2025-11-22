<x-admin-layout :showMobileFab="true">
    <x-slot name="title">
        <div class="page-icon card-success">
            <i class="ri-user-line"></i>
        </div>
        Lista de Usuarios
    </x-slot>
    <x-slot name="action">
        <!-- Menú desplegable de exportación -->
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
        <a href="{{ route('admin.users.create') }}" class="boton boton-primary">
            <span class="boton-icon"><i class="ri-add-box-fill"></i></span>
            <span class="boton-text">Crear Usuario</span>
        </a>
    </x-slot>

    <div class="actions-container">
        <!-- === Controles personalizados === -->
        <div class="tabla-controles">
            <div class="tabla-buscador">
                <i class="ri-search-eye-line buscador-icon"></i>
                <input type="text" id="customSearch" placeholder="Buscar usuarios por nombre o email"
                    autocomplete="off" />
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
                </button>
                <button id="exportSelectedCsv" class="boton-selection boton-info">
                    <span class="boton-selection-icon">
                        <i class="ri-file-text-fill"></i>
                    </span>
                    <span class="boton-selection-text">CSV</span>
                </button>
                <button id="exportSelectedPdf" class="boton-selection boton-accent">
                    <span class="boton-selection-icon">
                        <i class="ri-file-pdf-2-fill"></i>
                    </span>
                    <span class="boton-selection-text">PDF</span>
                </button>
                <button id="deleteSelectedBtn" class="boton-selection boton-danger">
                    <span class="boton-selection-icon">
                        <i class="ri-delete-bin-6-line"></i>
                    </span>
                    <span class="boton-selection-text">Eliminar</span>
                </button>
            </div>
            <div class="selection-info">
                <span id="selectionCount"></span>
                <button type="button" id="clearSelectionBtn" title="Deseleccionar todo">
                    <i class="ri-close-line"></i>
                </button>
            </div>
        </div>
        <!-- === Tabla === -->
        <div class="tabla-wrapper">
            <table id="tabla" class="tabla-general display">
                <thead>
                    <tr>
                        <th class="control"></th>
                        </th>
                        <th class="column-check-th column-not-order">
                            <div>
                                <input type="checkbox" id="checkAll" name="checkAll">
                            </div>
                        </th>
                        <th class="column-name-th">Nombre</th>
                        <th class="column-email-th">Email</th>
                        <th class="column-dni-th">DNI</th>
                        <th class="column-phone-th">Teléfono</th>
                        <th class="column-status-th">Estado</th>
                        <th class="column-date-th">Creado</th>
                        <th class="column-actions-th column-not-order">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr data-id="{{ $user->id }}" data-name="{{ $user->name }}">

                            <td class="control" title="Expandir detalles">
                            </td>
                            <td class="column-check-td">
                                <div>
                                    <input type="checkbox" class="check-row" id="check-row-{{ $user->id }}"
                                        name="users[]" value="{{ $user->id }}">
                                </div>
                            </td>
                            <td class="column-name-td">
                                <div class="user-info">
                                    @if ($user->image && file_exists(public_path('storage/' . $user->image)))
                                        <img src="{{ asset('storage/' . $user->image) }}" alt="{{ $user->name }}"
                                            class="user-avatar">
                                    @else
                                        <div class="user-avatar-placeholder"
                                            style="background: {{ $user->avatar_colors['background'] }}; color: {{ $user->avatar_colors['color'] }}">
                                            {{ $user->initials }}
                                        </div>
                                    @endif
                                    <span>{{ $user->name }} {{ $user->last_name }}</span>
                                </div>
                            </td>
                            <td class="column-email-td">{{ $user->email }}</td>
                            <td class="column-dni-td">{{ $user->dni ?? 'Sin DNI' }}</td>
                            <td class="column-phone-td">{{ $user->phone ?? 'Sin teléfono' }}</td>
                            <td class="column-status-td">
                                <label class="switch-tabla">
                                    <input type="checkbox" class="switch-status" data-id="{{ $user->id }}"
                                        {{ $user->status ? 'checked' : '' }}>
                                    <span class="slider"></span>
                                </label>
                            </td>
                            <td>{{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : 'Sin fecha' }}</td>
                            <td class="column-actions-td">
                                <div class="tabla-botones">
                                    <button class="boton-sm boton-info" data-id="{{ $user->id }}">
                                        <span class="boton-sm-icon"><i class="ri-eye-2-fill"></i></span>
                                    </button>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="boton-sm boton-warning">
                                        <span class="boton-sm-icon"><i class="ri-edit-circle-fill"></i></span>
                                    </a>
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                        class="delete-form" data-entity="usuario">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="boton-sm boton-danger">
                                            <span class="boton-sm-icon"><i class="ri-delete-bin-2-fill"></i></span>
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
            document.addEventListener('DOMContentLoaded', function() {
                const tableManager = new DataTableManager('#tabla', {
                    moduleName: 'users',
                    entityNameSingular: 'usuario',
                    entityNamePlural: 'usuarios',
                    deleteRoute: '/admin/users',
                    statusRoute: '/admin/users/{id}/status',
                    exportRoutes: {
                        excel: '/admin/users/export/excel',
                        csv: '/admin/users/export/csv',
                        pdf: '/admin/users/export/pdf'
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
            });
        </script>
    @endpush
</x-admin-layout>
