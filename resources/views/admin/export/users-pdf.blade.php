<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Usuarios</title>

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
            word-wrap: break-word;
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

        $items = collect($users);

        $totalUsers = $items->count();
        $totalActive = $items->where('status', true)->count();
        $totalInactive = $items->where('status', false)->count();

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
                    <div class="title">Reporte de Usuarios</div>
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
                ? 'Este archivo contiene únicamente usuarios seleccionados por el usuario.'
                : 'Este archivo contiene el listado completo de usuarios registrados.' }}
        </div>

        <!-- SUMMARY -->
        <div class="summary">
            <table class="summary-table">
                <tr>
                    <td>
                        <div class="card card-blue">
                            <div class="card-label">
                                {{ $isSelectedExport ? 'Seleccionados' : 'Total usuarios' }}
                            </div>
                            <div class="card-value">{{ $totalUsers }}</div>
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
                </tr>
            </table>
        </div>

        <!-- TABLE -->
        <table class="data">
            <colgroup>
                <col style="width:34px">
                <col style="width:20%">
                <col style="width:24%">
                <col style="width:14%">
                <col style="width:12%">
                <col style="width:16%">
                <col style="width:14%">
            </colgroup>

            <thead>
                <tr>
                    <th class="center">ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th class="center">Estado</th>
                    <th>Último acceso</th>
                    <th>Registro</th>
                </tr>
            </thead>

            <tbody>
                @forelse($users as $user)
                    @php
                        $fullName = trim(($user->name ?? '') . ' ' . ($user->last_name ?? ''));
                        $roleLabel = $user->role_list ?: '—';
                        $lastAccess = $user->last_login_at
                            ? $user->last_login_at->format('d/m/Y H:i')
                            : '—';
                        $registeredAt = $user->created_at
                            ? $user->created_at->format('d/m/Y H:i')
                            : '—';
                    @endphp

                    <tr>
                        <td class="center">{{ $user->id }}</td>

                        <td class="bold">{{ $fullName ?: '—' }}</td>

                        <td class="small">{{ $user->email ?: 'No registrado' }}</td>

                        <td class="small">{{ $roleLabel }}</td>

                        <td class="center">
                            @if($user->status)
                                <span class="active">Activo</span>
                            @else
                                <span class="inactive">Inactivo</span>
                            @endif
                        </td>

                        <td class="small">{{ $lastAccess }}</td>

                        <td class="small">{{ $registeredAt }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="center muted">
                            No existen usuarios registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>

    </div>

</body>

</html>
