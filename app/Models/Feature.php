<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    use HasFactory;

    protected $fillable = [
        'value',
        'description',
        'option_id',
    ];

    //relacion muchos a uno con option/ uno a muchos inversa
    public function option()
    {
        return $this->belongsTo(Option::class);
    }

    // relacion muchos a muchos con variantes
    public function variants()
    {
        return $this->belongsToMany(Variant::class)->withTimestamps();
    }
}
