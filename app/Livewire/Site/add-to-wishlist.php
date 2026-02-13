<?php

namespace App\Livewire\Site;

use Livewire\Component;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class AddToWishlist extends Component
{
    public $product;
    public string $variant = 'detail';
    public $isInWishlist = false;

    public function mount($product, string $variant = 'detail')
    {
        $this->product = $product;
        $this->variant = $variant;

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
            ->where('product_id', $this->product->id)
            ->first();

        $removed = false;

        if ($wishlistItem && !$wishlistItem->trashed()) {
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
        $this->dispatch('toast',
            type: $toastPayload['type'],
            title: $toastPayload['title'],
            message: $toastPayload['message'],
        );
        $this->dispatch('wishlistUpdated', id: $wishlistItem->id);
    }
}
