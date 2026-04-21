<?php

namespace App\Livewire\Site;

use Livewire\Component;
use App\Models\Product;

class LatestProducts extends Component
{
    public $products;

    public function mount()
    {
        $this->products = Product::with(['category', 'images'])
            ->where('status', true)
            ->whereHas('variants', function ($query) {
                $query->where('status', true)
                    ->where('stock', '>', 0);
            })
            ->latest('created_at')
            ->take(8)
            ->get()
            ->map(function ($product) {
                // Cargar la imagen principal del producto
                $product->mainImage = $product->images
                    ->where('is_main', true)
                    ->first() ?? $product->images
                    ->sortBy('order')
                    ->first();
                return $product;
            });
    }

    public function render()
    {
        return view('livewire.site.latest-products');
    }
}
