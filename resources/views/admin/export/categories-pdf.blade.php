<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Categorías</title>

    <style>
        @page {
            margin: 115px 34px 62px 34px;
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

        /* ===============================
           HEADER
        =============================== */
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

        .left {
            text-align: left;
        }

        .right {
            text-align: right;
        }

        .muted {
            color: #6B7280;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 4px;
        }

        .subtitle {
            font-size: 9px;
            color: #6B7280;
        }

        /* ===============================
           BRAND
        =============================== */
        .logo-cell {
            width: 38px;
            padding-right: 10px;
            vertical-align: middle;
        }

        .text-cell {
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
            color: #111827;
            line-height: 1.1;
        }

        .system-name {
            text-transform: uppercase
        }

        .system-name span {
            font-size: 18px;
            font-weight: normal;
            color: #111827;
        }

        .company-mini {
            font-size: 9px;
            color: #6B7280;
            margin-top: 2px;
        }

        /* ===============================
           FOOTER
        =============================== */
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

        /* ===============================
           SUMMARY
        =============================== */
        .summary {
            margin-bottom: 14px;
        }

        .summary td {
            width: 33.33%;
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
            margin-bottom: 4px;
            color: #6B7280;
        }

        .card-value {
            font-size: 16px;
            font-weight: bold;
            color: #111827;
        }

        .card-blue {
            border-top: 2px solid #3B82F6;
        }

        .card-green {
            border-top: 2px solid #10B981;
        }

        .card-orange {
            border-top: 2px solid #F59E0B;
        }

        /* ===============================
           TABLE
        =============================== */
        .data {
            table-layout: fixed;
        }

        .data th {
            background: #EEF2FF;
            border: 1px solid #E5E7EB;
            padding: 8px 6px;
            font-size: 9px;
            text-align: left;
        }

        .data td {
            border: 1px solid #E5E7EB;
            padding: 7px 6px;
            vertical-align: middle;
            font-size: 9px;
        }

        .data tbody tr:nth-child(even) td {
            background: #FAFAFA;
        }

        th.center,
        td.center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .small {
            font-size: 8.5px;
        }

        .active {
            color: #15803D;
            font-weight: bold;
        }

        .inactive {
            color: #B91C1C;
            font-weight: bold;
        }

        /* ===============================
           NOTICE
        =============================== */
        .notice {
            border: 1px solid #DBEAFE;
            background: #EFF6FF;
            padding: 8px 10px;
            margin-bottom: 12px;
            font-size: 9px;
        }
    </style>
</head>

<body>

    @php
        use Illuminate\Support\Facades\Auth;

        $items = collect($categories);

        $totalCategories = $items->count();
        $totalSubcategories = $items->whereNotNull('parent_id')->count();
        $totalProducts = (int) $items->sum('products_count');

        $generatedAt = now()->format('d/m/Y H:i');
        $userName = Auth::user()->name ?? 'Administrador';

        $companySettings = function_exists('company_setting') ? company_setting() : null;

        if ($companySettings && $companySettings->logo_path) {
            $fullPath = public_path('storage/' . $companySettings->logo_path);
            $pdfLogoUrl = file_exists($fullPath) ? $fullPath : public_path('images/logos/logo-geckommerce.png');
        } else {
            $pdfLogoUrl = public_path('images/logos/logo-geckommerce.png');
        }

        $companyName = !empty($companySettings?->name) ? $companySettings->name : config('app.name');

        $exportType = $isSelectedExport ? 'Exportación seleccionada' : 'Exportación total';
    @endphp

    <!-- =======================================
    HEADER
    ======================================= -->
    <div class="header">
        <table class="header-table">
            <tr>
                <td width="52%" class="left">
                    <table class="brand-table">
                        <tr>
                            <td class="logo-cell">
                                <img src="{{ $pdfLogoUrl }}" class="company-logo">
                            </td>
                            <td class="text-cell">
                                @if (!empty($companySettings?->name))
                                    <div class="company-name">{{ $companyName }}</div>
                                @else
                                    <div class="system-name">Gecko<span>Mmerce</span></div>
                                @endif
                                <div class="company-mini">Panel administrativo</div>
                            </td>
                        </tr>
                    </table>
                </td>

                <td width="48%" class="right">
                    <div class="title">Reporte de Categorías</div>
                    <div class="subtitle">{{ $exportType }}</div>
                    <div class="subtitle">Emitido: {{ $generatedAt }}</div>
                    <div class="subtitle">Usuario: {{ $userName }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- =======================================
    FOOTER
    ======================================= -->
    <div class="footer">
        <table class="footer-table">
            <tr>
                <td width="33%" class="left">
                    {{ $companyName }}
                </td>
                <td width="34%" class="center seal">
                    DOCUMENTO INTERNO
                </td>
                <td width="33%" class="right">
                    <span class="page-number"></span>
                </td>
            </tr>
        </table>
    </div>

    <!-- =======================================
    CONTENT
    ======================================= -->
    <div>

        <!-- NOTICE -->
        <div class="notice">
            {{ $isSelectedExport
                ? 'Este archivo contiene únicamente categorías seleccionadas por el usuario.'
                : 'Este archivo contiene el listado completo de categorías registradas.' }}
        </div>

        <!-- SUMMARY -->
        <div class="summary">
            <table class="summary-table">
                <tr>
                    <td>
                        <div class="card card-blue">
                            <div class="card-label">
                                {{ $isSelectedExport ? 'Seleccionadas' : 'Total categorías' }}
                            </div>
                            <div class="card-value">{{ $totalCategories }}</div>
                        </div>
                    </td>

                    <td>
                        <div class="card card-green">
                            <div class="card-label">Subcategorías</div>
                            <div class="card-value">{{ $totalSubcategories }}</div>
                        </div>
                    </td>

                    <td>
                        <div class="card card-orange">
                            <div class="card-label">Productos</div>
                            <div class="card-value">{{ $totalProducts }}</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- TABLE -->
        <table class="data">
            <colgroup>
                <col style="width:34px">
                <col style="width:22%">
                <col style="width:20%">
                <col style="width:34%">
                <col style="width:12%">
                <col style="width:12%">
            </colgroup>

            <thead>
                <tr>
                    <th class="center">ID</th>
                    <th>Categoría</th>
                    <th>Slug</th>
                    <th>Ubicación</th>
                    <th class="center">Productos</th>
                    <th class="center">Estado</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <td class="center">{{ $category->id }}</td>

                        <td class="bold">{{ $category->name }}</td>

                        <td class="small">{{ $category->slug ?: '—' }}</td>

                        <td class="small">
                            {{ $category->location ?? '—' }}
                        </td>

                        <td class="center">{{ (int) ($category->products_count ?? 0) }}</td>

                        <td class="center">
                            @if ($category->status)
                                <span class="active">Activo</span>
                            @else
                                <span class="inactive">Inactivo</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="center muted">
                            No existen categorías registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>

</body>

</html>
