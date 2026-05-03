<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AccessLogsExcelExport;
use App\Exports\AccessLogsCsvExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use App\Models\Audit;

class AccessLogController extends Controller
{

    public function __construct()
    {
        $this->middleware('can:accesos.index')->only(['index']);
        $this->middleware('can:accesos.export')->only(['exportExcel', 'exportPdf', 'exportCsv']);
    }
    /**
     * Listado de accesos con filtros
     */
    public function index(Request $request)
    {
        $logs = AccessLog::with('user:id,name,last_name,email')
            ->when($request->action, fn ($q) =>
                $q->where('action', $request->action)
            )
            ->when($request->status, fn ($q) =>
                $q->where('status', $request->status)
            )
            ->when($request->user_id, fn ($q) =>
                $q->where('user_id', $request->user_id)
            )
            ->when($request->from && $request->to, fn ($q) =>
                $q->whereBetween('created_at', [$request->from, $request->to])
            )
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.access-logs.index', compact('logs'));
    }

    /**
     * Exportar Excel
     */
    public function exportExcel(Request $request)
    {
        $ids = $request->input('ids');

        $filename = 'access_logs_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(
            new AccessLogsExcelExport($ids),
            $filename
        );
    }

    /**
     * Exportar CSV
     */
    public function exportCsv(Request $request)
    {
        $ids = $request->has('export_all')
            ? null
            : $request->input('ids');

        $filename = 'access_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return Excel::download(
            new AccessLogsCsvExport($ids),
            $filename,
            \Maatwebsite\Excel\Excel::CSV
        );
    }

    /**
     * Exportar PDF
     */
    public function exportPdf(Request $request)
    {
        $query = AccessLog::query()
            ->with('user:id,name,last_name,email')
            ->select([
                'id',
                'user_id',
                'action',
                'status',
                'ip_address',
                'user_agent',
                'created_at',
            ]);

        $isSelectedExport = false;

        if ($request->filled('ids')) {
            $query->whereIn('id', (array) $request->ids);
            $isSelectedExport = true;
        } elseif ($request->boolean('export_all')) {
            // exportación total
        } else {
            Session::flash('info', [
                'type'    => 'danger',
                'header'  => 'Error',
                'title'   => 'Sin selección',
                'message' => 'No se seleccionaron accesos para exportar.',
            ]);

            return back();
        }

        $logs = $query->orderByDesc('id')->get();

        if ($logs->isEmpty()) {
            Session::flash('info', [
                'type'    => 'danger',
                'header'  => 'Error',
                'title'   => 'Sin datos',
                'message' => 'No hay registros disponibles para exportar.',
            ]);

            return back();
        }

        $filename = 'access_logs_' . now()->format('Y-m-d_H-i-s') . '.pdf';

        Audit::create([
            'user_id'        => Auth::id(),
            'event'          => 'pdf_exported',
            'auditable_type' => AccessLog::class,
            'auditable_id'   => null,
            'old_values'     => null,
            'new_values'     => [
                'filename'   => $filename,
                'ids'        => $request->ids ?? null,
                'export_all' => $request->boolean('export_all'),
                'selected'   => $isSelectedExport,
                'total'      => $logs->count(),
                'module'     => 'access_logs',
            ],
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
        ]);

        $pdf = Pdf::loadView('admin.export.access-logs-pdf', [
            'logs'             => $logs,
            'isSelectedExport' => $isSelectedExport,
            'exportedBy'       => Auth::user()?->name . ' ' . Auth::user()?->last_name,
        ])->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }
}
