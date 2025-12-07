<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'posts';

    /**
     * Campos asignables en masa.
     */
    protected $fillable = [
        'title',
        'content',
        'image',
        'status',
        'slug',
        'views',
        'published_at',
        'visibility',
        'allow_comments',
        'reviewed_by',
        'reviewed_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * Casts automáticos.
     */
    protected $casts = [
        'published_at' => 'datetime',
        'reviewed_at'  => 'datetime',
        'allow_comments' => 'boolean',
        'views' => 'integer',
    ];

    /**
     * -----------------------------
     * Relaciones
     * -----------------------------
     */

    // Un post tiene muchas imágenes
    public function images()
    {
        return $this->hasMany(PostImage::class);
    }

    // Muchos a muchos con tags
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tag');
    }

    // Auditoría
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

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * -----------------------------
     * Mutators (generar slug automático)
     * -----------------------------
     */
    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value;

        if (! isset($this->attributes['slug']) || empty($this->attributes['slug'])) {
            $this->attributes['slug'] = Str::slug($value);
        }
    }

    /**
     * -----------------------------
     * Scopes (para filtros)
     * -----------------------------
     */

    // Posts publicados
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    // Borradores
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    // Filtro por visibilidad
    public function scopeVisibleTo($query, $type)
    {
        return $query->where('visibility', $type);
    }

    // Búsqueda rápida
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'LIKE', "%$term%")
              ->orWhere('content', 'LIKE', "%$term%");
        });
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
}
