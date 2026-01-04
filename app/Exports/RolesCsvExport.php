<?php

namespace App\Exports;

use App\Models\Role;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RolesCsvExport implements FromArray, WithHeadings
{
    protected $ids;

    public function __construct($ids = null)
    {
        $this->ids = $ids;
    }

    public function headings(): array
    {
        return ['ID', 'Nombre', 'Descripción', 'Fecha de creación'];
    }

    public function array(): array
    {
        $query = Role::select('id', 'name', 'description', 'created_at');

        if (!empty($this->ids)) {
            $query->whereIn('id', $this->ids);
        }

        return $query->get()->map(function ($role) {
            return [
                $role->id,
                $role->name,
                $role->description ?? 'Sin descripción',
                optional($role->created_at)->format('d/m/Y H:i') ?? '—',
            ];
        })->toArray();
    }
}
