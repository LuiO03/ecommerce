<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::withCount('roles')
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.permissions.index', compact('permissions'));
    }

    public function create()
    {
        return view('admin.permissions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'modulo' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
        ]);

        $name = trim($validated['name']); // SIN capitalizar
        $modulo = trim($validated['modulo']);
        $description = $validated['description'] 
            ? ucfirst(mb_strtolower(trim($validated['description'])))
            : null;

        $permission = Permission::create([
            'name' => $name,
            'modulo' => $modulo,
            'description' => $description,
            'guard_name' => 'web',
        ]);

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Permiso creado',
            'message' => "El permiso <strong>{$permission->name}</strong> fue creado correctamente.",
        ]);

        Session::flash('highlightRow', $permission->id);

        return redirect()->route('admin.permissions.index');
    }

    public function edit(Permission $permission)
    {
        return view('admin.permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
            'modulo' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
        ]);

        $name = trim($validated['name']); // SIN capitalizar
        $modulo = trim($validated['modulo']);
        $description = $validated['description'] 
            ? ucfirst(mb_strtolower(trim($validated['description'])))
            : null;

        $permission->update([
            'name' => $name,
            'modulo' => $modulo,
            'description' => $description,
        ]);

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Permiso actualizado',
            'message' => "El permiso <strong>{$permission->name}</strong> ha sido actualizado correctamente.",
        ]);

        Session::flash('highlightRow', $permission->id);

        return redirect()->route('admin.permissions.index');
    }

    public function destroy(Permission $permission)
    {
        $rolesCount = $permission->roles()->count();

        if ($rolesCount > 0) {
            Session::flash('info', [
                'type' => 'warning',
                'header' => 'Acción no permitida',
                'title' => 'No se puede eliminar este permiso',
                'message' => "El permiso <strong>{$permission->name}</strong> está asignado a {$rolesCount} rol(es).",
            ]);

            return redirect()->route('admin.permissions.index');
        }

        $name = $permission->name;
        $permission->delete();

        Session::flash('info', [
            'type' => 'danger',
            'header' => 'Permiso eliminado',
            'title' => 'Registro eliminado',
            'message' => "El permiso <strong>{$name}</strong> ha sido eliminado correctamente.",
        ]);

        return redirect()->route('admin.permissions.index');
    }
}
