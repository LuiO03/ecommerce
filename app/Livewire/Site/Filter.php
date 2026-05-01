<?php

namespace App\Livewire\Site;

use Livewire\Component;
use App\Models\Option;
use App\Models\Product;
use App\Models\Category;
use App\Models\Family;
use App\Models\Feature;
use App\Models\Brand;
use Illuminate\Support\Facades\DB;

class Filter extends Component
{
    public $search;
    public $family_id;
    public $category_id;
    public $family;
    public $category;
    public $subcategories = [];
    public $options = [];
    public $selectedFeatures = [];
    public $selectedFeaturesByOption = [];
    public $products = [];
    public $perPage = 12;
    public $perPageStep = 12;
    public $hasMore = false;
    public $sortBy = 'recent';
    public $featureCounts = [];
    public $totalProducts = 0;
    public $currentPage = 1;
    public $totalPages = 1;


    public $brandCounts = [];
    public $brand_id;
    public $brand;
    public $brands = [];
    public $selectedBrands = [];

    public function mount()
    {
        if ($this->category_id) {
            $category = Category::with('family')->find($this->category_id);
            if ($category) {
                $this->family_id = $this->family_id ?: $category->family_id;
                $this->family = $category->family;
                $this->category = $category;
            }
        }

        if (!$this->family && $this->family_id) {
            $this->family = Family::find($this->family_id);
        }

        if ($this->brand_id) {
            $this->brand = Brand::find($this->brand_id);
        }

        $this->loadSubcategories();
        $this->loadBrands();
        $this->loadProducts();
    }

    public function applyFilters()
    {
        $this->perPage = $this->perPageStep;
        $this->loadProducts();
    }

    public function clearFilters()
    {
        $this->reset(['selectedFeatures', 'selectedBrands']);
        $this->perPage = $this->perPageStep;
        $this->loadProducts();
    }

    public function loadMore()
    {
        $this->perPage += $this->perPageStep;
        $this->loadProducts();
    }

    public function updateSort($value)
    {
        $this->sortBy = $value;
        $this->perPage = $this->perPageStep;
        $this->loadProducts();
    }

    public function goToPage(int $page): void
    {
        $page = max(1, min($page, $this->totalPages));
        $this->perPage = $page * $this->perPageStep;
        $this->loadProducts();
    }

    public function nextPage(): void
    {
        $this->goToPage($this->currentPage + 1);
    }

    public function previousPage(): void
    {
        $this->goToPage($this->currentPage - 1);
    }

    protected function loadProducts(): void
    {
        $featuresByOption = [];
        $categoryIds = [];

        if ($this->search) {
            // Modo búsqueda global: sin filtros por variantes/opciones
            $this->options = [];
            $this->selectedFeaturesByOption = [];
        } else {
            if (!empty($this->selectedFeatures)) {
                $featuresByOption = Feature::query()
                    ->whereIn('id', $this->selectedFeatures)
                    ->get(['id', 'option_id'])
                    ->groupBy('option_id')
                    ->map(fn ($items) => $items->pluck('id')->all())
                    ->all();

                $this->selectedFeaturesByOption = collect($featuresByOption)
                    ->map(fn (array $featureIds) => count($featureIds))
                    ->all();
            } else {
                $this->selectedFeaturesByOption = [];
            }

            $categoryIds = $this->resolveCategoryIds();
            $this->loadOptions($categoryIds);
        }

        $baseProductQuery = DB::table('products')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
            ->join('variants', 'variants.product_id', '=', 'products.id')
            ->where('products.status', true)
            ->where('variants.status', true)
            ->select('products.id')
            ->distinct();

        if (empty($categoryIds) && $this->family_id) {
            $baseProductQuery->where('categories.family_id', $this->family_id);
        }

        if (!empty($categoryIds)) {
            $baseProductQuery->whereIn('products.category_id', $categoryIds);
        }

        if (!empty($this->selectedBrands)) {
            $baseProductQuery->whereIn('products.brand_id', $this->selectedBrands);
        }

        if ($this->search) {
            $term = '%' . $this->search . '%';
            $baseProductQuery->where(function ($builder) use ($term) {
                $builder->where('products.name', 'like', $term)
                    ->orWhere('products.sku', 'like', $term)
                    ->orWhere('products.description', 'like', $term)
                    ->orWhere('brands.name', 'like', $term);
            });
        }

        if (!empty($featuresByOption)) {
            $matchingVariantProductIds = $this->buildVariantMatchSubquery($featuresByOption)
                ->select('variants.product_id');

            $baseProductQuery->whereIn('products.id', $matchingVariantProductIds);
        }

        if ($this->brand_id) {
            $baseProductQuery->where('products.brand_id', $this->brand_id);
        }

        $total = (clone $baseProductQuery)->distinct()->count('products.id');
        $this->totalProducts = $total;
        $this->totalPages = max(1, (int) ceil($total / $this->perPageStep));
        $this->currentPage = min($this->totalPages, (int) ceil($this->perPage / $this->perPageStep));

        $query = Product::with(['category', 'images', 'brand'])
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
        $this->loadFacetCounts($featuresByOption, $categoryIds);
        $this->loadBrands();
        $this->filterOptionsByCounts();
    }

