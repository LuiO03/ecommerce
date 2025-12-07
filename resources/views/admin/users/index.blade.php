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

                <!-- Rol -->
                <div class="tabla-select-wrapper">
                    <div class="selector">
                        <select id="roleFilter">
                            <option value="">Todos los roles</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                            @endforeach
                            <option value="sin-rol">Sin rol</option>
                        </select>
                        <i class="ri-shield-user-line selector-icon"></i>
                    </div>
                </div>

                <!-- Verificación de Email -->
                <div class="tabla-select-wrapper">
                    <div class="selector">
                        <select id="verifiedFilter">
                            <option value="">Todos los usuarios</option>
                            <option value="1">Email verificado</option>
                            <option value="0">Sin verificar</option>
                        </select>
                        <i class="ri-mail-check-line selector-icon"></i>
                    </div>
                </div>
                <!-- Botón para limpiar filtros -->
                <button type="button" id="clearFiltersBtn" class="boton-clear-filters" title="Limpiar todos los filtros">
                    <span class="boton-icon"><i class="ri-filter-off-line"></i></span>
                    <span class="boton-text">Limpiar filtros</span>
                </button>
            </div>

        </div>

        <!-- Barra contextual de selección (oculta por defecto) -->
        <div class="selection-bar" id="selectionBar">
            <div class="selection-actions">
                <button id="exportSelectedExcel" class="boton-selection boton-success">
                    <span class="boton-selection-icon">
                        <i class="ri-file-excel-2-fill"></i>
                    </span>
                    <span class="boton-selection-text">Excel</span>
                    l
                    <span class="selection-badge" id="excelBadge">0</span>
                </button>
                <button id="exportSelectedCsv" class="boton-selection boton-orange">
                    <span class="boton-selection-icon">
                        <i class="ri-file-text-fill"></i>
                    </span>
                    <span class="boton-selection-text">CSV</span>
                    l
                    <span class="selection-badge" id="csvBadge">0</span>
                </button>
                <button id="exportSelectedPdf" class="boton-selection boton-secondary">
                    <span class="boton-selection-icon">
                        <i class="ri-file-pdf-2-fill"></i>
                    </span>
                    <span class="boton-selection-text">PDF</span>
                    l
                    <span class="selection-badge" id="pdfBadge">0</span>
                </button>
            </div>
            <button id="deleteSelected" class="boton-selection boton-danger">
                <span class="boton-selection-icon">
                    <i class="ri-delete-bin-line"></i>
                </span>
                <span class="boton-selection-text">Eliminar</span>
                l
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
                        </th>
                        <th class="column-check-th column-not-order">
                            <div>
                                <input type="checkbox" id="checkAll" name="checkAll">
                            </div>
                        </th>
                        <th class="column-id-th">ID</th>
                        <th class="column-name-th">Nombre</th>
                        <th class="column-last-name-th">Apellidos</th>
                        <th class="column-email-th">Email</th>
                        <th class="column-role-th">Rol</th>
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
                            <td class="column-id-td">
                                <span class="id-text">{{ $user->id }}</span>
                            </td>
                            <td class="column-name-td">
                                <div class="user-info">
                                    @if ($user->image)
                                        <img src="{{ asset('storage/' . $user->image) }}" alt="{{ $user->name }}"
                                            class="user-avatar" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="user-avatar-placeholder" style="display: none; background: {{ $user->avatar_colors['background'] }}; color: {{ $user->avatar_colors['color'] }}">
                                            {{ $user->initials }}
                                        </div>
                                    @else
                                        <div class="user-avatar-placeholder"
                                            style="background: {{ $user->avatar_colors['background'] }}; color: {{ $user->avatar_colors['color'] }}">
                                            {{ $user->initials }}
                                        </div>
                                    @endif
                                    <span>{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="column-last-name-td">
                                <span class="{{ $user->last_name ? '' : 'text-muted-td' }}">
                                    {{ $user->last_name ?? 'Sin apellidos' }}
                                </span>
                            </td>
                            <td class="column-email-td">{{ $user->email }}</td>
                            <td class="column-role-td" data-role="{{ $user->roles->isNotEmpty() ? $user->roles->first()->name : 'sin-rol' }}">
                                @if($user->roles->isNotEmpty())
                                    <span class="badge badge-primary">
                                        <i class="ri-shield-user-line"></i>
                                        {{ $user->roles->first()->name }}
                                    </span>
                                @else
                                    <span class="badge badge-gray">
                                        <i class="ri-file-unknow-line"></i>
                                        Sin rol
                                    </span>
                                @endif
                            </td>
                            <td class="column-status-td" data-verified="{{ $user->email_verified_at ? '1' : '0' }}">
                                <label class="switch-tabla">
                                    <input type="checkbox" class="switch-status" data-id="{{ $user->id }}"
                                        {{ $user->status ? 'checked' : '' }}>
                                    <span class="slider"></span>
                                </label>
                            </td>
                            <td>
                                <span class="{{ $user->created_at ? '' : 'text-muted-td' }}">
                                    {{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : 'Sin fecha' }}
                                </span>
                            </td>
                            <td class="column-actions-td">
                                <div class="tabla-botones">
                                    <button class="boton-sm boton-info btn-ver-usuario" data-slug="{{ $user->slug }}" title="Ver Usuario">
                                        <span class="boton-sm-icon"><i class="ri-eye-2-fill"></i></span>
                                    </button>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="boton-sm boton-warning" title="Editar Usuario">
                                        <span class="boton-sm-icon"><i class="ri-edit-circle-fill"></i></span>
                                    </a>

                                    @if (Auth::id() !== $user->id)
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                            class="delete-form" data-entity="usuario">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="boton-sm boton-danger" title="Eliminar Usuario">
                                                <span class="boton-sm-icon"><i class="ri-delete-bin-2-fill"></i></span>
                                            </button>
                                        </form>
                                    @else
                                        <button class="boton-sm boton-danger disabled"
                                            title="No puedes eliminar tu propia cuenta" disabled>
                                            <span class="boton-sm-icon"><i class="ri-lock-fill"></i></span>
                                        </button>
                                    @endif
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
                // 🔍 FILTROS PERSONALIZADOS
                // ========================================

                // Variables globales para los filtros
                let currentRoleFilter = '';
                let currentVerifiedFilter = '';

                // Función de filtrado personalizado para DataTables
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    // Solo aplicar a esta tabla
                    if (settings.nTable.id !== 'tabla') return true;

                    const row = tableManager.table.row(dataIndex).node();

                    // Filtro por Rol
                    if (currentRoleFilter !== '') {
                        const rowRole = $(row).find('.column-role-td').attr('data-role');
                        if (rowRole !== currentRoleFilter) {
                            return false;
                        }
                    }

                    // Filtro por Verificación de Email
                    if (currentVerifiedFilter !== '') {
                        const rowVerified = $(row).find('.column-status-td').attr('data-verified');
                        if (rowVerified !== currentVerifiedFilter) {
                            return false;
                        }
                    }

                    return true;
                });

                // Filtro por Rol
                $('#roleFilter').on('change', function() {
                    currentRoleFilter = this.value;
                    tableManager.table.draw();

                    // Actualizar estado de filtros activos
                    tableManager.checkFiltersActive();

                    console.log(`🔍 Filtro Rol: ${currentRoleFilter || 'Todos'}`);
                });

                // Filtro por Verificación de Email
                $('#verifiedFilter').on('change', function() {
                    currentVerifiedFilter = this.value;
                    tableManager.table.draw();

                    // Actualizar estado de filtros activos
                    tableManager.checkFiltersActive();

                    console.log(`🔍 Filtro Verificación: ${currentVerifiedFilter === '1' ? 'Verificados' : currentVerifiedFilter === '0' ? 'Sin verificar' : 'Todos'}`);
                });

                // Limpiar filtros personalizados cuando se presiona el botón
                const originalClearHandler = $('#clearFiltersBtn').data('events')?.click;
                $('#clearFiltersBtn').on('click', function() {
                    // Limpiar filtros personalizados
                    currentRoleFilter = '';
                    currentVerifiedFilter = '';
                    $('#roleFilter').val('');
                    $('#verifiedFilter').val('');

                    console.log('🧹 Filtros personalizados limpiados');
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
    @include('admin.users.modals.show-modal-user')
</x-admin-layout>
