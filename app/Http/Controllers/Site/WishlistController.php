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
    public function show()
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

        return view('site.wishlists.show', compact('wishlists'));
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

        return redirect()->route('wishlists.show');
    }


    public function edit(Wishlist $wishlist)
    {
        //
    }

    public function update(Request $request, Wishlist $wishlist)
    {
        //
    }

    public function destroy(WishlistItem $wishlistItem)
    {
        $wishlist = $wishlistItem->wishlist;
        if (!$wishlist || $wishlist->user_id !== Auth::id()) {
            abort(403);
        }

        $wishlistItem->delete();
        $wishlist->decrement('items_count');

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Producto eliminado de favoritos',
            'message' => 'El producto se ha eliminado de tu lista de deseos.',
        ]);

        return redirect()->route('wishlists.show');
    }
}
