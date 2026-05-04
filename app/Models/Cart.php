<?php

namespace App\Models;

use App\Services\Cart\CartService;
use App\Services\Cart\CartSummary;
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

    /**
     * Obtiene el precio total del carrito (subtotal).
     *
     * @return float
     * @deprecated Use getSummary()->subtotal instead
     */
    public function getTotalPriceAttribute(): float
    {
        return app(CartService::class)->getCartSubtotal($this);
    }

    /**
     * Obtiene un resumen completo del carrito.
     *
     * @return CartSummary
     */
    public function getSummary(): CartSummary
    {
        return app(CartService::class)->getCartSummary($this);
    }
}
