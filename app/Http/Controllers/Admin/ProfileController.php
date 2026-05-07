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
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
        $user = $request->user();
        $authId = Auth::id();

        /*
        |--------------------------------------------------------------------------
        | SOLO IMAGEN
        |--------------------------------------------------------------------------
        */
        if ($request->boolean('only_image')) {

            $validated = $request->validate([
                'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            ]);

            DB::transaction(function () use ($request, $user, $authId, $validated) {

                // Eliminar imagen anterior
                if ($user->image && Storage::disk('public')->exists($user->image)) {
                    Storage::disk('public')->delete($user->image);
                }

                $slug = User::generateUniqueSlug($user->name, $user->id);

                $imagePath = $this->storeProfileImage(
                    $validated['image'],
                    $slug
                );

                $user->update([
                    'image'      => $imagePath,
                    'updated_by' => $authId,
                ]);
            });

            Session::flash('toast', [
                'type'    => 'success',
                'title'   => 'Foto actualizada',
                'message' => 'La foto de perfil se guardó correctamente.',
            ]);

            return redirect()->route('admin.profile.index');
        }

        /*
        |--------------------------------------------------------------------------
        | SOLO FONDO
        |--------------------------------------------------------------------------
        */
        if ($request->boolean('only_background')) {

            $validated = $request->validate([
                'background_style' => 'required|string|max:30',
            ]);

            $user->update([
                'background_style' => trim($validated['background_style']),
                'updated_by'       => $authId,
            ]);

            Session::flash('toast', [
                'type'    => 'success',
                'title'   => 'Fondo actualizado',
                'message' => 'El fondo de perfil se guardó correctamente.',
            ]);

            return redirect()->route('admin.profile.index');
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDACIÓN GENERAL
        |--------------------------------------------------------------------------
        */
        $validated = $request->validate([
            'name' => 'required|string|min:3|max:255',

            'last_name' => 'nullable|string|max:255',

            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'document_type' => [
                'nullable',
                'string',
                Rule::in(['DNI', 'RUC', 'CE', 'PASAPORTE']),
            ],

            'document_number' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('users', 'document_number')
                    ->where(fn($query) => $query->where(
                        'document_type',
                        $request->input('document_type')
                    ))
                    ->ignore($user->id),
            ],

            'phone' => [
                'nullable',
                'regex:/^[0-9+\-\s()]+$/',
                'max:20',
            ],

            'address' => 'nullable|string|max:255',

            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            'background_style' => 'nullable|string|max:30',
        ]);

        /*
        |--------------------------------------------------------------------------
        | NORMALIZACIÓN
        |--------------------------------------------------------------------------
        */
        $name = ucwords(mb_strtolower(trim($validated['name'])));

        $lastName = !empty($validated['last_name'])
            ? ucwords(mb_strtolower(trim($validated['last_name'])))
            : null;

        $email = mb_strtolower(trim($validated['email']));

        $address = !empty($validated['address'])
            ? ucfirst(mb_strtolower(trim($validated['address'])))
            : null;

        $phone = !empty($validated['phone'])
            ? trim($validated['phone'])
            : null;

        $slug = User::generateUniqueSlug($name, $user->id);

        $imagePath = $user->image;

        /*
        |--------------------------------------------------------------------------
        | ELIMINAR IMAGEN
        |--------------------------------------------------------------------------
        */
        if ($request->input('remove_image') == '1') {

            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }

            $imagePath = null;
        }

        /*
        |--------------------------------------------------------------------------
        | NUEVA IMAGEN
        |--------------------------------------------------------------------------
        */
        elseif ($request->hasFile('image')) {

            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }

            $imagePath = $this->storeProfileImage(
                $request->file('image'),
                $slug
            );
        }

        /*
        |--------------------------------------------------------------------------
        | DATOS NUEVOS
        |--------------------------------------------------------------------------
        */
        $newData = [
            'name'             => $name,
            'last_name'        => $lastName,
            'email'            => $email,
            'address'          => $address,
            'document_type'    => $validated['document_type'] ?? null,
            'document_number'  => $validated['document_number'] ?? null,
            'phone'            => $phone,
            'image'            => $imagePath,
            'background_style' => $validated['background_style'] ?? $user->background_style,
            'updated_by'       => $authId,
        ];

        /*
        |--------------------------------------------------------------------------
        | AUDITORÍA - ORIGINAL
        |--------------------------------------------------------------------------
        */
        $oldValues = [
            'name'             => $user->name,
            'last_name'        => $user->last_name,
            'email'            => $user->email,
            'address'          => $user->address,
            'document_type'    => $user->document_type,
            'document_number'  => $user->document_number,
            'phone'            => $user->phone,
            'image'            => $user->image,
            'background_style' => $user->background_style,
        ];

        /*
        |--------------------------------------------------------------------------
        | DETECTAR CAMBIOS
        |--------------------------------------------------------------------------
        */
        $hasChanges = collect($newData)
            ->except('updated_by')
            ->some(fn($value, $key) => $oldValues[$key] != $value);

        if (!$hasChanges) {

            Session::flash('toast', [
                'type'    => 'info',
                'title'   => 'Sin cambios',
                'message' => 'No se detectaron cambios en el perfil.',
            ]);

            return redirect()->route('admin.profile.index');
        }

        /*
        |--------------------------------------------------------------------------
        | TRANSACCIÓN
        |--------------------------------------------------------------------------
        */
        DB::transaction(function () use (
            $user,
            $newData,
            $oldValues,
            $request,
            $authId
        ) {

            // Evitar eventos duplicados del trait
            Model::withoutEvents(function () use ($user, $newData) {
                $user->update($newData);
            });

            Audit::create([
                'user_id'        => $authId,
                'event'          => 'profile_updated',
                'auditable_type' => User::class,
                'auditable_id'   => $user->id,
                'old_values'     => $oldValues,
                'new_values'     => $newData,
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->userAgent(),
            ]);
        });

        Session::flash('toast', [
            'type'    => 'success',
            'title'   => 'Perfil actualizado',
            'message' => 'Tu perfil ha sido actualizado correctamente.',
        ]);

        return redirect()->route('admin.profile.index');
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER: GUARDAR IMAGEN
    |--------------------------------------------------------------------------
    */
    private function storeProfileImage($file, string $slug): string
    {
        $filename = $slug . '-' . Str::uuid() . '.' . $file->extension();

        $file->storeAs('users', $filename, 'public');

        return 'users/' . $filename;
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