    protected function loadOptions(array $categoryIds): void
    {
        $this->options = Option::query()
            ->whereHas('features.variants', function ($query) use ($categoryIds) {
                $query->where('variants.status', true)
                    ->whereHas('product', function ($query) use ($categoryIds) {
                        $query->where('products.status', true)
                            ->whereHas('category', function ($query) use ($categoryIds) {
                                if (!empty($categoryIds)) {
                                    $query->whereIn('categories.id', $categoryIds);
                                } elseif ($this->family_id) {
                                    $query->where('family_id', $this->family_id);
                                }
                            });
                    });
            })
            ->with([
                'features' => function ($query) use ($categoryIds) {
                    $query->whereHas('variants', function ($query) use ($categoryIds) {
                        $query->where('variants.status', true)
                            ->whereHas('product', function ($query) use ($categoryIds) {
                                $query->where('products.status', true)
                                    ->whereHas('category', function ($query) use ($categoryIds) {
                                        if (!empty($categoryIds)) {
                                            $query->whereIn('categories.id', $categoryIds);
                                        } elseif ($this->family_id) {
                                            $query->where('family_id', $this->family_id);
                                        }
                                    });
                            });
                    });
                }
            ])
            ->get();
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

    protected function loadFacetCounts(array $featuresByOption, array $categoryIds): void
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
                ->where('products.status', true)
                ->where('variants.status', true)
                ->where('features.option_id', $optionId);

            if (empty($categoryIds) && $this->family_id) {
                $query->where('categories.family_id', $this->family_id);
            }

            if (!empty($categoryIds)) {
                $query->whereIn('products.category_id', $categoryIds);
            }

            if (!empty($this->selectedBrands)) {
                $query->whereIn('products.brand_id', $this->selectedBrands);
            }

            if ($this->brand_id) {
                $query->where('products.brand_id', $this->brand_id);
            }

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

    protected function filterOptionsByCounts(): void
    {
        $counts = $this->featureCounts;

        $this->options = collect($this->options)
            ->map(function ($option) use ($counts) {
                $option->features = $option->features
                    ->filter(fn ($feature) => ($counts[$feature->id] ?? 0) > 0)
                    ->values();

                return $option;
            })
            ->filter(fn ($option) => $option->features->isNotEmpty())
            ->values();
    }

    protected function resolveCategoryIds(): array
    {
        if (!$this->category_id) {
            return [];
        }

        $rootId = Category::whereKey($this->category_id)->value('id');
        if (!$rootId) {
            return [];
        }

        $ids = [$rootId];
        $pending = [$rootId];

        while (!empty($pending)) {
            $children = Category::whereIn('parent_id', $pending)->pluck('id')->all();
            $children = array_values(array_diff($children, $ids));
            if (empty($children)) {
                break;
            }
            $ids = array_merge($ids, $children);
            $pending = $children;
        }

        return $ids;
    }

    protected function loadSubcategories(): void
    {
        $query = null;

        if ($this->category) {
            $query = Category::where('parent_id', $this->category->id);
        } elseif ($this->family_id) {
            $query = Category::where('family_id', $this->family_id)->whereNull('parent_id');
        }

        if (!$query) {
            $this->subcategories = [];
            return;
        }

        $this->subcategories = $query
            ->where('status', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);
    }

    protected function loadBrands(): void
    {
        if ($this->brand_id) {
            $this->brands = collect();
            return;
        }
        $query = Brand::query()
            ->where('status', true)
            ->whereHas('products', function ($q) {
                $q->where('status', true);

                if ($this->category_id) {
                    $q->whereIn('category_id', $this->resolveCategoryIds());
                } elseif ($this->family_id) {
                    $q->whereHas('category', fn($c) =>
                        $c->where('family_id', $this->family_id)
                    );
                }
            })
            ->withCount(['products as products_count' => function ($q) {
                $q->where('status', true);

                if ($this->category_id) {
                    $q->whereIn('category_id', $this->resolveCategoryIds());
                } elseif ($this->family_id) {
                    $q->whereHas('category', fn($c) =>
                        $c->where('family_id', $this->family_id)
                    );
                }
            }])
            ->orderBy('name');

        $this->brands = $query->get();
    }

    public function render()
    {
        return view('livewire.site.filter');
    }
}
