<?php

namespace App\Exports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CategoriesCsvExport implements FromArray, WithHeadings
{
    protected $ids;

    public function __construct($ids = null)
    {
        $this->ids = $ids;
    }

    public function headings(): array
    {
        return ['ID', 'Nombre', 'Descripción', 'Estado', 'Familia', 'Categoría Padre', 'Fecha de creación'];
    }

    public function array(): array
    {
        $query = Category::select('id', 'name', 'description', 'status', 'family_id', 'parent_id', 'created_at')
            ->with(['family', 'parent']);

        if (!empty($this->ids)) {
            $query->whereIn('id', $this->ids);
        }

        return $query->get()->map(function ($category) {
            return [
                $category->id,
                $category->name,
                $category->description,
                $category->status ? 'Activo' : 'Inactivo',
                $category->family ? $category->family->name : '—',
                $category->parent ? $category->parent->name : '—',
                optional($category->created_at)->format('d/m/Y H:i') ?? '—',
            ];
        })->toArray();
    }
}