<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Órdenes - {{ now()->format('d/m/Y') }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 10pt;
            color: #1f2937;
            padding: 20px;
        }

        .header {
            padding-bottom: 15px;
            border-bottom: 3px solid #2563eb;
        }

        .header h1 {
            font-size: 22pt;
            color: #2563eb;
            margin-bottom: 5px;
        }

        .header .subtitle {
            font-size: 10pt;
            color: #6b7280;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        thead {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
        }

        th {
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
            font-size: 10pt;
            border: 1px solid #1e40af;
        }

        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        tbody tr:hover {
            background-color: #eff6ff;
        }

        td {
            padding: 10px 8px;
            border: 1px solid #e5e7eb;
            font-size: 9pt;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 8pt;
            font-weight: 600;
        }

        .badge-status {
            background-color: #e0f2fe;
            color: #0369a1;
        }

        .badge-paid {
            background-color: #bbf7d0;
            color: #166534;
        }

        .badge-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-failed {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #d1d5db;
            text-align: center;
            font-size: 8pt;
            color: #6b7280;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #9ca3af;
            font-style: italic;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>📦 REPORTE DE ÓRDENES</h1>
        <p class="subtitle">Generado el {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    @if ($orders->isEmpty())
        <div class="no-data">
            No hay órdenes disponibles para mostrar.
        </div>
    @else
        <table>
            <thead>
                <tr>
                    <th class="text-center">ID</th>
                    <th>N° Orden</th>
                    <th>Cliente</th>
                    <th class="text-center">Total</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Pago</th>
                    <th class="text-center">ID Pago</th>
                    <th>Fecha creación</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                    <tr>
                        <td class="text-center">{{ $order->id }}</td>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->user->name ?? '—' }}</td>
                        <td class="text-center">S/. {{ number_format((float) $order->total, 2) }}</td>
                        <td class="text-center">
                            <span class="badge badge-status">{{ ucfirst($order->status) }}</span>
                        </td>
                        <td class="text-center">
                            @php
                                $payment = $order->payment_status;
                                $class = match($payment) {
                                    'paid' => 'badge-paid',
                                    'failed' => 'badge-failed',
                                    default => 'badge-pending',
                                };
                            @endphp
                            <span class="badge {{ $class }}">{{ ucfirst($payment) }}</span>
                        </td>
                        <td class="text-center">{{ $order->payment_id ?? '—' }}</td>
                        <td>{{ $order->created_at ? $order->created_at->format('d/m/Y H:i') : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            <strong>Total de órdenes:</strong> {{ $orders->count() }} |
            <strong>Documento generado por:</strong> {{ config('app.name') }}
        </div>
    @endif
</body>

</html>
