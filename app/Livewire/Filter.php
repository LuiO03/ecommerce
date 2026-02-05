<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Option;
use App\Models\Product;
use App\Models\Family;
use App\Models\Feature;
use Illuminate\Support\Facades\DB;

class Filter extends Component
{
    public $family_id;
    public $family;
    public $options = [];
    public $selectedFeatures = [];
    public $search = '';
    public $products = [];
    public $perPage = 12;
    public $hasMore = false;
    public $sortBy = 'recent';
    public $featureCounts = [];

    public function mount()
    {
        $this->family = Family::find($this->family_id);
        $this->options = Option::whereHas('products.category', function ($query) {
            $query->where('family_id', $this->family_id);
        })->with([
            'features' => function ($query) {
                $query->whereHas('variants.product.category', function ($query) {
                    $query->where('family_id', $this->family_id);
                });
            }
        ])->get();

        $this->loadProducts();
    }

    public function applyFilters()
    {
        $this->perPage = 12;
        $this->loadProducts();
    }

    public function clearFilters()
    {
        $this->reset(['selectedFeatures', 'search']);
        $this->perPage = 12;
        $this->loadProducts();
    }

    public function loadMore()
    {
        $this->perPage += 12;
        $this->loadProducts();
    }

    public function updateSort($value)
    {
        $this->sortBy = $value;
        $this->perPage = 12;
        $this->loadProducts();
    }

    protected function loadProducts(): void
    {
        $featuresByOption = [];
        if (!empty($this->selectedFeatures)) {
            $featuresByOption = Feature::query()
                ->whereIn('id', $this->selectedFeatures)
                ->get(['id', 'option_id'])
                ->groupBy('option_id')
                ->map(fn ($items) => $items->pluck('id')->all())
                ->all();
        }

        $baseProductQuery = DB::table('products')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->join('variants', 'variants.product_id', '=', 'products.id')
            ->where('categories.family_id', $this->family_id)
            ->where('products.status', true)
            ->where('variants.status', true)
            ->select('products.id')
            ->distinct();

        if (!empty($featuresByOption)) {
            $matchingVariantProductIds = $this->buildVariantMatchSubquery($featuresByOption)
                ->select('variants.product_id');

            $baseProductQuery->whereIn('products.id', $matchingVariantProductIds);
        }

        $total = (clone $baseProductQuery)->distinct()->count('products.id');

        $query = Product::with(['category', 'images'])
            ->whereIn('products.id', $baseProductQuery);

        // Aplicar ordenamiento
        switch ($this->sortBy) {
            case 'price-asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price-desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name-asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name-desc':
                $query->orderBy('name', 'desc');
                break;
            case 'recent':
            default:
                $query->latest('created_at');
                break;
        }

        $this->products = $query->take($this->perPage)
            ->get()
            ->map(function ($product) {
                $product->mainImage = $product->images
                    ->where('is_main', true)
                    ->first() ?? $product->images
                    ->sortBy('order')
                    ->first();

                return $product;
            });

        $this->hasMore = $this->products->count() < $total;
        $this->loadFacetCounts($featuresByOption);
    }

    protected function buildVariantMatchSubquery(array $featuresByOption)
    {
        $optionCount = count($featuresByOption);

        return DB::table('variants')
            ->join('feature_variant', 'feature_variant.variant_id', '=', 'variants.id')
            ->join('features', 'features.id', '=', 'feature_variant.feature_id')
            ->where('variants.status', true)
            ->where(function ($query) use ($featuresByOption) {
                foreach ($featuresByOption as $optionId => $featureIds) {
                    $query->orWhere(function ($optionQuery) use ($optionId, $featureIds) {
                        $optionQuery->where('features.option_id', $optionId)
                            ->whereIn('features.id', $featureIds);
                    });
                }
            })
            ->groupBy('variants.id', 'variants.product_id')
            ->havingRaw('COUNT(DISTINCT features.option_id) = ?', [$optionCount]);
    }

    protected function loadFacetCounts(array $featuresByOption): void
    {
        $optionIds = collect($this->options)->pluck('id')->all();
        $counts = [];

        foreach ($optionIds as $optionId) {
            $otherFilters = $featuresByOption;
            unset($otherFilters[$optionId]);

            $query = DB::table('features')
                ->join('feature_variant', 'feature_variant.feature_id', '=', 'features.id')
                ->join('variants', 'variants.id', '=', 'feature_variant.variant_id')
                ->join('products', 'products.id', '=', 'variants.product_id')
                ->join('categories', 'categories.id', '=', 'products.category_id')
                ->where('categories.family_id', $this->family_id)
                ->where('products.status', true)
                ->where('variants.status', true)
                ->where('features.option_id', $optionId);

            if (!empty($otherFilters)) {
                $matchingVariantIds = $this->buildVariantMatchSubquery($otherFilters)
                    ->select('variants.id');

                $query->whereIn('variants.id', $matchingVariantIds);
            }

            $optionCounts = $query
                ->select('features.id', DB::raw('COUNT(DISTINCT products.id) as total'))
                ->groupBy('features.id')
                ->pluck('total', 'features.id')
                ->all();

            $counts = array_replace($counts, $optionCounts);
        }

        $this->featureCounts = $counts;
    }

    public function render()
    {
        return view('livewire.filter');
    }


}
