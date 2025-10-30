<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku', 'name', 'slug', 'description', 'price', 'discount', 'status', 'category_id'
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
}