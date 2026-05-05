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

/**
 * Filter Component para el frontend
 *
 * Gestiona el catálogo de productos con filtros jerárquicos:
 * - Por categoría (anidables)
 * - Por marca
 * - Por opciones/features (colores, tallas, etc.)
 * - Por búsqueda de texto
 *
 * ⚠️ IMPORTANTE: Todos los contadores respetan el stock disponible (variants.stock >= 1)
 *
 * Flujo de carga:
 * 1. mount() → Resolver contexto (family, category, brand)
 * 2. loadProducts() → Query base con todos los filtros
 * 3. loadOptions() → Opciones disponibles en la categoría
 * 4. loadFacetCounts() → Contar productos por cada feature
 * 5. filterOptionsByCounts() → Ocultar features sin productos
 * 6. loadBrands() → Marcas disponibles
 */
class Filter extends Component
{
    // Context
    public $search;
    public $family_id;
    public $category_id;
    public $family;
    public $category;
    public $brand_id;
    public $brand;

    // Sidebar filters
    public $subcategories = [];
    public $brands = [];
    public $options = [];
    public $selectedFeaturesByOption = [];
    /**
     * Filtros de características (features)
     * ⚠️ NO tiene watcher automático - solo se aplica al hacer clic en "Aplicar filtros"
     * Usa wire:model.defer en el blade para sincronizarse manualmente
     */
    public $selectedFeatures = [];

    /**
     * Filtros de marcas
     * ⚠️ NO tiene watcher automático - solo se aplica al hacer clic en "Aplicar filtros"
     * Usa wire:model.defer en el blade para sincronizarse manualmente
     */
    public $selectedBrands = [];
    public $featureCounts = [];

    // Products & pagination
    public $products = [];
    public $sortBy = 'recent';
    public $totalProducts = 0;
    public $currentPage = 1;
    public $totalPages = 1;
    public $perPage = 24;
    public $perPageStep = 24;
    public $hasMore = false;

