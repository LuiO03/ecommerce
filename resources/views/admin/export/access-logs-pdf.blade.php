@php use Illuminate\Support\Str; @endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registros de Acceso - {{ now()->format('d/m/Y') }}</title>
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
        .badge-primary {
            background-color: #2563eb;
            color: #fff;
        }
        .badge-secondary {
            background-color: #64748b;
            color: #fff;
        }
        .badge-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-gray {
            background-color: #e5e7eb;
            color: #222;
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
        <h1>🛡️ REPORTE DE ACCESOS</h1>
        <p class="subtitle">Generado el {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    @if ($logs->isEmpty())
        <div class="no-data">
            No hay registros de acceso disponibles para mostrar.
        </div>
    @else
        <table>
            <thead>
                <tr>
                    <th class="text-center">ID</th>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Acción</th>
                    <th>Estado</th>
                    <th>IP</th>
                    <th>Fecha</th>
                    <th>Agente</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                    <tr>
                        <td class="text-center">{{ $log->id }}</td>
                        <td>{{ $log->user ? $log->user->name : 'Invitado' }}</td>
                        <td>{{ $log->email }}</td>
                        <td>
                            @if($log->action === 'login')
                                <span class="badge badge-primary">Login</span>
                            @elseif($log->action === 'logout')
                                <span class="badge badge-secondary">Logout</span>
                            @elseif($log->action === 'failed')
                                <span class="badge badge-danger">Fallido</span>
                            @else
                                <span class="badge badge-gray">{{ $log->action_label }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $log->status === 'success' ? 'badge-success' : 'badge-danger' }}">
                                {{ $log->status_label }}
                            </span>
                        </td>
                        <td>{{ $log->ip_address }}</td>
                        <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ Str::limit($log->user_agent, 60) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="footer">
            <strong>Total de accesos:</strong> {{ $logs->count() }} |
            <strong>Documento generado por:</strong> {{ config('app.name') }}
        </div>
    @endif
</body>
</html>
