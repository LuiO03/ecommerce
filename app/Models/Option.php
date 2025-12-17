<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Option extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'description',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public const TYPE_SIZE = 1;
    public const TYPE_COLOR = 2;
    public const TYPE_GENDER = 3;

    public static function typeLabels(): array
    {
        return [
            self::TYPE_SIZE => 'Talla',
            self::TYPE_COLOR => 'Color',
            self::TYPE_GENDER => 'Sexo',
        ];
    }

    public static function generateUniqueSlug(string $name, ?int $id = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $counter = 1;

        while (self::where('slug', $slug)
            ->when($id, fn($query) => $query->where('id', '!=', $id))
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

    //relacion muchos a muchos con productos
    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('value')->withTimestamps();
    }

    //relacion uno a muchos con features
    public function features()
    {
        return $this->hasMany(Feature::class)->orderBy('id');
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
}
