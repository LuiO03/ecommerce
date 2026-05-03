@section('title', 'Auditoría del sistema')

<x-admin-layout :showMobileFab="false">
    <x-slot name="title">
        <div class="page-icon card-warning">
            <i class="ri-history-line"></i>
        </div>
        Auditoría del Sistema
    </x-slot>
    @can('auditorias.export')
        <x-slot name="action">
            <!-- Menú desplegable de exportación -->
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
            <button class="boton-form boton-action" title="Buscar o filtrar posts" id="toggleFiltersBtn">
                <span class="boton-form-icon">
                    <i class="ri-search-eye-fill"></i>
                </span>
                <span class="boton-form-text">
                    Buscar o filtrar
                </span>
            </button>
        </x-slot>
    @endcan

    <div class="actions-container">
        <!-- Filtros -->
        <aside class="tabla-filtros">
            <span class="tabla-filtros-title">
                Buscar
            </span>
            <article class="tabla-buscador">
                <button type="button" id="searchBtn" class="buscador-btn">
                <i class="ri-search-eye-line buscador-icon"></i>
                </button>
                <input type="text" id="customSearch" placeholder="Buscar por usuario, evento o modelo"
                    autocomplete="off" />
                <button type="button" id="clearSearch" class="buscador-clear">
                    <i class="ri-close-circle-fill"></i>
                </button>
            </article>
            <span class="tabla-filtros-title">
                Aplicar filtros
            </span>
            <!-- Cantidad -->
            <article class="tabla-select-wrapper">
                <div class="selector">
                    <select id="entriesSelect">
                        <option value="10" selected>10/pág.</option>
                        <option value="25">25/pág.</option>
                        <option value="50">50/pág.</option>
                    </select>
                    <i class="ri-arrow-down-s-line selector-icon"></i>
                </div>
            </article>

            <!-- Tipo de evento -->
            <article class="tabla-select-wrapper">
                <div class="selector">
                    <select id="eventFilter">
                        <option value="">Todos los eventos</option>
                        <option value="created">Created</option>
                        <option value="updated">Updated</option>
                        <option value="deleted">Deleted</option>
                    </select>
                    <i class="ri-flashlight-line selector-icon"></i>
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

        <!-- Barra contextual de selección (oculta por defecto) -->
        @can('auditorias.export')
            <div class="selection-bar" id="selectionBar">
                <div class="selection-actions">
                    <button id="exportSelectedExcel" class="boton-selection boton-success" title="Exportar registros seleccionados a Excel">
                        <span class="boton-selection-icon">
                            <i class="ri-file-excel-2-fill"></i>
                        </span>
                        <span class="boton-selection-text">Excel</span>
                        <span class="boton-selection-dot">•</span>
                        <span class="selection-badge" id="excelBadge">0</span>
                    </button>
                    <button id="exportSelectedPdf" class="boton-selection boton-secondary" title="Exportar registros seleccionados a PDF">
                        <span class="boton-selection-icon">
                            <i class="ri-file-pdf-2-fill"></i>
                        </span>
                        <span class="boton-selection-text">PDF</span>
                        <span class="boton-selection-dot">•</span>
                        <span class="selection-badge" id="pdfBadge">0</span>
                    </button>
                    <button id="exportSelectedCsv" class="boton-selection boton-orange" title="Exportar registros seleccionados a CSV">
                        <span class="boton-selection-icon">
                            <i class="ri-file-text-fill"></i>
                        </span>
                        <span class="boton-selection-text">CSV</span>
                        <span class="boton-selection-dot">•</span>
                        <span class="selection-badge" id="csvBadge">0</span>
                    </button>

                </div>
                <div class="selection-info">
                    <span id="selectionCount">0 seleccionados</span>
                    <button class="selection-close" id="clearSelection" title="Deseleccionar todo">
                        <i class="ri-close-large-fill"></i>
                    </button>
                </div>
            </div>
        @endcan

        <!-- TABLA -->
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
                        <th class="column-name-th">Usuario</th>
                        <th>Modelo</th>
                        <th>Evento</th>
                        <th>Descripción</th>
                        <th>IP</th>
                        <th class="column-date-th">Fecha</th>
                        <th class="column-actions-th column-not-order">Acciones</th>
                    </tr>
                </thead>
                <tbody>
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
                    exportRoutes: {
                        excel: '/admin/audits/export/excel',
                        csv: '/admin/audits/export/csv',
                        pdf: '/admin/audits/export/pdf'
                    },
                    csrfToken: '{{ csrf_token() }}',
                    features: {
                        selection: true,
                        statusToggle: false,
                        responsive: true,
                        export: true,
                        filters: true
                    },
                    processing: true,
                    serverSide: true,
                    ajax: '{{ route('admin.audits.data') }}'
                });

                const dt = tableManager.getTable();

                // En server-side, los filtros se aplican en backend.
                // DataTableManager envía los filtros como d.filters (ej.: filters[eventFilter]).
                $('#eventFilter').on('change', function() {
                    dt.draw();
                });

                $('#applyFiltersBtn').on('click', function() {
                    dt.draw();
                });
            });
        </script>
    @endpush
    @include('admin.audits.modals.show-modal-audit')
</x-admin-layout>
