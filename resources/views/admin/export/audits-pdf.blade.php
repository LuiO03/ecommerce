<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Auditorías</title>

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
        .brand-table,
        .data {
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
            content: "Página " counter(page);
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
            font-size: 16px;
            font-weight: bold;
        }

        .card-blue { border-top: 2px solid #3B82F6; }
        .card-green { border-top: 2px solid #10B981; }
        .card-orange { border-top: 2px solid #F59E0B; }
        .card-red { border-top: 2px solid #EF4444; }

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
            font-size: 8.5px;
            vertical-align: top;
            word-wrap: break-word;
        }

        .data tbody tr:nth-child(even) td {
            background: #FAFAFA;
        }

        .bold { font-weight: bold; }
        .small { font-size: 8px; }

        .success {
            color: #15803D;
            font-weight: bold;
        }

        .warning {
            color: #B45309;
            font-weight: bold;
        }

        .danger {
            color: #B91C1C;
            font-weight: bold;
        }
    </style>
</head>

<body>

@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Str;

    $items = collect($audits);

    $totalAudits = $items->count();
    $usersCount = $items->pluck('user_id')->filter()->unique()->count();

    $createdCount = $items->where('event', 'created')->count();
    $updatedCount = $items->where('event', 'updated')->count();
    $deletedCount = $items->where('event', 'deleted')->count();

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
            <td width="52%">
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
                <div class="title">Reporte de Auditorías</div>
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
            ? 'Este archivo contiene únicamente registros de auditoría seleccionados.'
            : 'Este archivo contiene el historial completo de auditorías registradas.' }}
    </div>

    <div class="summary">
        <table class="summary-table">
            <tr>
                <td>
                    <div class="card card-blue">
                        <div class="card-label">Registros</div>
                        <div class="card-value">{{ $totalAudits }}</div>
                    </div>
                </td>

                <td>
                    <div class="card card-green">
                        <div class="card-label">Creaciones</div>
                        <div class="card-value">{{ $createdCount }}</div>
                    </div>
                </td>

                <td>
                    <div class="card card-orange">
                        <div class="card-label">Actualizaciones</div>
                        <div class="card-value">{{ $updatedCount }}</div>
                    </div>
                </td>

                <td>
                    <div class="card card-red">
                        <div class="card-label">Usuarios únicos</div>
                        <div class="card-value">{{ $usersCount }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table class="data">
        <colgroup>
            <col style="width:5%">
            <col style="width:18%">
            <col style="width:12%">
            <col style="width:18%">
            <col style="width:10%">
            <col style="width:10%">
            <col style="width:12%">
            <col style="width:15%">
        </colgroup>

        <thead>
            <tr>
                <th class="center">ID</th>
                <th>Usuario</th>
                <th>Evento</th>
                <th>Modelo</th>
                <th>ID Ref.</th>
                <th>IP</th>
                <th>Fecha</th>
                <th>User Agent</th>
            </tr>
        </thead>

        <tbody>
            @forelse($audits as $audit)
                <tr>
                    <td class="center">{{ $audit->id }}</td>

                    <td>
                        <span class="bold">
                            {{ $audit->user?->name }} {{ $audit->user?->last_name }}
                        </span><br>

                        <span class="small muted">
                            {{ $audit->user?->email ?? 'Sistema' }}
                        </span>
                    </td>

                    <td>
                        @if($audit->event === 'created')
                            <span class="success">Creado</span>
                        @elseif($audit->event === 'updated')
                            <span class="warning">Actualizado</span>
                        @elseif($audit->event === 'deleted')
                            <span class="danger">Eliminado</span>
                        @else
                            {{ ucfirst(str_replace('_', ' ', $audit->event)) }}
                        @endif
                    </td>

                    <td>
                        {{ class_basename($audit->auditable_type) }}
                    </td>

                    <td class="center">
                        {{ $audit->auditable_id ?: '—' }}
                    </td>

                    <td>
                        {{ $audit->ip_address ?: '—' }}
                    </td>

                    <td>
                        {{ $audit->created_at?->format('d/m/Y') }}<br>
                        <span class="small muted">
                            {{ $audit->created_at?->format('H:i') }}
                        </span>
                    </td>

                    <td class="small">
                        {{ Str::limit($audit->user_agent ?: '—', 70) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="center muted">
                        No existen auditorías registradas.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</div>

</body>
</html>
