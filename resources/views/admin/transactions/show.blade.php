@section('title', 'Detalle de Transacción')

<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-info">
            <i class="ri-exchange-dollar-line"></i>
        </div>
        Transacción #{{ $transaction->id }}
    </x-slot>

    <x-slot name="action">
        <a href="{{ route('admin.transactions.index') }}" class="boton-form boton-accent">
            <span class="boton-form-icon"><i class="ri-arrow-go-back-line"></i></span>
            <span class="boton-form-text">Volver</span>
        </a>
    </x-slot>

    @php
        $payment = $transaction->payment;
        $gross = (float) ($payment?->amount ?? 0);
        $fee = (float) ($payment?->fee ?? 0);
        $net = (float) ($payment?->net_amount ?? ($gross - $fee));
        $feeRate = $gross > 0 ? ($fee / $gross) * 100 : 0;
    @endphp

    <div class="options-wrapper">
        <div class="form-body">
            <div class="card-header">
                <span class="card-title">Detalle del movimiento</span>
            </div>
            <div class="meta-row-fit">
                <div><strong>ID:</strong> {{ $transaction->id }}</div>
                <div><strong>Tipo:</strong> {{ strtoupper((string) $transaction->type) }}</div>
                <div><strong>Monto:</strong> S/. {{ number_format((float) $transaction->amount, 2) }}</div>
                <div><strong>Descripción:</strong> {{ $transaction->description ?: '—' }}</div>
                <div><strong>Registrado:</strong> {{ $transaction->created_at ? $transaction->created_at->format('d/m/Y H:i') : '—' }}</div>
            </div>
        </div>

        <div class="form-body">
            <div class="card-header">
                <span class="card-title">Pago relacionado</span>
            </div>
            @if ($payment)
                <div class="meta-row-fit">
                    <div>
                        <strong>Pago:</strong>
                        <a href="{{ route('admin.payments.show', $payment) }}" class="text-accent font-semibold">
                            #{{ $payment->id }}
                        </a>
                    </div>
                    <div><strong>Pasarela:</strong> {{ strtoupper((string) $payment->provider) }}</div>
                    <div><strong>Bruto:</strong> S/. {{ number_format($gross, 2) }}</div>
                    <div><strong>Comisión:</strong> S/. {{ number_format($fee, 2) }}</div>
                    <div><strong>% comisión:</strong> {{ number_format($feeRate, 2) }}%</div>
                    <div><strong>Neto:</strong> S/. {{ number_format($net, 2) }}</div>
                    <div><strong>Gateway ID:</strong> {{ $payment->transaction_id ?: '—' }}</div>
                    <div><strong>Estado:</strong> {{ ucfirst((string) $payment->status) }}</div>
                </div>

                <div class="meta-row-fit mt-4">
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
                </div>
            @else
                <div class="tabla-no-data">
                    <i class="ri-bank-card-line"></i>
                    <span>No se encontró el pago relacionado.</span>
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
