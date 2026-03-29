<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Boleta #{{ $order->order_number }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #111827;
        }

        .invoice {
            max-width: 720px;
            margin: 0 auto;
            padding: 24px;
        }

        /* HEADER */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .company {
            max-width: 60%;
        }

        .company img {
            max-height: 50px;
            margin-bottom: 6px;
            filter: grayscale(100%);
        }

        .company h1 {
            font-size: 18px;
            margin: 0;
        }

        .company p {
            margin: 2px 0;
            font-size: 11px;
        }

        /* CAJA BOLETA */
        .meta-box {
            border: 1px solid #111;
            text-align: center;
            min-width: 180px;
            border-radius: 6px;
        }

        .meta-box div {
            padding: 6px;
        }

        .meta-box .ruc {
            font-size: 11px;
            font-weight: 600;
        }

        .meta-box .doc-type {
            font-size: 13px;
            font-weight: 700;
        }

        .meta-box .doc-number {
            font-size: 14px;
            font-weight: 700;
        }

        /* SECCIONES */
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 16px;
        }

        .card {
            border: 1px solid #e5e7eb;
            padding: 10px;
            border-radius: 6px;
        }

        .card strong {
            font-weight: 600;
        }

        .small {
            font-size: 11px;
        }

        /* TABLA */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th {
            background: #f9fafb;
            font-weight: 600;
        }

        th,
        td {
            padding: 6px 8px;
            border: 1px solid #e5e7eb;
            font-size: 11px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* TOTALES */
        .totals {
            max-width: 280px;
            margin-left: auto;
            margin-top: 12px;
            border-top: 1px solid #111;
            padding-top: 8px;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        .totals-row.total {
            font-size: 16px;
            font-weight: 700;
        }

        .small {
            font-size: 10px;
            color: #6b7280;
        }

        /* FOOTER */
        .footer {
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            margin-top: 20px;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <div class="invoice">

        <!-- HEADER -->
        <div class="header">
            <div class="company">
                @if ($companyInfo->logo_path)
                    <img src="{{ asset('storage/' . $companyInfo->logo_path) }}" alt="Logo de la empresa">
                @else
                    <img src="{{ asset('images/logos/logo-geckommerce.png') }}" alt="Logo">
                @endif

                <h1>{{ $companyInfo->name ?? config('app.name') }}</h1>

                <p><strong>Dirección:</strong> {{ $companyInfo->address ?? '-' }}</p>
                <p><strong>Email:</strong> {{ $companyInfo->email ?? '-' }}</p>
                <p><strong>Teléfono:</strong> {{ $companyInfo->phone ?? '-' }}</p>
            </div>

            <div class="meta-box">
                <div class="ruc">RUC: {{ $companyInfo->ruc ?? '-' }}</div>
                <div class="doc-type">BOLETA DE VENTA</div>
                <div class="doc-number">{{ $order->order_number }}</div>
            </div>
        </div>

        <!-- CLIENTE -->
        <div class="grid">
            <div class="card">
                <strong>Cliente:</strong><br>
                {{ $order->user->name ?? 'Cliente' }} {{ $order->user->last_name ?? '' }}<br>
                <strong>Email:</strong> {{ $order->user->email ?? '-' }}<br>
                <strong>Tipo de Documento:</strong>
                {{ $order->user->document_type ?? '-' }}<br>
                <strong>Documento:</strong> {{ $order->user->document_number ?? '-' }}<br>
                <strong>Dirección:</strong> {{ $order->shipping_address }}
            </div>

            <div class="card">
                <strong>Fecha:</strong><br>
                {{ $order->created_at->format('d/m/Y H:i') }}<br><br>

                <strong>Método de pago:</strong>
                {{ strtoupper($order->payment_method ?? 'NIUBIZ') }}<br>

                <strong>Estado:</strong>
                {{ ucfirst($order->payment_status ?? 'paid') }}
            </div>
        </div>

        <!-- TABLA -->
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="text-center">Cant.</th>
                    <th class="text-right">P. Unit</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>
                            {{ $item->product->name ?? 'Producto' }}
                            @php
                                $variantLabels = [];

                                if (
                                    $item->variant &&
                                    $item->variant->features &&
                                    $item->variant->features->isNotEmpty()
                                ) {
                                    foreach ($item->variant->features as $feature) {
                                        $option = $feature->option;
                                        $optionName = $option->name ?? ($option->slug ?? null);

                                        $label = $optionName ? $optionName . ': ' . $feature->value : $feature->value;

                                        $variantLabels[] = $label;
                                    }
                                }
                            @endphp
                            @if (!empty($variantLabels))
                                <br>
                                <span class="small">
                                    {{ implode(' · ', $variantLabels) }}
                                </span>
                            @endif
                        </td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">S/. {{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right">S/. {{ number_format($item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- TOTALES -->
        <div class="totals">
            <div class="totals-row">
                <span>Subtotal</span>
                <span>S/. {{ number_format($order->subtotal ?? 0, 2) }}</span>
            </div>
            <div class="totals-row">
                <span>Envío</span>
                <span>S/. {{ number_format($order->shipping_cost, 2) }}</span>
            </div>
            <div class="totals-row total">
                <span>Total</span>
                <span>S/. {{ number_format($order->total, 2) }}</span>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="footer">
            <p>Gracias por tu compra</p>
            <p>Este documento es un comprobante de pago.</p>
        </div>

    </div>
</body>

</html>
```
