<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ClientsCsvExport;
use App\Exports\ClientsExcelExport;
use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Models\User;
use App\Models\Client;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Storage;

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
        $clients = Client::query()
            ->withCount([
                'addresses',
                'orders',
            ])
            ->select([
                'id',
                'name',
                'last_name',
                'email',
                'slug',
                'status',
                'created_at',
                'image',
                'dni',
                'phone',
                'email_verified_at',
            ])
            ->orderByDesc('id')
            ->get();

        $roles = Role::all();

        return view('admin.clients.index', [
            'clients' => $clients,
            'roles' => $roles,
        ]);
    }


        // ======================
    //        DESTROY
    // ======================
    public function destroy(Client $client)
    {
        $name = $client->name;

        if ($client->image && Storage::disk('public')->exists($client->image)) {
            Storage::disk('public')->delete($client->image);
        }

        $client->deleted_by = Auth::id();
        $client->saveQuietly();;

        $client->delete();

        Session::flash('info', [
            'type' => 'danger',
            'header' => 'Registro eliminado',
            'title' => 'Cliente eliminado',
            'message' => "El cliente <strong>{$name}</strong> fue eliminado.",
        ]);

        return redirect()->route('admin.clients.index');
    }


    // =====================================
    //     ELIMINACIÓN MÚLTIPLE
    // =====================================
    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'users' => 'sometimes|array|min:1',
            'users.*' => 'exists:users,id',
            'ids' => 'sometimes|array|min:1',
            'ids.*' => 'exists:users,id'
        ]);

        $userIds = $request->users ?? $request->ids;

        if (empty($userIds)) {
            Session::flash('info', [
                'type' => 'danger',
                'header' => 'Error',
                'title' => 'Sin selección',
                'message' => 'No seleccionaste clientes.',
            ]);
            return redirect()->route('admin.clients.index');
        }

        $users = Client::whereIn('id', $userIds)->get();

        if ($users->isEmpty()) {
            Session::flash('info', [
                'type' => 'danger',
                'header' => 'Error',
                'title' => 'No encontradas',
                'message' => 'Los usuarios seleccionados no existen.',
            ]);
            return redirect()->route('admin.clients.index');
        }

        $authId = Auth::id();
        $usersToDelete = $users->reject(fn($user) => $user->id === $authId);
        $excluded = $users->count() - $usersToDelete->count();

        $names = [];

        foreach ($usersToDelete as $client) {
            $names[] = $client->name;

            if ($client->image && Storage::disk('public')->exists($client->image)) {
                Storage::disk('public')->delete($client->image);
            }

            $client->deleted_by = $authId;
            $client->saveQuietly();
            $client->deleteQuietly();
        }

        $message = 'Se eliminaron los siguientes clientes:';
        if ($excluded > 0) {
            $message .= ' (Tu propio usuario no fue eliminado)';
        }

        Session::flash('info', [
            'type' => 'danger',
            'header' => 'Eliminación múltiple',
            'title' => 'Clientes eliminados',
            'message' => $message,
            'list' => $names,
        ]);

        // Auditoría de eliminación múltiple
        Audit::create([
            'user_id'        => $authId,
            'event'          => 'bulk_deleted',
            'auditable_type' => Client::class,
            'auditable_id'   => null,
            'old_values'     => [
                'ids'   => $userIds,
                'names' => $names,
            ],
            'new_values'     => null,
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
        ]);

        return redirect()->route('admin.clients.index');
    }

    // ======================
    //     EXPORT EXCEL
    // ======================
    public function exportExcel(Request $request)
    {
        $ids = $request->input('ids');
        $filename = 'clientes_'.now()->format('Y-m-d_H-i-s').'.xlsx';

        Audit::create([
            'user_id' => Auth::id(),
            'event' => 'excel_exported',
            'auditable_type' => Client::class,
            'auditable_id' => null,
            'old_values' => null,
            'new_values' => [
                'ids' => $ids,
                'export_all' => $request->boolean('export_all', false),
                'filename' => $filename,
                'module' => 'clientes',
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return Excel::download(new ClientsExcelExport($ids), $filename);
    }

    // ======================
    //       EXPORT PDF
    // ======================
    public function exportPdf(Request $request)
    {
        // Solo usuarios con rol Cliente
        $query = Client::query()
            ->select([
                'id',
                'name',
                'last_name',
                'email',
                'dni',
                'phone',
                'status',
                'email_verified_at',
                'last_login_at',
                'created_at',
                'updated_at',
            ])
            ->with('roles:id,name');

        $isSelectedExport = false;

        if ($request->filled('ids')) {
            $query->whereIn('id', (array) $request->ids);
            $isSelectedExport = true;
        } elseif ($request->boolean('export_all')) {
            // exportación total
        } else {
            Session::flash('info', [
                'type' => 'danger',
                'header' => 'Error',
                'title' => 'Sin selección',
                'message' => 'No se seleccionaron clientes para exportar.',
            ]);

            return back();
        }

        $clients = $query
            ->orderByDesc('id')
            ->get();

        if ($clients->isEmpty()) {
            Session::flash('info', [
                'type' => 'danger',
                'header' => 'Error',
                'title' => 'Sin datos',
                'message' => 'No hay clientes disponibles para exportar.',
            ]);

            return back();
        }

        $filename = 'clientes_'.now()->format('Y-m-d_H-i-s').'.pdf';

        Audit::create([
            'user_id' => Auth::id(),
            'event' => 'pdf_exported',
            'auditable_type' => Client::class,
            'auditable_id' => null,
            'old_values' => null,
            'new_values' => [
                'filename' => $filename,
                'ids' => $request->ids ?? null,
                'export_all' => $request->boolean('export_all'),
                'selected' => $isSelectedExport,
                'total' => $clients->count(),
                'module' => 'clientes',
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $pdf = Pdf::loadView('admin.export.clients-pdf', [
            'clients' => $clients,
            'isSelectedExport' => $isSelectedExport,
            'exportedBy' => Auth::user()?->name.' '.Auth::user()?->last_name,
        ])->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

    // ======================
    //        EXPORT CSV
    // ======================
    public function exportCsv(Request $request)
    {
        $ids = $request->has('export_all') ? null : $request->input('ids');
        $filename = 'clientes_'.now()->format('Y-m-d_H-i-s').'.csv';

        Audit::create([
            'user_id' => Auth::id(),
            'event' => 'csv_exported',
            'auditable_type' => Client::class,
            'auditable_id' => null,
            'old_values' => null,
            'new_values' => [
                'ids' => $ids,
                'export_all' => $request->boolean('export_all', false),
                'filename' => $filename,
                'module' => 'clientes',
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return Excel::download(new ClientsCsvExport($ids), $filename, \Maatwebsite\Excel\Excel::CSV);
    }

    public function show($slug)
    {
        $client = Client::query()
            ->where('slug', $slug)
            ->with([
                'roles:id,name',
                'addresses',
                'orders',
            ])
            ->withCount([
                'addresses',
                'orders',
            ])
            ->withSum('orders', 'total')
            ->firstOrFail();

        $createdBy = $client->created_by ? User::find($client->created_by) : null;
        $updatedBy = $client->updated_by ? User::find($client->updated_by) : null;

        $lastOrder = $client->orders->sortByDesc('created_at')->first();

        return response()->json([
            'id' => '#'.$client->id,
            'raw_id' => $client->id,
            'slug' => $client->slug,

            'name' => $client->name,
            'last_name' => $client->last_name,
            'email' => $client->email,
            'phone' => $client->phone,

            'role' => $client->getRoleNames()->first(),

            'status' => $client->status,
            'email_verified_at' => $client->email_verified_at,
            'image' => $client->image,

            'orders_count' => $client->orders_count,
            'addresses_count' => $client->addresses_count,
            'total_spent' => $client->orders_sum_total ?? 0,

            'last_order_at' => $lastOrder?->created_at?->format('d/m/Y H:i'),

            'main_address' => $client->addresses->first()
                ? $client->addresses->first()->address_line
                : null,

            'last_login_at' => $client->last_login_at?->format('d/m/Y H:i') ?? '—',
            'created_at' => $client->created_at?->format('d/m/Y H:i') ?? '—',

            'created_by_name' => $createdBy
                ? trim($createdBy->name.' '.$createdBy->last_name)
                : 'Sistema',

            'updated_by_name' => $updatedBy
                ? trim($updatedBy->name.' '.$updatedBy->last_name)
                : '—',
        ]);
    }

}
