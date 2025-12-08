<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Posts</title>

    <style>
        @page { margin: 35px 45px; }
        body { margin: 0; font-family: "Noto Sans", "DejaVu Sans", sans-serif; font-size: 12px; color: #2b2b2b; }
        .header { text-align: center; margin-bottom: 18px; }
        .title { font-size: 21px; font-weight: 700; color: #1a1a1a; text-transform: uppercase; letter-spacing: 0.7px; }
        .divider { width: 100%; height: 2px; background: #F97316; margin-top: 8px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th { background: #F97316; color: #fff; font-size: 12px; padding: 8px 5px; border: 1px solid #EA580C; text-align: center; }
        td { border: 1px solid #d1d1d1; padding: 7px 8px; font-size: 11px; word-wrap: break-word; }
        tr:nth-child(even) td { background: #FFF7ED; }
        .status, .visibility, .comments { text-align: center; font-weight: bold; }
        .ok { color: #15803d; }
        .warn { color: #b45309; }
        .danger { color: #b91c1c; }
    </style>
</head>
<body>

    <div class="header">
        <div class="title">Listado de Posts</div>
        <div class="divider"></div>
    </div>

    <table>
        <colgroup>
            <col style="width: 8%">
            <col style="width: 28%">
            <col style="width: 14%">
            <col style="width: 14%">
            <col style="width: 12%">
            <col style="width: 12%">
            <col style="width: 12%">
        </colgroup>
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Estado</th>
                <th>Visibilidad</th>
                <th>Vistas</th>
                <th>Comentarios</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach($posts as $post)
                <tr>
                    <td>{{ $post->id }}</td>
                    <td>{{ $post->title }}</td>
                    <td class="status @switch($post->status)
                        @case('published') ok @break
                        @case('pending') warn @break
                        @case('rejected') danger @break
                        @default ''
                    @endswitch">
                        @switch($post->status)
                            @case('draft') Borrador @break
                            @case('pending') Pendiente @break
                            @case('published') Publicado @break
                            @case('rejected') Rechazado @break
                            @default {{ ucfirst($post->status) }}
                        @endswitch
                    </td>
                    <td class="visibility">
                        @switch($post->visibility)
                            @case('public') Público @break
                            @case('private') Privado @break
                            @case('registered') Registrado @break
                            @default {{ ucfirst($post->visibility) }}
                        @endswitch
                    </td>
                    <td>{{ $post->views }}</td>
                    <td class="comments {{ $post->allow_comments ? 'ok' : 'danger' }}">
                        {{ $post->allow_comments ? 'Sí' : 'No' }}
                    </td>
                    <td>{{ $post->created_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
