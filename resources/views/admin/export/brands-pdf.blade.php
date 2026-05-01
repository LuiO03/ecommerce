<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Marcas</title>

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

    <div class="header">
        <div class="title">Listado de Marcas</div>
        <div class="divider"></div>
    </div>

    <table>
        <colgroup>
            <col style="width: 8%">
            <col style="width: 26%">
            <col style="width: 46%">
            <col style="width: 10%">
            <col style="width: 10%">
        </colgroup>

        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Productos</th>
                <th>Estado</th>
            </tr>
        </thead>

        <tbody>
            @foreach($brands as $brand)
                <tr>
                    <td>{{ $brand->id }}</td>
                    <td>{{ $brand->name }}</td>
                    <td style="white-space: pre-line;">
                        {{ $brand->description ?: 'Sin descripción' }}
                    </td>
                    <td style="text-align:center;">
                        {{ $brand->products_count ?? 0 }}
                    </td>
                    <td class="status {{ $brand->status ? 'active' : 'inactive' }}">
                        {{ $brand->status ? 'Activo' : 'Inactivo' }}
                    </td>
                </tr>
            @endforeach
        </tbody>

    </table>

</body>
</html>
