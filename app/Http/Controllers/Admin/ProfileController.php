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


class ProfileController extends Controller
{
    // ======================
    //      VISTA PRINCIPAL PERFIL
    // ======================
    public function index()
    {
        $user = Auth::user();
        return view('admin.profile.index', compact('user'));
    }

    // ======================
    //      ACTUALIZAR PERFIL
    // ======================
    public function update(Request $request)
    {
        
        $user = User::query()->findOrFail(Auth::id());

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
