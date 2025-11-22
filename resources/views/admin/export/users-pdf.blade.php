<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Usuarios - {{ now()->format('d/m/Y') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 10pt;
            color: #1f2937;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #2563eb;
        }

        .header h1 {
            font-size: 22pt;
            color: #2563eb;
            margin-bottom: 5px;
        }

        .header .subtitle {
            font-size: 10pt;
            color: #6b7280;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        thead {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
        }

        th {
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
            font-size: 10pt;
            border: 1px solid #1e40af;
        }

        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        tbody tr:hover {
            background-color: #eff6ff;
        }

        td {
            padding: 10px 8px;
            border: 1px solid #e5e7eb;
            font-size: 9pt;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 8pt;
            font-weight: 600;
        }

        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #d1d5db;
            text-align: center;
            font-size: 8pt;
            color: #6b7280;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #9ca3af;
            font-style: italic;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>📋 REPORTE DE USUARIOS</h1>
        <p class="subtitle">Generado el {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    @if ($users->isEmpty())
        <div class="no-data">
            No hay usuarios disponibles para mostrar.
        </div>
    @else
        <table>
            <thead>
                <tr>
                    <th class="text-center">ID</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Email</th>
                    <th>DNI</th>
                    <th>Teléfono</th>
                    <th class="text-center">Estado</th>
                    <th>Fecha Creación</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td class="text-center">{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->last_name ?? '—' }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->dni ?? '—' }}</td>
                        <td>{{ $user->phone ?? '—' }}</td>
                        <td class="text-center">
                            @if ($user->status)
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-danger">Inactivo</span>
                            @endif
                        </td>
                        <td>{{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            <strong>Total de usuarios:</strong> {{ $users->count() }} |
            <strong>Documento generado por:</strong> {{ config('app.name') }}
        </div>
    @endif
</body>

</html>
