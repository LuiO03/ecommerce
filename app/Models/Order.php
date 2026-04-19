<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Order extends Model
{
    protected $fillable = [
        'user_id',
        'address_id',
        'pickup_store_code',
        'delivery_type',
        'pdf_path',
        'order_number',
        'total',
        'subtotal',
        'shipping_cost',
        'status',
        'shipping_address',
        'shipping_city',
        'shipping_phone',
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

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function getPaymentMethodAttribute($value): ?string
    {
        if (!empty($value)) {
            return $value;
        }

        return $this->latestPayment?->provider;
    }

    public function getPaymentStatusAttribute($value): ?string
    {
        if (!empty($value)) {
            return $value;
        }

        return $this->latestPayment?->status;
    }

    public function getPaymentIdAttribute($value): ?string
    {
        if (!empty($value)) {
            return $value;
        }

        return $this->latestPayment?->transaction_id;
    }
}
