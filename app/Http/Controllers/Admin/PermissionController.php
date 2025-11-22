<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;


use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    /**
     * Listar permisos.
     */
    public function index()
    {
        $permissions = Permission::withCount('roles')->orderBy('id', 'desc')->get();
        return view('admin.permissions.index', compact('permissions'));
    }

    /**
     * Mostrar formulario de creación.
     */
    public function create()
    {
        return view('admin.permissions.create');
    }

    /**
     * Guardar nuevo permiso.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
        ]);

        DB::beginTransaction();
        try {
            $permission = Permission::create([
                'name' => $validated['name'],
                'guard_name' => 'web',
            ]);

            DB::commit();
            Session::flash('toast', [
                'type' => 'success',
                'title' => 'Permiso creado',
                'message' => "El permiso <strong>{$permission->name}</strong> fue creado correctamente.",
            ]);
            Session::flash('highlightRow', $permission->id);
            return redirect()->route('admin.permissions.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('toast', [
                'type' => 'danger',
                'title' => 'Error al crear permiso',
                'message' => 'Ocurrió un error al intentar crear el permiso.',
            ]);
            return back()->withInput();
        }
    }

    /**
     * Mostrar formulario de edición.
     */
    public function edit(Permission $permission)
    {
        return view('admin.permissions.edit', compact('permission'));
    }

    /**
     * Actualizar permiso.
     */
    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
        ]);

        DB::beginTransaction();
        try {
            $permission->update(['name' => $validated['name']]);
            DB::commit();
            Session::flash('toast', [
                'type' => 'success',
                'title' => 'Permiso actualizado',
                'message' => "El permiso <strong>{$permission->name}</strong> ha sido actualizado correctamente.",
            ]);
            Session::flash('highlightRow', $permission->id);
            return redirect()->route('admin.permissions.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('toast', [
                'type' => 'danger',
                'title' => 'Error al actualizar permiso',
                'message' => 'Ocurrió un error al intentar actualizar el permiso.',
            ]);
            return back()->withInput();
        }
    }

    /**
     * Eliminar permiso.
     */
    public function destroy(Permission $permission)
    {
        // Prevenir eliminar permisos en uso por roles
        $rolesCount = $permission->roles()->count();
        if ($rolesCount > 0) {
            Session::flash('info', [
                'type' => 'warning',
                'header' => 'Acción no permitida',
                'title' => 'No se puede eliminar este permiso',
                'message' => "El permiso <strong>{$permission->name}</strong> está asignado a {$rolesCount} rol(es). Reasigna o elimina los roles primero.",
            ]);
            return redirect()->route('admin.permissions.index');
        }

        DB::beginTransaction();
        try {
            $name = $permission->name;
            $permission->delete();
            DB::commit();
            Session::flash('info', [
                'type' => 'danger',
                'header' => 'Permiso eliminado',
                'title' => 'Registro eliminado',
                'message' => "El permiso <strong>{$name}</strong> ha sido eliminado correctamente.",
            ]);
            return redirect()->route('admin.permissions.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('toast', [
                'type' => 'danger',
                'title' => 'Error al eliminar permiso',
                'message' => 'Ocurrió un error al intentar eliminar el permiso.',
            ]);
            return redirect()->route('admin.permissions.index');
        }
    }
}
