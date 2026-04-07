<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Post;

class BlogController extends Controller
{
    public function index()
    {
        $posts = Post::published()
            ->visibleTo('public')
            ->with(['mainImage', 'tags'])
            ->orderByDesc('published_at')
            ->paginate(9);

        $featured = Post::published()
            ->visibleTo('public')
            ->with('mainImage')
            ->orderByDesc('views')
            ->limit(3)
            ->get();

        return view('site.blog.index', [
            'posts' => $posts,
            'featured' => $featured,
        ]);
    }

    public function show(Post $post)
    {
        abort_unless($post->status === 'published' && $post->visibility === 'public', 404);

        $post->increment('views');

        $related = Post::published()
            ->visibleTo('public')
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('site.blog.show', [
            'post' => $post,
            'related' => $related,
        ]);
    }
}
