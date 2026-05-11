<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClaimMessage extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'claim_type',
        'claim_detail',
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
