<?php

namespace App\Livewire\Site;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;
use App\Models\Family;

class ProductList extends Component
{
    public $limit = 8;
    public $categoryId = null;
    public $excludeId = null;
    public $title = 'Últimos Productos';
    public $subtitle = 'Descubre nuestras incorporaciones más recientes';
    public $orderBy = 'latest';
    public $strict = true;
    public $scope = 'strict_category';
    public $onSale = false;
    public $brandId = null;
    // opciones:
    // strict_category
    // category_with_children
    // family_fallback (tu actual)

    public function mount(
        $limit = 8,
        $categoryId = null,
        $excludeId = null,
        $title = null,
        $subtitle = null,
        $orderBy = 'latest',
        $strict = true,
        $scope = 'strict_category',
        $onSale = false,
        $brandId = null
    ) {
        $this->limit = $limit;
        $this->categoryId = $categoryId;
        $this->excludeId = $excludeId;
        $this->orderBy = $orderBy;
        $this->strict = $strict;
        $this->scope = $this->normalizeScope($scope);
        $this->onSale = filter_var($onSale, FILTER_VALIDATE_BOOLEAN);
        $this->brandId = $brandId;

        if ($title) $this->title = $title;
        if ($subtitle) $this->subtitle = $subtitle;
    }

    /**
     * Query base optimizada
     */
    private function baseQuery()
    {
        return Product::with([
                'brand',
                'category',
                'images' => fn($q) =>
                    $q->orderBy('is_main', 'desc')
                    ->orderBy('order')
            ])
            ->where('status', true)

            ->when($this->brandId, fn($q) =>
                $q->where('brand_id', $this->brandId)
            )

            ->when($this->onSale, fn($q) =>
                $q->whereNotNull('discount')
                ->when($this->onSale, fn($q) =>
                    $q->where('discount', '>', 0)
                )
            )

            ->whereHas('variants', fn($q) =>
                $q->where('status', true)
                ->where('stock', '>', 0)
            )

            ->when($this->excludeId, fn($q) =>
                $q->where('id', '!=', $this->excludeId)
            );
    }

    private function normalizeScope($scope)
    {
        return in_array($scope, [
            'strict_category', // solo categoría exacta
            'category_with_children', // categoría + hijos
            'family_fallback' // Similares
        ]) ? $scope : 'strict_category';
    }

    /**
     * Construye prioridades de categorías
     */
    private function resolveCategoryPriorityIds()
    {
        if (!$this->categoryId) return [];

        $category = Category::with('children')->find($this->categoryId);
        if (!$category) return [];

        $priority = [];

        // SIEMPRE: misma categoría
        $priority[1] = [$category->id];

        // SOLO si quieres incluir hijos
        if (in_array($this->scope, ['category_with_children', 'family_fallback'])) {
            $priority[2] = $category->children->pluck('id')->toArray();
        }

        // SOLO si quieres fallback a familia
        if ($this->scope === 'family_fallback' && $category->family_id) {
            $family = Family::with('categories')->find($category->family_id);
            if ($family) {
                $priority[3] = $family->categories
                ->pluck('id')
                ->diff([$category->id]) // 👈 evita duplicar
                ->values()
                ->toArray();
            }
        }

        return $priority;
    }

    /**
     * Aplica orden dinámico
     */
    private function applyOrder($query)
    {
        return match ($this->orderBy) {
            'oldest' => $query->oldest('created_at'),
            'cheap' => $query->orderBy('price', 'asc'),
            'expensive' => $query->orderBy('price', 'desc'),
            default => $query->latest('created_at'),
        };
    }

    /**
     * Query principal optimizada (UNA sola query)
     */
    public function getProducts()
    {
        $query = $this->baseQuery();

        // SIN categoría
        if (!$this->categoryId) {
            $products = $this->applyOrder($query)
                ->take($this->limit)
                ->get();
            // Asignar mainImage
            $products = $products->map(function ($p) {
                $p->mainImage = $p->images->where('is_main', true)->first() ?? $p->images->sortBy('order')->first();
                return $p;
            });
            return ($this->strict && $products->count() < $this->limit)
                ? collect()
                : $products;
        }

        $priorityGroups = $this->resolveCategoryPriorityIds();

        $allIds = collect($priorityGroups)
            ->flatten()
            ->unique()
            ->values();

        if ($allIds->isEmpty()) {
            return collect();
        }

        // CASE SQL para prioridad
        $caseSql = "CASE";

        foreach ($priorityGroups as $level => $ids) {
            if (!empty($ids)) {
                $idsList = implode(',', array_map('intval', $ids));
                $caseSql .= " WHEN category_id IN ($idsList) THEN $level";
            }
        }

        $caseSql .= " ELSE 999 END";

        $products = $query
            ->whereIn('category_id', $allIds)
            ->orderByRaw($caseSql) // prioridad
            ->when($this->orderBy === 'cheap', fn($q) => $q->orderBy('price', 'asc'))
            ->when($this->orderBy === 'expensive', fn($q) => $q->orderBy('price', 'desc'))
            ->when($this->orderBy === 'oldest', fn($q) => $q->oldest('created_at'))
            ->when($this->orderBy === 'latest', fn($q) => $q->latest('created_at'))
            ->take($this->limit)
            ->get();
        // Asignar mainImage
        $products = $products->map(function ($p) {
            $p->mainImage = $p->images->where('is_main', true)->first() ?? $p->images->sortBy('order')->first();
            return $p;
        });
        // STRICT MODE
        if ($this->strict && $products->count() < $this->limit) {
            return collect();
        }
        return $products;
    }

    public function render()
    {
        return view('livewire.site.product-list', [
            'products' => $this->getProducts()
        ]);
    }
}
