@section('title', 'Órdenes')

<x-admin-layout :showMobileFab="true">
    <x-slot name="title">
        <div class="page-icon card-primary">
            <i class="ri-shopping-bag-3-line"></i>
        </div>
        Lista de Órdenes
    </x-slot>

    <x-slot name="action">
        @can('ordenes.export')
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
    </x-slot>

    <div class="actions-container">
        <div class="tabla-controles">
            <div class="tabla-buscador">
                <i class="ri-search-eye-line buscador-icon"></i>
                <input type="text" id="customSearch" placeholder="Buscar órdenes por cliente o N°" autocomplete="off" />
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
                            <option value="date-desc">Más recientes</option>
                            <option value="date-asc">Más antiguos</option>
                            <option value="name-asc">Cliente (A-Z)</option>
                            <option value="name-desc">Cliente (Z-A)</option>
                        </select>
                        <i class="ri-sort-asc selector-icon"></i>
                    </div>
                </div>

                <div class="tabla-select-wrapper">
                    <div class="selector">
                        <select id="statusFilter">
                            <option value="">Todos los estados</option>
                            <option value="pending">Pendientes</option>
                            <option value="paid">Pagadas</option>
                            <option value="processing">En proceso</option>
                            <option value="shipped">Enviadas</option>
                            <option value="delivered">Entregadas</option>
                            <option value="cancelled">Canceladas</option>
                        </select>
                        <i class="ri-filter-3-line selector-icon"></i>
                    </div>
                </div>

                <button type="button" id="clearFiltersBtn" class="boton-clear-filters" title="Limpiar todos los filtros">
                    <span class="boton-icon"><i class="ri-filter-off-line"></i></span>
                    <span class="boton-text">Limpiar filtros</span>
                </button>
            </div>
        </div>

        @can('ordenes.export')
        <div class="selection-bar" id="selectionBar">
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
            <div class="selection-info">
                <span id="selectionCount">0 seleccionadas</span>
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
                        @can('ordenes.export')
                        <th class="column-check-th column-not-order">
                            <div>
                                <input type="checkbox" id="checkAll" name="checkAll">
                            </div>
                        </th>
                        @endcan
                        <th class="column-id-th">ID</th>
                        <th class="column-name-th">N° Orden</th>
                        <th class="column-client-th">Cliente</th>
                        <th class="column-total-th">Total</th>
                        <th class="column-status-th">Estado</th>
                        <th class="column-status-th">Pago</th>
                        <th class="column-status-th">ID Pago</th>
                        <th class="column-date-th">Creado</th>
                        <th class="column-actions-th column-not-order">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr data-id="{{ $order->id }}" data-name="{{ $order->order_number }}">
                            <td class="control" title="Expandir detalles"></td>
                            @can('ordenes.export')
                            <td class="column-check-td">
                                <div>
                                    <input type="checkbox" class="check-row" id="check-row-{{ $order->id }}" name="orders[]" value="{{ $order->id }}">
                                </div>
                            </td>
                            @endcan
                            <td class="column-id-td">
                                <span class="id-text">{{ $order->id }}</span>
                            </td>
                            <td class="column-name-td">
                                {{ $order->order_number }}
                            </td>
                            <td class="column-client-td">
                                {{ $order->user->name ?? '—' }}
                            </td>
                            <td class="column-total-td">
                                S/. {{ number_format((float) $order->total, 2) }}
                            </td>
                            <!--
                                $table->enum('status', [
                                    'pending',     // pendiente
                                    'paid',        // pagado
                                    'processing',  // en proceso
                                    'shipped',     // enviado
                                    'delivered',   // entregado
                                    'cancelled'    // cancelado
                                ])->default('pending');
                            -->
                            <td class="column-status-td" data-status="{{ $order->status }}">
                                @switch($order->status)
                                    @case('pending')
                                        <span class="badge badge-warning">
                                            <i class="ri-time-line"></i>
                                            Pendiente
                                        </span>
                                    @break
                                    @case('paid')
                                        <span class="badge badge-success">
                                            <i class="ri-check-line"></i>
                                            Pagada
                                        </span>
                                    @break
                                    @case('processing')
                                        <span class="badge badge-orange">
                                            <i class="ri-loader-4-line"></i>
                                            En proceso
                                        </span>
                                    @break
                                    @case('shipped')
                                        <span class="badge badge-secondary">
                                            <i class="ri-truck-line"></i>
                                            Enviada
                                        </span>
                                    @case('delivered')
                                        <span class="badge badge-secondary">
                                            <i class="ri-checkbox-multiple-line"></i>
                                            Entregada
                                        </span>
                                    @break
                                    @case('refunded')
                                        <span class="badge badge-info">
                                            <i class="ri-refund-2-line"></i>
                                            Reembolsada
                                        </span>
                                    @break
                                    @case('cancelled')
                                        <span class="badge badge-danger">
                                            <i class="ri-close-circle-line"></i>
                                            Cancelada
                                        </span>
                                    @break
                                @endswitch
                            </td>
                            <td class="column-status-td" data-status="{{ $order->payment_status }}">
                                <span class="badge badge-secondary">{{ ucfirst($order->payment_status) }}</span>
                            </td>
                            <td class="column-status-td">
                                <span>{{ $order->payment_id ?? '—' }}</span>
                            </td>
                            <td>
                                <span>{{ $order->created_at ? $order->created_at->format('d/m/Y H:i') : 'Sin fecha' }}</span>
                            </td>
                            <td class="column-actions-td">
                                <div class="tabla-botones">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="boton-sm boton-warning" title="Ver orden">
                                        <span class="boton-sm-icon"><i class="ri-eye-fill"></i></span>
                                    </a>
                                    @if ($order->pdf_path)
                                        <a href="{{ asset('storage/' . $order->pdf_path) }}" target="_blank" class="boton-sm boton-danger" title="Ver boleta PDF">
                                            <span class="boton-sm-icon"><i class="ri-file-pdf-2-fill"></i></span>
                                        </a>
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
                    moduleName: 'orders',
                    entityNameSingular: 'orden',
                    entityNamePlural: 'ordenes',
                    deleteRoute: null,
                    statusRoute: null,
                    exportRoutes: {
                        excel: '/admin/orders/export/excel',
                        csv: '/admin/orders/export/csv',
                        pdf: '/admin/orders/export/pdf'
                    },
                    csrfToken: '{{ csrf_token() }}',
                    pageLength: 10,
                    lengthMenu: [5, 10, 25, 50],
                    features: {
                        selection: true,
                        export: true,
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
