<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ClientsCsvExport;
use App\Exports\ClientsExcelExport;
use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\Permission\Models\Role;

class ClientController extends Controller
{
    public function __construct()
    {
        // Permisos específicos del módulo Clientes
        $this->middleware('can:clientes.index')->only(['index']);
        $this->middleware('can:clientes.export')->only(['exportExcel', 'exportPdf', 'exportCsv']);
    }

    /**
     * Listar solo usuarios con rol Cliente.
     */
    public function index()
    {
        $users = User::role('Cliente')
            ->with('roles')
            ->select(['id', 'name', 'last_name', 'email', 'slug', 'status', 'created_at', 'image', 'dni', 'phone', 'email_verified_at'])
            ->orderByDesc('id')
            ->get();

        $roles = Role::all();

        return view('admin.clients.index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    // ======================
    //     EXPORT EXCEL
    // ======================
    public function exportExcel(Request $request)
    {
        $ids = $request->input('ids');
        $filename = 'clientes_'.now()->format('Y-m-d_H-i-s').'.xlsx';

        Audit::create([
            'user_id'        => Auth::id(),
            'event'          => 'excel_exported',
            'auditable_type' => User::class,
            'auditable_id'   => null,
            'old_values'     => null,
            'new_values'     => [
                'ids'        => $ids,
                'export_all' => $request->boolean('export_all', false),
                'filename'   => $filename,
                'module'     => 'clientes',
            ],
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
        ]);

        return Excel::download(new ClientsExcelExport($ids), $filename);
    }

    // ======================
    //       EXPORT PDF
    // ======================
    public function exportPdf(Request $request)
    {
        if ($request->has('ids')) {
            $users = User::role('Cliente')->whereIn('id', $request->ids)->get();
        } elseif ($request->has('export_all')) {
            $users = User::role('Cliente')->get();
        } else {
            return back()->with('error', 'No se seleccionaron clientes para exportar.');
        }

        if ($users->isEmpty()) {
            return back()->with('error', 'No hay clientes disponibles.');
        }

        $filename = 'clientes_'.now()->format('Y-m-d_H-i-s').'.pdf';

        Audit::create([
            'user_id'        => Auth::id(),
            'event'          => 'pdf_exported',
            'auditable_type' => User::class,
            'auditable_id'   => null,
            'old_values'     => null,
            'new_values'     => [
                'ids'        => $request->ids ?? null,
                'export_all' => $request->boolean('export_all', false),
                'filename'   => $filename,
                'module'     => 'clientes',
            ],
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
        ]);

        return Pdf::view('admin.export.clients-pdf', compact('users'))
            ->format('a4')
            ->name($filename)
            ->download();
    }

    // ======================
    //        EXPORT CSV
    // ======================
    public function exportCsv(Request $request)
    {
        $ids = $request->has('export_all') ? null : $request->input('ids');
        $filename = 'clientes_'.now()->format('Y-m-d_H-i-s').'.csv';

        Audit::create([
            'user_id'        => Auth::id(),
            'event'          => 'csv_exported',
            'auditable_type' => User::class,
            'auditable_id'   => null,
            'old_values'     => null,
            'new_values'     => [
                'ids'        => $ids,
                'export_all' => $request->boolean('export_all', false),
                'filename'   => $filename,
                'module'     => 'clientes',
            ],
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
        ]);

        return Excel::download(new ClientsCsvExport($ids), $filename, \Maatwebsite\Excel\Excel::CSV);
    }
}
