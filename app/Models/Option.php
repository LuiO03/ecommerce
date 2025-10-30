<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Option extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'slug', 'description', 'created_by', 'updated_by'];
    //relacion muchos a muchos con productos
    public function products(){
        return $this->belongsToMany(Product::class)->withPivot('value')->withTimestamps();
    }
    //relacion uno a muchos con features
    public function features(){
        return $this->hasMany(Feature::class);
    }
}