    // Constants
    private const MIN_STOCK = 1;

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
        $this->currentPage = 1;
        $this->loadProducts();
    }

    public function clearFilters()
    {
        $this->reset(['selectedFeatures', 'selectedBrands']);
        $this->currentPage = 1;
        $this->loadProducts();
    }

    public function loadMore()
    {
        $this->nextPage();
    }

    public function updateSort($value)
    {
        $this->sortBy = $value;
        $this->currentPage = 1;
        $this->loadProducts();
    }

    public function goToPage(int $page): void
    {
        $this->currentPage = max(1, min($page, $this->totalPages));
        $this->loadProducts();
        $this->dispatch('scrollToTop');
    }

    public function nextPage(): void
    {
        $this->goToPage($this->currentPage + 1);
    }

    public function previousPage(): void
    {
        $this->goToPage($this->currentPage - 1);
    }

    public function getPageTitleProperty(): string
    {
        if (!empty($this->search)) {
            return 'Resultados de búsqueda';
        }

        if ($this->category) {
            return $this->category->name;
        }

        if ($this->brand) {
            return $this->brand->name;
        }

        if ($this->family) {
            return $this->family->name;
        }

        return 'Tienda';
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

        $baseProductQuery = $this->buildBaseProductQuery($categoryIds, $featuresByOption);

        // Calcular total y paginación
        $total = (clone $baseProductQuery)->distinct()->count('products.id');
        $this->totalProducts = $total;
        $this->totalPages = max(1, (int) ceil($total / $this->perPageStep));
        $this->currentPage = max(1, min($this->currentPage, $this->totalPages));
        $offset = ($this->currentPage - 1) * $this->perPageStep;

        // Obtener productos con ordenamiento
        $query = Product::with(['category', 'images', 'brand'])
            ->whereIn('products.id', $baseProductQuery)
            ->orderBy(...$this->getSortingOrder());

        $this->products = $query->skip($offset)
            ->take($this->perPageStep)
            ->get()
            ->map(function ($product) {
                $product->mainImage = $product->images
                    ->where('is_main', true)
                    ->first() ?? $product->images->sortBy('order')->first();

                return $product;
            });

        $this->hasMore = $this->currentPage < $this->totalPages;

        // Cargar datos de filtros
        $this->loadFacetCounts($featuresByOption, $categoryIds);
        $this->loadBrands();
        $this->filterOptionsByCounts();
    }

    /**
     * Construir la query base para productos con todos los filtros
     */
    protected function buildBaseProductQuery(array $categoryIds, array $featuresByOption)
    {
        $query = DB::table('products')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
            ->join('variants', 'variants.product_id', '=', 'products.id')
            ->where('products.status', true)
            ->where('variants.status', true)
            ->where('variants.stock', '>=', self::MIN_STOCK)
            ->select('products.id')
            ->distinct();

        // Filtro de categoría
        $this->applyCategoryFilterToQuery($query, $categoryIds);

        // Filtro de marcas
        if (!empty($this->selectedBrands)) {
            $query->whereIn('products.brand_id', $this->selectedBrands);
        }

        if ($this->brand_id) {
            $query->where('products.brand_id', $this->brand_id);
        }

        // Búsqueda
        if ($this->search) {
            $term = '%' . $this->search . '%';
            $query->where(function ($builder) use ($term) {
                $builder->where('products.name', 'like', $term)
                    ->orWhere('products.sku', 'like', $term)
                    ->orWhere('products.description', 'like', $term)
                    ->orWhere('brands.name', 'like', $term);
            });
        }

        // Filtro de features
        if (!empty($featuresByOption)) {
            $matchingVariantProductIds = $this->buildVariantMatchSubquery($featuresByOption)
                ->select('variants.product_id');

            $query->whereIn('products.id', $matchingVariantProductIds);
        }

        return $query;
    }

    /**
     * Aplicar filtro de categoría en query principal
     */
    protected function applyCategoryFilterToQuery($query, array $categoryIds): void
    {
        if (!empty($categoryIds)) {
            $query->whereIn('products.category_id', $categoryIds);
        } elseif ($this->family_id) {
            $query->where('categories.family_id', $this->family_id);
        }
    }

    /**
     * Obtener los parámetros de ordenamiento
     */
    protected function getSortingOrder(): array
    {
        return match ($this->sortBy) {
            'price-asc' => ['price', 'asc'],
            'price-desc' => ['price', 'desc'],
            'name-asc' => ['name', 'asc'],
            'name-desc' => ['name', 'desc'],
            default => ['created_at', 'desc'],
        };
    }

    protected function loadOptions(array $categoryIds): void
    {
        $this->options = Option::query()
            ->whereHas('features.variants', function ($query) use ($categoryIds) {
                $this->applyVariantAndProductFilters($query, $categoryIds);
            })
            ->with([
                'features' => function ($query) use ($categoryIds) {
                    $query->whereHas('variants', function ($query) use ($categoryIds) {
                        $this->applyVariantAndProductFilters($query, $categoryIds);
                    });
                }
            ])
            ->get();
    }

    /**
     * Aplicar filtros comunes a variantes y productos
     * - Stock disponible
     * - Status activo
     * - Categoría correcta
     */
    protected function applyVariantAndProductFilters($query, array $categoryIds): void
    {
        $query->where('variants.status', true)
            ->where('variants.stock', '>=', self::MIN_STOCK)
            ->whereHas('product', function ($query) use ($categoryIds) {
                $query->where('products.status', true)
                    ->whereHas('category', function ($query) use ($categoryIds) {
                        $this->applyCategoryFilter($query, $categoryIds);
                    });
            });
    }

    protected function buildVariantMatchSubquery(array $featuresByOption)
    {
        $optionCount = count($featuresByOption);

        return DB::table('variants')
            ->join('feature_variant', 'feature_variant.variant_id', '=', 'variants.id')
            ->join('features', 'features.id', '=', 'feature_variant.feature_id')
            ->where('variants.status', true)
            ->where('variants.stock', '>=', self::MIN_STOCK)
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
                ->where('variants.stock', '>=', self::MIN_STOCK)
                ->where('features.option_id', $optionId);

            $this->applyCategoryFilter($query, $categoryIds);

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

    /**
     * Aplicar filtro de categoría en base a IDs o Family
     */
    protected function applyCategoryFilter($query, array $categoryIds): void
    {
        if (!empty($categoryIds)) {
            $query->whereIn('categories.id', $categoryIds);
        } elseif ($this->family_id) {
            $query->where('categories.family_id', $this->family_id);
        }
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

        // Obtener categoría raíz y todas sus subcategorías (jerárquicamente)
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

        $this->brands = Brand::query()
            ->where('status', true)
            ->whereHas('products', function ($q) {
                $this->applyProductFiltersForBrands($q);
            })
            ->withCount(['products as products_count' => function ($q) {
                $this->applyProductFiltersForBrands($q);
            }])
            ->orderBy('name')
            ->get();
    }

    /**
     * Aplicar filtros de producto para la carga de marcas
     */
    private function applyProductFiltersForBrands($q): void
    {
        $q->where('status', true)
            ->whereHas('variants', fn($v) =>
                $v->where('status', true)->where('stock', '>=', self::MIN_STOCK)
            );

        if ($this->category_id) {
            $q->whereIn('category_id', $this->resolveCategoryIds());
        } elseif ($this->family_id) {
            $q->whereHas('category', fn($c) =>
                $c->where('family_id', $this->family_id)
            );
        }
    }

    public function render()
    {
        return view('livewire.site.filter');
    }
}
