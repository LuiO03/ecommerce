<div class="form-body">
    <div class="card-header">
        <span class="card-title">Pagos y transacciones</span>
    </div>

    @if ($order->payments->isEmpty())
        <div class="tabla-no-data">
            <i class="ri-bank-card-line"></i>
            <span>No hay pagos registrados para esta orden.</span>
        </div>
    @else
        <div class="tabla-wrapper">
            <table class="tabla-general w-full tabla-normal">
                <thead>
                    <tr>
                        <th class="text-center">ID Pago</th>
                        <th>Proveedor</th>
                        <th class="text-right">Monto</th>
                        <th class="text-right">Comisión</th>
                        <th class="text-right">Neto</th>
                        <th class="text-center">Estado</th>
                        <th>Transacción Gateway</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->payments->sortByDesc('id') as $payment)
                        <tr>
                            <td class="text-center">{{ $payment->id }}</td>
                            <td>{{ strtoupper($payment->provider) }}</td>
                            <td class="text-right">S/. {{ number_format((float) $payment->amount, 2) }}</td>
                            <td class="text-right">S/. {{ number_format((float) $payment->fee, 2) }}</td>
                            <td class="text-right">S/. {{ number_format((float) ($payment->net_amount ?? 0), 2) }}</td>
                            <td class="text-center">
                                @switch($payment->status)
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
                                        <span class="badge badge-secondary">{{ ucfirst($payment->status) }}</span>
                                @endswitch
                            </td>
                            <td>{{ $payment->transaction_id ?? '—' }}</td>
                            <td>{{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : '—' }}</td>
                        </tr>

                        @if ($payment->transactions->isNotEmpty())
                            <tr>
                                <td colspan="8" class="p-0">
                                    <div class="tabla-wrapper">
                                        <table class="tabla-general w-full tabla-normal">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">ID Mov.</th>
                                                    <th>Tipo</th>
                                                    <th class="text-right">Monto</th>
                                                    <th>Descripción</th>
                                                    <th>Registrado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($payment->transactions->sortByDesc('id') as $transaction)
                                                    <tr>
                                                        <td class="text-center">{{ $transaction->id }}</td>
                                                        <td>
                                                            <span class="badge badge-secondary">{{ strtoupper($transaction->type) }}</span>
                                                        </td>
                                                        <td class="text-right">S/. {{ number_format((float) $transaction->amount, 2) }}</td>
                                                        <td>{{ $transaction->description ?? '—' }}</td>
                                                        <td>{{ $transaction->created_at ? $transaction->created_at->format('d/m/Y H:i') : '—' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
