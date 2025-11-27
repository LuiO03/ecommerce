<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Roles</title>
    <style>
        @page { margin: 35px 45px; }
        body { margin: 0; font-family: "Noto Sans", "DejaVu Sans", sans-serif; font-size: 12px; color: #2b2b2b; }
        .header { text-align: center; margin-bottom: 18px; }
        .title { font-size: 21px; font-weight: 700; color: #1a1a1a; text-transform: uppercase; letter-spacing: 0.7px; }
        .divider { width: 100%; height: 2px; background: #4F46E5; margin-top: 8px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th { background: #4F46E5; color: #fff; font-size: 12px; padding: 8px 5px; border: 1px solid #4338CA; text-align: center; }
        td { border: 1px solid #d1d1d1; padding: 7px 8px; font-size: 11px; word-wrap: break-word; }
        tr:nth-child(even) td { background: #F3F4FF; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Listado de Roles</div>
        <div class="divider"></div>
    </div>
    <table>
        <colgroup>
            <col style="width: 8%">
            <col style="width: 25%">
            <col style="width: 45%">
            <col style="width: 22%">
        </colgroup>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach($roles as $role)
                <tr>
                    <td>{{ $role->id }}</td>
                    <td>{{ $role->name }}</td>
                    <td style="white-space: pre-line;">{{ $role->description ?? 'Sin descripción' }}</td>
                    <td>{{ $role->created_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
