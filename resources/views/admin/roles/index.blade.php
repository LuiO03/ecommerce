@section('title', 'Roles')

<x-admin-layout :showMobileFab="true">
    <x-slot name="title">
        <div class="page-icon card-primary"><i class="ri-shield-user-line"></i></div>
        Lista de Roles
    </x-slot>
    <x-slot name="action">
        @can('roles.export')
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
        @can('roles.create')
            <a href="{{ route('admin.roles.create') }}" class="boton-form boton-accent">
                <span class="boton-form-icon"><i class="ri-add-box-fill"></i></span>
                <span class="boton-form-text">Crear Rol</span>
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
                <input type="text" id="customSearch" placeholder="Buscar roles por nombre" autocomplete="off" />
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
                                <span class="badge badge-primary"
                                title="{{ $role->users_count }} {{ Str::plural('usuario', $role->users_count) }}">
                                    <i class="ri-user-3-fill"></i>
                                    {{ $role->users_count }}
                                </span>
                            </td>
                            <td class="column-date-td">
                                <span class="{{ $role->created_at ? '' : 'text-muted-td' }}">
                                    {{ $role->created_at ? $role->created_at->format('d/m/Y H:i') : 'Sin fecha' }}
                                </span>
                            </td>
                            <td class="column-actions-td">
                                <button class="boton-show-actions">
                                    <i class="ri-more-fill"></i>
                                </button>
                                <div class="tabla-botones">
                                    <button class="boton-sm boton-info btn-ver-rol" data-id="{{ $role->id }}"
                                        title="Ver detalles del rol">
                                        <i class="ri-eye-2-fill"></i>
                                        <span class="boton-sm-text">Ver Rol</span>
                                    </button>
                                    {{-- === BOTÓN EDITAR === --}}
                                    @if (!$role->isProtected() && auth()->user()->can('roles.edit'))
                                        <a href="{{ route('admin.roles.edit', $role) }}"
                                            class="boton-sm boton-warning" title="Editar rol">
                                            <i class="ri-edit-circle-fill"></i>
                                            <span class="boton-sm-text">Editar Rol</span>
                                        </a>
                                    @else
                                        <button class="boton-sm boton-warning disabled" title="No editable" disabled>
                                            <i class="ri-lock-fill"></i>
                                            <span class="boton-sm-text">Editar Rol</span>
                                        </button>
                                    @endif

                                    {{-- === BOTÓN ELIMINAR === --}}
                                    @if (!$role->isProtected() && $role->users_count == 0 && auth()->user()->can('roles.delete'))
                                        <form action="{{ route('admin.roles.destroy', $role) }}" method="POST"
                                            class="delete-form" data-entity="rol">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="boton-sm boton-danger"
                                                title="Eliminar rol">
                                                <i class="ri-delete-bin-6-fill"></i>
                                                <span class="boton-sm-text">Borrar Rol</span>
                                            </button>
                                        </form>
                                    @else
                                        <button class="boton-sm boton-danger disabled" title="No se puede eliminar"
                                            disabled>
                                            <i class="ri-lock-fill"></i>
                                            <span class="boton-sm-text">Borrar Rol</span>
                                        </button>
                                    @endif
                                    {{-- === BOTÓN PERMISOS === --}}
                                    @if (!$role->isProtected() && auth()->user()->can('roles.edit'))
                                        <a href="{{ route('admin.roles.permissions', $role) }}"
                                            class="boton-sm boton-primary" title="Gestionar permisos">
                                            <i class="ri-key-2-fill"></i>
                                            <span class="boton-sm-text">Configurar Permisos</span>
                                        </a>
                                    @else
                                        <button class="boton-sm boton-primary disabled"
                                            title="No se pueden gestionar permisos" disabled>
                                            <i class="ri-lock-fill"></i>
                                            <span class="boton-sm-text">Configurar Permisos</span>
                                        </button>
                                    @endif

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
            });
        </script>
    @endpush
    @include('admin.roles.modals.show-modal-role')
</x-admin-layout>
