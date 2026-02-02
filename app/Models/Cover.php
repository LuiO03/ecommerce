<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Cover extends Model
{
    use HasFactory, Auditable;

    public $timestamps = true;

    protected $fillable = [
        'slug',
        'image_path',
        'title',
        'description',
        'start_at',
        'end_at',
        'position',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'status' => 'boolean',
    ];

    public function scopeForSelect($query)
    {
        return $query->select('id', 'title', 'slug')->orderBy('position');
    }

    public function scopeForTable($query)
    {
        return $query->select('id', 'slug', 'image_path', 'title', 'start_at', 'end_at', 'position', 'status', 'created_at')
            ->orderBy('position')
            ->orderByDesc('id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
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

    public static function generateUniqueSlug($title, $id = null)
    {
        $slug = Str::slug($title);
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
        if ($this->image_path && Storage::disk('public')->exists($this->image_path)) {
            return Storage::disk('public')->url($this->image_path);
        }
        return asset('images/default-cover.jpg');
    }
}
