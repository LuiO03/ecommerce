<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Family extends Model
{
    use HasFactory;
    public $timestamps = true;
    // asignacion masiva, $fillable define los campos que se pueden asignar masivamente
    protected $fillable = ['name', 'slug', 'description', 'image', 'status', 'created_by', 'updated_by'];

    public function scopeForSelect($query)
    {
        return $query->select('id', 'name')->orderBy('name');
    }

    public function scopeForTable($query)
    {
        return $query->select('id', 'name', 'description', 'status', 'created_at')->orderByDesc('id');
    }


    //relacion uno a muchos
    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
