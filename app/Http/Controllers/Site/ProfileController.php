<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Addresses;
use App\Models\Order;
use App\Models\WishlistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class ProfileController extends Controller
{
    public function index()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        $orders = Order::with('items.product.images')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $addresses = Addresses::where('user_id', $user->id)
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        $wishlistItems = WishlistItem::with('product.images', 'product.category', 'wishlist')
            ->whereHas('wishlist', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->limit(6)
            ->get();

        return view('site.profile.index', [
            'user' => $user,
            'orders' => $orders,
            'addresses' => $addresses,
            'wishlistItems' => $wishlistItems,
            'activeSection' => 'overview',
        ]);
    }

    public function details()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        return view('site.profile.index', [
            'user' => $user,
            'activeSection' => 'details',
        ]);
    }

    public function orders()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        $orders = Order::with('items.product.images')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('site.profile.index', [
            'user' => $user,
            'orders' => $orders,
            'activeSection' => 'orders',
        ]);
    }

    public function wishlist()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        $wishlistItems = WishlistItem::with('product.images', 'product.category', 'wishlist')
            ->whereHas('wishlist', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();

        return view('site.profile.index', [
            'user' => $user,
            'wishlistItems' => $wishlistItems,
            'activeSection' => 'wishlist',
        ]);
    }

    public function addresses()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        $addresses = Addresses::where('user_id', $user->id)
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        return view('site.profile.index', [
            'user' => $user,
            'addresses' => $addresses,
            'activeSection' => 'addresses',
        ]);
    }

    public function security()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        return view('site.profile.index', [
            'user' => $user,
            'activeSection' => 'security',
        ]);
    }

    public function updateDetails(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        $validated = $request->validate([
            'name'           => 'required|string|max:255|min:3',
            'last_name'      => 'nullable|string|max:255',
            'email'          => 'required|email|unique:users,email,' . $user->id,
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string|max:255',
            'document_type'  => 'nullable|string|in:DNI,RUC,CE,PASAPORTE',
            'document_number'=> 'nullable|string|max:30',
        ]);

        $name = ucwords(mb_strtolower($validated['name']));
        $address = isset($validated['address']) && $validated['address'] !== null
            ? ucfirst(mb_strtolower($validated['address']))
            : null;

        $user->update([
            'name'           => $name,
            'last_name'      => $validated['last_name'] ?? null,
            'email'          => $validated['email'],
            'phone'          => $validated['phone'] ?? null,
            'address'        => $address,
            'document_type'  => $validated['document_type'] ?? null,
            'document_number'=> $validated['document_number'] ?? null,
            'updated_by'     => $user->id,
        ]);

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Datos actualizados',
            'message' => 'Tus datos de cuenta se han actualizado correctamente.',
        ]);

        return redirect()->route('site.profile.details');
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

        return redirect()->route('site.profile.details', ['#password-section' => null]);
    }
}
