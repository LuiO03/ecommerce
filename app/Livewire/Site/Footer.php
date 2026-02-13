<?php

namespace App\Livewire\Site;

use Livewire\Component;

class Footer extends Component
{
    public $companySettings;

    public function mount(): void
    {
        $this->companySettings = company_setting();
    }

    public function render()
    {
        return view('livewire.site.footer');
    }
}
