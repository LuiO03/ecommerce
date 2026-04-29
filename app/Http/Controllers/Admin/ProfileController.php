<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\LaravelPdf\Facades\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    // ======================
    //   QUITAR FOTO DE PERFIL
    // ======================
    public function removeImage(Request $request)
    {
        $user = User::query()->findOrFail(Auth::id());
        if ($user->image && Storage::disk('public')->exists($user->image)) {
            Storage::disk('public')->delete($user->image);
        }
        $user->update([
            'image' => null,
            'updated_by' => Auth::id(),
        ]);
        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Foto eliminada',
            'message' => 'La foto de perfil ha sido eliminada correctamente.',
        ]);
        return redirect()->route('admin.profile.index');
    }
    // ======================
    //      VISTA PRINCIPAL PERFIL
    // ======================
    public function index()
    {
        $fondos = [
    'fondo-estilo-1',
    'fondo-estilo-2',
    'fondo-estilo-4',
    'fondo-estilo-5',
    'fondo-estilo-6',
    'fondo-estilo-7',
    'fondo-estilo-8',
    'fondo-estilo-9',
    'fondo-estilo-10',
    'fondo-estilo-11',
    'fondo-estilo-12',
    'fondo-estilo-13',
    'fondo-estilo-14',
    'fondo-estilo-15',
    'fondo-estilo-17',
    'fondo-estilo-18',
    'fondo-estilo-19',
    'fondo-estilo-20',
    'fondo-estilo-21',
    'fondo-estilo-22',
    'fondo-estilo-23',
    'fondo-estilo-24',
    'fondo-estilo-25',
];
        $user = Auth::user();
        // Obtener sesiones activas desde la tabla sessions
        $sessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderByDesc('last_activity')
            ->get();
        return view('admin.profile.index', compact('user', 'sessions', 'fondos'));
    }
    // ======================
    //      CERRAR SESIÓN DE OTRO DISPOSITIVO (manual)
    // ======================
    public function logoutSession(Request $request)
    {
        DB::table('sessions')->where('id', $request->session_id)->delete();
        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Sesión cerrada',
            'message' => 'La sesión de otro dispositivo ha sido cerrada correctamente.',
        ]);
        return back()->with('success', 'Sesión cerrada correctamente.');
    }

    // ======================
    //      ACTUALIZAR PERFIL
    // ======================
    public function update(Request $request)
    {
        $user = User::query()->findOrFail(Auth::id());

        // Si solo se envía imagen (desde la modal)
        if ($request->has('only_image')) {
            $request->validate([
                'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            ]);
            // Eliminar imagen anterior si existe
            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }
            $ext = $request->file('image')->getClientOriginalExtension();
            $slug = User::generateUniqueSlug($user->name, $user->id);
            $filename = $slug . '-' . time() . '.' . $ext;
            $imagePath = 'users/' . $filename;
            $request->file('image')->storeAs('users', $filename, 'public');
            $user->update([
                'image' => $imagePath,
                'updated_by' => Auth::id(),
            ]);
            Session::flash('toast', [
                'type' => 'success',
                'title' => 'Foto actualizada',
                'message' => 'La foto de perfil se guardó correctamente.',
            ]);
            return redirect()->route('admin.profile.index');
        }

        // Si solo se envía fondo (desde el formulario independiente)
        if ($request->has('only_background')) {
            $request->validate([
                'background_style' => 'required|string|max:30',
            ]);
            $user->update([
                'background_style' => $request->background_style,
                'updated_by' => Auth::id(),
            ]);
            Session::flash('toast', [
                'type' => 'success',
                'title' => 'Fondo actualizado',
                'message' => 'El fondo de perfil se guardó correctamente.',
            ]);
            return redirect()->route('admin.profile.index');
        }

        $request->validate([
            'name'      => 'required|string|max:255|min:3',
            'email'     => 'required|email|unique:users,email,' . $user->id,
            'last_name' => 'nullable|string|max:255',
            'document_type' => 'nullable|string|in:DNI,RUC,CE,PASAPORTE',
            'document_number' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('users', 'document_number')
                    ->where(fn ($query) => $query->where('document_type', $request->input('document_type')))
                    ->ignore($user->id),
            ],
            'dni'       => 'nullable|string|max:20|unique:users,dni,' . $user->id,
            'phone'     => 'nullable|string|max:15',
            'address'   => 'nullable|string|max:255',
            'image'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'background_style' => 'nullable|string|max:30',
        ]);

        $name = ucwords(mb_strtolower($request->name));
        $address = $request->address ? ucfirst(mb_strtolower($request->address)) : null;
        $slug = User::generateUniqueSlug($name, $user->id);
        $imagePath = $user->image;

        // Guardar valores originales antes de actualizar para la auditoría manual
        $original = $user->getOriginal();

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
            $filename = $slug . '-' . time() . '.' . $ext;
            $imagePath = 'users/' . $filename;
            $request->file('image')->storeAs('users', $filename, 'public');
        }

        // Evitar que el trait Auditable registre un "updated" extra aquí
        Model::withoutEvents(function () use ($user, $name, $request, $address, $imagePath) {
            $user->update([
                'name'        => $name,
                'last_name'   => $request->last_name,
                'email'       => $request->email,
                'address'     => $address,
                'document_type' => $request->document_type,
                'document_number' => $request->document_number,
                'dni'         => $request->dni,
                'phone'       => $request->phone,
                'image'       => $imagePath,
                'background_style' => $request->background_style,
                'updated_by'  => Auth::id(),
            ]);
        });

        // Auditoría específica para "Mi perfil"
        try {
            $oldValues = [
                'name'       => $original['name'] ?? null,
                'last_name'  => $original['last_name'] ?? null,
                'email'      => $original['email'] ?? null,
                'address'    => $original['address'] ?? null,
                'document_type' => $original['document_type'] ?? null,
                'document_number' => $original['document_number'] ?? null,
                'dni'        => $original['dni'] ?? null,
                'phone'      => $original['phone'] ?? null,
                'image'      => $original['image'] ?? null,
                'background_style' => $original['background_style'] ?? null,
            ];

            $newValues = [
                'name'       => $user->name,
                'last_name'  => $user->last_name,
                'email'      => $user->email,
                'address'    => $user->address,
                'document_type' => $user->document_type,
                'document_number' => $user->document_number,
                'dni'        => $user->dni,
                'phone'      => $user->phone,
                'image'      => $user->image,
                'background_style' => $user->background_style,
            ];

            Audit::create([
                'user_id'        => Auth::id(),
                'event'          => 'profile_updated',
                'auditable_type' => User::class,
                'auditable_id'   => $user->id,
                'old_values'     => $oldValues,
                'new_values'     => $newValues,
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->userAgent(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Perfil actualizado',
            'message' => "Tu perfil ha sido actualizado correctamente.",
        ]);

        return redirect()->route('admin.profile.index');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        // Verificar contraseña actual
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'La contraseña actual es incorrecta.'
            ]);
        }

        // Evitar reutilizar la misma contraseña
        if (Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'La nueva contraseña no puede ser igual a la actual.'
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'updated_by' => $user->id,
        ]);

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Contraseña actualizada',
            'message' => 'Tu contraseña se ha cambiado correctamente.',
        ]);

        return redirect()->route('admin.profile.index');
    }

    // ======================
    //   EXPORTAR PERFIL
    // ======================
    public function exportExcel()
    {
        $user = Auth::user();
        $filename = 'mi_perfil_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new \App\Exports\UsersExcelExport([$user->id]), $filename);
    }

    public function exportPdf()
    {
        $user = Auth::user();
        $filename = 'mi_perfil_' . now()->format('Y-m-d_H-i-s') . '.pdf';
        return Pdf::view('profile.export.pdf', ['users' => collect([$user])])
            ->format('a4')
            ->name($filename)
            ->download();
    }

    public function exportCsv()
    {
        $user = Auth::user();
        $filename = 'mi_perfil_' . now()->format('Y-m-d_H-i-s') . '.csv';
        return Excel::download(new \App\Exports\UsersCsvExport([$user->id]), $filename, \Maatwebsite\Excel\Excel::CSV);
    }
}
