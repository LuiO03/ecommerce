<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Addresses extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'address_line',
        'district',
        'reference',
        'receiver_type',
        'receiver_name',
        'receiver_last_name',
        'receiver_phone',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
