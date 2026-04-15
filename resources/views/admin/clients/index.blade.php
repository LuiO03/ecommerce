@section('title', 'Clientes')

<x-admin-layout :showMobileFab="true">
    <x-slot name="title">
        <div class="page-icon card-success">
            <i class="ri-user-line"></i>
        </div>
        Lista de Clientes
    </x-slot>
    <x-slot name="action">
        @can('clientes.export')
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
    </x-slot>

    <div class="actions-container">

        <div class="tabla-filtros">
            <span class="tabla-filtros-title">
                Buscar
            </span>
            <article class="tabla-buscador">
                <i class="ri-search-eye-line buscador-icon"></i>
                <input type="text" id="customSearch" placeholder="Buscar clientes por nombre o email"
                    autocomplete="off" />
                <button type="button" id="clearSearch" class="buscador-clear" title="Limpiar búsqueda">
                    <i class="ri-close-circle-fill"></i>
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
                    <select id="roleFilter">
                        <option value="">Todos los roles</option>
                        @foreach($roles as $role)
                            @if($role->name === 'Cliente')
                                <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                            @endif
                        @endforeach
                        <option value="sin-rol">Sin rol</option>
                    </select>
                    <i class="ri-shield-user-line selector-icon"></i>
                </div>
            </article>

            <article class="tabla-select-wrapper">
                <div class="selector">
                    <select id="verifiedFilter">
                        <option value="">Todos los clientes</option>
                        <option value="1">Email verificado</option>
                        <option value="0">Sin verificar</option>
                    </select>
                    <i class="ri-mail-check-line selector-icon"></i>
                </div>
            </article>

            <article class="filters-actions">
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
            </article>
        </div>

        @canany(['clientes.export', 'clientes.delete'])
        <div class="selection-bar" id="selectionBar">
            @can('clientes.export')
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
            @can('clientes.delete')
            <button id="deleteSelected" class="boton-selection boton-danger">
                <span class="boton-selection-icon">
                    <i class="ri-delete-bin-line"></i>
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

        <div class="tabla-wrapper">
            <table id="tabla" class="tabla-general display">
                <thead>
                    <tr>
                        <th class="control"></th>
                        @canany(['clientes.export', 'clientes.delete'])
                        <th class="column-check-th column-not-order">
                            <div>
                                <input type="checkbox" id="checkAll" name="checkAll">
                            </div>
                        </th>
                        @endcanany
                        <th class="column-id-th">ID</th>
                        <th class="column-name-th">Nombre</th>
                        <th class="column-last-name-th">Apellidos</th>
                        <th class="column-email-th">Email</th>
                        <th class="column-verified-th">Verificado</th>
                        @can('clientes.update-status')
                            <th class="column-status-th">Estado</th>
                        @endcan
                        <th class="column-date-th">Creado</th>
                        <th class="column-actions-th column-not-order">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr data-id="{{ $user->id }}" data-name="{{ $user->name }}">

                            <td class="control" title="Expandir detalles">
                            </td>
                            @canany(['clientes.export', 'clientes.delete'])
                            <td class="column-check-td">
                                <div>
                                    <input type="checkbox" class="check-row" id="check-row-{{ $user->id }}"
                                        name="users[]" value="{{ $user->id }}">
                                </div>
                            </td>
                            @endcanany
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
                            <td class="column-verified-td">
                                @if ($user->email_verified_at)
                                    <span class="badge badge-success" title="Email verificado">
                                        <i class="ri-checkbox-circle-fill"></i>
                                        Verificado
                                    </span>
                                @else
                                    <span class="badge badge-danger" title="Email sin verificar">
                                        <i class="ri-close-circle-fill"></i>
                                        Sin verificar
                                    </span>
                                @endif
                            </td>
                            @can('clientes.update-status')
                                <td class="column-status-td" >
                                    <label class="switch-tabla">
                                        <input type="checkbox" class="switch-status" data-id="{{ $user->id }}"
                                            {{ $user->status ? 'checked' : '' }}>
                                        <span class="slider"></span>
                                    </label>
                                </td>
                            @endcan
                            <td>
                                <span class="{{ $user->created_at ? '' : 'text-muted-td' }}">
                                    {{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : 'Sin fecha' }}
                                </span>
                            </td>
                            <td class="column-actions-td">
                                <div class="tabla-botones">
                                    <button class="boton-sm boton-info btn-ver-usuario" data-slug="{{ $user->slug }}" title="Ver Cliente">
                                        <i class="ri-eye-2-fill"></i>
                                    </button>
                                    @if (Auth::id() !== $user->id)
                                        @can('clientes.delete')
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                            class="delete-form" data-entity="cliente">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="boton-sm boton-danger" title="Eliminar Cliente">
                                                <i class="ri-delete-bin-2-fill"></i>
                                            </button>
                                        </form>
                                        @else
                                        <button class="boton-sm boton-danger disabled" title="Sin permiso para eliminar" disabled>
                                            <i class="ri-lock-fill"></i>
                                        </button>
                                        @endcan
                                    @else
                                        <button class="boton-sm boton-danger disabled"
                                            title="No puedes eliminar tu propia cuenta" disabled>
                                            <i class="ri-lock-fill"></i>
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
                    moduleName: 'clients',
                    entityNameSingular: 'cliente',
                    entityNamePlural: 'clientes',
                    deleteRoute: '/admin/users',
                    statusRoute: '/admin/users/{id}/status',
                    exportRoutes: {
                        excel: '/admin/clients/export/excel',
                        csv: '/admin/clients/export/csv',
                        pdf: '/admin/clients/export/pdf'
                    },
                    csrfToken: '{{ csrf_token() }}',

                    pageLength: 10,
                    lengthMenu: [5, 10, 25, 50],

                    features: {
                        selection: true,
                        export: true,
                        filters: true,
                        statusToggle: true,
                        responsive: true,
                        customPagination: true
                    },

                    callbacks: {
                        onDraw: () => {
                            console.log('🔄 Tabla de clientes redibujada');
                        },
                        onStatusChange: (id, status, response) => {
                            console.log(`✅ Estado actualizado: Cliente ID ${id} -> ${status ? 'Activo' : 'Inactivo'}`);
                        },
                        onDelete: () => {
                            console.log('🗑️ Clientes eliminados');
                        },
                        onExport: (type, format, count) => {
                            console.log(`📤 Exportación de clientes: ${type} (${format}) - ${count || 'todos'} registros`);
                        }
                    }
                });

                let currentRoleFilter = '';
                let currentVerifiedFilter = '';

                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    if (settings.nTable.id !== 'tabla') return true;

                    const row = tableManager.table.row(dataIndex).node();

                    if (currentRoleFilter !== '') {
                        const rowRole = $(row).find('.column-role-td').attr('data-role');
                        if (rowRole !== currentRoleFilter) {
                            return false;
                        }
                    }

                    if (currentVerifiedFilter !== '') {
                        const rowVerified = $(row).find('.column-status-td').attr('data-verified');
                        if (rowVerified !== currentVerifiedFilter) {
                            return false;
                        }
                    }

                    return true;
                });

                $('#roleFilter').on('change', function() {
                    currentRoleFilter = this.value;
                    tableManager.table.draw();
                    tableManager.checkFiltersActive();
                });

                $('#verifiedFilter').on('change', function() {
                    currentVerifiedFilter = this.value;
                    tableManager.table.draw();
                    tableManager.checkFiltersActive();
                });

                $('#clearFiltersBtn').on('click', function() {
                    currentRoleFilter = '';
                    currentVerifiedFilter = '';
                    $('#roleFilter').val('');
                    $('#verifiedFilter').val('');
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
                        }, 100);
                    })();
                @endif
            });
        </script>
    @endpush

    @include('admin.users.modals.show-modal-user')
</x-admin-layout>
