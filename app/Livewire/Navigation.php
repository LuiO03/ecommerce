<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Family;

class Navigation extends Component
{
    public function render()
    {
        return view('livewire.navigation');
    }

    public $families;

    public function mount()
    {
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
