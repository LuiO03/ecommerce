<?php

namespace App\Exports;

use App\Models\Brand;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BrandsCsvExport implements FromArray, WithHeadings
{
    protected $ids;

    public function __construct($ids = null)
    {
        $this->ids = $ids;
    }

    public function headings(): array
    {
        return ['ID', 'Nombre', 'Descripción', 'Productos', 'Estado', 'Fecha de creación'];
    }

    public function array(): array
    {
        $query = Brand::withCount('products')
            ->select('id', 'name', 'description', 'status', 'created_at');

        if (!empty($this->ids)) {
            $query->whereIn('id', $this->ids);
        }

        return $query->get()->map(function ($brand) {
            return [
                $brand->id,
                $brand->name,
                $brand->description,
                $brand->products_count,
                $brand->status ? 'Activo' : 'Inactivo',
                optional($brand->created_at)->format('d/m/Y H:i') ?? '—',
            ];
        })->toArray();
    }
}
