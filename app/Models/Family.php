<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Family extends Model
{
    use HasFactory, Auditable;
    public $timestamps = true;
    // asignacion masiva, $fillable define los campos que se pueden asignar masivamente
    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'image',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

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

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public static function generateUniqueSlug($name, $id = null)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while (self::where('slug', $slug)
            ->when($id, fn($q) => $q->where('id', '!=', $id))
            ->exists()
        ) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? Storage::url($this->image) : asset('images/no-image.png');
    }
}
