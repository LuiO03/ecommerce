@section('title', 'Detalle de Pago')

<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-info">
            <i class="ri-bank-card-line"></i>
        </div>
        Pago #{{ $payment->id }}
    </x-slot>

    <x-slot name="action">
        <a href="{{ route('admin.payments.index') }}" class="boton-form boton-accent">
            <span class="boton-form-icon"><i class="ri-arrow-go-back-line"></i></span>
            <span class="boton-form-text">Volver</span>
        </a>
    </x-slot>

    @php
        $gross = (float) $payment->amount;
        $fee = (float) ($payment->fee ?? 0);
        $net = (float) ($payment->net_amount ?? ($gross - $fee));
        $feeRate = $gross > 0 ? ($fee / $gross) * 100 : 0;
    @endphp

    <div class="options-wrapper">
        <div class="form-row-fit">
            <div class="meta-group">
                <h2 class="card-title">Resumen de comisión de pasarela</h2>
                <div class="meta-row-fit">
                    <div><strong>Pasarela:</strong> {{ strtoupper((string) $payment->provider) }}</div>
                    <div><strong>Bruto:</strong> S/. {{ number_format($gross, 2) }}</div>
                    <div><strong>Comisión:</strong> S/. {{ number_format($fee, 2) }}</div>
                    <div><strong>% comisión:</strong> {{ number_format($feeRate, 2) }}%</div>
                    <div><strong>Neto:</strong> S/. {{ number_format($net, 2) }}</div>
                </div>
            </div>
        </div>

        <div class="form-body">
            <div class="card-header">
                <span class="card-title">Datos del pago</span>
            </div>
            <div class="meta-row-fit">
                <div><strong>ID:</strong> {{ $payment->id }}</div>
                <div>
                    <strong>Orden:</strong>
                    @if ($payment->order)
                        <a href="{{ route('admin.orders.show', $payment->order) }}" class="text-accent font-semibold">
                            {{ $payment->order->order_number }}
                        </a>
                    @else
                        —
                    @endif
                </div>
                <div><strong>Cliente:</strong> {{ $payment->order?->user?->name ?? '—' }}</div>
                <div><strong>Gateway ID:</strong> {{ $payment->transaction_id ?: '—' }}</div>
                <div><strong>Estado:</strong> {{ ucfirst((string) $payment->status) }}</div>
                <div><strong>Pagado en:</strong> {{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : '—' }}</div>
                <div><strong>Creado:</strong> {{ $payment->created_at ? $payment->created_at->format('d/m/Y H:i') : '—' }}</div>
            </div>
        </div>

        <div class="form-body">
            <div class="card-header">
                <span class="card-title">Transacciones asociadas</span>
            </div>
            @if ($payment->transactions->isEmpty())
                <div class="tabla-no-data">
                    <i class="ri-exchange-dollar-line"></i>
                    <span>Este pago no tiene transacciones asociadas.</span>
                </div>
            @else
                <div class="tabla-wrapper">
                    <table class="tabla-general w-full tabla-normal">
                        <thead>
                            <tr>
                                <th class="text-center">ID</th>
                                <th>Tipo</th>
                                <th class="text-right">Monto</th>
                                <th>Descripción</th>
                                <th>Fecha</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payment->transactions->sortByDesc('id') as $transaction)
                                <tr>
                                    <td class="text-center">{{ $transaction->id }}</td>
                                    <td><span class="badge badge-secondary">{{ strtoupper((string) $transaction->type) }}</span></td>
                                    <td class="text-right">S/. {{ number_format((float) $transaction->amount, 2) }}</td>
                                    <td>{{ $transaction->description ?: '—' }}</td>
                                    <td>{{ $transaction->created_at ? $transaction->created_at->format('d/m/Y H:i') : '—' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.transactions.show', $transaction) }}" class="boton-table boton-editar" title="Ver transacción">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
