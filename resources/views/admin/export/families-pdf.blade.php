<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Familias</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
        }

        h2 {
            text-align: center;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #999;
            padding: 6px;
            text-align: left;
        }

        th {
            background: #f2f2f2;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        .status {
            text-align: center;
        }
    </style>
</head>
<body>
    <h2>Listado de Familias</h2>
    <table>
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
                    <td>{{ $family->description }}</td>
                    <td class="status">{{ $family->status ? 'Activo' : 'Inactivo' }}</td>
                    <td>{{ optional($family->created_at)->format('d/m/Y H:i') ?? 'Sin fecha' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
