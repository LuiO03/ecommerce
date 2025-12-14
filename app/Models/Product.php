<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'slug',
        'description',
        'price',
        'discount',
        'status',
        'category_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // 🔹 Relación con categoría
    public function category()
    {
        return $this->belongsTo(Category::class);
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
    public function options(){
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

    public static function generateUniqueSlug(string $name, ?int $id = null): string
    {
        $slug = \Illuminate\Support\Str::slug($name);
        $original = $slug;
        $counter = 1;

        while (self::where('slug', $slug)
            ->when($id, fn ($query) => $query->where('id', '!=', $id))
            ->exists()) {
            $slug = $original . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
