<?php

namespace App\Livewire\Site;

use Livewire\Component;
use App\Models\Family;
use App\Models\Wishlist;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Navigation extends Component
{

    public function render()
    {
        $user = auth()->user();

        $hasAvatarImage = false;

        if ($user) {
            $hasAvatarImage = $user->image && Storage::disk('public')->exists($user->image);
        }

        $companySettings = function_exists('company_setting') ? company_setting() : null;

        $brandLogoUrl = null;

        if ($companySettings && $companySettings->logo_path) {
            $path = ltrim($companySettings->logo_path, '/');

            if (Str::startsWith($path, ['http://', 'https://'])) {
                $brandLogoUrl = $path;
            } elseif (Storage::disk('public')->exists($path)) {
                $brandLogoUrl = asset('storage/' . $path);
            }
        }

        $brandName = $companySettings->name ?? null;
        return view('livewire.site.navigation', [
            'user' => $user,
            'brandLogoUrl' => $brandLogoUrl,
            'brandName' => $brandName,
            'hasAvatarImage' => $hasAvatarImage,
        ]);
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
