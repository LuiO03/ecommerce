<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Tag;

class BlogController extends Controller
{
    public function index()
    {
        $tagSlug = request('tag');
        $search = request('search'); // 👈 definir primero

        $query = Post::published()
            ->visibleTo('public')
            ->with(['mainImage', 'tags']);

        // Filtro por tag
        if ($tagSlug) {
            $query->whereHas('tags', function ($q) use ($tagSlug) {
                $q->where('slug', $tagSlug);
            });
        }

        // Filtro por búsqueda
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Tag actual
        $currentTag = $tagSlug
            ? Tag::where('slug', $tagSlug)->first()
            : null;

        $posts = $query->latest('published_at')->paginate(9);

        // Featured SOLO si no hay filtros
        $featured = collect();

        if (!$tagSlug && !$search) {
            $featured = Post::published()
                ->visibleTo('public')
                ->with('mainImage')
                ->orderByDesc('views')
                ->limit(1)
                ->get();
        }

        $allTags = Tag::withCount('posts')
            ->having('posts_count', '>', 0)
            ->orderByDesc('posts_count')
            ->limit(12)
            ->get();

        $latestPosts = Post::published()
            ->visibleTo('public')
            ->latest('published_at')
            ->limit(5)
            ->get();

        return view('site.blog.index', compact(
            'posts',
            'featured',
            'allTags',
            'latestPosts',
            'currentTag'
        ));
    }

    public function show(Post $post)
    {
        abort_unless($post->status === 'published' && $post->visibility === 'public', 404);
        // traer autor


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
