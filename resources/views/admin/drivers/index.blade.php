@section('title', 'Conductores')

<x-admin-layout :showMobileFab="true">
    <x-slot name="title">
        <div class="page-icon card-info">
            <i class="ri-steering-2-fill"></i>
        </div>
        Lista de Conductores
    </x-slot>
    <x-slot name="action">
        <button class="boton-form boton-action" title="Buscar o filtrar posts" id="toggleFiltersBtn">
            <span class="boton-form-icon">
                <i class="ri-search-eye-fill"></i>
            </span>
            <span class="boton-form-text">
                Buscar o filtrar
            </span>
        </button>
        @can('conductores.create')
            <a href="{{ route('admin.drivers.create') }}" class="boton-form boton-accent">
                <span class="boton-form-icon"><i class="ri-add-box-fill"></i></span>
                <span class="boton-form-text">Nuevo Conductor</span>
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
                <input type="text" id="customSearch" placeholder="Buscar conductores por nombre, email o placa"
                    autocomplete="off" />
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

        <div class="tabla-wrapper">
            <table id="tabla" class="tabla-general display">
                <thead>
                    <tr>
                        <th class="control"></th>
                        <th class="column-id-th">ID</th>
                        <th class="column-name-th">Conductor</th>
                        <th class="column-email-th">Email</th>
                        <th class="column-phone-th">Teléfono</th>
                        <th class="column-status-th">Estado</th>
                        <th class="column-status-th">Vehículo</th>
                        <th class="column-plate-th">Placa</th>
                        <th class="column-date-th">Creado</th>
                        <th class="column-actions-th column-not-order">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($drivers as $driver)
                        <tr data-id="{{ $driver->id }}" data-name="{{ $driver->user?->name }}">
                            <td class="control" title="Expandir detalles"></td>
                            <td class="column-id-td">
                                <span class="id-text">{{ $driver->id }}</span>
                            </td>
                            <td class="column-name-td">
                                <div class="user-info">
                                    <span>{{ $driver->user?->name }} {{ $driver->user?->last_name }}</span>
                                </div>
                            </td>
                            <td class="column-email-td">
                                {{ $driver->user?->email ?? '—' }}
                            </td>
                            <td class="column-phone-td">
                                {{ $driver->phone ?? ($driver->user?->phone ?? '—') }}
                            </td>
                            <td class="column-status-td">
                                @switch($driver->status)
                                    @case('available')
                                        <span class="badge badge-success">
                                            <i class="ri-checkbox-circle-line"></i>
                                            Disponible
                                        </span>
                                    @break

                                    @case('busy')
                                        <span class="badge badge-warning">
                                            <i class="ri-timer-flash-line"></i>
                                            Ocupado
                                        </span>
                                    @break

                                    @case('inactive')

                                        @default
                                            <span class="badge badge-secondary">
                                                <i class="ri-pause-circle-line"></i>
                                                Inactivo
                                            </span>
                                        @break
                                    @endswitch
                                </td>
                                <td class="column-status-td">
                                    @if ($driver->vehicle_type === 'motorcycle')
                                        <span class="badge badge-info">
                                            <i class="ri-motorbike-line"></i>
                                            Moto
                                        </span>
                                    @else
                                        <span class="badge badge-info">
                                            <i class="ri-taxi-line"></i>
                                            Auto
                                        </span>
                                    @endif
                                </td>
                                <td class="column-plate-td">
                                    {{ $driver->vehicle_plate ?? 'Sin placa' }}
                                </td>
                                <td>
                                    <span>{{ $driver->created_at ? $driver->created_at->format('d/m/Y H:i') : 'Sin fecha' }}</span>
                                </td>
                                <td class="column-actions-td">
                                    <button class="boton-show-actions">
                                        <i class="ri-more-fill"></i>
                                    </button>
                                    <div class="tabla-botones">
                                        @can('conductores.edit')
                                            <a href="{{ route('admin.drivers.edit', $driver) }}" class="boton-sm boton-warning"
                                                title="Editar conductor">
                                                <i class="ri-edit-2-fill"></i>
                                                <span class="boton-sm-text">Editar Conductor</span>
                                            </a>
                                        @endcan
                                        @can('conductores.delete')
                                            <form action="{{ route('admin.drivers.destroy', $driver) }}" method="POST"
                                                class="delete-form" data-entity="conductor" style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="boton-sm boton-danger"
                                                    title="Eliminar conductor">
                                                    <i class="ri-delete-bin-6-fill"></i>
                                                    <span class="boton-sm-text">Eliminar Conductor</span>
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
                    const tableManager = new DataTableManager('#tabla', {
                        moduleName: 'drivers',
                        entityNameSingular: 'conductor',
                        entityNamePlural: 'conductores',
                        deleteRoute: null,
                        statusRoute: null,
                        exportRoutes: {},
                        csrfToken: '{{ csrf_token() }}',
                        pageLength: 10,
                        lengthMenu: [5, 10, 25, 50],
                        features: {
                            selection: false,
                            export: false,
                            filters: true,
                            statusToggle: false,
                            responsive: true,
                            customPagination: true,
                        },
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
                });
            </script>
        @endpush
    </x-admin-layout>
