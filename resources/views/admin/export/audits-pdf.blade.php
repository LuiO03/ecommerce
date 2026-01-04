<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Auditorías</title>

    <style>
        @page {
            margin: 35px 45px;
        }

        body {
            margin: 0;
            font-family: "Noto Sans", "DejaVu Sans", sans-serif;
            font-size: 12px;
            color: #2b2b2b;
        }

        .header {
            text-align: center;
            margin-bottom: 18px;
        }

        .title {
            font-size: 21px;
            font-weight: 700;
            color: #1a1a1a;
            text-transform: uppercase;
            letter-spacing: 0.7px;
        }

        .divider {
            width: 100%;
            height: 2px;
            background: #4F46E5;
            margin-top: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th {
            background: #4F46E5;
            color: #fff;
            font-size: 12px;
            padding: 8px 5px;
            border: 1px solid #4338CA;
            text-align: center;
        }

        td {
            border: 1px solid #d1d1d1;
            padding: 7px 8px;
            font-size: 11px;
            word-wrap: break-word;
        }

        tr:nth-child(even) td {
            background: #F3F4FF;
        }

        .event {
            font-weight: 600;
        }

        .model-cell {
            text-align: center;
        }

        .id-cell {
            text-align: center;
        }

        .ip-cell {
            font-family: monospace;
            font-size: 10px;
        }

        .date-cell {
            white-space: nowrap;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="title">Listado de Auditorías</div>
        <div class="divider"></div>
    </div>

    <table>
        <colgroup>
            <col style="width: 6%">
            <col style="width: 18%">
            <col style="width: 10%">
            <col style="width: 32%">
            <col style="width: 10%">
            <col style="width: 8%">
            <col style="width: 16%">
        </colgroup>

        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Evento</th>
                <th>Descripción</th>
                <th>Modelo</th>
                <th>ID Reg.</th>
                <th>IP / Fecha</th>
            </tr>
        </thead>

        <tbody>
            @foreach($audits as $audit)
                <tr>
                    <td class="id-cell">{{ $audit->id }}</td>

                    <td>
                        @if($audit->user)
                            {{ $audit->user->name }}
                        @else
                            Sistema / Invitado
                        @endif
                    </td>

                    <td class="event">{{ $audit->event }}</td>

                    <td style="white-space: pre-line;">
                        {{ $audit->description }}
                    </td>

                    <td class="model-cell">
                        {{ $audit->auditable_type ? class_basename($audit->auditable_type) : '—' }}
                    </td>

                    <td class="id-cell">
                        {{ $audit->auditable_id ?? '—' }}
                    </td>

                    <td>
                        <div class="ip-cell">{{ $audit->ip_address ?? '—' }}</div>
                        <div class="date-cell">
                            {{ $audit->created_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>

    </table>

</body>
</html>
