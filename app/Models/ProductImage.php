<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'variant_id', 'path', 'alt', 'is_main', 'order'];

    protected function casts(): array
    {
        return [
            'is_main' => 'boolean',
            'order' => 'integer',
        ];
    }

    // 🔹 Relación inversa con producto
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // 🔹 Relación inversa con variante
    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }
}
