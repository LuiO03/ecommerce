<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AccessLogsExcelExport;
use App\Exports\AccessLogsCsvExport;
use Spatie\LaravelPdf\Facades\Pdf;

class AccessLogController extends Controller
{
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
     * Detalle de un acceso (para modal)
     */
    public function show(AccessLog $accessLog)
    {
        $accessLog->load('user:id,name,last_name,email');

        return response()->json([
            'id'        => '#' . $accessLog->id,
            'user'      => $accessLog->user
                ? trim($accessLog->user->name . ' ' . $accessLog->user->last_name)
                : '—',
            'email'     => $accessLog->email,
            'action'    => ucfirst($accessLog->action),
            'status'    => ucfirst($accessLog->status),
            'ip'        => $accessLog->ip_address,
            'agent'     => $accessLog->user_agent,
            'created_at'=> $accessLog->created_at->format('d/m/Y H:i'),
            'created_human' => $accessLog->created_at->diffForHumans(),
        ]);
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
        if ($request->has('ids')) {
            $logs = AccessLog::whereIn('id', $request->ids)->get();
        } elseif ($request->has('export_all')) {
            $logs = AccessLog::all();
        } else {
            Session::flash('info', [
                'type' => 'danger',
                'title' => 'Sin selección',
                'message' => 'No se seleccionaron accesos para exportar.',
            ]);

            return back();
        }

        if ($logs->isEmpty()) {
            Session::flash('info', [
                'type' => 'danger',
                'title' => 'Sin datos',
                'message' => 'No hay registros disponibles para exportar.',
            ]);

            return back();
        }

        $filename = 'access_logs_' . now()->format('Y-m-d_H-i-s') . '.pdf';

        return Pdf::view('admin.export.access-logs-pdf', compact('logs'))
            ->format('a4')
            ->name($filename)
            ->download();
    }
}
