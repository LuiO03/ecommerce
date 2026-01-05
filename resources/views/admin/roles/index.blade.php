@section('title', 'Roles')

<x-admin-layout :showMobileFab="true">
    <x-slot name="title">
        <div class="page-icon card-primary"><i class="ri-shield-user-line"></i></div>
        Lista de Roles
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
        <a href="{{ route('admin.roles.create') }}" class="boton boton-primary">
            <span class="boton-icon"><i class="ri-add-box-fill"></i></span>
            <span class="boton-text">Crear Rol</span>
        </a>
    </x-slot>
    <div class="actions-container">
        <!-- === Controles personalizados === -->
        <div class="tabla-controles">
            <div class="tabla-buscador">
                <i class="ri-search-eye-line buscador-icon"></i>
                <input type="text" id="customSearch" placeholder="Buscar roles por nombre" autocomplete="off" />
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
                <!-- Botón para limpiar filtros -->
                <button type="button" id="clearFiltersBtn" class="boton-clear-filters"
                    title="Limpiar todos los filtros">
                    <span class="boton-icon"><i class="ri-filter-off-line"></i></span>
                    <span class="boton-text">Limpiar filtros</span>
                </button>
            </div>

        </div>
        <!-- === Tabla === -->
        <div class="tabla-wrapper">
            <table id="tabla" class="tabla-general display">
                <thead>
                    <tr>
                        <th class="control"></th>
                        <th class="column-id-th">ID</th>
                        <th class="column-name-th">Nombre</th>
                        <th class="column-description-th">Descripción</th>
                        <th class="column-users-th">Usuarios</th>
                        <th class="column-date-th">Creado</th>
                        <th class="column-actions-th column-not-order">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $role)
                        <tr data-id="{{ $role->id }}" data-name="{{ $role->name }}">
                            <td class="control" title="Expandir detalles"></td>
                            <td class="column-id-td">
                                <span class="id-text">{{ $role->id }}</span>
                            </td>
                            <td class="column-name-td">
                                {{ $role->name }}</td>
                            <td class="column-description-td">
                                <span class="{{ $role->description ? '' : 'text-muted-td' }}">
                                    {{ $role->description ?? 'Sin descripción' }}
                                </span>
                            </td>
                            <td class="column-users-td">
                                <span class="badge badge-info">
                                    {{ $role->users_count }}
                                    <i class="ri-user-3-line"></i>
                                </span>
                            </td>
                            <td class="column-date-td">
                                {{ $role->created_at ? $role->created_at->format('d/m/Y H:i') : 'Sin fecha' }}</td>
                            <td class="column-actions-td">
                                <div class="tabla-botones">
                                    {{-- === BOTÓN EDITAR === --}}
                                    @if (!in_array($role->name, ['Administrador', 'Superadministrador']))
                                        <a href="{{ route('admin.roles.edit', $role) }}" class="boton boton-warning"
                                            title="Editar rol">
                                            <span class="boton-icon"><i class="ri-edit-2-fill"></i></span>
                                            <span class="boton-text">Editar</span>
                                        </a>
                                    @else
                                        <button class="boton boton-warning disabled" title="No editable" disabled>
                                            <span class="boton-icon"><i class="ri-lock-fill"></i></span>
                                            <span class="boton-text">Editar</span>
                                        </button>
                                    @endif

                                    {{-- === BOTÓN ELIMINAR === --}}
                                    @if (!in_array($role->name, ['Administrador', 'Superadministrador']) && $role->users_count == 0)
                                        <form action="{{ route('admin.roles.destroy', $role) }}" method="POST"
                                        class="delete-form" data-entity="rol">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="boton boton-danger" title="Eliminar rol">
                                                <span class="boton-text">Borrar</span>
                                                <span class="boton-icon"><i class="ri-delete-bin-6-fill"></i></span>
                                            </button>
                                        </form>
                                    @else
                                        <button class="boton boton-danger disabled" title="No se puede eliminar"
                                            disabled>
                                            <span class="boton-text">Borrar</span>
                                            <span class="boton-icon"><i class="ri-lock-fill"></i></span>
                                        </button>
                                    @endif
                                    <a href="{{ route('admin.roles.permissions', $role) }}"
                                        class="boton boton-primary" title="Ver y gestionar permisos">
                                        <span class="boton-icon"><i class="ri-key-2-fill"></i></span>
                                        <span class="boton-text">Permisos</span>
                                    </a>
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
                    moduleName: 'roles',
                    entityNameSingular: 'rol',
                    entityNamePlural: 'roles',
                    deleteRoute: '/admin/roles',
                    exportRoutes: {
                        excel: '/admin/roles/export/excel',
                        csv: '/admin/roles/export/csv',
                        pdf: '/admin/roles/export/pdf'
                    },
                    csrfToken: '{{ csrf_token() }}',
                    pageLength: 10,
                    lengthMenu: [5, 10, 25, 50],
                    features: {
                        selection: false,
                        export: true,
                        filters: true,
                        statusToggle: false,
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
                        }, 100);
                    })();
                @endif
            });
        </script>
    @endpush
</x-admin-layout>
