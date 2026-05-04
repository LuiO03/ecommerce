<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Posts</title>

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

        .system-name{
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
            font-size: 8.5px;
            vertical-align: middle;
        }

        .data tbody tr:nth-child(even) td {
            background: #FAFAFA;
        }

        .bold { font-weight: bold; }

        .small { font-size: 8px; }

        .status-published { color: #15803D; font-weight: bold; }
        .status-draft { color: #92400E; font-weight: bold; }
        .status-pending { color: #1D4ED8; font-weight: bold; }
        .status-rejected { color: #B91C1C; font-weight: bold; }

        .status-private { color: #6B7280; font-weight: bold; }
        .status-authenticated { color: #2563EB; font-weight: bold; }

        .visibility {
            font-weight: bold;
        }
    </style>
</head>

<body>

@php
    use Illuminate\Support\Facades\Auth;

    $items = collect($posts);

    $totalPosts = $items->count();
    $published = $items->where('status', 'published')->count();
    $drafts = $items->where('status', 'draft')->count();
    $totalViews = $items->sum('views');

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
                <div class="title">Reporte de Posts</div>
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
            ? 'Este archivo contiene únicamente posts seleccionados por el usuario.'
            : 'Este archivo contiene el listado completo de posts registrados.' }}
    </div>

    <div class="summary">
        <table class="summary-table">
            <tr>
                <td>
                    <div class="card blue">
                        <div class="card-label">Posts</div>
                        <div class="card-value">{{ $totalPosts }}</div>
                    </div>
                </td>

                <td>
                    <div class="card green">
                        <div class="card-label">Publicados</div>
                        <div class="card-value">{{ $published }}</div>
                    </div>
                </td>

                <td>
                    <div class="card orange">
                        <div class="card-label">Borradores</div>
                        <div class="card-value">{{ $drafts }}</div>
                    </div>
                </td>

                <td>
                    <div class="card red">
                        <div class="card-label">Vistas</div>
                        <div class="card-value">{{ $totalViews }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table class="data">
        <colgroup>
            <col style="width:26%">
            <col style="width:16%">
            <col style="width:12%">
            <col style="width:12%">
            <col style="width:8%">
            <col style="width:8%">
            <col style="width:10%">
            <col style="width:8%">
        </colgroup>

        <thead>
            <tr>
                <th>Título</th>
                <th>Autor</th>
                <th>Estado</th>
                <th>Visibilidad</th>
                <th class="center">Vistas</th>
                <th class="center">Imgs</th>
                <th class="center">Publicado</th>
                <th class="center">Creado</th>
            </tr>
        </thead>

        <tbody>
            @forelse($posts as $post)
                <tr>
                    <td class="bold">{{ $post->title }}</td>

                    <td class="small">
                        {{ trim(($post->creator->name ?? '') . ' ' . ($post->creator->last_name ?? '')) ?: '—' }}
                    </td>

                    <td class="center">
                        @if($post->status === 'published')
                            <span class="status-published">Publicado</span>
                        @elseif($post->status === 'draft')
                            <span class="status-draft">Borrador</span>
                        @elseif($post->status === 'pending')
                            <span class="status-pending">Pendiente</span>
                        @else
                            <span class="status-rejected">Rechazado</span>
                        @endif
                    </td>

                    <td class="center small">
                        @if($post->visibility === 'public')
                            <span class="status-published">Público</span>
                        @elseif($post->visibility === 'private')
                            <span class="status-private">Privado</span>
                        @elseif($post->visibility === 'authenticated')
                            <span class="status-authenticated">Autenticado</span>
                        @endif
                    </td>

                    <td class="center">{{ $post->views }}</td>

                    <td class="center">{{ $post->images_count }}</td>

                    <td class="center small">
                        {{ $post->published_at ? \Carbon\Carbon::parse($post->published_at)->format('d/m/Y') : '—' }}
                    </td>

                    <td class="center small">
                        {{ $post->created_at ? $post->created_at->format('d/m/Y') : '—' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="center muted">
                        No existen posts registrados.
                    </td>
                </tr>
            @endforelse
        </tbody>

    </table>

</div>

</body>
</html>
