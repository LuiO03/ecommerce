<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->query('q'));

        $products = collect();
        $categories = collect();

        if ($query !== '') {
            $products = Product::query()
                ->with(['category', 'images'])
                ->where('status', true)
                ->whereHas('variants', function ($variantQuery) {
                    $variantQuery->where('status', true);
                })
                ->where(function ($builder) use ($query) {
                    $builder->where('name', 'like', '%' . $query . '%')
                        ->orWhere('sku', 'like', '%' . $query . '%')
                        ->orWhere('description', 'like', '%' . $query . '%');
                })
                ->latest('created_at')
                ->limit(60)
                ->get()
                ->map(function ($product) {
                    $product->mainImage = $product->images
                        ->where('is_main', true)
                        ->first() ?? $product->images
                        ->sortBy('order')
                        ->first();

                    return $product;
                });

            $categories = Category::query()
                ->where('status', true)
                ->where('name', 'like', '%' . $query . '%')
                ->with(['family:id,name'])
                ->orderBy('name')
                ->limit(20)
                ->get();
        }

        return view('site.search.index', [
            'query' => $query,
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    public function suggestions(Request $request)
    {
        $query = trim((string) $request->query('q'));

        if ($query === '' || mb_strlen($query) < 2) {
            return response()->json([
                'products' => [],
                'categories' => [],
            ]);
        }

        $products = Product::query()
            ->select('id', 'name', 'slug', 'price', 'discount')
            ->where('status', true)
            ->whereHas('variants', function ($variantQuery) {
                $variantQuery->where('status', true);
            })
            ->where('name', 'like', '%' . $query . '%')
            ->orderBy('name')
            ->limit(5)
            ->get();

        $categories = Category::query()
            ->select('id', 'name', 'slug', 'family_id')
            ->where('status', true)
            ->where('name', 'like', '%' . $query . '%')
            ->orderBy('name')
            ->limit(5)
            ->get();

        return response()->json([
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}
