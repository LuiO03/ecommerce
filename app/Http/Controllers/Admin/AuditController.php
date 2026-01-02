<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function __construct()
    {
        // Permiso genérico para ver auditorías (ajustar al nombre real del permiso si es distinto)
        $this->middleware('can:auditorias.index')->only(['index', 'show']);
    }

    /**
     * Listado de auditorías del sistema.
     *
     * Referencia: estructura simple de index() en FamilyController,
     * pero aplicada al modelo Audit.
     */
    public function index(Request $request)
    {
        $audits = Audit::with('user')
            ->orderByDesc('id')
            ->get();

        return view('admin.audits.index', compact('audits'));
    }

    /**
     * Devuelve los datos completos de una auditoría en formato JSON.
     */
    public function show(Audit $audit)
    {
        $audit->load('user');

        return response()->json([
            'id'                  => $audit->id,
            'event'               => $audit->event,
            'event_label'         => ucfirst($audit->event),
            'description'         => $audit->description,
            'auditable_type'      => $audit->auditable_type,
            'auditable_type_name' => $audit->model_name,
            'auditable_id'        => $audit->auditable_id,
            'old_values'          => $audit->old_values,
            'new_values'          => $audit->new_values,
            'ip_address'          => $audit->ip_address,
            'user_agent'          => $audit->user_agent,
            'created_at'          => optional($audit->created_at)->format('d/m/Y H:i:s'),
            'user_name'           => optional($audit->user)->name,
            'user_email'          => optional($audit->user)->email,
        ]);
    }
}
