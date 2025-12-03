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
        'description',
        'order',
    ];

    /**
     * Relación: cada imagen pertenece a un post
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
