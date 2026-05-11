<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Brand extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'image',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    public function scopeForSelect($query)
    {
        return $query->select('id', 'name')->orderBy('name');
    }

    public function scopeForTable($query)
    {
        return $query
            ->select('id', 'name', 'description', 'status', 'created_at')
            ->orderByDesc('id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
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
            ->when($id, fn ($q) => $q->where('id', '!=', $id))
            ->exists()
        ) {
            $slug = $originalSlug.'-'.$count;
            $count++;
        }

        return $slug;
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return asset('images/no-image.png');
        }

        $path = 'storage/' . $this->image;
        if (file_exists(public_path($path))) {
            return asset($path);
        }

        return asset('images/no-image.png');
    }
}
