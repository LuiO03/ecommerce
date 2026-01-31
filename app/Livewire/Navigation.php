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
    public function mount(){
        $this->families = Family::orderBy('name')->get();
    }
}
