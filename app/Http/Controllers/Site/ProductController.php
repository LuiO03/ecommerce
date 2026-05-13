<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Option;

class ProductController extends Controller
{
    public function show($slug)
    {
        // Obtener producto activo
        $product = Product::where('slug', $slug)
            ->where('status', true)
            ->with([
                'category.parent',
                'images',
            ])
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Incrementar vistas
        |--------------------------------------------------------------------------
        | Solo incrementa una vez por sesión para evitar:
        | - refresh infinitos
        | - inflar métricas
        | - conteos exagerados
        |--------------------------------------------------------------------------
        */
        $sessionKey = 'viewed_product_' . $product->id;

        if (!session()->has($sessionKey)) {

            $product->incrementQuietly('views_count');

            session()->put($sessionKey, true);
        }

        /*
        |--------------------------------------------------------------------------
        | Variantes activas
        |--------------------------------------------------------------------------
        */
        $hasActiveVariants = $product->variants()
            ->where('status', true)
            ->exists();

        /*
        |--------------------------------------------------------------------------
        | Variantes disponibles
        |--------------------------------------------------------------------------
        */
        $variants = $product->variants()
            ->where('status', true)
            ->where('stock', '>', 0)
            ->with('features.option')
            ->get();

        $hasAvailableVariants = $variants->isNotEmpty();

        /*
        |--------------------------------------------------------------------------
        | Opciones agrupadas (color, talla, etc)
        |--------------------------------------------------------------------------
        */
        $variantOptions = $variants
            ->flatMap(fn($variant) => $variant->features)
            ->groupBy('option_id')
            ->map(function ($features) {

                $option = $features->first()?->option;

                return (object) [
                    'option_id' => $option?->id,
                    'name' => $option?->name ?? 'Opción',
                    'slug' => $option?->slug,

                    'is_color' => $option?->slug === Option::COLOR_SLUG,

                    'features' => $features
                        ->unique('id')
                        ->values()
                        ->map(fn($feature) => (object) [
                            'id' => $feature->id,
                            'value' => $feature->value,
                            'description' => $feature->description,
                        ])
                        ->all(),
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Payload para JS
        |--------------------------------------------------------------------------
        */
        $variantsPayload = $variants
            ->map(fn($variant) => [
                'id' => $variant->id,
                'price' => $variant->price,
                'stock' => $variant->stock,

                'features' => $variant->features
                    ->map(fn($feature) => [
                        'id' => $feature->id,
                        'option_id' => $feature->option_id,
                        'option_slug' => $feature->option?->slug,
                        'value' => $feature->value,
                        'description' => $feature->description,
                    ])
                    ->values()
                    ->all(),
            ])
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Breadcrumbs
        |--------------------------------------------------------------------------
        */
        $breadcrumbItems = [];

        if ($product->category) {

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
        ];

        /*
        |--------------------------------------------------------------------------
        | Vista
        |--------------------------------------------------------------------------
        */
        return view('site.products.show', compact(
            'product',
            'breadcrumbItems',
            'variants',
            'variantOptions',
            'variantsPayload',
            'hasActiveVariants',
            'hasAvailableVariants'
        ));
    }
}
