<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersCsvExport implements FromArray, WithHeadings
{
    protected $ids;

    public function __construct($ids = null)
    {
        $this->ids = $ids;
    }

    public function headings(): array
    {
        return ['ID', 'Nombre', 'Apellido', 'Email', 'DNI', 'Teléfono', 'Estado', 'Fecha de creación'];
    }

    public function array(): array
    {
        $query = User::select('id', 'name', 'last_name', 'email', 'dni', 'phone', 'status', 'created_at');

        if (!empty($this->ids)) {
            $query->whereIn('id', $this->ids);
        }

        return $query->get()->map(function ($user) {
            return [
                $user->id,
                $user->name,
                $user->last_name ?? '—',
                $user->email,
                $user->dni ?? '—',
                $user->phone ?? '—',
                $user->status ? 'Activo' : 'Inactivo',
                optional($user->created_at)->format('d/m/Y H:i') ?? '—',
            ];
        })->toArray();
    }
}
