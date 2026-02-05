<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cover;
use App\Models\Product;

class WellcomeController extends Controller
{
    public function index()
    {
        $covers = Cover::where('status', true)
            ->orderBy('order', 'asc')
            ->whereDate('start_at', '<=', now())
            ->where(function ($query) {
                $query->whereNull('end_at')
                ->orWhere('end_at', '>=', now());
            })
            ->get();

        $lastProducts = Product::with('category', 'images')
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

        return view('welcome', compact('covers', 'lastProducts'));
    }
}
