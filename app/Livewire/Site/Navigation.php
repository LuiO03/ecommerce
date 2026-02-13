<?php

namespace App\Livewire\Site;

use Livewire\Component;
use App\Models\Family;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;

class Navigation extends Component
{
    public function render()
    {
        return view('livewire.site.navigation');
    }

    public $families;
    public $wishlistCount = 0;

    public function mount()
    {
        if (Auth::check()) {
            $wishlist = Wishlist::where('user_id', Auth::id())->first();
            $this->wishlistCount = $wishlist ? $wishlist->items()->count() : 0;
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
}
