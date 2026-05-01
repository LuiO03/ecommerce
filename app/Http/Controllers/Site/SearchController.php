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
        if ($query === '') {
            return redirect()->route('site.home');
        }

        return view('site.search.results', [
            'query' => $query,
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
            ->with(['images' => function ($q) {
                $q->orderBy('is_main', 'desc')->orderBy('order');
            }])
            ->where('status', true)
            ->whereHas('variants', function ($variantQuery) {
                $variantQuery->where('status', true);
            })
            ->where(function ($q) use ($query) {
                $term = '%' . $query . '%';

                $q->where('name', 'like', $term)
                    ->orWhere('sku', 'like', $term)
                    ->orWhereHas('brand', function ($brandQuery) use ($term) {
                        $brandQuery->where('name', 'like', $term);
                    });
            })
            ->orderBy('name')
            ->limit(8)
            ->get()
            ->map(function (Product $product) {
                $image = $product->images->first();

                $price = (float) $product->price;
                $discountPercent = ! is_null($product->discount)
                    ? min(max((float) $product->discount, 0), 100)
                    : 0;
                $hasDiscount = $discountPercent > 0;
                $discounted = $hasDiscount
                    ? max($price * (1 - $discountPercent / 100), 0)
                    : $price;

                return [
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => $price,
                    'discounted_price' => $discounted,
                    'has_discount' => $hasDiscount,
                    'image_url' => $image ? asset('storage/' . $image->path) : null,
                ];
            });

        $categories = Category::query()
            ->select('id', 'name', 'slug')
            ->where('status', true)
            ->where('name', 'like', '%' . $query . '%')
            ->orderBy('name')
            ->limit(8)
            ->get();

        return response()->json([
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}
