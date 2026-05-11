<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'slug',
        'description',
        'price',
        'discount',
        'min_stock',
        'status',
        'category_id',
        'brand_id',
        'created_by',
        'updated_by',
        'deleted_by',
        'featured',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'discount' => 'integer',
            'min_stock' => 'integer',
            'status' => 'boolean',
            'featured' => 'boolean',
        ];
    }

    /**
     * Devuelve el stock mínimo para alerta (campo o config)
     */
    public function getMinStock(): int
    {
        return $this->min_stock ?? config('products.min_stock', 10);
    }

    // 🔹 Relación con categoría
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    // 🔹 Relación con variantes
    public function variants()
    {
        return $this->hasMany(Variant::class);
    }

    // 🔹 Relación con imágenes
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    // relacion muchos a muchos con opciones
    public function options()
    {
        return $this->belongsToMany(Option::class)->withPivot('value')->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function getVariantOptionsAttribute()
    {
        $variants = $this->variants()
            ->where('status', true)
            ->where('stock', '>', 0)
            ->with('features.option')
            ->get();

        return $variants
            ->flatMap(fn ($variant) => $variant->features)
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
                        ->map(fn ($feature) => (object) [
                            'id' => $feature->id,
                            'value' => $feature->value,
                            'description' => $feature->description,
                        ])
                        ->all(),
                ];
            })
            ->values();
    }

    public static function generateUniqueSlug(string $name, ?int $id = null): string
    {
        $slug = \Illuminate\Support\Str::slug($name);
        $original = $slug;
        $counter = 1;

        while (self::where('slug', $slug)
            ->when($id, fn ($query) => $query->where('id', '!=', $id))
            ->exists()) {
            $slug = $original.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
