<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Addresses;
use App\Models\Order;
use App\Models\WishlistItem;
use Illuminate\Support\Facades\Auth;

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
}
