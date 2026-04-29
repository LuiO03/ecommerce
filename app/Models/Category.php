<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    use HasFactory, Auditable;
    protected $fillable = [
        'name', 'slug', 'description', 'image', 'status',
        'family_id', 'parent_id', 'created_by', 'updated_by',
        'deleted_by',
    ];

    public function scopeForSelect($query)
    {
        return $query->select('id', 'name')->orderBy('name');
    }

    public function scopeForTable($query)
    {
        return $query->select('id', 'name', 'description', 'status',
        'family_id', 'parent_id', 'created_at')->orderByDesc('id');
    }

    public function getSubcategoriesFlat($categoryId)
    {
        return Category::withCount('products')
            ->where('parent_id', $categoryId)
            ->orderBy('name')
            ->get();
    }

    // relacion uno a muchos inversa
    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
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

    public static function generateUniqueSlug($name, $id = null)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while (self::where('slug', $slug)
            ->when($id, fn($q) => $q->where('id', '!=', $id))
            ->exists()
        ) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }

    public function getLevelAttribute(): int
    {
        $level = 1;
        $parent = $this->parent;

        while ($parent) {
            $level++;
            $parent = $parent->parent;
        }

        return $level;
    }

    public function getLocationAttribute(): string
    {
        $familyName = $this->family?->name
            ?? $this->parent?->family?->name
            ?? 'Sin familia';

        $parts = [$familyName];

        if ($this->parent) {
            $parts[] = $this->parent->name;
        }

        $parts[] = $this->name;

        return implode(' › ', $parts);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? Storage::url($this->image) : asset('images/no-image.png');
    }
}
