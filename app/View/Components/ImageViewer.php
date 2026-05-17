<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ImageViewer extends Component
{
    public function __construct(
        public ?string $src = null,
        public string $alt = '',
        public ?string $title = null,
        public ?string $description = null,
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.image-viewer');
    }
}
