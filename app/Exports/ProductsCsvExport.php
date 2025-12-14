<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsCsvExport implements FromArray, WithHeadings
{
    protected $ids;

    public function __construct($ids = null)
    {
        $this->ids = $ids;
    }

    public function headings(): array
    {
        return ['ID', 'SKU', 'Nombre', 'Categoría', 'Precio', 'Descuento', 'Estado', 'Variantes', 'Fecha de creación'];
    }

    public function array(): array
    {
        $query = Product::with(['category:id,name'])
            ->withCount(['variants'])
            ->select('id', 'sku', 'name', 'price', 'discount', 'status', 'category_id', 'created_at');

        if (!empty($this->ids)) {
            $query->whereIn('id', $this->ids);
        }

        return $query->get()->map(function ($product) {
            return [
                $product->id,
                $product->sku,
                $product->name,
                optional($product->category)->name ?? '—',
                number_format((float) $product->price, 2),
                $product->discount !== null
                    ? number_format((float) $product->discount, 2)
                    : '—',
                $product->status ? 'Activo' : 'Inactivo',
                $product->variants_count,
                optional($product->created_at)?->format('d/m/Y H:i') ?? '—',
            ];
        })->toArray();
    }
}
