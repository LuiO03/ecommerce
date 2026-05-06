<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'name',
        'email',
        'topic',
        'order_number',
        'message',
        'idempotency_key',
        'ip_address',
        'user_agent',
        'submitted_at',
        'response',
        'status',
        'read_at',
        'replied_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'read_at' => 'datetime',
        'replied_at' => 'datetime',
    ];
}
