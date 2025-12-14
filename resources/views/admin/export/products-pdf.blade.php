<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Productos</title>
    <style>
        @page {
            margin: 35px 45px;
        }

        body {
            margin: 0;
            font-family: "Noto Sans", "DejaVu Sans", sans-serif;
            font-size: 12px;
            color: #1f2937;
        }

        .header {
            text-align: center;
            margin-bottom: 18px;
        }

        .title {
            font-size: 21px;
            font-weight: 700;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        .divider {
            width: 100%;
            height: 2px;
            background: #7c3aed;
            margin-top: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th {
            background: #7c3aed;
            color: #ffffff;
            font-size: 12px;
            padding: 8px 5px;
            border: 1px solid #5b21b6;
            text-align: center;
        }

        td {
            border: 1px solid #d1d5db;
            padding: 7px 8px;
            font-size: 11px;
            word-wrap: break-word;
        }

        tr:nth-child(even) td {
            background: #f5f3ff;
        }

        .status {
            text-align: center;
            font-weight: 600;
        }

        .active {
            color: #047857;
        }

        .inactive {
            color: #b91c1c;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Listado de Productos</div>
        <div class="divider"></div>
    </div>

    <table>
        <colgroup>
            <col style="width: 6%">
            <col style="width: 16%">
            <col style="width: 20%">
            <col style="width: 18%">
            <col style="width: 14%">
            <col style="width: 12%">
            <col style="width: 14%">
        </colgroup>
        <thead>
            <tr>
                <th>ID</th>
                <th>SKU</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Precio</th>
                <th>Descuento</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
                <tr>
                    <td>{{ $product->id }}</td>
                    <td>{{ $product->sku }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category->name ?? 'Sin categoría' }}</td>
                    <td>S/ {{ number_format((float) $product->price, 2) }}</td>
                    <td>
                        @if (!is_null($product->discount))
                            S/ {{ number_format((float) $product->discount, 2) }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="status {{ $product->status ? 'active' : 'inactive' }}">
                        {{ $product->status ? 'Activo' : 'Inactivo' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
