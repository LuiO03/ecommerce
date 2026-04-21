@section('title', 'Pagos')

<x-admin-layout :showMobileFab="true">
    <x-slot name="title">
        <div class="page-icon card-info">
            <i class="ri-bank-card-line"></i>
        </div>
        Lista de Pagos
    </x-slot>

    @php
        $totalBruto = (float) $payments->sum('amount');
        $totalComisiones = (float) $payments->sum('fee');
        $totalNeto = (float) $payments->sum(function ($payment) {
            return $payment->net_amount ?? (float) $payment->amount - (float) $payment->fee;
        });
        $ratioComision = $totalBruto > 0 ? ($totalComisiones / $totalBruto) * 100 : 0;
    @endphp

    <div class="options-wrapper">

        <div class="module-stats">
            <div class="stat-card ripple-card">
                <div class="stat-icon card-info">
                    <i class="ri-wallet-line"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Pagos:</span>
                    <h1 class="stat-value">{{ $payments->count() }}</h1>
                </div>
            </div>
            <div class="stat-card ripple-card">
                <div class="stat-icon card-success">
                    <i class="ri-safe-3-line"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Bruto:</span>
                    <h3 class="stat-value">S/. {{ number_format($totalBruto, 2) }}</h3>
                </div>
            </div>
            <div class="stat-card ripple-card">
                <div class="stat-icon card-danger">
                    <i class="ri-hand-coin-line"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Comisiones:</span>
                    <h3 class="stat-value">S/. {{ number_format($totalComisiones, 2) }}</h3>
                </div>
            </div>
            <div class="stat-card ripple-card">
                <div class="stat-icon card-warning">
                    <i class="ri-percent-line"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-label">% Comisión:</span>
                    <h3 class="stat-value">{{ number_format($ratioComision, 2) }}%</h3>
                </div>
            </div>
            <div class="stat-card ripple-card">
                <div class="stat-icon card-info">
                    <i class="ri-money-dollar-circle-line"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Neto:</span>
                    <h3 class="stat-value">S/. {{ number_format($totalNeto, 2) }}</h3>
                </div>
            </div>

        </div>


        <div class="form-body">
            <div class="card-header">
                <span class="card-title">Ranking de comisiones por pasarela</span>
            </div>

            @if ($ranking->isEmpty())
                <div class="tabla-no-data">
                    <i class="ri-bar-chart-box-line"></i>
                    <span>No hay datos para el ranking con los filtros actuales.</span>
                </div>
            @else
                <div class="tabla-wrapper">
                    <table class="tabla-general w-full tabla-normal">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th>Pasarela</th>
                                <th class="text-right">Pagos</th>
                                <th class="text-right">Bruto</th>
                                <th class="text-right">Comisiones</th>
                                <th class="text-right">% Comisión</th>
                                <th class="text-right">Neto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ranking as $index => $item)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>{{ strtoupper((string) $item->provider) }}</td>
                                    <td class="text-right">{{ (int) $item->payments_count }}</td>
                                    <td class="text-right">S/. {{ number_format((float) $item->gross_total, 2) }}</td>
                                    <td class="text-right">S/. {{ number_format((float) $item->fee_total, 2) }}</td>
                                    <td class="text-right">{{ number_format((float) $item->fee_rate, 2) }}%</td>
                                    <td class="text-right">S/. {{ number_format((float) $item->net_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="form-body">
            <div class="card-header">
                <span class="card-title">Detalle de pagos por pasarela</span>
            </div>

            <aside class="tabla-filtros">
                <span class="tabla-filtros-title">Buscar</span>
                <article class="tabla-buscador">
                    <i class="ri-search-eye-line buscador-icon"></i>
                    <input type="text" id="customSearch" placeholder="Orden, cliente, pasarela o gateway ID"
                        autocomplete="off" />
                    <button type="button" id="clearSearch" class="buscador-clear" title="Limpiar búsqueda">
                        <i class="ri-close-circle-fill"></i>
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
                            <option value="name-asc">Pasarela (A-Z)</option>
                            <option value="name-desc">Pasarela (Z-A)</option>
                            <option value="date-desc">Más recientes</option>
                            <option value="date-asc">Más antiguos</option>
                        </select>
                        <i class="ri-sort-asc selector-icon"></i>
                    </div>
                </article>

                <article class="tabla-select-wrapper">
                    <div class="selector">
                        <select id="providerFilter">
                            <option value="">Pasarelas (todas)</option>
                            @foreach ($payments->pluck('provider')->filter()->unique()->sort() as $provider)
                                <option value="{{ $provider }}">{{ strtoupper((string) $provider) }}</option>
                            @endforeach
                        </select>
                        <i class="ri-bank-card-line selector-icon"></i>
                    </div>
                </article>

                <article class="tabla-select-wrapper">
                    <div class="selector">
                        <select id="paymentStatusFilter">
                            <option value="">Estados (todos)</option>
                            <option value="pending">Pendiente</option>
                            <option value="paid">Pagado</option>
                            <option value="failed">Fallido</option>
                            <option value="refunded">Reembolsado</option>
                        </select>
                        <i class="ri-filter-3-line selector-icon"></i>
                    </div>
                </article>

                <article class="tabla-select-wrapper tabla-select-double">
                    <div class="selector">
                        <input type="date" id="dateFromFilter" class="w-full bg-transparent" placeholder="Desde">
                    </div>
                </article>

                <article class="tabla-select-wrapper tabla-select-double">
                    <div class="selector">
                        <input type="date" id="dateToFilter" class="w-full bg-transparent" placeholder="Hasta">
                    </div>
                </article>

                <button type="button" id="clearFiltersBtn" class="boton-clear-filters"
                    title="Limpiar todos los filtros">
                    <span class="boton-icon"><i class="ri-filter-off-line"></i></span>
                    <span class="boton-text">Limpiar filtros</span>
                </button>

                <button class="boton-form boton-accent" title="Aplicar filtros y búsqueda" id="applyFiltersBtn">
                    <span class="boton-form-icon"><i class="ri-filter-fill"></i></span>
                    <span class="boton-form-text">Mostrar resultados</span>
                </button>
            </aside>


            @if ($payments->isEmpty())
                <div class="tabla-no-data">
                    <i class="ri-bank-card-line"></i>
                    <span>No hay pagos registrados.</span>
                </div>
            @else
                <div class="tabla-wrapper">
                    <table id="tabla" class="tabla-general display">
                        <thead>
                            <tr>
                                <th class="control"></th>
                                <th class="column-id-th">ID</th>
                                <th>Orden</th>
                                <th>Cliente</th>
                                <th class="column-name-th">Pasarela</th>
                                <th class="text-right">Bruto</th>
                                <th class="text-right">Comisión</th>
                                <th class="text-right">% Comisión</th>
                                <th class="text-right">Neto</th>
                                <th class="column-status-th">Estado</th>
                                <th>ID transacción</th>
                                <th class="column-date-th">Pagado</th>
                                <th class="column-actions-th column-not-order">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payments as $payment)
                                @php
                                    $gross = (float) $payment->amount;
                                    $fee = (float) ($payment->fee ?? 0);
                                    $net = (float) ($payment->net_amount ?? $gross - $fee);
                                    $feeRate = $gross > 0 ? ($fee / $gross) * 100 : 0;
                                    $paidDate = $payment->paid_at ?? $payment->created_at;
                                @endphp
                                <tr data-id="{{ $payment->id }}"
                                    data-name="{{ strtoupper((string) $payment->provider) }}">
                                    <td class="control" title="Expandir detalles"></td>
                                    <td class="column-id-td">
                                        <span class="id-text">{{ $payment->id }}</span>
                                    </td>
                                    <td>
                                        @if ($payment->order)
                                            <a href="{{ route('admin.orders.show', $payment->order) }}"
                                                class="text-accent font-semibold">
                                                {{ $payment->order->order_number }}
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $payment->order?->user?->name ?? '—' }}</td>
                                    <td class="column-name-td"
                                        data-provider="{{ mb_strtolower((string) $payment->provider) }}">
                                        {{ strtoupper((string) $payment->provider) }}</td>
                                    <td class="text-right">S/. {{ number_format($gross, 2) }}</td>
                                    <td class="text-right">S/. {{ number_format($fee, 2) }}</td>
                                    <td class="text-right">{{ number_format($feeRate, 2) }}%</td>
                                    <td class="text-right">S/. {{ number_format($net, 2) }}</td>
                                    <td class="column-status-td"
                                        data-status="{{ mb_strtolower((string) $payment->status) }}">
                                        @switch($payment->status)
                                            @case('pending')
                                                <span class="badge badge-warning"><i class="ri-time-line"></i>
                                                    Pendiente</span>
                                            @break

                                            @case('paid')
                                                <span class="badge badge-success"><i class="ri-checkbox-circle-line"></i>
                                                    Pagado</span>
                                            @break

                                            @case('failed')
                                                <span class="badge badge-danger"><i class="ri-error-warning-line"></i>
                                                    Fallido</span>
                                            @break

                                            @case('refunded')
                                                <span class="badge badge-info"><i class="ri-refund-2-line"></i>
                                                    Reembolsado</span>
                                            @break

                                            @default
                                                <span
                                                    class="badge badge-secondary">{{ ucfirst((string) $payment->status) }}</span>
                                        @endswitch
                                    </td>
                                    <td>{{ $payment->transaction_id ?: '—' }}</td>
                                    <td class="column-date-td"
                                        data-paid-date="{{ $paidDate ? $paidDate->format('Y-m-d') : '' }}">
                                        {{ $paidDate ? $paidDate->format('d/m/Y H:i') : 'Sin fecha' }}
                                    </td>
                                    <td class="column-actions-td">
                                        <a href="{{ route('admin.payments.show', $payment) }}"
                                            class="boton-table boton-editar" title="Ver detalle">
                                            <i class="ri-eye-line"></i>
                                        </a>
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
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (!document.querySelector('#tabla')) {
                    return;
                }

                const tableManager = new DataTableManager('#tabla', {
                    moduleName: 'payments',
                    entityNameSingular: 'pago',
                    entityNamePlural: 'pagos',
                    csrfToken: '{{ csrf_token() }}',
                    pageLength: 10,
                    lengthMenu: [5, 10, 25, 50],
                    features: {
                        selection: false,
                        export: false,
                        filters: true,
                        statusToggle: false,
                        responsive: true,
                        customPagination: true
                    }
                });

                let currentProviderFilter = '';
                let currentPaymentStatusFilter = '';
                let currentDateFrom = '';
                let currentDateTo = '';

                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    if (settings.nTable.id !== 'tabla') return true;

                    const row = tableManager.table.row(dataIndex).node();
                    if (!row) return true;

                    if (currentProviderFilter !== '') {
                        const rowProvider = $(row).find('.column-name-td').attr('data-provider') || '';
                        if (rowProvider !== currentProviderFilter) {
                            return false;
                        }
                    }

                    if (currentPaymentStatusFilter !== '') {
                        const rowStatus = $(row).find('.column-status-td').attr('data-status') || '';
                        if (rowStatus !== currentPaymentStatusFilter) {
                            return false;
                        }
                    }

                    if (currentDateFrom !== '' || currentDateTo !== '') {
                        const rowDateRaw = $(row).find('.column-date-td').attr('data-paid-date') || '';
                        if (!rowDateRaw) {
                            return false;
                        }

                        const rowDate = new Date(`${rowDateRaw}T00:00:00`);
                        if (Number.isNaN(rowDate.getTime())) {
                            return false;
                        }

                        if (currentDateFrom !== '') {
                            const fromDate = new Date(`${currentDateFrom}T00:00:00`);
                            if (!Number.isNaN(fromDate.getTime()) && rowDate < fromDate) {
                                return false;
                            }
                        }

                        if (currentDateTo !== '') {
                            const toDate = new Date(`${currentDateTo}T23:59:59`);
                            if (!Number.isNaN(toDate.getTime()) && rowDate > toDate) {
                                return false;
                            }
                        }
                    }

                    return true;
                });

                $('#providerFilter').on('change', function() {
                    currentProviderFilter = ($(this).val() || '').toString().toLowerCase();
                    tableManager.table.draw();
                    tableManager.checkFiltersActive();
                });

                $('#paymentStatusFilter').on('change', function() {
                    currentPaymentStatusFilter = ($(this).val() || '').toString().toLowerCase();
                    tableManager.table.draw();
                    tableManager.checkFiltersActive();
                });

                $('#dateFromFilter').on('change', function() {
                    currentDateFrom = ($(this).val() || '').toString();
                    tableManager.table.draw();
                    tableManager.checkFiltersActive();
                });

                $('#dateToFilter').on('change', function() {
                    currentDateTo = ($(this).val() || '').toString();
                    tableManager.table.draw();
                    tableManager.checkFiltersActive();
                });

                $('#applyFiltersBtn').on('click', function() {
                    tableManager.table.draw();
                });

                $('#clearFiltersBtn').on('click', function() {
                    currentProviderFilter = '';
                    currentPaymentStatusFilter = '';
                    currentDateFrom = '';
                    currentDateTo = '';

                    $('#providerFilter').val('');
                    $('#paymentStatusFilter').val('');
                    $('#dateFromFilter').val('');
                    $('#dateToFilter').val('');

                    tableManager.table.draw();
                    tableManager.checkFiltersActive();
                });
            });
        </script>
    @endpush
</x-admin-layout>
