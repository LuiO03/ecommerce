<x-admin-layout :showMobileFab="false">
    <x-slot name="title">
        <div class="page-icon card-warning">
            <i class="ri-history-line"></i>
        </div>
        Auditoría del Sistema
    </x-slot>

    <x-slot name="action">
        <!-- Menú de exportación placeholder (no implementado aún) -->
        <div class="export-menu-container">
            <button type="button" class="boton-form boton-action" disabled>
                <span class="boton-form-icon"><i class="ri-download-2-fill"></i></span>
                <span class="boton-form-text">Exportar (próximamente)</span>
            </button>
        </div>
    </x-slot>

    <div class="actions-container">
        <!-- CONTROLES -->
        <div class="tabla-controles">
            <!-- Buscador -->
            <div class="tabla-buscador">
                <i class="ri-search-eye-line buscador-icon"></i>
                <input type="text" id="customSearch" placeholder="Buscar por usuario, evento o modelo"
                    autocomplete="off" />
                <button type="button" id="clearSearch" class="buscador-clear">
                    <i class="ri-close-circle-fill"></i>
                </button>
            </div>

            <!-- Filtros -->
            <div class="tabla-filtros">
                <!-- Cantidad -->
                <div class="tabla-select-wrapper">
                    <div class="selector">
                        <select id="entriesSelect">
                            <option value="10" selected>10/pág.</option>
                            <option value="25">25/pág.</option>
                            <option value="50">50/pág.</option>
                        </select>
                        <i class="ri-arrow-down-s-line selector-icon"></i>
                    </div>
                </div>

                <!-- Tipo de evento -->
                <div class="tabla-select-wrapper">
                    <div class="selector">
                        <select id="eventFilter">
                            <option value="">Todos los eventos</option>
                            <option value="created">Created</option>
                            <option value="updated">Updated</option>
                            <option value="deleted">Deleted</option>
                        </select>
                        <i class="ri-flashlight-line selector-icon"></i>
                    </div>
                </div>

                <button type="button" id="clearFiltersBtn" class="boton-clear-filters">
                    <i class="ri-filter-off-line"></i>
                    Limpiar filtros
                </button>
            </div>
        </div>

        <!-- TABLA -->
        <div class="tabla-wrapper">
            <table id="tabla" class="tabla-general display">
                <thead>
                    <tr>
                        <th class="control"></th>
                        <th class="column-id-th">ID</th>
                        <th class="column-name-th">Usuario</th>
                        <th>Evento</th>
                        <th>Descripción</th>
                        <th>Id del Registro</th>
                        <th>Modelo</th>
                        <th>IP</th>
                        <th class="column-date-th">Fecha</th>
                        <th class="column-actions-th column-not-order">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($audits as $audit)
                        <tr data-id="{{ $audit->id }}">
                            <td class="control"></td>
                            <td class="column-id-td">{{ $audit->id }}</td>

                            <td class="column-name-td">
                                @if ($audit->user)
                                    <span class="badge badge-primary">
                                        <i class="ri-user-3-line"></i>
                                        {{ $audit->user->name }}
                                    </span>
                                @else
                                    <span class="badge badge-gray">
                                        <i class="ri-user-unfollow-line"></i>
                                        Sistema / Invitado
                                    </span>
                                @endif
                            </td>

                            <td data-event="{{ $audit->event }}">
                                @php($eventLabel = ucfirst($audit->event))

                                @switch($audit->event)
                                    @case('created')
                                        <span class="badge badge-success">
                                            <i class="ri-add-circle-fill"></i>
                                            Creado
                                        </span>
                                    @break

                                    @case('updated')
                                        <span class="badge badge-warning">
                                            <i class="ri-pencil-fill"></i>
                                            Actualizado
                                        </span>
                                    @break

                                    @case('deleted')
                                        <span class="badge badge-danger">
                                            <i class="ri-delete-bin-fill"></i>
                                            Eliminado
                                        </span>
                                    @break

                                    @case('status_updated')
                                        <span class="badge badge-primary">
                                            <i class="ri-refresh-fill"></i>
                                            Estado Actualizado
                                        </span>
                                    @break

                                    @case('bulk_deleted')
                                        <span class="badge badge-danger">
                                            <i class="ri-delete-bin-2-fill"></i>
                                            Eliminación Múltiple
                                        </span>
                                    @break

                                    @case('pdf_exported')
                                        <span class="badge badge-pink">
                                            <i class="ri-file-download-fill"></i>
                                            PDF Exportado
                                        </span>
                                    @break

                                    @case('excel_exported')
                                        <span class="badge badge-success">
                                            <i class="ri-file-download-fill"></i>
                                            Excel Exportado
                                        </span>
                                    @break

                                    @case('csv_exported')
                                        <span class="badge badge-orange">
                                            <i class="ri-file-download-fill"></i>
                                            CSV Exportado
                                        </span>
                                    @break

                                    @case('post_approved')
                                        <span class="badge badge-success">
                                            <i class="ri-checkbox-circle-fill"></i>
                                            Post Aprobado
                                        </span>
                                    @break
                                    @case('post_rejected')
                                        <span class="badge badge-danger">
                                            <i class="ri-close-circle-fill"></i>
                                            Post Rechazado
                                        </span>
                                    @break
                                    @case('permissions_updated')
                                        <span class="badge badge-primary">
                                            <i class="ri-shield-check-fill"></i>
                                            Permisos Actualizados
                                        </span>
                                    @break

                                    @case('profile_updated')
                                        <span class="badge badge-gray">
                                            <i class="ri-user-settings-fill"></i>
                                            Perfil Actualizado
                                        </span>
                                    @break

                                    @default
                                        <span class="badge badge-secondary">
                                            <i class="ri-question-fill"></i>
                                            {{ $eventLabel }}
                                        </span>
                                @endswitch
                            </td>

                            <td>
                                {{ $audit->description }}
                            </td>

                            <td>
                                <code>#{{ $audit->auditable_id ?? '—' }}</code>
                            </td>

                            <td>
                                <code>{{ class_basename($audit->auditable_type) ?? '—' }}</code>
                            </td>

                            <td>
                                <code>{{ $audit->ip_address ?? '—' }}</code>
                            </td>

                            <td class="column-date-td">
                                {{ optional($audit->created_at)->format('d/m/Y H:i') }}
                            </td>

                            <td class="column-actions-td">
                                <div class="tabla-botones">
                                    <button class="boton boton-info btn-ver-audit" data-id="{{ $audit->id }}"
                                        title="Ver cambios">
                                        <span class="boton-text">Ver cambios</span>
                                        <span class="boton-icon"><i class="ri-eye-2-fill"></i></span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- FOOTER -->
        <div class="tabla-footer">
            <div id="tableInfo" class="tabla-info"></div>
            <div id="tablePagination" class="tabla-paginacion"></div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                const tableManager = new DataTableManager('#tabla', {
                    moduleName: 'audits',
                    entityNamePlural: 'auditorías',
                    deleteRoute: null,
                    exportRoutes: {},
                    csrfToken: '{{ csrf_token() }}',
                    features: {
                        selection: false,
                        statusToggle: false,
                        responsive: true,
                        export: false,
                        filters: true
                    }
                });

                let eventFilter = '';

                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    if (settings.nTable.id !== 'tabla') return true;

                    const row = tableManager.table.row(dataIndex).node();

                    if (eventFilter) {
                        const rowEvent = ($(row).find('[data-event]').attr('data-event') || '').trim();
                        if (rowEvent !== eventFilter) {
                            return false;
                        }
                    }

                    return true;
                });

                $('#eventFilter').on('change', function() {
                    eventFilter = this.value;
                    tableManager.table.draw();
                });
            });
        </script>
    @endpush
    @include('admin.audits.modals.show-modal-audit')
</x-admin-layout>
