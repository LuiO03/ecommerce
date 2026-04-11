<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Addresses;
use App\Models\Order;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Secciones para la vista unificada (tipo SPA ligera)
        $activeSection = $request->get('section', 'overview');

        // Pedidos (se usa en resumen y en la pestaña de pedidos)
        $orders = Order::with('items.product.images')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        // Direcciones (resumen + pestaña de direcciones)
        $addresses = Addresses::where('user_id', $user->id)
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        // Favoritos (resumen + pestaña de favoritos)
        $wishlistItems = WishlistItem::with('product.images', 'product.category', 'wishlist')
            ->whereHas('wishlist', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();

        // Sesiones activas (pestaña de seguridad)
        $sessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderByDesc('last_activity')
            ->get();

        return view('site.profile.index', [
            'user' => $user,
            'orders' => $orders,
            'addresses' => $addresses,
            'wishlistItems' => $wishlistItems,
            'sessions' => $sessions,
            'activeSection' => $activeSection,
        ]);
    }

    public function details()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        return redirect()->route('site.profile.index', ['section' => 'details']);
    }

    public function orders()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        return redirect()->route('site.profile.index', ['section' => 'orders']);
    }

    public function wishlist()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        return redirect()->route('site.profile.index', ['section' => 'wishlist']);
    }

    public function addresses()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        return redirect()->route('site.profile.index', ['section' => 'addresses']);
    }

    public function security()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        return redirect()->route('site.profile.index', ['section' => 'security']);
    }

    public function logoutSession(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $request->validate([
            'session_id' => 'required|string',
        ]);

        DB::table('sessions')
            ->where('id', $request->input('session_id'))
            ->where('user_id', Auth::id())
            ->delete();

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Sesión cerrada',
            'message' => 'La sesión de ese dispositivo se cerró correctamente.',
        ]);

        return redirect()->route('site.profile.index', ['section' => 'security']);
    }

    public function updateDetails(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        $rules = [
            'name'            => 'required|string|max:255|min:3',
            'last_name'       => 'nullable|string|max:255',
            'email'           => 'required|email|unique:users,email,' . $user->id,
            'phone'           => 'nullable|string|max:20',
            'address'         => 'nullable|string|max:255',
            'document_type'   => 'nullable|string|in:DNI,RUC,CE,PASAPORTE',
            'document_number' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('users', 'document_number')
                    ->where(fn ($query) => $query->where('document_type', $request->input('document_type')))
                    ->ignore($user->id),
            ],
            'image'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_image'    => 'nullable|in:0,1',
        ];

        $messages = [
            'document_number.unique' => 'Ya existe una cuenta con ese tipo y número de documento.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            if ($validator->errors()->has('document_number')) {
                Session::flash('info', [
                    'type' => 'warning',
                    'header' => 'Documento duplicado',
                    'title' => 'Documento ya registrado',
                    'message' => 'Ya existe una cuenta con ese tipo y número de documento.',
                ]);
            }

            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        $name = ucwords(mb_strtolower($validated['name']));
        $address = isset($validated['address']) && $validated['address'] !== null
            ? ucfirst(mb_strtolower($validated['address']))
            : null;

        $imagePath = $user->image;

        // Eliminar imagen existente
        if (($request->input('remove_image') ?? '0') === '1') {
            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }
            $imagePath = null;
        }
        // Subir nueva imagen
        elseif ($request->hasFile('image')) {
            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }

            $ext = $request->file('image')->getClientOriginalExtension();
            $slug = User::generateUniqueSlug($name, $user->id);
            $filename = $slug . '-' . time() . '.' . $ext;
            $imagePath = 'users/' . $filename;
            $request->file('image')->storeAs('users', $filename, 'public');
        }

        $user->update([
            'name'           => $name,
            'last_name'      => $validated['last_name'] ?? null,
            'email'          => $validated['email'],
            'phone'          => $validated['phone'] ?? null,
            'address'        => $address,
            'document_type'  => $validated['document_type'] ?? null,
            'document_number'=> $validated['document_number'] ?? null,
            'image'          => $imagePath,
            'updated_by'     => $user->id,
        ]);

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Datos actualizados',
            'message' => 'Tus datos de cuenta se han actualizado correctamente.',
        ]);

        return redirect()->route('site.profile.index', ['section' => 'details']);
    }

    public function updatePassword(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->forceFill([
            'password'             => Hash::make($request->password),
            'last_password_update' => now(),
            'updated_by'           => $user->id,
        ])->save();

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Contraseña actualizada',
            'message' => 'Tu contraseña se ha cambiado correctamente.',
        ]);

        return redirect()->to(route('site.profile.index', ['section' => 'security']) . '#password-section');
    }
}
