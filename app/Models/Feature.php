<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Feature extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'slug', 'description', 'option_id', 'created_by', 'updated_by'];
    //relacion muchos a uno con option/ uno a muchos inversa
    public function option(){
        return $this->belongsTo(Option::class);
    }

    // relacion muchos a muchos con variantes
    public function variants(){
        return $this->belongsToMany(Variant::class)->withTimestamps();
    }
}
