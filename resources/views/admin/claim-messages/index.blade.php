@section('title', 'Reclamos y Quejas')

<x-admin-layout :showMobileFab="true">
    <x-slot name="title">
        <div class="page-icon card-warning">
            <i class="ri-alert-fill"></i>
        </div>
        Reclamos y Quejas
    </x-slot>

    <x-slot name="action">
        <button class="boton-form boton-action" title="Buscar o filtrar reclamos" id="toggleFiltersBtn">
            <span class="boton-form-icon">
                <i class="ri-search-eye-fill"></i>
            </span>
            <span class="boton-form-text">Buscar o filtrar</span>
        </button>
    </x-slot>

    <div class="actions-container">
        <aside class="tabla-filtros">
            <span class="tabla-filtros-title">Buscar</span>
            <article class="tabla-buscador">
                <i class="ri-search-eye-line buscador-icon"></i>
                <input type="text" id="customSearch" placeholder="Buscar por nombre, correo o tipo" autocomplete="off" />
                <button type="button" id="clearSearch" class="buscador-clear" title="Limpiar búsqueda">
                    <i class="ri-close-large-fill"></i>
                </button>
            </article>

            <span class="tabla-filtros-title">Aplicar filtros</span>
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
                        <option value="new">Nuevo</option>
                        <option value="read">Leído</option>
                        <option value="replied">Respondido</option>
                    </select>
                    <i class="ri-filter-3-line selector-icon"></i>
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
                <span class="boton-form-text">Mostrar resultados</span>
            </button>
        </aside>

        @can('claim-messages.delete')
            <div class="selection-bar" id="selectionBar">
                <button id="deleteSelected" class="boton-selection boton-danger" title="Eliminar registros seleccionados">
                    <span class="boton-selection-icon">
                        <i class="ri-delete-bin-fill"></i>
                    </span>
                    <span class="boton-selection-text">Eliminar</span>
                    <span class="boton-selection-dot">•</span>
                    <span class="selection-badge" id="deleteBadge">0</span>
                </button>

                <div class="selection-info">
                    <span id="selectionCount">0 seleccionados</span>
                    <button class="selection-close" id="clearSelection" title="Deseleccionar todo">
                        <i class="ri-close-large-fill"></i>
                    </button>
                </div>
            </div>
        @endcan

        <div class="tabla-wrapper">
            <table id="tabla" class="tabla-general display">
                <thead>
                    <tr>
                        <th class="control"></th>
                        @can('claim-messages.delete')
                            <th class="column-check-th column-not-order">
                                <div>
                                    <input type="checkbox" id="checkAll" name="checkAll">
                                </div>
                            </th>
                        @endcan
                        <th class="column-id-th">ID</th>
                        <th class="column-name-th">Nombre</th>
                        <th class="column-email-th">Correo</th>
                        <th class="column-phone-th">Teléfono</th>
                        <th class="column-name-th">Tipo</th>
                        <th class="column-description-th">Respuesta</th>
                        <th class="column-status-th">Estado</th>
                        <th class="column-date-th">Creado</th>
                        <th class="column-actions-th column-not-order">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($claims as $claim)
                        <tr data-id="{{ $claim->id }}" data-name="{{ $claim->name }}">
                            <td class="control" title="Expandir detalles"></td>
                            @can('claim-messages.delete')
                                <td class="column-check-td">
                                    <div>
                                        <input type="checkbox" class="check-row" id="check-row-{{ $claim->id }}"
                                            name="claims[]" value="{{ $claim->id }}">
                                    </div>
                                </td>
                            @endcan
                            <td class="column-id-td"><span class="id-text">{{ $claim->id }}</span></td>
                            <td class="column-name-td">{{ $claim->name }}</td>
                            <td class="column-email-td">{{ $claim->email }}</td>
                            <td class="column-phone-td">{{ $claim->phone ?? 'No especificado' }}</td>
                            <td class="column-name-td">
                                @if ($claim->claim_type === "reclamo")
                                    <span class="badge badge-danger">
                                        <i class="ri-alert-line"></i>
                                        Reclamo
                                    </span>
                                @elseif ($claim->claim_type === "queja")
                                    <span class="badge badge-warning">
                                        <i class="ri-emotion-sad-fill"></i>
                                        Queja
                                    </span>
                                @endif
                            </td>
                            <td class="column-description-td">
                                <span class="{{ $claim->response ? '' : 'text-muted-td' }}">
                                    {{ $claim->response ? Str::limit($claim->response, 70) : 'Sin respuesta' }}
                                </span>
                            </td>
                            <td class="column-status-td" data-status="{{ $claim->status }}">
                                @if ($claim->status === 'new')
                                    <span class="badge badge-warning"><i class="ri-error-warning-fill"></i> Nuevo</span>
                                @elseif ($claim->status === 'read')
                                    <span class="badge badge-info"><i class="ri-eye-fill"></i> Leído</span>
                                @else
                                    <span class="badge badge-success"><i class="ri-checkbox-circle-fill"></i> Respondido</span>
                                @endif
                            </td>
                            <td class="column-date-td">
                                {{ $claim->created_at ? $claim->created_at->format('d/m/Y H:i') : 'Sin fecha' }}
                            </td>
                            <td class="column-actions-td">
                                <button class="boton-show-actions"><i class="ri-more-fill"></i></button>
                                <div class="tabla-botones">
                                    @can('claim-messages.view')
                                        <button class="boton-sm boton-info btn-ver-claim-message"
                                            data-id="{{ $claim->id }}" title="Ver reclamo">
                                            <i class="ri-eye-2-fill"></i>
                                            <span class="boton-sm-text">Ver reclamo</span>
                                        </button>
                                    @endcan

                                    @can('claim-messages.reply')
                                        <button type="button" class="boton-sm boton-success btn-reply-claim-message"
                                            data-id="{{ $claim->id }}" title="Responder reclamo">
                                            <i class="ri-reply-fill"></i>
                                            <span class="boton-sm-text">Responder</span>
                                        </button>
                                    @endcan

                                    @can('claim-messages.delete')
                                        <form action="{{ route('admin.claim-messages.destroy', $claim) }}" method="POST"
                                            class="delete-form" data-entity="reclamo">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Eliminar reclamo" class="boton-sm boton-danger">
                                                <i class="ri-delete-bin-2-fill"></i>
                                                <span class="boton-sm-text">Eliminar</span>
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

    @include('admin.claim-messages.modals.show-modal-claim-message')

    @push('scripts')
        <script>
            $(document).ready(function() {
                const tableManager = new DataTableManager('#tabla', {
                    moduleName: 'claim-messages',
                    entityNameSingular: 'reclamo',
                    entityNamePlural: 'reclamos',
                    deleteRoute: '/admin/claim-messages',
                    csrfToken: '{{ csrf_token() }}',
                    pageLength: 10,
                    lengthMenu: [5, 10, 25, 50],
                    features: {
                        selection: true,
                        export: false,
                        filters: true,
                        statusToggle: false,
                        responsive: true,
                        customPagination: true
                    }
                });

            });
        </script>
    @endpush
</x-admin-layout>
