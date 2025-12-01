<?php

namespace App\Http\Controllers\Admin;

use App\Exports\UsersCsvExport;
use App\Exports\UsersExcelExport;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\LaravelPdf\Facades\Pdf;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')
            ->select(['id', 'name', 'last_name', 'email', 'slug', 'status', 'created_at', 'image', 'dni', 'phone', 'email_verified_at'])
            ->orderByDesc('id')
            ->get();

        $roles = \Spatie\Permission\Models\Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    // ======================
    //     EXPORT EXCEL
    // ======================
    public function exportExcel(Request $request)
    {
        $ids = $request->input('ids');
        $filename = 'usuarios_'.now()->format('Y-m-d_H-i-s').'.xlsx';

        return Excel::download(new UsersExcelExport($ids), $filename);
    }

    // ======================
    //       EXPORT PDF
    // ======================
    public function exportPdf(Request $request)
    {
        if ($request->has('ids')) {
            $users = User::whereIn('id', $request->ids)->get();
        } elseif ($request->has('export_all')) {
            $users = User::all();
        } else {
            return back()->with('error', 'No se seleccionaron usuarios para exportar.');
        }

        if ($users->isEmpty()) {
            return back()->with('error', 'No hay usuarios disponibles.');
        }

        $filename = 'usuarios_'.now()->format('Y-m-d_H-i-s').'.pdf';

        return Pdf::view('admin.export.users-pdf', compact('users'))
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
        $filename = 'usuarios_'.now()->format('Y-m-d_H-i-s').'.csv';

        return Excel::download(new UsersCsvExport($ids), $filename, \Maatwebsite\Excel\Excel::CSV);
    }

    // ======================
    //         CREATE
    // ======================
    public function create()
    {
        $roles = \Spatie\Permission\Models\Role::all();

        return view('admin.users.create', compact('roles'));
    }

    // ======================
    //        STORE
    // ======================
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|min:3',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'status' => 'required|boolean',
            'role' => 'required|exists:roles,name',

            'last_name' => 'nullable|string|max:255',
            'dni' => 'nullable|string|max:20|unique:users,dni',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Capitalizar nombre y apellido
        $name = ucwords(mb_strtolower($request->name));
        $lastName = $request->last_name ? ucwords(mb_strtolower($request->last_name)) : null;

        // Slug único
        $slug = User::generateUniqueSlug($name);

        // Imagen
        $imagePath = null;
        if ($request->hasFile('image')) {
            $ext = $request->file('image')->getClientOriginalExtension();
            $filename = $slug.'-'.time().'.'.$ext;
            $imagePath = 'users/'.$filename;

            $request->file('image')->storeAs('users', $filename, 'public');
        }

        $user = User::create([
            'name' => $name,
            'last_name' => $lastName,
            'email' => $request->email,
            'password' => $request->password,
            'slug' => $slug,
            'status' => (bool) $request->status,
            'address' => $request->address,
            'dni' => $request->dni,
            'phone' => $request->phone,
            'image' => $imagePath,

            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        // Asignar rol
        $user->assignRole($request->role);

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Usuario creado',
            'message' => "El usuario <strong>{$user->name}</strong> se creó correctamente.",
        ]);

        Session::flash('highlightRow', $user->id);

        return redirect()->route('admin.users.index');
    }

    // ======================
    //          EDIT
    // ======================
    public function edit(User $user)
    {
        $roles = \Spatie\Permission\Models\Role::all();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    // ======================
    //         UPDATE
    // ======================
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255|min:3',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'status' => 'required|boolean',
            'role' => 'required|exists:roles,name',

            'last_name' => 'nullable|string|max:255',
            'dni' => 'nullable|string|max:20|unique:users,dni,'.$user->id,
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:255',

            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Capitalizar nombre, apellido y dirección
        $name = ucwords(mb_strtolower($request->name));
        $lastName = $request->last_name ? ucwords(mb_strtolower($request->last_name)) : null;
        $address = $request->address ? ucfirst(mb_strtolower($request->address)) : null;

        $slug = User::generateUniqueSlug($name, $user->id);

        $imagePath = $user->image;

        // Eliminar imagen
        if ($request->input('remove_image') == '1') {
            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }
            $imagePath = null;
        }
        // Nueva imagen
        elseif ($request->hasFile('image')) {
            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }
            $ext = $request->file('image')->getClientOriginalExtension();
            $filename = $slug.'-'.time().'.'.$ext;
            $imagePath = 'users/'.$filename;
            $request->file('image')->storeAs('users', $filename, 'public');
        }

        $user->update([
            'name' => $name,
            'last_name' => $lastName,
            'email' => $request->email,
            'slug' => $slug,
            'status' => (bool) $request->status,
            'address' => $address,
            'dni' => $request->dni,
            'phone' => $request->phone,
            'image' => $imagePath,
            'updated_by' => Auth::id(),
        ]);

        // Sincronizar rol
        $user->syncRoles([$request->role]);

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Usuario actualizado',
            'message' => "El usuario <strong>{$user->name}</strong> ha sido actualizado.",
        ]);

        Session::flash('highlightRow', $user->id);

        return redirect()->route('admin.users.index');
    }

    // ======================
    //        DESTROY
    // ======================
    public function destroy(User $user)
    {
        $name = $user->name;

        if ($user->image && Storage::disk('public')->exists($user->image)) {
            Storage::disk('public')->delete($user->image);
        }

        $user->deleted_by = Auth::id();
        $user->save();

        $user->delete();

        Session::flash('info', [
            'type' => 'danger',
            'header' => 'Registro eliminado',
            'title' => 'Usuario eliminado',
            'message' => "El usuario <strong>{$name}</strong> fue eliminado.",
        ]);

        return redirect()->route('admin.users.index');
    }

    // =====================================
    //     ELIMINACIÓN MÚLTIPLE
    // =====================================
    public function destroyMultiple(Request $request)
    {
        // Capitalizar nombre y dirección
        $name = ucwords(mb_strtolower($request->name));
        $address = $request->address ? ucfirst(mb_strtolower($request->address)) : null;

        $request->validate([
            'users' => 'sometimes|array|min:1',
            'users.*' => 'exists:users,id',
        ]);

        $userIds = $request->users;

        if (! $userIds) {
            Session::flash('info', [
                'type' => 'danger',
                'header' => 'Error',
                'title' => 'Sin selección',
                'message' => 'No seleccionaste usuarios.',
            ]);

            return redirect()->route('admin.users.index');
        }

        $users = User::whereIn('id', $userIds)->get();

        $names = [];

        foreach ($users as $user) {
            $names[] = $user->name;

            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }

            $user->deleted_by = Auth::id();
            $user->save();
        }

        Session::flash('info', [
            'type' => 'danger',
            'header' => 'Eliminación múltiple',
            'title' => 'Usuarios eliminados',
            'message' => 'Se eliminaron los siguientes usuarios:',
            'list' => $names,
        ]);

        return redirect()->route('admin.users.index');
    }

    // ======================
    //     CAMBIO DE ESTADO
    // ======================
    public function updateStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'status' => 'required|boolean',
        ]);

        $user->update([
            'status' => $request->status,
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente',
            'status' => $user->status,
        ]);
    }

    public function show($slug)
    {
        $user = User::where('slug', $slug)->firstOrFail();

        $createdBy = $user->created_by ? User::find($user->created_by) : null;
        $updatedBy = $user->updated_by ? User::find($user->updated_by) : null;

        return response()->json([
            'id' => '#'.$user->id,
            'slug' => $user->slug,
            'name' => $user->name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'role' => $user->getRoleNames()->first(),
            'dni' => $user->dni,
            'phone' => $user->phone,
            'address' => $user->address,
            'status' => $user->status,
            'email_verified_at' => $user->email_verified_at,
            'image' => $user->image,
            'created_by_name' => $createdBy ? trim($createdBy->name.' '.$createdBy->last_name) : 'Sistema',
            'updated_by_name' => $updatedBy ? trim($updatedBy->name.' '.$updatedBy->last_name) : '—',
            'created_at' => $user->created_at?->format('d/m/Y H:i') ?? '—',
            'updated_at' => $user->updated_at?->format('d/m/Y H:i') ?? '—',
        ]);
    }
}
