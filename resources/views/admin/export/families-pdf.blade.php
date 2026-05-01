<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Familias</title>

    <style>
        /* Dompdf-friendly: evita layouts modernos complejos */
        @page {
            margin: 105px 45px 60px 45px;
        }

        body {
            margin: 0;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 12px;
            color: #2b2b2b;
        }

        * {
            box-sizing: border-box;
        }

        /* ==========================
            HEADER / FOOTER (fijos)
        =========================== */
        .pdf-header {
            position: fixed;
            left: 0;
            right: 0;
            top: -85px;
            height: 85px;
        }

        .pdf-footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: -45px;
            height: 45px;
            font-size: 10px;
            color: #2b2b2b;
        }

        .brand-bar {
            border-bottom: 2px solid #4F46E5;
            padding-bottom: 10px;
        }

        .brand-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .brand-left {
            width: 38%;
            vertical-align: middle;
        }

        .brand-right {
            width: 62%;
            text-align: right;
            vertical-align: middle;
        }

        .brand-name {
            font-size: 14px;
            font-weight: 700;
            color: #1a1a1a;
        }

        .brand-sub {
            font-size: 10px;
            margin-top: 2px;
        }

        .brand-logo {
            height: 26px;
            width: auto;
            display: inline-block;
            vertical-align: middle;
        }

        .report-title {
            font-size: 18px;
            font-weight: 800;
            color: #1a1a1a;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        .report-meta {
            font-size: 10px;
            margin-top: 4px;
        }

        .meta-pill {
            display: inline-block;
            padding: 4px 8px;
            border: 1px solid #d1d1d1;
            border-radius: 999px;
            margin-left: 6px;
            white-space: nowrap;
        }

        .footer-line {
            border-top: 1px solid #d1d1d1;
            padding-top: 8px;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .footer-left {
            width: 70%;
            text-align: left;
        }

        .footer-right {
            width: 30%;
            text-align: right;
        }

        /* ==========================
            CONTENIDO
        =========================== */
        .content {
            width: 100%;
        }

        /* ==========================
            TABLA
        =========================== */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        thead {
            display: table-header-group;
        }

        th {
            background: #F3F4FF;
            color: #1a1a1a;
            font-size: 11px;
            padding: 9px 8px;
            border: 1px solid #d1d1d1;
            text-align: left;
        }

        th.th-center {
            text-align: center;
        }

        td {
            border: 1px solid #d1d1d1;
            padding: 9px 8px;
            font-size: 11px;
            vertical-align: top;
            word-wrap: break-word;
        }

        tr:nth-child(even) td {
            background: #F3F4FF;
        }

        .col-id {
            text-align: center;
            font-weight: 700;
        }

        .col-name {
            font-weight: 700;
            color: #1a1a1a;
        }

        .status {
            text-align: center;
            font-weight: 800;
            letter-spacing: 0.2px;
        }

        .active {
            color: #15803d;
        }

        .inactive {
            color: #b91c1c;
        }

        .date {
            text-align: center;
            white-space: nowrap;
        }
    </style>
</head>
<body>

    @php
        $familiesCollection = collect($families);
        $total = $familiesCollection->count();
        $totalActive = $familiesCollection->where('status', true)->count();
        $totalInactive = $total - $totalActive;
        $generatedAt = now()->format('d/m/Y H:i');

        $company = function_exists('company_setting') ? company_setting() : null;
        $companyName = $company->name ?? 'Ecommerce';

        $logoSrc = null;
        if ($company && !empty($company->logo_path)) {
            $raw = ltrim((string) $company->logo_path, '/');
            if (preg_match('/^https?:\/\//', $raw)) {
                $logoSrc = $raw;
            } else {
                $local = public_path($raw);
                if (is_file($local)) {
                    $logoSrc = $local;
                } else {
                    $localStorage = public_path('storage/' . $raw);
                    if (is_file($localStorage)) {
                        $logoSrc = $localStorage;
                    }
                }
            }
        }
    @endphp

    <!-- Header fijo -->
    <div class="pdf-header">
        <div class="brand-bar">
            <table class="brand-table">
                <tr>
                    <td class="brand-left">
                        @if ($logoSrc)
                            <img src="{{ $logoSrc }}" class="brand-logo" alt="{{ $companyName }}">
                        @endif
                        <div class="brand-name">{{ $companyName }}</div>
                        <div class="brand-sub">Reporte administrativo</div>
                    </td>
                    <td class="brand-right">
                        <div class="report-title">Listado de Familias</div>
                        <div class="report-meta">
                            <span class="meta-pill">Generado: {{ $generatedAt }}</span>
                            <span class="meta-pill">Total: {{ $total }}</span>
                            <span class="meta-pill">Activas: {{ $totalActive }}</span>
                            <span class="meta-pill">Inactivas: {{ $totalInactive }}</span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Footer fijo -->
    <div class="pdf-footer">
        <div class="footer-line">
            <table class="footer-table">
                <tr>
                    <td class="footer-left">
                        {{ $companyName }} · Exportación PDF
                    </td>
                    <td class="footer-right">
                        {{ $generatedAt }}
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="content">
        <table class="data-table">
        <colgroup>
            <col style="width: 8%">
            <col style="width: 20%">
            <col style="width: 45%">
            <col style="width: 12%">
            <col style="width: 15%">
        </colgroup>

        <thead>
            <tr>
                <th class="th-center">ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th class="th-center">Estado</th>
                <th class="th-center">Fecha</th>
            </tr>
        </thead>

        <tbody>
            @foreach($families as $family)
                <tr>
                    <td class="col-id">{{ $family->id }}</td>

                    <td class="col-name">{{ $family->name }}</td>

                    <td style="white-space: pre-line;">{{ $family->description ?? '—' }}</td>

                    <td class="status {{ $family->status ? 'active' : 'inactive' }}">
                        {{ $family->status ? 'Activo' : 'Inactivo' }}
                    </td>

                    <td class="date">
                        {{ $family->created_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}
                    </td>
                </tr>
            @endforeach
        </tbody>

        </table>
    </div>

</body>
</html>
