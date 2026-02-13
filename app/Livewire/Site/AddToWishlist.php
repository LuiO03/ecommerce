<?php

namespace App\Livewire\Site;

use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class AddToWishlist extends Component
{
    public $product;

    public $isInWishlist = false;

    public function mount($product)
    {
        $this->product = $product;

        if (Auth::check()) {
            $wishlist = Wishlist::where('user_id', Auth::id())->first();

            if ($wishlist) {
                $this->isInWishlist = $wishlist->items()
                    ->where('product_id', $this->product->id)
                    ->exists();
            }
        }
    }

    public function render()
    {
        return view('livewire.site.add-to-wishlist');
    }

    public function addToWishlist()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $userId = Auth::id();
        $wishlist = Wishlist::firstOrCreate(
            [
                'user_id' => $userId,
                'slug' => 'default-'.$userId,
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
            ->where('product_id', $this->product->id)
            ->first();

        $removed = false;

        if ($wishlistItem && ! $wishlistItem->trashed()) {
            $wishlistItem->delete();
            $wishlist->decrement('items_count');
            $this->isInWishlist = false;
            $removed = true;
        } else {
            if ($wishlistItem && $wishlistItem->trashed()) {
                $wishlistItem->restore();
            } else {
                $wishlistItem = WishlistItem::create([
                    'wishlist_id' => $wishlist->id,
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                ]);
            }
            $wishlist->increment('items_count');
            $this->isInWishlist = true;
        }

        $toastPayload = [
            'type' => 'success',
            'title' => $removed ? 'Producto eliminado de favoritos' : 'Producto agregado a favoritos',
            'message' => $removed
                ? 'El producto se ha eliminado de tu lista de deseos.'
                : 'El producto se ha agregado a favoritos correctamente.',
        ];

        Session::flash('toast', $toastPayload);

        $this->dispatch('toast', $toastPayload);
        $this->dispatch('wishlistUpdated', id: $wishlistItem->id);
    }
}
