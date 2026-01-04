<?php

namespace App\Exports;

use App\Models\Audit;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AuditsCsvExport implements FromArray, WithHeadings
{
    protected $ids;

    public function __construct($ids = null)
    {
        $this->ids = $ids;
    }

    public function headings(): array
    {
        return ['ID', 'Usuario', 'Evento', 'Descripción', 'Modelo', 'ID Registro', 'IP', 'Fecha'];
    }

    public function array(): array
    {
        $query = Audit::with('user')
            ->select('id', 'user_id', 'event', 'description', 'auditable_type', 'auditable_id', 'ip_address', 'created_at')
            ->orderBy('id');

        if (!empty($this->ids)) {
            $query->whereIn('id', $this->ids);
        }

        return $query->get()->map(function ($audit) {
            return [
                $audit->id,
                optional($audit->user)->name ?? 'Sistema / Invitado',
                $audit->event,
                $audit->description,
                $audit->auditable_type ? class_basename($audit->auditable_type) : '—',
                $audit->auditable_id ?? '—',
                $audit->ip_address ?? '—',
                optional($audit->created_at)?->format('d/m/Y H:i') ?? '—',
            ];
        })->toArray();
    }
}
