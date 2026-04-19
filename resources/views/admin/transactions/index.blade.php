@section('title', 'Transacciones')

<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-info">
            <i class="ri-exchange-dollar-line"></i>
        </div>
        Lista de Transacciones
    </x-slot>

    @php
        $totalTransacciones = (float) $transactions->sum('amount');
        $totalComisiones = (float) $transactions->filter(function ($transaction) {
            return mb_strtolower((string) $transaction->type) === 'fee';
        })->sum('amount');
    @endphp

    <div class="options-wrapper">
        <div class="form-row-fit">
            <div class="meta-group">
                <h2 class="card-title">Resumen de movimientos</h2>
                <div class="meta-row-fit">
                    <div><strong>Movimientos:</strong> {{ $transactions->count() }}</div>
                    <div><strong>Total movimientos:</strong> S/. {{ number_format($totalTransacciones, 2) }}</div>
                    <div><strong>Comisiones registradas (type=fee):</strong> S/. {{ number_format($totalComisiones, 2) }}</div>
                </div>
            </div>
        </div>

        <div class="form-body">
            <div class="card-header">
                <span class="card-title">Detalle de transacciones</span>
            </div>

            @if ($transactions->isEmpty())
                <div class="tabla-no-data">
                    <i class="ri-exchange-dollar-line"></i>
                    <span>No hay transacciones registradas.</span>
                </div>
            @else
                <div class="tabla-wrapper">
                    <table class="tabla-general w-full tabla-normal">
                        <thead>
                            <tr>
                                <th class="text-center">ID</th>
                                <th>Pago</th>
                                <th>Orden</th>
                                <th>Cliente</th>
                                <th>Pasarela</th>
                                <th>Tipo</th>
                                <th class="text-right">Monto</th>
                                <th>Descripción</th>
                                <th>Fecha</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transactions as $transaction)
                                <tr>
                                    <td class="text-center">{{ $transaction->id }}</td>
                                    <td>
                                        @if ($transaction->payment)
                                            <a href="{{ route('admin.payments.show', $transaction->payment) }}" class="text-accent font-semibold">
                                                #{{ $transaction->payment->id }}
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        @if ($transaction->payment?->order)
                                            <a href="{{ route('admin.orders.show', $transaction->payment->order) }}" class="text-accent font-semibold">
                                                {{ $transaction->payment->order->order_number }}
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $transaction->payment?->order?->user?->name ?? '—' }}</td>
                                    <td>{{ strtoupper((string) ($transaction->payment?->provider ?? '—')) }}</td>
                                    <td><span class="badge badge-secondary">{{ strtoupper((string) $transaction->type) }}</span></td>
                                    <td class="text-right">S/. {{ number_format((float) $transaction->amount, 2) }}</td>
                                    <td>{{ $transaction->description ?: '—' }}</td>
                                    <td>{{ $transaction->created_at ? $transaction->created_at->format('d/m/Y H:i') : '—' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.transactions.show', $transaction) }}" class="boton-table boton-editar" title="Ver detalle">
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
