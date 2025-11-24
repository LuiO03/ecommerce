<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Mostrar detalle de permisos por rol.
     */
    public function index()
    {
        $roles = Role::withCount('users')->orderBy('id', 'desc')->get();
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        return view('admin.roles.create');
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string|max:255',
        ]);

        // Capitalizar nombre
        $name = ucwords(mb_strtolower($validated['name']));
        // Descripción: primera letra mayúscula, resto minúscula
        $description = $validated['description'] ? ucfirst(mb_strtolower($validated['description'])) : null;

        DB::beginTransaction();

        try {
            $role = Role::create([
                'name' => $name,
                'guard_name' => 'web',
                'description' => $description,
            ]);

            DB::commit();

            Session::flash('toast', [
                'type' => 'success',
                'title' => 'Rol creado',
                'message' => "El rol <strong>{$role->name}</strong> fue creado correctamente.",
            ]);

            Session::flash('highlightRow', $role->id);

            return redirect()->route('admin.roles.index', $role);
        } catch (\Exception $e) {
            DB::rollBack();

            Session::flash('toast', [
                'type' => 'danger',
                'title' => 'Error al crear rol',
                'message' => 'Ocurrió un error al intentar crear el rol. Por favor, inténtalo de nuevo.',
            ]);

            return back()->withInput();
        }
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        return view('admin.roles.edit', compact('role'));
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:255',
        ]);

        // Capitalizar nombre
        $name = ucwords(mb_strtolower($validated['name']));
        // Descripción: primera letra mayúscula, resto minúscula
        $description = $validated['description'] ? ucfirst(mb_strtolower($validated['description'])) : null;

        DB::beginTransaction();

        try {
            $role->update([
                'name' => $name,
                'description' => $description,
            ]);

            DB::commit();

            Session::flash('toast', [
                'type' => 'success',
                'title' => 'Rol actualizado',
                'message' => "El rol <strong>{$role->name}</strong> ha sido actualizado correctamente.",
            ]);

            Session::flash('highlightRow', $role->id);

            return redirect()->route('admin.roles.index', $role);
        } catch (\Exception $e) {
            DB::rollBack();

            Session::flash('toast', [
                'type' => 'danger',
                'title' => 'Error al actualizar rol',
                'message' => 'Ocurrió un error al intentar actualizar el rol. Por favor, inténtalo de nuevo.',
            ]);

            return back()->withInput();
        }
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role)
    {
        // Prevenir eliminar el rol de superadministrador
        if ($role->id === 1) {
            Session::flash('info', [
                'type' => 'warning',
                'header' => 'Acción no permitida',
                'title' => 'No se puede eliminar este rol',
                'message' => "El rol <strong>{$role->name}</strong> es el rol de superadministrador y no puede ser eliminado.",
            ]);

            return redirect()->route('admin.roles.index');
        }

        // Verificar si el usuario autenticado tiene este rol
        $currentUser = Auth::user();
        if ($currentUser && $currentUser->roles->contains('id', $role->id)) {
            Session::flash('info', [
                'type' => 'warning',
                'header' => 'Acción no permitida',
                'title' => 'No se puede eliminar este rol',
                'message' => "No puedes eliminar el rol <strong>{$role->name}</strong> porque es tu rol actual.",
            ]);

            return redirect()->route('admin.roles.index');
        }

        // Verificar si hay usuarios asignados a este rol
        $usersCount = User::role($role->name)->count();
        if ($usersCount > 0) {
            Session::flash('info', [
                'type' => 'warning',
                'header' => 'Acción no permitida',
                'title' => 'No se puede eliminar este rol',
                'message' => "El rol <strong>{$role->name}</strong> tiene {$usersCount} usuario(s) asignado(s). Reasígnalos primero.",
            ]);

            return redirect()->route('admin.roles.index');
        }

        DB::beginTransaction();

        try {
            $name = $role->name;

            // Eliminar el rol y sus relaciones con permisos
            $role->delete();

            DB::commit();

            Session::flash('info', [
                'type' => 'danger',
                'header' => 'Rol eliminado',
                'title' => 'Registro eliminado',
                'message' => "El rol <strong>{$name}</strong> ha sido eliminado correctamente.",
            ]);

            return redirect()->route('admin.roles.index');
        } catch (\Exception $e) {
            DB::rollBack();

            Session::flash('toast', [
                'type' => 'danger',
                'title' => 'Error al eliminar rol',
                'message' => 'Ocurrió un error al intentar eliminar el rol. Por favor, inténtalo de nuevo.',
            ]);

            return redirect()->route('admin.roles.index');
        }
    }
    /**
     * Mostrar detalle de permisos por rol.
     */
    public function permissions(Role $role)
    {
        $role->load('permissions');
        // Agrupar todos los permisos por módulo
        $allPermissions = Permission::where('guard_name', 'web')->orderBy('name')->get();
        $modules = [];
        foreach ($allPermissions as $permission) {
            $parts = explode(' ', $permission->name, 2);
            if (count($parts) === 2) {
                $action = $parts[0];
                $module = $parts[1];
                $modules[$module][] = [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'action' => $action,
                    'description' => $permission->description,
                    'assigned' => $role->permissions->contains('id', $permission->id)
                ];
            } else {
                $modules['Otros'][] = [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'action' => $permission->name,
                    'description' => $permission->description,
                    'assigned' => $role->permissions->contains('id', $permission->id)
                ];
            }
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
