<?php

namespace App\Exports;

use App\Models\AccessLog;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AccessLogsCsvExport implements FromArray, WithHeadings, Responsable
{
    private $ids;
    private $filename;

    public function __construct($ids = null)
    {
        $this->ids = $ids;
        $this->filename = 'access_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';
    }

    public function headings(): array
    {
        return [
            'ID', 'Usuario', 'Email', 'Acción', 'Estado', 'IP', 'Fecha', 'Agente'
        ];
    }

    public function array(): array
    {
        $query = AccessLog::with('user:id,name')
            ->when($this->ids, fn($q) => $q->whereIn('id', $this->ids))
            ->orderByDesc('created_at');

        return $query->get()->map(function($log) {
            return [
                $log->id,
                $log->user ? $log->user->name : 'Invitado',
                $log->email,
                $log->action_label,
                $log->status_label,
                $log->ip_address,
                $log->created_at->format('d/m/Y H:i'),
                $log->user_agent,
            ];
        })->toArray();
    }

    public function toResponse($request)
    {
        return \Maatwebsite\Excel\Facades\Excel::download($this, $this->filename, \Maatwebsite\Excel\Excel::CSV);
    }
}
