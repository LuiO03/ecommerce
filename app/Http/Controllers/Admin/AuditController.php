<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Exports\AuditsExcelExport;
use App\Exports\AuditsCsvExport;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuditController extends Controller
{
    public function __construct()
    {
        // Permisos para ver y exportar auditorías
        $this->middleware('can:auditorias.index')->only(['index', 'show']);
        $this->middleware('can:auditorias.export')->only(['exportExcel', 'exportPdf', 'exportCsv']);
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
            'event_label'         => $audit->event_label,
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

    public function exportExcel(Request $request)
    {
        $ids = $request->input('ids');
        $filename = 'auditorias_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        Audit::create([
            'user_id'        => Auth::id(),
            'event'          => 'excel_exported',
            'auditable_type' => Audit::class,
            'auditable_id'   => null,
            'old_values'     => null,
            'new_values'     => [
                'ids'        => $ids,
                'export_all' => $request->boolean('export_all', false),
                'filename'   => $filename,
            ],
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
        ]);

        return Excel::download(new AuditsExcelExport($ids), $filename);
    }

    public function exportPdf(Request $request)
    {
        if ($request->has('ids')) {
            $audits = Audit::with('user')->whereIn('id', $request->ids)->get();
        } elseif ($request->has('export_all')) {
            $audits = Audit::with('user')->get();
        } else {
            Session::flash('info', [
                'type' => 'danger',
                'header' => 'Error',
                'title' => 'Sin selección',
                'message' => 'No se seleccionaron auditorías para exportar.',
            ]);
            return back()->with('error', 'No se seleccionaron auditorías para exportar.');
        }

        if ($audits->isEmpty()) {
            Session::flash('info', [
                'type' => 'danger',
                'header' => 'Error',
                'title' => 'Sin datos',
                'message' => 'No hay auditorías disponibles para exportar.',
            ]);
            return back()->with('error', 'No hay auditorías disponibles para exportar.');
        }

        $filename = 'auditorias_' . now()->format('Y-m-d_H-i-s') . '.pdf';

        Audit::create([
            'user_id'        => Auth::id(),
            'event'          => 'pdf_exported',
            'auditable_type' => Audit::class,
            'auditable_id'   => null,
            'old_values'     => null,
            'new_values'     => [
                'ids'        => $request->ids ?? null,
                'export_all' => $request->boolean('export_all', false),
                'filename'   => $filename,
            ],
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
        ]);

        $pdf = Pdf::loadView('admin.export.audits-pdf', compact('audits'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

    public function exportCsv(Request $request)
    {
        $ids = $request->has('export_all') ? null : $request->input('ids');

        $filename = 'auditorias_' . now()->format('Y-m-d_H-i-s') . '.csv';

        Audit::create([
            'user_id'        => Auth::id(),
            'event'          => 'csv_exported',
            'auditable_type' => Audit::class,
            'auditable_id'   => null,
            'old_values'     => null,
            'new_values'     => [
                'ids'        => $ids,
                'export_all' => $request->boolean('export_all', false),
                'filename'   => $filename,
            ],
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
        ]);

        return Excel::download(new AuditsCsvExport($ids), $filename, \Maatwebsite\Excel\Excel::CSV);
    }
}
