<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostImage;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

use Maatwebsite\Excel\Facades\Excel;
use Spatie\LaravelPdf\Facades\Pdf;

use App\Exports\PostsExcelExport;
use App\Exports\PostsCsvExport;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::select([
                'id', 'title', 'status', 'visibility','views', 'allow_comments', 'created_by', 'created_at'
            ])
            ->withCount('images')
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.posts.index', compact('posts'));
    }

    public function exportExcel(Request $request)
    {
        $ids = $request->input('ids');
        $filename = 'posts_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new PostsExcelExport($ids), $filename);
    }

    public function exportCsv(Request $request)
    {
        $ids = $request->has('export_all') ? null : $request->input('ids');
        $filename = 'posts_' . now()->format('Y-m-d_H-i-s') . '.csv';
        return Excel::download(new PostsCsvExport($ids), $filename, \Maatwebsite\Excel\Excel::CSV);
    }

    public function exportPdf(Request $request)
    {
        if ($request->has('ids')) {
            $posts = Post::whereIn('id', $request->ids)->get();
        } elseif ($request->has('export_all')) {
            $posts = Post::all();
        } else {
            return back()->with('error', 'No se seleccionaron posts para exportar.');
        }

        if ($posts->isEmpty()) {
            return back()->with('error', 'No hay posts disponibles para exportar.');
        }

        $filename = 'posts_' . now()->format('Y-m-d_H-i-s') . '.pdf';
        return Pdf::view('admin.export.posts-pdf', compact('posts'))
            ->format('a4')
            ->name($filename)
            ->download();
    }

    public function create()
    {
        $tags = Tag::orderBy('name')->get();
        return view('admin.posts.create', compact('tags'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'           => 'required|string|max:255|unique:posts,title',
            'content'         => 'required|string|min:10',
            'status'          => 'required|boolean',
            'visibility'      => 'required|string|in:public,private',
            'allow_comments'  => 'sometimes|boolean',
            'published_at'    => 'nullable|date',
            'tags'            => 'nullable|array',
            'tags.*'          => 'exists:tags,id',
            'image'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'images'          => 'nullable|array',
            'images.*'        => 'image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        $title = ucfirst(mb_strtolower($request->title));
        $slug = Post::generateUniqueSlug($title);

        $post = Post::create([
            'title'          => $title,
            'slug'           => $slug,
            'content'        => $request->content,
            'status'         => (bool) $request->status,
            'visibility'     => $request->visibility,
            'allow_comments' => $request->has('allow_comments'),
            'published_at'   => null,
            'created_by'     => Auth::id(),
            'updated_by'     => Auth::id(),
        ]);

        if ($request->hasFile('image')) {
            $img = $request->file('image');
            $filename = $slug . '-main.' . $img->getClientOriginalExtension();
            $img->storeAs('posts', $filename, 'public');
            $post->image = "posts/$filename";
            $post->save();
        }

        if ($request->tags) {
            $post->tags()->sync($request->tags);
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $img) {
                $filename = $slug . '-' . time() . '-' . $index . '.' . $img->getClientOriginalExtension();
                $path = "posts/$filename";
                $img->storeAs('posts', $filename, 'public');

                $order = PostImage::where('post_id', $post->id)->max('order') + 1;
                PostImage::create([
                    'post_id'     => $post->id,
                    'path'        => $path,
                    'description' => null,
                    'order'       => $order
                ]);
            }
        }

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Post creado',
            'message' => "El post <strong>{$post->title}</strong> se ha creado correctamente.",
        ]);

        Session::flash('highlightRow', $post->id);

        return redirect()->route('admin.posts.index');
    }

    public function edit(Post $post)
    {
        $tags = Tag::orderBy('name')->get();
        $post->load('images');
        return view('admin.posts.edit', compact('post', 'tags'));
    }

    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title'           => 'required|string|max:255|unique:posts,title,' . $post->id,
            'content'         => 'required|string|min:10',
            'status'          => 'required|boolean',
            'visibility'      => 'required|string|in:public,private',
            'allow_comments'  => 'sometimes|boolean',
            'published_at'    => 'nullable|date',
            'tags'            => 'nullable|array',
            'tags.*'          => 'exists:tags,id',
            'image'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'images'          => 'nullable|array',
            'images.*'        => 'image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        $title = ucfirst(mb_strtolower($request->title));
        $slug = Post::generateUniqueSlug($title, $post->id);

        $post->update([
            'title'          => $title,
            'slug'           => $slug,
            'content'        => $request->content,
            'status'         => (bool) $request->status,
            'visibility'     => $request->visibility,
            'allow_comments' => $request->has('allow_comments'),
            'published_at'   => $request->published_at ?? $post->published_at,
            'updated_by'     => Auth::id(),
        ]);

        if ($request->hasFile('image')) {
            if ($post->image && Storage::disk('public')->exists($post->image)) {
                Storage::disk('public')->delete($post->image);
            }
            $img = $request->file('image');
            $filename = $slug . '-main.' . $img->getClientOriginalExtension();
            $img->storeAs('posts', $filename, 'public');
            $post->image = "posts/$filename";
            $post->save();
        }

        $post->tags()->sync($request->tags ?? []);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $img) {
                $filename = $slug . '-' . time() . '-' . $index . '.' . $img->getClientOriginalExtension();
                $path = "posts/$filename";
                $img->storeAs('posts', $filename, 'public');

                $order = PostImage::where('post_id', $post->id)->max('order') + 1;
                PostImage::create([
                    'post_id'     => $post->id,
                    'path'        => $path,
                    'description' => null,
                    'order'       => $order
                ]);
            }
        }

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Post actualizado',
            'message' => "El post <strong>{$post->title}</strong> se ha actualizado correctamente.",
        ]);

        Session::flash('highlightRow', $post->id);

        return redirect()->route('admin.posts.index');
    }

    public function destroy(Post $post)
    {
        foreach ($post->images as $img) {
            if (Storage::disk('public')->exists($img->path)) {
                Storage::disk('public')->delete($img->path);
            }
            $img->delete();
        }

        if ($post->image && Storage::disk('public')->exists($post->image)) {
            Storage::disk('public')->delete($post->image);
        }

        $name = $post->title;
        $post->deleted_by = Auth::id();
        $post->save();
        $post->delete();

        Session::flash('info', [
            'type' => 'danger',
            'header' => 'Registro eliminado',
            'title' => 'Post eliminado',
            'message' => "El post <strong>{$name}</strong> ha sido eliminado.",
        ]);

        return redirect()->route('admin.posts.index');
    }

    public function show($slug)
    {
        $post = Post::with([
            'creator:id,name,last_name',
            'updater:id,name,last_name',
            'deleter:id,name,last_name',
            'reviewer:id,name,last_name',
            'tags:id,name',
            'images'
        ])->where('slug', $slug)->firstOrFail();

        return response()->json([
            'id'               => '#' . $post->id,
            'slug'             => $post->slug,
            'title'            => $post->title,
            'content'          => $post->content,
            'status'           => $post->status,
            'visibility'       => $post->visibility,
            'allow_comments'   => $post->allow_comments,
            'published_at'     => $post->published_at?->format('d/m/Y H:i'),
            'image'            => $post->image,
            'tags'             => $post->tags->pluck('name'),
            'images'           => $post->images,
            'created_by_name'  => $post->creator ? $post->creator->name . ' ' . $post->creator->last_name : 'Sistema',
            'updated_by_name'  => $post->updater ? $post->updater->name . ' ' . $post->updater->last_name : '—',
            'deleted_by_name'  => $post->deleter ? $post->deleter->name . ' ' . $post->deleter->last_name : '—',
            'reviewed_by_name' => $post->reviewer ? $post->reviewer->name . ' ' . $post->reviewer->last_name : '—',
            'created_at'       => $post->created_at->format('d/m/Y H:i'),
            'updated_at'       => $post->updated_at->format('d/m/Y H:i'),
            'deleted_at'       => $post->deleted_at?->format('d/m/Y H:i'),
            'reviewed_at'      => $post->reviewed_at?->format('d/m/Y H:i'),
        ]);
    }
}
