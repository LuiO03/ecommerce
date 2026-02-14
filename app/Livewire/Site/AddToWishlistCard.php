<?php

namespace App\Livewire\Site;

use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AddToWishlistCard extends Component
{
    public int $productId;

    public bool $isInWishlist = false;

    public function mount(int $productId)
    {
        $this->productId = $productId;

        if (Auth::check()) {
            $wishlist = Wishlist::where('user_id', Auth::id())->first();

            if ($wishlist) {
                $this->isInWishlist = $wishlist->items()
                    ->where('product_id', $this->productId)
                    ->exists();
            }
        }
    }

    public function render()
    {
        return view('livewire.site.add-to-wishlist-card');
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
            ->where('product_id', $this->productId)
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
                    'product_id' => $this->productId,
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
                ? 'El producto se ha quitado de tu lista de deseos.'
                : 'El producto se ha agregado a favoritos.',
        ];

        $this->dispatch('toast', $toastPayload);
        $this->dispatch('wishlistUpdated', id: $wishlistItem->id);
    }
}
