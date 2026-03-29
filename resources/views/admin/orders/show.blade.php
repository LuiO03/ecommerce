@section('title', 'Detalle de Orden')

<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-info">
            <i class="ri-shopping-bag-3-line"></i>
        </div>
        Orden #{{ $order->order_number }}
    </x-slot>

    <div class="options-wrapper order-detail-page">
        <div class="form-columns-row">
            <div class="form-column">
                <h2 class="card-title">Información de la orden</h2>
                <hr class="w-full my-0 border-default">
                <div class="form-row-fit">
                    <p><strong>ID:</strong> {{ $order->id }}</p>
                    <p><strong>N° de orden:</strong> {{ $order->order_number }}</p>
                    <p>
                        <strong>Cliente:</strong>
                        {{ trim((optional($order->user)->name ?? '') . ' ' . (optional($order->user)->last_name ?? '')) ?: '—' }}
                    </p>
                    <p>
                        <strong>Documento:</strong>
                        {{ optional($order->user)->document_type ?? '—' }}
                        {{ optional($order->user)->document_number ?? '' }}
                    </p>
                    <p>
                        <strong>Estado:</strong>
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
                    </p>
                    <p>
                        <strong>Estado de pago:</strong>
                        <span class="badge badge-secondary">{{ ucfirst($order->payment_status) }}</span>
                    </p>
                    <p>
                        <strong>ID de pago:</strong> {{ $order->payment_id ?? '—' }}
                    </p>
                    @if ($order->pdf_path)
                            <p>
                                <strong>Factura/Boleta PDF:</strong>
                                <a href="{{ route('admin.orders.invoice-preview', $order) }}" target="_blank" class="text-primary underline">
                                    Ver comprobante (vista previa HTML)
                                </a>
                            </p>
                    @endif
                    <p>
                        <strong>Creado:</strong>
                        {{ $order->created_at ? $order->created_at->format('d/m/Y H:i') : '—' }}
                    </p>

                </div>
            </div>
            <div class="form-column">
                <h2 class="card-title">Datos de envío</h2>
                <hr class="w-full my-0 border-default">
                <div class="card-body space-y-2">
                    <p><strong>Dirección:</strong> {{ $order->shipping_address }}</p>
                    <p><strong>Ciudad:</strong> {{ $order->shipping_city ?? '—' }}</p>
                    <p><strong>Teléfono:</strong> {{ $order->shipping_phone ?? '—' }}</p>
                </div>
            </div>
        </div>
        <div class="form-body">
            <div class="form-row-fit">
                <div class="card-body">
                    <div class="card-header">
                        <h2 class="card-title">Productos</h2>
                    </div>
                    <table class="tabla-general w-full">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Variante</th>
                                <th class="text-center">Cant.</th>
                                <th class="text-right">P. Unitario</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($order->items as $item)
                                <tr>
                                    <td>{{ $item->product->name ?? '—' }}</td>
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
                                    <td class="text-right">S/. {{ number_format((float) $item->unit_price, 2) }}</td>
                                    <td class="text-right">S/. {{ number_format((float) $item->line_total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No hay ítems asociados a esta
                                        orden.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="form-body">
            <h2 class="card-title">Resumen de totales</h2>
            <hr class="w-full my-0 border-default">
            <div class="form-row-fill">
                <div class="card-body space-y-2">
                    <p class="flex justify-between"><span>Subtotal:</span> <span>S/.
                            {{ number_format((float) ($order->subtotal ?? 0), 2) }}</span></p>
                    <p class="flex justify-between"><span>Envío:</span> <span>S/.
                            {{ number_format((float) $order->shipping_cost, 2) }}</span></p>
                    <p class="flex justify-between font-semibold text-lg"><span>Total:</span> <span>S/.
                            {{ number_format((float) $order->total, 2) }}</span></p>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
