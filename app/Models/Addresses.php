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
    ];

    protected function casts(): array
    {
        return [
            'type' => 'string',
            'address_line' => 'string',
            'district' => 'string',
            'reference' => 'string',
            'receiver_type' => 'string',
            'receiver_name' => 'string',
            'receiver_last_name' => 'string',
            'receiver_phone' => 'string',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
