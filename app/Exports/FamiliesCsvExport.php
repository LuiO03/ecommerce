<?php

namespace App\Exports;

use App\Models\Family;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FamiliesCsvExport implements FromArray, WithHeadings
{
    protected $ids;

    public function __construct($ids = null)
    {
        $this->ids = $ids;
    }

    public function headings(): array
    {
        return ['ID', 'Nombre', 'Descripción', 'Estado', 'Fecha de creación'];
    }

    public function array(): array
    {
        $query = Family::select('id', 'name', 'description', 'status', 'created_at');

        if (!empty($this->ids)) {
            $query->whereIn('id', $this->ids);
        }

        return $query->get()->map(function ($family) {
            return [
                $family->id,
                $family->name,
                $family->description,
                $family->status ? 'Activo' : 'Inactivo',
                optional($family->created_at)->format('d/m/Y H:i') ?? '—',
            ];
        })->toArray();
    }
}
