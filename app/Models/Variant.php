<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'price',
        'stock',
        'status',
        'image_path',
        'product_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // 🔹 Relación inversa con producto
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    // relacion muchos a muchos con features
    public function features(){
        return $this->belongsToMany(Feature::class)->withTimestamps();
    }
    // 🔹 Relación con imágenes específicas
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    protected static function booted()
    {
        static::created(function ($variant) {
            $variant->sku = $variant->product->sku . '-' . str_pad($variant->id, 5, '0', STR_PAD_LEFT);
            $variant->save();
        });
    }
}

