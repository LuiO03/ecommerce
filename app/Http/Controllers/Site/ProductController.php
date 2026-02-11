<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Option;

class ProductController extends Controller
{
    public function show($slug)
    {
        $product = Product::where('slug', $slug)->with(['category', 'images'])->firstOrFail();
        $variants = $product->variants()
            ->where('status', true)
            ->where('stock', '>', 0)
            ->with('features.option')
            ->get();

        $variantOptions = $variants
            ->flatMap(fn ($variant) => $variant->features)
            ->groupBy('option_id')
            ->map(function ($features) {
                $option = $features->first()?->option;

                return [
                    'option_id' => $option?->id,
                    'name' => $option?->name ?? 'Opcion',
                    'slug' => $option?->slug,
                    'is_color' => $option?->slug === Option::COLOR_SLUG,
                    'features' => $features
                        ->unique('id')
                        ->values()
                        ->map(fn ($feature) => [
                            'id' => $feature->id,
                            'value' => $feature->value,
                            'description' => $feature->description,
                        ])
                        ->all(),
                ];
            })
            ->values();

        $variantsPayload = $variants
            ->map(fn ($variant) => [
                'id' => $variant->id,
                'price' => $variant->price,
                'stock' => $variant->stock,
                'features' => $variant->features->map(fn ($feature) => [
                    'id' => $feature->id,
                    'option_id' => $feature->option_id,
                    'option_slug' => $feature->option?->slug,
                    'value' => $feature->value,
                    'description' => $feature->description,
                ])->values()->all(),
            ])
            ->values();
        $breadcrumbItems = [];

        if ($product->category) {
            // Agregar categorías padres
            $parents = [];
            $current = $product->category;
            while ($current) {
                $parents[] = $current;
                $current = $current->parent;
            }

            foreach (array_reverse($parents) as $parent) {
                $breadcrumbItems[] = [
                    'label' => $parent->name,
                    'url' => route('categories.show', $parent),
                ];
            }
        }

        $breadcrumbItems[] = [
            'label' => $product->name,
            'icon' => 'ri-product-hunt-line',
        ];

        return view('site.products.show', compact(
            'product',
            'breadcrumbItems',
            'variants',
            'variantOptions',
            'variantsPayload'
        ));
    }
}
