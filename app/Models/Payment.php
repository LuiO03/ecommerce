<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'provider',
        'transaction_id',
        'amount',
        'fee',
        'net_amount',
        'status',
        'paid_at',
        'response',
    ];

    protected $casts = [
        'amount' => 'float',
        'fee' => 'float',
        'net_amount' => 'float',
        'paid_at' => 'datetime',
        'response' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
