<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PostImage extends Model
{
    use HasFactory;

    protected $table = 'post_images';

    protected $fillable = [
        'post_id',
        'path',
        'alt',
        'description',
        'is_main',
        'order',
    ];

    protected $casts = [
        'is_main' => 'boolean',
        'order' => 'integer',
    ];

    protected $appends = ['url'];

    protected $visible = ['id', 'post_id', 'path', 'alt', 'description', 'is_main', 'order', 'url', 'created_at', 'updated_at'];

    /**
     * Relación: cada imagen pertenece a un post
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Accessor: genera URL pública desde el campo path
     */
    public function getUrlAttribute()
    {
        if (!$this->path) {
            return null;
        }
        return asset('storage/' . $this->path);
    }
}
