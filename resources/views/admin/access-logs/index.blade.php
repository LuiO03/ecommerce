<x-admin-layout :showMobileFab="false">
    <x-slot name="title">
        <div class="page-icon card-info">
            <i class="ri-shield-user-line"></i>
        </div>
        Registros de Acceso
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
    </x-slot>

    <div class="actions-container">

        <!-- CONTROLES -->
        <div class="tabla-controles">
            <!-- Buscador -->
            <div class="tabla-buscador">
                <i class="ri-search-eye-line buscador-icon"></i>
                <input type="text" id="customSearch" placeholder="Buscar por usuario, email o IP"
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

                <!-- Acción -->
                <div class="tabla-select-wrapper">
                    <div class="selector">
                        <select id="actionFilter">
                            <option value="">Todas las acciones</option>
                            <option value="login">Login</option>
                            <option value="logout">Logout</option>
                            <option value="failed">Fallido</option>
                        </select>
                        <i class="ri-login-box-line selector-icon"></i>
                    </div>
                </div>

                <!-- Estado -->
                <div class="tabla-select-wrapper">
                    <div class="selector">
                        <select id="statusFilter">
                            <option value="">Todos los estados</option>
                            <option value="success">Success</option>
                            <option value="failed">Failed</option>
                        </select>
                        <i class="ri-shield-check-line selector-icon"></i>
                    </div>
                </div>

                <button type="button" id="clearFiltersBtn" class="boton-clear-filters">
                    <i class="ri-filter-off-line"></i>
                    Limpiar filtros
                </button>
            </div>
        </div>

        <!-- Barra contextual -->
        <div class="selection-bar" id="selectionBar">
            <div class="selection-actions">

                <button id="exportSelectedExcel" class="boton-selection boton-success">
                    <span class="boton-selection-icon"><i class="ri-file-excel-2-fill"></i></span>
                    <span class="boton-selection-text">Excel</span>
                    <span class="selection-badge" id="excelBadge">0</span>
                </button>

                <button id="exportSelectedCsv" class="boton-selection boton-orange">
                    <span class="boton-selection-icon"><i class="ri-file-text-fill"></i></span>
                    <span class="boton-selection-text">CSV</span>
                    <span class="selection-badge" id="csvBadge">0</span>
                </button>

                <button id="exportSelectedPdf" class="boton-selection boton-secondary">
                    <span class="boton-selection-icon"><i class="ri-file-pdf-2-fill"></i></span>
                    <span class="boton-selection-text">PDF</span>
                    <span class="selection-badge" id="pdfBadge">0</span>
                </button>

            </div>

            <div class="selection-info">
                <span id="selectionCount">0 seleccionados</span>
                <button class="selection-close" id="clearSelection">
                    <i class="ri-close-large-fill"></i>
                </button>
            </div>
        </div>

        <!-- TABLA -->
        <div class="tabla-wrapper">
            <table id="tabla" class="tabla-general display">
                <thead>
                    <tr>
                        <th class="control"></th>
                        <th class="column-check-th column-not-order">
                            <div><input type="checkbox" id="checkAll"></div>
                        </th>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Acción</th>
                        <th>Estado</th>
                        <th>IP</th>
                        <th>Fecha</th>
                        <th>Agente</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($logs as $log)
                        <tr data-id="{{ $log->id }}">
                            <td class="control"></td>
                            <td class="column-check-td">
                                <div>
                                    <input type="checkbox" class="check-row"
                                        value="{{ $log->id }}">
                                </div>
                            </td>

                            <td>{{ $log->id }}</td>

                            <td>
                                @if ($log->user)
                                    <span class="badge badge-info">
                                        <i class="ri-user-3-line"></i>
                                        {{ $log->user->name }}
                                    </span>
                                @else
                                    <span class="badge badge-gray">
                                        <i class="ri-user-unfollow-line"></i>
                                        Invitado
                                    </span>
                                @endif
                            </td>

                            <td>{{ $log->email ?? '—' }}</td>

                            <td data-action="{{ $log->action }}">
                                @if ($log->action === 'login')
                                    <span class="badge badge-primary">
                                        <i class="ri-login-box-line"></i>
                                        {{ $log->action_label }}
                                    </span>
                                @elseif($log->action === 'logout')
                                    <span class="badge badge-secondary">
                                        <i class="ri-logout-box-line"></i>
                                        {{ $log->action_label }}
                                    </span>
                                @elseif($log->action === 'failed')
                                    <span class="badge badge-danger">
                                        <i class="ri-error-warning-line"></i>
                                        {{ $log->action_label }}
                                    </span>
                                @else
                                    <span class="badge badge-gray">
                                        <i class="ri-question-line"></i>
                                        {{ $log->action_label }}
                                    </span>
                                @endif
                            </td>

                            <td data-status="{{ $log->status }}">
                                <span
                                    class="badge {{ $log->status === 'success' ? 'badge-success' : 'badge-danger' }}">
                                    {{ $log->status_label }}
                                </span>
                            </td>

                            <td>
                                <code>{{ $log->ip_address ?? '—' }}</code>
                            </td>

                            <td>
                                {{ $log->created_at->format('d/m/Y H:i') }}
                            </td>

                            <td>
                                @php($agent = $log->agent_info)

                                <span class="agent-info" title="{{ $log->user_agent }}">
                                    <span class="agent-browser">
                                        <i class="{{ $agent['browser_icon'] }} "></i>
                                        {{ $agent['browser'] }}
                                    </span>

                                    <span class="agent-dot">•</span>

                                    <span class="agent-os">
                                        <i class="{{ $agent['os_icon'] }}"></i>
                                        {{ $agent['os'] }}
                                    </span>
                                </span>
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
                    moduleName: 'access-logs',
                    entityNamePlural: 'registros',
                    deleteRoute: null, // ❌ No eliminar logs
                    exportRoutes: {
                        excel: '/admin/access-logs/export/excel',
                        csv: '/admin/access-logs/export/csv',
                        pdf: '/admin/access-logs/export/pdf'
                    },
                    csrfToken: '{{ csrf_token() }}',

                    features: {
                        selection: false,
                        statusToggle: false,
                        responsive: true,
                        export: true,
                        filters: true
                    }
                });

                // Filtros personalizados
                let actionFilter = '';
                let statusFilter = '';

                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    if (settings.nTable.id !== 'tabla') return true;

                    const row = tableManager.table.row(dataIndex).node();

                    if (actionFilter && $(row).find('[data-action]').data('action') !== actionFilter) {
                        return false;
                    }

                    if (statusFilter && $(row).find('[data-status]').data('status') !== statusFilter) {
                        return false;
                    }

                    return true;
                });

                $('#actionFilter').on('change', function() {
                    actionFilter = this.value;
                    tableManager.table.draw();
                });

                $('#statusFilter').on('change', function() {
                    statusFilter = this.value;
                    tableManager.table.draw();
                });

            });
        </script>
    @endpush
</x-admin-layout>
