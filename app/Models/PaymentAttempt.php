<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentAttempt extends Model
{
    protected $fillable = [
        'idempotency_key',
        'user_id',
        'payment_method',
        'purchase_number',
        'request_hash',
        'status',
        'order_id',
        'payment_record_id',
        'result_payload',
    ];

    protected $casts = [
        'result_payload' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function paymentRecord(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_record_id');
    }
}
