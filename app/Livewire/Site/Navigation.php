<?php

namespace App\Livewire\Site;

use Livewire\Component;
use App\Models\Family;
use App\Models\Wishlist;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class Navigation extends Component
{
    public function render()
    {
        return view('livewire.site.navigation');
    }

    public $families;
    public $wishlistCount = 0;
    public $cartCount = 0;

    public function mount()
    {
        if (Auth::check()) {
            $wishlist = Wishlist::where('user_id', Auth::id())->first();
            $this->wishlistCount = $wishlist ? $wishlist->items()->count() : 0;
            $cart = Cart::where('user_id', Auth::id())
                ->where('is_active', true)
                ->first();

            // Puedes optar por mostrar items_count (líneas distintas)
            // o items_quantity (unidades totales). Aquí usamos unidades totales.
            $this->cartCount = $cart ? (int) $cart->items_quantity : 0;
        }

        // Cargar familias con sus categorías anidadas recursivamente
        $this->families = Family::with([
            'categories' => function ($query) {
                $query->whereNull('parent_id')->orderBy('name');
            },
            'categories.children' => function ($query) {
                $query->orderBy('name');
            },
            'categories.children.children' => function ($query) {
                $query->orderBy('name');
            },
            'categories.children.children.children' => function ($query) {
                $query->orderBy('name');
            }
        ])
        ->orderBy('name')
        ->get();
    }

    #[On('wishlistUpdated')]
    #[On('cartUpdated')]
    public function refreshCounts(): void
    {
        if (! Auth::check()) {
            $this->wishlistCount = 0;
            $this->cartCount = 0;

            return;
        }

        $wishlist = Wishlist::where('user_id', Auth::id())->first();
        $this->wishlistCount = $wishlist ? $wishlist->items()->count() : 0;

        $cart = Cart::where('user_id', Auth::id())
            ->where('is_active', true)
            ->first();

        $this->cartCount = $cart ? (int) $cart->items_quantity : 0;
    }
}
