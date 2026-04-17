<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Order extends Model
{
    protected $fillable = [
        'user_id',
        'address_id',
        'pickup_store_code',
        'pdf_path',
        'order_number',
        'total',
        'subtotal',
        'shipping_cost',
        'status',
        'shipping_address',
        'shipping_city',
        'shipping_phone',
        'payment_method',
        'payment_id',
        'payment_status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Addresses::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
