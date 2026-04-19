@section('title', 'Órdenes')

<x-admin-layout :showMobileFab="true">
    <x-slot name="title">
        <div class="page-icon card-info">
            <i class="ri-shopping-bag-3-line"></i>
        </div>
        Lista de Pedidos
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
        <button class="boton-form boton-action" title="Buscar o filtrar posts" id="toggleFiltersBtn">
            <span class="boton-form-icon">
                <i class="ri-search-eye-fill"></i>
            </span>
            <span class="boton-form-text">
                Buscar o filtrar
            </span>
        </button>
    </x-slot>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    @endpush

    <div class="actions-container">
        <aside class="tabla-filtros">
            <span class="tabla-filtros-title">
                Buscar
            </span>
            <article class="tabla-buscador">
                <i class="ri-search-eye-line buscador-icon"></i>
                <input type="text" id="customSearch" placeholder="Buscar órdenes por cliente o N°" autocomplete="off" />
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
                        <option value="date-desc">Más recientes</option>
                        <option value="date-asc">Más antiguos</option>
                        <option value="name-asc">Cliente (A-Z)</option>
                        <option value="name-desc">Cliente (Z-A)</option>
                        <option value="total-desc">Monto (mayor a menor)</option>
                        <option value="total-asc">Monto (menor a mayor)</option>
                    </select>
                    <i class="ri-sort-asc selector-icon"></i>
                </div>
            </article>

            <article class="tabla-select-wrapper">
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
            </article>

            <article class="tabla-select-wrapper">
                <div class="selector">
                    <select id="paymentStatusFilter">
                        <option value="">Pago (todos)</option>
                        <option value="pending">Pendiente</option>
                        <option value="paid">Pagado</option>
                        <option value="processing">En proceso</option>
                        <option value="refunded">Reembolsado</option>
                        <option value="failed">Fallido</option>
                    </select>
                    <i class="ri-bank-card-line selector-icon"></i>
                </div>
            </article>

            <article class="tabla-select-wrapper tabla-select-double">
                <div class="selector">
                    <input type="text" id="dateRange" name="dateRange" placeholder="Seleccionar rango de fechas" autocomplete="off">
                    <i class="ri-calendar-line selector-icon"></i>
                </div>
            </article>

            <!-- Botón para limpiar filtros -->
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
        </aside>

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
                        <th class="column-name-th">Cliente</th>
                        <th class="column-order-th">N° Orden</th>
                        <th class="column-status-th">Entrega</th>
                        <th class="column-total-th">Total</th>
                        <th class="column-status-th">Estado</th>
                        <th class="column-status-th">Tipo de Pago</th>
                        <th class="column-status-th">Estado de Pago</th>
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
                                {{ $order->user->name ?? '—' }}
                            </td>
                            <td class="column-order-td">
                                {{ $order->order_number }}
                            </td>
                            <td class="column-status-td" data-status="{{ $order->delivery_type ?? 'delivery' }}">
                                @if (($order->delivery_type ?? 'delivery') === 'pickup')
                                    <span class="badge badge-secondary">
                                        <i class="ri-store-2-line"></i>
                                        Recojo
                                    </span>
                                    @if ($order->pickup_store_code)
                                        <div class="text-xs text-muted mt-1">{{ $order->pickup_store_code }}</div>
                                    @endif
                                @else
                                    <span class="badge badge-primary">
                                        <i class="ri-truck-line"></i>
                                        Delivery
                                    </span>
                                @endif
                            </td>
                            <td class="column-total-td">
                                S/. {{ number_format((float) $order->total, 2) }}
                            </td>
                            <td class="column-status-td" data-status="{{ $order->status }}">
                                @switch($order->status)
                                    @case('pending')
                                        <span class="badge badge-warning">
                                            <i class="ri-time-line"></i>
                                            Pendiente
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
                                    @break
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
                                    @default
                                        <span class="badge badge-secondary">
                                            <i class="ri-question-line"></i>
                                            {{ ucfirst($order->status) }}
                                        </span>

                                @endswitch
                            </td>
                            <td class="column-status-td" data-status="{{ $order->payment_status }}">
                                <span class="badge badge-secondary">
                                    {{ $order->payment_method ? strtoupper($order->payment_method) : '—' }}
                                </span>
                            </td>
                            <td class="column-status-td" data-status="{{ $order->payment_status }}">
                                @switch($order->payment_status)
                                    @case('pending')
                                        <span class="badge badge-warning">
                                            <i class="ri-time-line"></i>
                                            Pendiente
                                        </span>
                                    @break
                                    @case('paid')
                                        <span class="badge badge-success">
                                            <i class="ri-checkbox-circle-line"></i>
                                            Pagado
                                        </span>
                                    @break
                                    @case('failed')
                                        <span class="badge badge-danger">
                                            <i class="ri-error-warning-line"></i>
                                            Fallido
                                        </span>
                                    @break
                                    @case('refunded')
                                        <span class="badge badge-info">
                                            <i class="ri-refund-2-line"></i>
                                            Reembolsado
                                        </span>
                                    @break
                                    @case('cancelled')
                                        <span class="badge badge-danger">
                                            <i class="ri-close-circle-line"></i>
                                            Cancelado
                                        </span>
                                    @break
                                    @default
                                        <span class="badge badge-secondary">
                                            <i class="ri-question-line"></i>
                                            {{ $order->payment_status ? ucfirst($order->payment_status) : '—' }}
                                        </span>
                                @endswitch
                            </td>
                            <td>
                                <span>{{ $order->created_at ? $order->created_at->format('d/m/Y H:i') : 'Sin fecha' }}</span>
                            </td>
                            <td class="column-actions-td">
                                <button class="boton-show-actions">
                                    <i class="ri-more-fill"></i>
                                </button>
                                <div class="tabla-botones">
                                    @can('ordenes.view')
                                    <a href="{{ route('admin.orders.show', $order) }}" class="boton-sm boton-warning" title="Ver orden">
                                        <i class="ri-eye-fill"></i>
                                        <span class="boton-sm-text">Ver Pedido</span>
                                    </a>
                                    @endcan
                                    @can('ordenes.export')
                                    @if ($order->pdf_path)
                                        <a href="{{ asset('storage/' . $order->pdf_path) }}" target="_blank" class="boton-sm boton-danger" title="Ver boleta PDF">
                                            <i class="ri-file-pdf-2-fill"></i>
                                            <span class="boton-sm-text">Boleta PDF</span>
                                        </a>
                                    @endif
                                    @endcan
                                    @can('ordenes.update')
                                        @if ($order->status === 'pending')
                                            <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="order-dispatch-form" data-order="{{ $order->order_number }}" style="display:inline-block;">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="processing">
                                                <button type="submit" class="boton-sm boton-primary" title="Marcar como listo para despachar">
                                                    <i class="ri-check-double-line"></i>
                                                    <span class="boton-sm-text">Despachar</span>
                                                </button>
                                            </form>
                                        @elseif ($order->status === 'processing')
                                            <a href="{{ route('admin.orders.show', $order) }}" class="boton-sm boton-info" title="Asignar repartidor">
                                                <i class="ri-user-follow-line"></i>
                                                <span class="boton-sm-text">Asignar repartidor</span>
                                            </a>
                                        @endif
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
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
        <script>
            $(document).ready(function() {
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

                const table = tableManager.getTable();
                const idColumn = tableManager.config.columns.id;
                const nameColumn = tableManager.config.columns.name;
                const dateColumn = tableManager.config.columns.date;
                const totalColumn = $('#tabla thead th.column-total-th').index();

                const originalCheckFiltersActive = tableManager.checkFiltersActive.bind(tableManager);

                tableManager.checkFiltersActive = function () {
                    // Reimplementar lógica de filtros activos + incluir rango de fechas

                    const searchVal = $('#customSearch').val();
                    let anyFilterActive = !!(searchVal && searchVal.trim() !== '');

                    // Selects estándar de filtros (excepto entriesSelect)
                    $('.tabla-filtros .selector select').each(function () {
                        const $select = $(this);
                        if ($select.attr('id') === 'entriesSelect') return;

                        const hasValue = ($select.val() ?? '') !== '';
                        $select.closest('.tabla-select-wrapper').toggleClass('filter-active', hasValue);
                        if (hasValue) anyFilterActive = true;
                    });

                    // Rango de fechas (input con Flatpickr)
                    const rangeVal = $('#dateRange').val();
                    const hasRange = !!(rangeVal && rangeVal.trim() !== '');
                    $('.tabla-select-wrapper.tabla-select-double').toggleClass('filter-active', hasRange);
                    if (hasRange) anyFilterActive = true;

                    // Botón global de limpiar filtros
                    $('#clearFiltersBtn').toggleClass('active', anyFilterActive);
                };

                // Inicializar Flatpickr en modo rango sobre un solo input
                if (window.flatpickr) {
                    flatpickr('#dateRange', {
                        mode: 'range',
                        dateFormat: 'Y-m-d',
                        maxDate: 'today',
                        locale: window.flatpickr.l10ns.es || 'es',
                        rangeSeparator: ' a ',
                        onChange: function () {
                            $('#dateRange').trigger('change');
                        }
                    });
                }

                // Eliminar el filtro genérico de status de DataTableManager (usa switch-status)
                if ($.fn.dataTable.ext.search.length) {
                    $.fn.dataTable.ext.search.pop();
                }

                // Re-configurar ordenamiento personalizado (incluye monto total)
                $('#sortFilter').off('change').on('change', function() {
                    const sortValue = $(this).val();
                    const wrapper = $(this).closest('.tabla-select-wrapper');

                    if (!sortValue) {
                        wrapper.removeClass('filter-active');
                        if (idColumn !== null) {
                            table.order([[idColumn, 'desc']]).draw();
                        } else {
                            table.order([[0, 'desc']]).draw();
                        }
                        return;
                    }

                    wrapper.addClass('filter-active');

                    switch (sortValue) {
                        case 'name-asc':
                            if (nameColumn !== null) table.order([[nameColumn, 'asc']]).draw();
                            break;
                        case 'name-desc':
                            if (nameColumn !== null) table.order([[nameColumn, 'desc']]).draw();
                            break;
                        case 'date-desc':
                            if (dateColumn !== null) table.order([[dateColumn, 'desc']]).draw();
                            break;
                        case 'date-asc':
                            if (dateColumn !== null) table.order([[dateColumn, 'asc']]).draw();
                            break;
                        case 'total-desc':
                            if (totalColumn !== -1) table.order([[totalColumn, 'desc']]).draw();
                            break;
                        case 'total-asc':
                            if (totalColumn !== -1) table.order([[totalColumn, 'asc']]).draw();
                            break;
                    }
                });

                function parseDateTime(value) {
                    if (!value || value.trim() === '' || value === 'Sin fecha') {
                        return null;
                    }

                    const parts = value.split(' ');
                    const dateParts = (parts[0] || '').split('/');
                    const timeParts = (parts[1] || '00:00').split(':');

                    if (dateParts.length !== 3) return null;

                    const day = parseInt(dateParts[0], 10);
                    const month = parseInt(dateParts[1], 10) - 1;
                    const year = parseInt(dateParts[2], 10);

                    const hours = parseInt(timeParts[0] || '0', 10);
                    const minutes = parseInt(timeParts[1] || '0', 10);

                    return new Date(year, month, day, hours, minutes, 0, 0);
                }

                // Filtro combinado por estado, estado de pago y rango de fechas
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    if (settings.nTable.id !== 'tabla') return true;

                    const $row = $(table.row(dataIndex).node());

                    const selectedStatus = $('#statusFilter').val();
                    const selectedPaymentStatus = $('#paymentStatusFilter').val();
                    const dateRangeVal = $('#dateRange').val();

                    // Estado de la orden (primera columna status)
                    if (selectedStatus) {
                        const rowStatusCell = $row.find('td.column-status-td').eq(0);
                        const rowStatus = (rowStatusCell.data('status') || '').toString();
                        if (rowStatus !== selectedStatus) {
                            return false;
                        }
                    }

                    // Estado de pago (segunda columna status)
                    if (selectedPaymentStatus) {
                        const rowPaymentCell = $row.find('td.column-status-td').eq(1);
                        const rowPaymentStatus = (rowPaymentCell.data('status') || '').toString();
                        if (rowPaymentStatus !== selectedPaymentStatus) {
                            return false;
                        }
                    }

                    // Filtro por rango de fechas (columna "Creado") usando un solo input con rango
                    if (dateColumn !== null && dateRangeVal) {
                        let dateFromVal = null;
                        let dateToVal = null;

                        const parts = dateRangeVal.split(' a ');
                        if (parts.length === 2) {
                            dateFromVal = parts[0].trim();
                            dateToVal = parts[1].trim();
                        }

                        if (!dateFromVal && !dateToVal) {
                            return true;
                        }
                        const createdText = data[dateColumn];
                        const createdDate = parseDateTime(createdText);

                        // Si la fila no tiene fecha válida y se está filtrando por rango, la excluimos
                        if (!createdDate) {
                            return false;
                        }

                        if (dateFromVal) {
                            const fromParts = dateFromVal.split('-');
                            const fromDate = new Date(
                                parseInt(fromParts[0], 10),
                                parseInt(fromParts[1], 10) - 1,
                                parseInt(fromParts[2], 10),
                                0,
                                0,
                                0,
                                0
                            );
                            if (createdDate < fromDate) {
                                return false;
                            }
                        }

                        if (dateToVal) {
                            const toParts = dateToVal.split('-');
                            const toDate = new Date(
                                parseInt(toParts[0], 10),
                                parseInt(toParts[1], 10) - 1,
                                parseInt(toParts[2], 10),
                                23,
                                59,
                                59,
                                999
                            );
                            if (createdDate > toDate) {
                                return false;
                            }
                        }
                    }

                    return true;
                });

                $('#statusFilter, #paymentStatusFilter, #dateRange').on('change', function() {
                    table.draw();
                    tableManager.checkFiltersActive();
                });

                // Extender botón de "Limpiar filtros" para también resetear fechas
                $('#clearFiltersBtn').off('click').on('click', function() {
                    tableManager.clearFilters();
                    const $dateRange = $('#dateRange');
                    $dateRange.val('');
                    const inputEl = $dateRange[0];
                    if (inputEl && inputEl._flatpickr) {
                        inputEl._flatpickr.clear();
                    }
                    table.draw();
                    tableManager.checkFiltersActive();
                });

                // Confirmación antes de marcar una orden como "lista para despachar"
                $(document).on('submit', '.order-dispatch-form', function(e) {
                    e.preventDefault();
                    const form = this;
                    const orderNumber = $(form).data('order');

                    if (typeof window.showConfirm !== 'function') {
                        form.submit();
                        return;
                    }

                    window.showConfirm({
                        type: 'info',
                        header: 'Confirmar despacho',
                        title: '¿Marcar orden como lista para despachar?',
                        message: `Vas a marcar la orden <strong>${orderNumber}</strong> como <strong>En proceso</strong>.`,
                        confirmText: 'Sí, despachar',
                        cancelText: 'No, cancelar',
                        onConfirm: function () {
                            form.submit();
                        }
                    });
                });
            });
        </script>
    @endpush
</x-admin-layout>
