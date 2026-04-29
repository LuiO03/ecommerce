<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Boleta #{{ $order->order_number }}</title>

    <style>
        @page {
            margin: 25px 30px;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
        }

        .invoice {
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* HEADER */
        .header td {
            vertical-align: top;
        }

        .company-logo {
            max-height: 55px;
            margin-bottom: 6px;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .company-info {
            font-size: 10px;
            line-height: 1.5;
        }

        .meta-box {
            width: 220px;
            border: 1px solid #111;
        }

        .meta-box td {
            text-align: center;
            padding: 6px;
            border-bottom: 1px solid #111;
        }

        .meta-box tr:last-child td {
            border-bottom: none;
        }

        .ruc {
            font-size: 10px;
            font-weight: bold;
        }

        .doc-type {
            font-size: 13px;
            font-weight: bold;
        }

        .doc-number {
            font-size: 13px;
            font-weight: bold;
        }

        /* BOXES */
        .section {
            margin-top: 14px;
        }

        .box {
            border: 1px solid #d1d5db;
            padding: 8px;
            vertical-align: top;
        }

        .box-title {
            font-weight: bold;
            margin-bottom: 4px;
        }

        .small {
            font-size: 10px;
            color: #4b5563;
        }

        /* PRODUCTS */
        .products {
            margin-top: 14px;
        }

        .products th {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 7px;
            font-size: 10px;
            text-align: center;
        }

        .products td {
            border: 1px solid #d1d5db;
            padding: 7px;
            font-size: 10px;
            vertical-align: top;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* TOTALS */
        .totals {
            width: 260px;
            margin-left: auto;
            margin-top: 12px;
        }

        .totals td {
            padding: 5px 0;
            font-size: 11px;
        }

        .grand-total td {
            font-size: 14px;
            font-weight: bold;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
        }

        /* FOOTER */
        .footer {
            margin-top: 24px;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }

        .badge {
            font-size: 10px;
            font-weight: bold;
        }
    </style>
</head>

<body>

@php
    $companySettings = $companyInfo ?? null;

    $pdfLogoUrl = null;

    if ($companySettings && $companySettings->logo_path) {
        $fullPath = public_path('storage/' . $companySettings->logo_path);

        if (file_exists($fullPath)) {
            $pdfLogoUrl = $fullPath;
        }
    }else{
        $pdfLogoUrl = public_path('images/logos/logo-geckommerce.png');
    }

    $pdfCompanyName = $companySettings->name ?? config('app.name');
    $pdfAddress = $companySettings->address ?? '-';
    $pdfEmail = $companySettings->email ?? '-';
    $pdfPhone = $companySettings->phone ?? '-';
    $pdfRuc = $companySettings->ruc ?? '-';
@endphp

<div class="invoice">

    <!-- HEADER -->
    <table class="header">
        <tr>
            <td width="65%">
                @if($pdfLogoUrl)
                    <img src="{{ $pdfLogoUrl }}" class="company-logo">
                @endif

                <div class="company-name">{{ $pdfCompanyName }}</div>

                <div class="company-info">
                    Dirección: {{ $pdfAddress }}<br>
                    Email: {{ $pdfEmail }}<br>
                    Teléfono: {{ $pdfPhone }}
                </div>
            </td>

            <td width="35%">
                <table class="meta-box">
                    <tr>
                        <td class="ruc">RUC: {{ $pdfRuc }}</td>
                    </tr>
                    <tr>
                        <td class="doc-type">BOLETA DE VENTA</td>
                    </tr>
                    <tr>
                        <td class="doc-number">{{ $order->order_number }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- CLIENTE -->
    <table class="section">
        <tr>
            <td width="65%" class="box">
                <div class="box-title">Datos del Cliente</div>

                {{ $order->user->name ?? 'Cliente' }} {{ $order->user->last_name ?? '' }}<br>
                Email: {{ $order->user->email ?? '-' }}<br>
                Documento: {{ $order->user->document_type ?? '-' }} -
                {{ $order->user->document_number ?? '-' }}<br>

                @if(($order->delivery_type ?? 'delivery') === 'pickup')
                    Entrega: <span class="badge">Recojo en tienda</span><br>
                    Tienda: {{ $order->pickup_store_code ?? '-' }}
                @else
                    Entrega: <span class="badge">Delivery</span><br>
                    Dirección: {{ $order->shipping_address ?? '-' }}
                @endif
            </td>

            <td width="35%" class="box">
                <div class="box-title">Información de Pago</div>

                Fecha: {{ $order->created_at->format('d/m/Y H:i') }}<br>
                Método: {{ strtoupper($order->payment_method ?? 'NIUBIZ') }}<br>
                Estado: {{ ucfirst($order->payment_status ?? 'paid') }}
            </td>
        </tr>
    </table>

    <!-- PRODUCTOS -->
    <table class="products">
        <thead>
            <tr>
                <th width="52%">Descripción</th>
                <th width="16%">P. Unit</th>
                <th width="12%">Cant.</th>
                <th width="20%">Total</th>
            </tr>
        </thead>
        <tbody>
        @foreach($order->items as $item)
            <tr>
                <td>
                    {{ $item->product->name ?? 'Producto' }}

                    @php
                        $variantLabels = [];

                        if ($item->variant && $item->variant->features) {
                            foreach ($item->variant->features as $feature) {
                                $option = $feature->option;
                                $name = $option->name ?? '';
                                $variantLabels[] = $name . ': ' . $feature->value;
                            }
                        }
                    @endphp

                    @if(count($variantLabels))
                        <br>
                        <span class="small">{{ implode(' · ', $variantLabels) }}</span>
                    @endif
                </td>

                <td class="text-right">S/ {{ number_format($item->unit_price, 2) }}</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">S/ {{ number_format($item->line_total, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <!-- TOTALES -->
    <table class="totals">
        <tr>
            <td>Subtotal</td>
            <td class="text-right">S/ {{ number_format($order->subtotal ?? 0, 2) }}</td>
        </tr>

        <tr>
            <td>Envío</td>
            <td class="text-right">S/ {{ number_format($order->shipping_cost ?? 0, 2) }}</td>
        </tr>

        <tr class="grand-total">
            <td>Total</td>
            <td class="text-right">S/ {{ number_format($order->total, 2) }}</td>
        </tr>
    </table>

    <!-- FOOTER -->
    <div class="footer">
        Gracias por su compra<br>
        Documento generado automáticamente.
    </div>

</div>

</body>
</html>
