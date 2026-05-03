<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Clientes</title>

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

        .header {
            position: fixed;
            top: -98px;
            left: 0;
            right: 0;
            height: 88px;
            border-bottom: 1px solid #E5E7EB;
            padding-bottom: 8px;
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

        .center {
            text-align: center;
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
            color: #111827;
            line-height: 1.1;
        }

        .system-name{
            text-transform: uppercase
        }

        .system-name span {
            font-weight: normal;
        }

        .company-mini {
            font-size: 9px;
            color: #6B7280;
            margin-top: 2px;
        }

        .page-number:before {
            content: "Página " counter(page) " de " counter(pages);
        }

        .seal {
            font-weight: bold;
            letter-spacing: .6px;
            color: #111827;
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

        .card-purple {
            border-top: 2px solid #8B5CF6;
        }

        .notice {
            border: 1px solid #DBEAFE;
            background: #EFF6FF;
            padding: 8px 10px;
            margin-bottom: 12px;
            font-size: 9px;
        }

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
            word-wrap: break-word;
        }

        .data tbody tr:nth-child(even) td {
            background: #FAFAFA;
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

        .verified {
            color: #2563EB;
            font-weight: bold;
        }

        .pending {
            color: #D97706;
            font-weight: bold;
        }
    </style>
</head>

<body>

@php
use Illuminate\Support\Facades\Auth;

$items = collect($clients);

$totalClients = $items->count();
$totalActive = $items->where('status', true)->count();
$totalInactive = $items->where('status', false)->count();
$totalVerified = $items->whereNotNull('email_verified_at')->count();

$generatedAt = now()->format('d/m/Y H:i');

$clientName = $exportedBy ?? (Auth::user()->name ?? 'Administrador');

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

<!-- HEADER -->
<div class="header">
    <table class="header-table">
        <tr>
            <td width="52%" class="left">
                <table class="brand-table">
                    <tr>
                        <td class="logo-cell">
                            <img src="{{ $pdfLogoUrl }}" class="company-logo">
                        </td>

                        <td>
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
                <div class="title">Reporte de Clientes</div>
                <div class="subtitle">{{ $exportType }}</div>
                <div class="subtitle">Emitido: {{ $generatedAt }}</div>
                <div class="subtitle">Usuario: {{ $clientName }}</div>
            </td>
        </tr>
    </table>
</div>

<!-- FOOTER -->
<div class="footer">
    <table class="footer-table">
        <tr>
            <td width="33%" class="left">{{ $companyName }}</td>

            <td width="34%" class="center seal">
                DOCUMENTO INTERNO
            </td>

            <td width="33%" class="right">
                <span class="page-number"></span>
            </td>
        </tr>
    </table>
</div>

<!-- CONTENT -->
<div>

    <div class="notice">
        {{ $isSelectedExport
            ? 'Este archivo contiene únicamente clientes seleccionados por el usuario.'
            : 'Este archivo contiene el listado completo de clientes registrados.' }}
    </div>

    <!-- SUMMARY -->
    <div class="summary">
        <table class="summary-table">
            <tr>
                <td>
                    <div class="card card-blue">
                        <div class="card-label">
                            {{ $isSelectedExport ? 'Seleccionados' : 'Total clientes' }}
                        </div>
                        <div class="card-value">{{ $totalClients }}</div>
                    </div>
                </td>

                <td>
                    <div class="card card-green">
                        <div class="card-label">Activos</div>
                        <div class="card-value">{{ $totalActive }}</div>
                    </div>
                </td>

                <td>
                    <div class="card card-orange">
                        <div class="card-label">Inactivos</div>
                        <div class="card-value">{{ $totalInactive }}</div>
                    </div>
                </td>

                <td>
                    <div class="card card-purple">
                        <div class="card-label">Emails verificados</div>
                        <div class="card-value">{{ $totalVerified }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- TABLE -->
    <table class="data">
        <colgroup>
            <col style="width:34px">
            <col style="width:19%">
            <col style="width:22%">
            <col style="width:12%">
            <col style="width:12%">
            <col style="width:10%">
            <col style="width:12%">
            <col style="width:13%">
        </colgroup>

        <thead>
            <tr>
                <th class="center">ID</th>
                <th>Cliente</th>
                <th>Email</th>
                <th>DNI</th>
                <th>Teléfono</th>
                <th class="center">Estado</th>
                <th class="center">Email</th>
                <th>Registro</th>
            </tr>
        </thead>

        <tbody>
            @forelse($clients as $client)

                @php
                    $fullName = trim(($client->name ?? '') . ' ' . ($client->last_name ?? ''));
                    $registeredAt = $client->created_at
                        ? $client->created_at->format('d/m/Y')
                        : '—';
                @endphp

                <tr>
                    <td class="center">{{ $client->id }}</td>

                    <td class="bold">{{ $fullName ?: '—' }}</td>

                    <td class="small">{{ $client->email ?: '—' }}</td>

                    <td class="center">{{ $client->dni ?: 'No registrado' }}</td>

                    <td class="center">{{ $client->phone ?: 'No registrado' }}</td>

                    <td class="center">
                        @if($client->status)
                            <span class="active">Activo</span>
                        @else
                            <span class="inactive">Inactivo</span>
                        @endif
                    </td>

                    <td class="center">
                        @if($client->email_verified_at)
                            <span class="verified">Verificado</span>
                        @else
                            <span class="pending">Pendiente</span>
                        @endif
                    </td>

                    <td class="small">{{ $registeredAt }}</td>
                </tr>

            @empty
                <tr>
                    <td colspan="8" class="center muted">
                        No existen clientes registrados.
                    </td>
                </tr>
            @endforelse
        </tbody>

    </table>

</div>

</body>
</html>
