<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RolesExcelExport;
use App\Exports\RolesCsvExport;
use Spatie\LaravelPdf\Facades\Pdf;
use App\Models\Role;


class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:roles.index')->only(['index']);
        $this->middleware('can:roles.create')->only(['create', 'store']);
        $this->middleware('can:roles.edit')->only(['edit', 'update']);
        $this->middleware('can:roles.delete')->only(['destroy']);
        $this->middleware('can:roles.export')->only(['exportExcel', 'exportPdf', 'exportCsv']);
        $this->middleware('can:roles.assign-permissions')->only(['permissions', 'updatePermissions']);
    }

    public function index()
    {
        $roles = Role::withCount('users')
        ->orderBy('id', 'desc')
        ->get();

        return view('admin.roles.index', compact('roles'));
    }

    // ============================
    //   EXPORTACIONES
    // ============================

    public function exportExcel(Request $request)
    {
        $ids = $request->input('ids');
        $filename = 'roles_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new RolesExcelExport($ids), $filename);
    }

    public function exportPdf(Request $request)
    {
        if ($request->has('ids')) {
            $roles = Role::whereIn('id', $request->ids)->get();
        } elseif ($request->has('export_all')) {
            $roles = Role::all();
        } else {
            return back()->with('error', 'No se seleccionaron roles para exportar.');
        }

        if ($roles->isEmpty()) {
            return back()->with('error', 'No hay roles disponibles para exportar.');
        }

        $filename = 'roles_' . now()->format('Y-m-d_H-i-s') . '.pdf';

        return Pdf::view('admin.export.roles-pdf', compact('roles'))
            ->format('a4')
            ->name($filename)
            ->download();
    }

    public function exportCsv(Request $request)
    {
        $ids = $request->has('export_all') ? null : $request->input('ids');

        $filename = 'roles_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return Excel::download(new RolesCsvExport($ids), $filename, \Maatwebsite\Excel\Excel::CSV);
    }

    // ============================
    //   CREAR
    // ============================

    public function create()
    {
        $permissions = Permission::orderBy('module')->get()->groupBy('module');

        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|min:3|max:255|unique:roles,name',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
        ]);

        $name = ucwords(mb_strtolower($request->name));

        $role = Role::create([
            'name'        => $name,
            'description' => $request->description ? ucfirst($request->description) : null,
            'guard_name'  => 'web',
            'created_by'  => Auth::id(),
            'updated_by'  => Auth::id(),
        ]);

        if ($request->permissions) {
            $role->syncPermissions($request->permissions);
        }

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Rol creado',
            'message' => "El rol <strong>{$role->name}</strong> se ha creado correctamente.",
        ]);

        Session::flash('highlightRow', $role->id);

        return redirect()->route('admin.roles.index');
    }

    // ============================
    //   EDITAR
    // ============================

    public function edit(Role $role)
    {
        if (in_array($role->name, ['Administrador', 'Superadministrador'])) {
            Session::flash('info', [
                'type' => 'warning',
                'header' => 'Protegido',
                'title' => 'Rol del sistema',
                'message' => "El rol <strong>{$role->name}</strong> no puede ser modificado.",
            ]);
            return redirect()->route('admin.roles.index');
        }

        $permissions = Permission::orderBy('module')->get()->groupBy('module');
        $assigned = $role->permissions->pluck('name')->toArray();

        return view('admin.roles.edit', compact('role', 'permissions', 'assigned'));
    }


    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name'        => 'required|string|min:3|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
        ]);

        $name = ucwords(mb_strtolower($request->name));

        $role->update([
            'name'        => $name,
            'description' => $request->description ? ucfirst($request->description) : null,
            'updated_by'  => Auth::id(),
        ]);

        // Sincronizar permisos
        $role->syncPermissions($request->permissions ?? []);

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Rol actualizado',
            'message' => "El rol <strong>{$role->name}</strong> se ha actualizado correctamente.",
        ]);

        Session::flash('highlightRow', $role->id);

        return redirect()->route('admin.roles.index');
    }

    // ============================
    //   ELIMINAR 1 SOLO
    // ============================

    public function destroy(Role $role)
    {
        if (in_array($role->name, ['Administrador', 'Superadministrador'])) {
            Session::flash('info', [
                'type' => 'warning',
                'header' => 'Protegido',
                'title' => 'Rol del sistema',
                'message' => "El rol <strong>{$role->name}</strong> no puede eliminarse.",
            ]);
            return redirect()->route('admin.roles.index');
        }

        if ($role->users()->count() > 0) {
            Session::flash('info', [
                'type' => 'warning',
                'header' => 'No se puede eliminar',
                'title' => 'Rol en uso',
                'message' => "El rol <strong>{$role->name}</strong> tiene usuarios asignados y no puede eliminarse.",
            ]);
            return redirect()->route('admin.roles.index');
        }

        $role->delete();

        Session::flash('info', [
            'type' => 'danger',
            'header' => 'Registro eliminado',
            'title' => 'Rol eliminado',
            'message' => "El rol <strong>{$role->name}</strong> fue eliminado.",
        ]);

        return redirect()->route('admin.roles.index');
    }

        /**
     * Mostrar detalle de permisos por rol.
     */
    public function permissions(Role $role)
    {
        $role->load('permissions');

        // Traer todos los permisos ordenados
        $allPermissions = Permission::where('guard_name', 'web')
            ->orderBy('module')
            ->orderBy('name')
            ->get();

        $modules = [];

        foreach ($allPermissions as $permission) {

            $module = $permission->module ?? 'Otros';

            $modules[$module][] = [
                'id' => $permission->id,
                'name' => $permission->name,
                'action' => $permission->name, // ahora name completo es la acción
                'description' => $permission->description,
                'created_at' => optional($permission->created_at)->timestamp ?? 0,
                'assigned' => $role->permissions->contains('id', $permission->id),
            ];
        }

        return view('admin.roles.permissions', compact('role', 'modules'));
    }

    /**
     * Actualizar los permisos asignados a un rol.
     */
    public function updatePermissions(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Filtrar solo permisos válidos para el guard 'web'
        $permissionIds = $validated['permissions'] ?? [];
        $validPermissions = \Spatie\Permission\Models\Permission::whereIn('id', $permissionIds)
            ->where('guard_name', 'web')
            ->pluck('id')
            ->toArray();

        $role->syncPermissions($validPermissions);

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Permisos actualizados',
            'message' => "Los permisos del rol <strong>{$role->name}</strong> se han actualizado correctamente.",
        ]);

        return redirect()->route('admin.roles.index');
    }
}
