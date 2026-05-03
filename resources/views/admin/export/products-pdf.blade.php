<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Productos</title>

    <style>
        @page {
            margin: 115px 28px 62px 28px;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111827;
        }

        * {
            box-sizing: border-box;
        }

        .header {
            position: fixed;
            top: -98px;
            left: 0;
            right: 0;
            height: 88px;
            border-bottom: 1px solid #E5E7EB;
            padding-bottom: 8px;
        }

        .header-table,
        .footer-table,
        .summary-table,
        .data,
        .brand-table {
            width: 100%;
            border-collapse: collapse;
        }

        .left { text-align: left; }
        .right { text-align: right; }
        .center { text-align: center; }

        .muted { color: #6B7280; }

        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .subtitle {
            font-size: 9px;
            color: #6B7280;
        }

        .logo-cell {
            width: 38px;
            padding-right: 10px;
            vertical-align: middle;
        }

        .company-logo {
            width: 34px;
            height: 34px;
        }

        .company-name,
        .system-name {
            font-size: 18px;
            font-weight: bold;
            line-height: 1.1;
        }

        .system-name {
            text-transform: uppercase;
        }

        .system-name span {
            font-weight: normal;
        }

        .company-mini {
            font-size: 9px;
            color: #6B7280;
        }

        .footer {
            position: fixed;
            bottom: -48px;
            left: 0;
            right: 0;
            height: 36px;
            border-top: 1px solid #E5E7EB;
            padding-top: 6px;
            font-size: 9px;
            color: #6B7280;
        }

        .page-number:before {
            content: "Página " counter(page) " de " counter(pages);
        }

        .seal {
            font-weight: bold;
            letter-spacing: .6px;
            color: #111827;
        }

        .notice {
            border: 1px solid #DBEAFE;
            background: #EFF6FF;
            padding: 8px 10px;
            margin-bottom: 12px;
            font-size: 9px;
        }

        .summary {
            margin-bottom: 14px;
        }

        .summary td {
            width: 25%;
            padding: 0 4px;
        }

        .card {
            border: 1px solid #E5E7EB;
            background: #F9FAFB;
            padding: 10px 6px;
            text-align: center;
        }

        .card-label {
            font-size: 9px;
            color: #6B7280;
            margin-bottom: 4px;
        }

        .card-value {
            font-size: 15px;
            font-weight: bold;
        }

        .blue   { border-top: 2px solid #3B82F6; }
        .green  { border-top: 2px solid #10B981; }
        .orange { border-top: 2px solid #F59E0B; }
        .red    { border-top: 2px solid #EF4444; }

        .data {
            table-layout: fixed;
        }

        .data th {
            background: #EEF2FF;
            border: 1px solid #E5E7EB;
            padding: 7px 5px;
            font-size: 8.5px;
            text-align: left;
        }

        .data td {
            border: 1px solid #E5E7EB;
            padding: 6px 5px;
            font-size: 8.3px;
            vertical-align: middle;
        }

        .data tbody tr:nth-child(even) td {
            background: #FAFAFA;
        }

        .bold { font-weight: bold; }

        .small { font-size: 8px; }

        .active {
            color: #15803D;
            font-weight: bold;
        }

        .inactive {
            color: #B91C1C;
            font-weight: bold;
        }

        .warning {
            color: #B45309;
            font-weight: bold;
        }

        .money {
            font-weight: bold;
        }
    </style>
</head>

<body>

@php
    use Illuminate\Support\Facades\Auth;

    $items = collect($products);

    $totalProducts   = $items->count();
    $activeProducts  = $items->where('status', true)->count();
    $totalStock      = $items->sum('variants_stock_sum');
    $avgPrice        = $items->avg('price');

    $generatedAt = now()->format('d/m/Y H:i');

    $userName = $exportedBy ?? (Auth::user()->name ?? 'Administrador');

    $companySettings = function_exists('company_setting') ? company_setting() : null;

    if ($companySettings && $companySettings->logo_path) {
        $fullPath = public_path('storage/' . $companySettings->logo_path);
        $pdfLogoUrl = file_exists($fullPath)
            ? $fullPath
            : public_path('images/logos/logo-geckommerce.png');
    } else {
        $pdfLogoUrl = public_path('images/logos/logo-geckommerce.png');
    }

    $companyName = !empty($companySettings?->name)
        ? $companySettings->name
        : config('app.name');

    $exportType = $isSelectedExport
        ? 'Exportación seleccionada'
        : 'Exportación total';
@endphp

<div class="header">
    <table class="header-table">
        <tr>
            <td width="50%" class="left">
                <table class="brand-table">
                    <tr>
                        <td class="logo-cell">
                            <img src="{{ $pdfLogoUrl }}" class="company-logo">
                        </td>

                        <td>
                            @if(!empty($companySettings?->name))
                                <div class="company-name">{{ $companyName }}</div>
                            @else
                                <div class="system-name">Gecko<span>Mmerce</span></div>
                            @endif

                            <div class="company-mini">Panel administrativo</div>
                        </td>
                    </tr>
                </table>
            </td>

            <td width="50%" class="right">
                <div class="title">Reporte de Productos</div>
                <div class="subtitle">{{ $exportType }}</div>
                <div class="subtitle">Emitido: {{ $generatedAt }}</div>
                <div class="subtitle">Usuario: {{ $userName }}</div>
            </td>
        </tr>
    </table>
</div>

<div class="footer">
    <table class="footer-table">
        <tr>
            <td width="33%" class="left">{{ $companyName }}</td>
            <td width="34%" class="center seal">DOCUMENTO INTERNO</td>
            <td width="33%" class="right"><span class="page-number"></span></td>
        </tr>
    </table>
</div>

<div>

    <div class="notice">
        {{ $isSelectedExport
            ? 'Este archivo contiene únicamente productos seleccionados por el usuario.'
            : 'Este archivo contiene el listado completo de productos registrados.' }}
    </div>

    <div class="summary">
        <table class="summary-table">
            <tr>
                <td>
                    <div class="card blue">
                        <div class="card-label">Productos</div>
                        <div class="card-value">{{ $totalProducts }}</div>
                    </div>
                </td>

                <td>
                    <div class="card green">
                        <div class="card-label">Activos</div>
                        <div class="card-value">{{ $activeProducts }}</div>
                    </div>
                </td>

                <td>
                    <div class="card orange">
                        <div class="card-label">Stock total</div>
                        <div class="card-value">{{ $totalStock }}</div>
                    </div>
                </td>

                <td>
                    <div class="card red">
                        <div class="card-label">Precio prom.</div>
                        <div class="card-value">S/ {{ number_format($avgPrice ?? 0, 2) }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table class="data">
        <colgroup>
            <col style="width:18%">
            <col style="width:10%">
            <col style="width:12%">
            <col style="width:12%">
            <col style="width:9%">
            <col style="width:8%">
            <col style="width:8%">
            <col style="width:8%">
            <col style="width:7%">
            <col style="width:8%">
        </colgroup>

        <thead>
            <tr>
                <th>Producto</th>
                <th>SKU</th>
                <th>Categoría</th>
                <th>Marca</th>
                <th class="center">Precio</th>
                <th class="center">Desc.</th>
                <th class="center">Stock</th>
                <th class="center">Vars.</th>
                <th class="center">Imgs</th>
                <th class="center">Estado</th>
            </tr>
        </thead>

        <tbody>
            @forelse($products as $product)
                @php
                    $stock = (int) ($product->variants_stock_sum ?? 0);
                    $minStock = (int) ($product->min_stock ?? 0);
                @endphp

                <tr>
                    <td class="bold">{{ $product->name }}</td>

                    <td class="small">{{ $product->sku }}</td>

                    <td class="small">{{ $product->category->name ?? '—' }}</td>

                    <td class="small">{{ $product->brand->name ?? '—' }}</td>

                    <td class="center money">
                        S/ {{ number_format($product->price, 2) }}
                    </td>

                    <td class="center">
                        {{ $product->discount ? $product->discount.'%' : '—' }}
                    </td>

                    <td class="center">
                        @if($minStock > 0 && $stock <= $minStock)
                            <span class="warning">{{ $stock }}</span>
                        @else
                            {{ $stock }}
                        @endif
                    </td>

                    <td class="center">{{ $product->variants_count }}</td>

                    <td class="center">{{ $product->images_count }}</td>

                    <td class="center">
                        @if($product->status)
                            <span class="active">Activo</span>
                        @else
                            <span class="inactive">Inactivo</span>
                        @endif
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="10" class="center muted">
                        No existen productos registrados.
                    </td>
                </tr>
            @endforelse
        </tbody>

    </table>

</div>

</body>
</html>
