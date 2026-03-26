<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cart extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'items_count',
        'items_quantity',
        'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function getTotalPriceAttribute(): float
    {
        $this->loadMissing('items.product', 'items.variant.features.option');

        $subtotal = 0.0;

        foreach ($this->items as $item) {
            $product = $item->product;
            if (!$product) {
                continue;
            }

            $variant = $item->variant;

            $discountPercent = !is_null($product->discount)
                ? min(max((float) $product->discount, 0), 100)
                : 0.0;
            $hasDiscount = $discountPercent > 0;

            $basePrice = ($variant && $variant->price && $variant->price > 0)
                ? (float) $variant->price
                : (float) $product->price;

            $discounted = $hasDiscount
                ? max($basePrice * (1 - $discountPercent / 100), 0)
                : $basePrice;

            $lineTotal = $discounted * (int) $item->quantity;
            $subtotal += $lineTotal;
        }

        return $subtotal;
    }
}
