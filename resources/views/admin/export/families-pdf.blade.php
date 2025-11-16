<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Familias</title>

    <style>
        /* Aplicar márgenes reales en todas las páginas del PDF */
        @page {
            margin: 35px 45px;
        }

        /* Reiniciar el body porque @page controla el margen */
        body {
            margin: 0;
            font-family: "Noto Sans", "DejaVu Sans", sans-serif;
            font-size: 12px;
            color: #2b2b2b;
        }

        /* ==========================
            ENCABEZADO
        =========================== */
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

        /* ==========================
            TABLA
        =========================== */
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

        .status {
            text-align: center;
            font-weight: bold;
        }

        .active {
            color: #15803d;
        }

        .inactive {
            color: #b91c1c;
        }
    </style>
</head>
<body>

    <!-- Encabezado -->
    <div class="header">
        <div class="title">Listado de Familias</div>
        <div class="divider"></div>
    </div>

    <!-- Tabla -->
    <table>
        <colgroup>
            <col style="width: 8%">
            <col style="width: 20%">
            <col style="width: 45%">
            <col style="width: 12%">
            <col style="width: 15%">
        </colgroup>

        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Estado</th>
                <th>Fecha</th>
            </tr>
        </thead>

        <tbody>
            @foreach($families as $family)
                <tr>
                    <td>{{ $family->id }}</td>

                    <td>{{ $family->name }}</td>

                    <td style="white-space: pre-line;">
                        {{ $family->description ?? '—' }}
                    </td>

                    <td class="status {{ $family->status ? 'active' : 'inactive' }}">
                        {{ $family->status ? 'Activo' : 'Inactivo' }}
                    </td>

                    <td>
                        {{ $family->created_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}
                    </td>
                </tr>
            @endforeach
        </tbody>

    </table>

</body>
</html>
