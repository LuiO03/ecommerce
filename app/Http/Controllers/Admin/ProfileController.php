<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\LaravelPdf\Facades\Pdf;
use Illuminate\Support\Facades\DB;


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
        $user = Auth::user();
            // Obtener sesiones activas desde la tabla sessions
            $sessions = DB::table('sessions')
                ->where('user_id', $user->id)
                ->orderByDesc('last_activity')
                ->get();
            return view('admin.profile.index', compact('user', 'sessions'));
    }
        // ======================
        //      CERRAR SESIÓN DE OTRO DISPOSITIVO (manual)
        // ======================
        public function logoutSession(Request $request)
        {
            DB::table('sessions')->where('id', $request->session_id)->delete();
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

        $user->update([
            'name'        => $name,
            'last_name'   => $request->last_name,
            'email'       => $request->email,
            'address'     => $address,
            'dni'         => $request->dni,
            'phone'       => $request->phone,
            'image'       => $imagePath,
            'background_style' => $request->background_style,
            'updated_by'  => Auth::id(),
        ]);

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Perfil actualizado',
            'message' => "Tu perfil ha sido actualizado correctamente.",
        ]);

        return redirect()->route('admin.profile.index');
    }

    public function updatePassword()
    {
        $user = Auth::user();
        return view('admin.profile.password', compact('user'));
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
