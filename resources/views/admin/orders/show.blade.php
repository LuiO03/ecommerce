@section('title', 'Detalle de Orden')

<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-info">
            <i class="ri-shopping-bag-3-line"></i>
        </div>

        <div class="page-edit-title">
            <span class="page-subtitle">Boleta Electrónica</span>
            Pedido #{{ $order->order_number }}
        </div>
    </x-slot>

    <x-slot name="action">
        @if ($order->pdf_path)
            <a href="{{ route('admin.orders.invoice-preview', $order) }}" target="_blank" class="boton-form boton-action"
                id="exportMenuBtn" title="Ver comprobante">
                <span class="boton-form-icon"><i class="ri-file-pdf-2-fill"></i></span>
                <span class="boton-form-text">Obtener PDF</span>
            </a>
        @endif
        <a href="{{ route('admin.payments.index') }}" class="boton-form boton-action">
            <span class="boton-form-icon"><i class="ri-bank-card-line"></i></span>
            <span class="boton-form-text">Pagos</span>
        </a>
        <a href="{{ route('admin.transactions.index') }}" class="boton-form boton-action">
            <span class="boton-form-icon"><i class="ri-exchange-dollar-line"></i></span>
            <span class="boton-form-text">Transacciones</span>
        </a>
        <a href="{{ route('admin.orders.index') }}" class="boton-form boton-accent">
            <span class="boton-form-icon"><i class="ri-arrow-go-back-line"></i></span>
            <span class="boton-form-text">Volver</span>
        </a>
    </x-slot>

    <div class="options-wrapper order-detail-page">
        @php
            $latestPayment = $order->latestPayment;
            $deliveryType = $order->delivery_type ?? 'delivery';
        @endphp

        <div class="form-row-fit">
            <div class="meta-group">
                <h2 class="card-title">Información de la orden</h2>
                <div class="meta-row-fit">
                    <div><strong>ID:</strong> {{ $order->id }}</div>
                    <div><strong>N° de orden:</strong> {{ $order->order_number }}</div>
                    <div>
                        <strong>Estado:</strong>
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
                        @endswitch
                    </div>
                    <div>
                        <strong>Estado de pago:</strong>
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

                            @default
                                <span class="badge badge-secondary">
                                    {{ $order->payment_status ? ucfirst($order->payment_status) : 'Sin registro' }}
                                </span>
                        @endswitch
                    </div>
                    <div>
                        <strong>Proveedor:</strong> {{ $latestPayment?->provider ? strtoupper($latestPayment->provider) : '—' }}
                    </div>
                    <div>
                        <strong>ID de pago:</strong> {{ $order->payment_id ?? '—' }}
                    </div>
                    <div>
                        <strong>Entrega:</strong>
                        @if ($deliveryType === 'pickup')
                            <span class="badge badge-secondary">
                                <i class="ri-store-2-line"></i>
                                Recojo en tienda
                            </span>
                        @else
                            <span class="badge badge-secondary">
                                <i class="ri-truck-line"></i>
                                Delivery
                            </span>
                        @endif
                    </div>
                    <div>
                        <strong>Pagado en:</strong>
                        {{ $latestPayment?->paid_at ? $latestPayment->paid_at->format('d/m/Y H:i') : '—' }}
                    </div>
                    <div>
                        <strong>Creado:</strong>
                        {{ $order->created_at ? $order->created_at->format('d/m/Y H:i') : '—' }}
                    </div>
                </div>
            </div>
            <div class="meta-group">
                <h2 class="card-title">Información del cliente</h2>
                <div class="meta-row-fit">

                    <div>
                        <strong>Cliente:</strong>
                        {{ trim((optional($order->user)->name ?? '') . ' ' . (optional($order->user)->last_name ?? '')) ?: '—' }}
                    </div>
                    <div>
                        <strong>Tipo de Documento:</strong>
                        {{ optional($order->user)->document_type ?? '—' }}
                    </div>
                    <div>
                        <strong>Número de Documento:</strong>
                        {{ optional($order->user)->document_number ?? '' }}
                    </div>
                </div>
            </div>

            <div class="meta-group">
                <h2 class="card-title">Datos de entrega</h2>
                <div class="meta-row-fit">
                    @if ($deliveryType === 'pickup')
                        <div><strong>Modo:</strong> Recojo en tienda</div>
                        <div><strong>Código de tienda:</strong> {{ $order->pickup_store_code ?? '—' }}</div>
                        <div><strong>Dirección registrada:</strong> {{ $order->shipping_address ?? '—' }}</div>
                    @else
                        <div><strong>Dirección:</strong> {{ $order->shipping_address ?? '—' }}</div>
                        <div><strong>Ciudad:</strong> {{ $order->shipping_city ?? '—' }}</div>
                        <div><strong>Teléfono:</strong> {{ $order->shipping_phone ?? '—' }}</div>
                        <div><strong>Dirección guardada:</strong> {{ $order->address?->address_line ?? '—' }}</div>
                        <div><strong>Distrito:</strong> {{ $order->address?->district ?? '—' }}</div>
                        <div><strong>Referencia:</strong> {{ $order->address?->reference ?? '—' }}</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="form-body">
            <div class="card-header">
                <span class="card-title">Productos</span>
            </div>
            <div class="tabla-wrapper">
                <table class="tabla-general w-full tabla-normal" id="table">
                    <thead>
                        <tr>
                            <th class="column-name-th text-start">Producto</th>
                            <th class="text-start">Detalles</th>
                            <th class="text-center">Cant.</th>
                            <th class="text-right">P. Unitario</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($order->items as $item)
                            <tr>
                                <td class="column-name-td">{{ $item->product->name ?? '—' }}</td>
                                <td>
                                    @if ($item->variant && $item->variant->features->isNotEmpty())
                                        @php
                                            $variantLabels = [];
                                            foreach ($item->variant->features as $feature) {
                                                $option = $feature->option;
                                                $optionName = $option->name ?? ($option->slug ?? null);
                                                $label = $optionName
                                                    ? $optionName . ': ' . $feature->value
                                                    : $feature->value;
                                                $variantLabels[] = $label;
                                            }
                                        @endphp
                                        <span>{{ implode(' · ', $variantLabels) }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-right">S/. {{ number_format((float) $item->unit_price, 2) }}
                                </td>
                                <td class="text-right">S/. {{ number_format((float) $item->line_total, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    <div class="tabla-no-data">
                                        <i class="ri-survey-line"></i>
                                        <span>No hay ítems asociados a esta orden.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="form-row-fill">
            <div class="meta-group">
                <h2 class="card-title">Resumen de totales</h2>
                <div class="meta-row-fit">
                    <div>
                        <strong>Subtotal:</strong>
                        <span>S/.
                            {{ number_format((float) ($order->subtotal ?? 0), 2) }}
                        </span>
                    </div>
                    <div>
                        <strong>Envío:</strong> <span>S/.
                            {{ number_format((float) $order->shipping_cost, 2) }}</span>
                    </div>
                </div>
                <hr class="w-full my-0 border-default">
                <div class="meta-total">
                    <span>Total:</span>
                    <span>S/.{{ number_format((float) $order->total, 2) }}</span>
                </div>
            </div>
        </div>

        @include('admin.orders.partials.payments-transactions', ['order' => $order])

        <div class="form-footer">
            <a href="{{ url()->previous() }}" class="boton-form boton-volver">
                <span class="boton-form-icon">
                    <i class="ri-arrow-left-circle-fill"></i>
                </span>
                <span class="boton-form-text">Volver</span>
            </a>
        </div>
    </div>
</x-admin-layout>
