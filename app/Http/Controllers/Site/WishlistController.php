<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userId = Auth::id();
        $wishlists = collect();

        if ($userId) {
            $wishlists = WishlistItem::with('product.images', 'product.category', 'wishlist')
                ->whereHas('wishlist', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->get();
        }

        return view('site.wishlists.index', compact('wishlists'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $userId = Auth::id();
        $wishlist = Wishlist::firstOrCreate(
            [
                'user_id' => $userId,
                'slug' => 'default-' . $userId,
            ],
            [
                'name' => 'Mis favoritos',
                'description' => 'Lista principal de deseos',
                'is_public' => false,
                'status' => true,
            ]
        );

        $wishlistItem = WishlistItem::withTrashed()
            ->where('wishlist_id', $wishlist->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($wishlistItem) {
            if ($wishlistItem->trashed()) {
                $wishlistItem->restore();
                $wishlist->increment('items_count');
            }
        } else {
            $wishlistItem = WishlistItem::create([
                'wishlist_id' => $wishlist->id,
                'product_id' => $request->product_id,
                'quantity' => 1,
            ]);
            $wishlist->increment('items_count');
        }

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Producto agregado a favoritos',
            'message' => 'El producto se ha agregado a favoritos correctamente.',
        ]);

        return redirect()->route('wishlists.index');
    }


    public function edit(Wishlist $wishlist)
    {
        //
    }

    public function update(Request $request, Wishlist $wishlist)
    {
        //
    }

    public function destroyAll(Request $request)
    {
        if (! Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'toast' => [
                        'type' => 'warning',
                        'title' => 'Sesión requerida',
                        'message' => 'Inicia sesión para gestionar tu lista de deseos.',
                    ],
                ], 401);
            }

            return redirect()->route('login');
        }

        $userId = Auth::id();

        $wishlist = Wishlist::where('user_id', $userId)->first();

        if ($wishlist) {
            $wishlist->items()->delete();
            $wishlist->items_count = 0;
            $wishlist->save();
        }

        $toast = [
            'type' => 'success',
            'title' => 'Lista de deseos vaciada',
            'message' => 'Se han eliminado todos los productos de tu lista de deseos.',
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'toast' => $toast,
            ]);
        }

        Session::flash('toast', $toast);

        return redirect()->route('wishlists.index');
    }

    public function destroy(Request $request, WishlistItem $wishlistItem)
    {
        $wishlist = $wishlistItem->wishlist;
        if (!$wishlist || $wishlist->user_id !== Auth::id()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'toast' => [
                        'type' => 'danger',
                        'title' => 'Acceso no autorizado',
                        'message' => 'No puedes modificar esta lista de deseos.',
                    ],
                ], 403);
            }

            abort(403);
        }

        $wishlistItem->delete();
        $wishlist->decrement('items_count');

        $toast = [
            'type' => 'success',
            'title' => 'Producto eliminado de favoritos',
            'message' => 'El producto se ha eliminado de tu lista de deseos.',
        ];

        // Recontar items restantes del usuario para actualizar contadores en cliente
        $remaining = WishlistItem::whereHas('wishlist', function ($query) {
            $query->where('user_id', Auth::id());
        })->count();

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'toast' => $toast,
                'remaining_count' => $remaining,
            ]);
        }

        Session::flash('toast', $toast);

        return redirect()->route('wishlists.index');
    }
}
