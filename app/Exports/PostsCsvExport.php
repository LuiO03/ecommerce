<?php

namespace App\Exports;

use App\Models\Post;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PostsCsvExport implements FromArray, WithHeadings
{
    protected $ids;

    public function __construct($ids = null)
    {
        $this->ids = $ids;
    }

    public function headings(): array
    {
        return ['ID', 'Título', 'Estado', 'Visibilidad', 'Vistas', 'Comentarios', 'Creado'];
    }

    public function array(): array
    {
        $query = Post::select('id', 'title', 'status', 'visibility', 'views', 'allow_comments', 'created_at');

        if (!empty($this->ids)) {
            $query->whereIn('id', $this->ids);
        }

        return $query->get()->map(function ($post) {
            return [
                $post->id,
                $post->title,
                match ($post->status) {
                    'draft' => 'Borrador',
                    'pending' => 'Pendiente',
                    'published' => 'Publicado',
                    'rejected' => 'Rechazado',
                    default => ucfirst($post->status),
                },
                match ($post->visibility) {
                    'public' => 'Público',
                    'private' => 'Privado',
                    'registered' => 'Registrado',
                    default => ucfirst($post->visibility),
                },
                $post->views,
                $post->allow_comments ? 'Sí' : 'No',
                optional($post->created_at)?->format('d/m/Y H:i') ?? '—',
            ];
        })->toArray();
    }
}
