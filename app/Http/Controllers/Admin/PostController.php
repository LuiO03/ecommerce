<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PostsCsvExport;
use App\Exports\PostsExcelExport;
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

class PostController extends Controller
{
    public function create()
    {
        $tags = Tag::orderBy('name')->get();

        return view('admin.posts.create', compact('tags'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255|unique:posts,title',
            'content' => 'required|string|min:10',
            'status' => 'required|string|in:draft,pending,published,rejected',
            'visibility' => 'required|string|in:public,private,registered',
            'allow_comments' => 'sometimes|boolean',
            'published_at' => 'nullable|date',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $title = ucfirst(mb_strtolower($request->title));
        $slug = Post::generateUniqueSlug($title);

        $post = Post::create([
            'title' => $title,
            'slug' => $slug,
            'content' => $request->content,
            'status' => $request->status,
            'visibility' => $request->visibility,
            'allow_comments' => $request->boolean('allow_comments'),
            'published_at' => null,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        if ($request->hasFile('image')) {
            $img = $request->file('image');
            $filename = $slug.'-main.'.$img->getClientOriginalExtension();
            $img->storeAs('posts', $filename, 'public');
            PostImage::where('post_id', $post->id)->update(['is_main' => false]);

            PostImage::create([
                'post_id' => $post->id,
                'path' => "posts/$filename",
                'alt' => $title,
                'description' => null,
                'is_main' => true,
                'order' => 0,
            ]);
        }

        if ($request->tags) {
            $post->tags()->sync($request->tags);
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $img) {
                $filename = $slug.'-'.time().'-'.$index.'.'.$img->getClientOriginalExtension();
                $path = "posts/$filename";
                $img->storeAs('posts', $filename, 'public');

                $order = (PostImage::where('post_id', $post->id)->max('order') ?? -1) + 1;
                PostImage::create([
                    'post_id' => $post->id,
                    'path' => $path,
                    'alt' => $post->title,
                    'description' => null,
                    'is_main' => false,
                    'order' => $order,
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

    public function index()
    {
        $posts = Post::select([
            'id', 'title', 'slug', 'status', 'visibility', 'views', 'allow_comments', 'created_by', 'created_at',
        ])
            ->withCount('images')
            ->with(['mainImage:id,post_id,path'])
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.posts.index', compact('posts'));
    }

    public function edit(Post $post)
    {
        $tags = Tag::orderBy('name')->get();
        $post->load(['images' => fn ($query) => $query->orderByDesc('is_main')->orderBy('order')]);

        return view('admin.posts.edit', compact('post', 'tags'));
    }

    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title' => 'required|string|max:255|unique:posts,title,'.$post->id,
            'content' => 'required|string|min:10',
            'status' => 'required|string|in:draft,pending,published,rejected',
            'visibility' => 'required|string|in:public,private,registered',
            'allow_comments' => 'sometimes|boolean',
            'published_at' => 'nullable|date',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_image' => 'sometimes|boolean',
        ]);

        $title = ucfirst(mb_strtolower($request->title));
        $slug = Post::generateUniqueSlug($title, $post->id);

        $post->update([
            'title' => $title,
            'slug' => $slug,
            'content' => $request->content,
            'status' => $request->status,
            'visibility' => $request->visibility,
            'allow_comments' => $request->boolean('allow_comments'),
            'published_at' => $request->published_at ?? $post->published_at,
            'updated_by' => Auth::id(),
        ]);

        $mainImage = $post->images()->where('is_main', true)->first();

        if ($request->input('remove_image') === '1' && $mainImage) {
            if (Storage::disk('public')->exists($mainImage->path)) {
                Storage::disk('public')->delete($mainImage->path);
            }

            $mainImage->delete();
            $mainImage = null;
        }

        if ($request->hasFile('image')) {
            $img = $request->file('image');
            $filename = $slug.'-main.'.$img->getClientOriginalExtension();
            $path = "posts/$filename";
            $img->storeAs('posts', $filename, 'public');

            PostImage::where('post_id', $post->id)
                ->when($mainImage, fn ($query) => $query->where('id', '!=', $mainImage->id))
                ->update(['is_main' => false]);

            if ($mainImage) {
                if (Storage::disk('public')->exists($mainImage->path)) {
                    Storage::disk('public')->delete($mainImage->path);
                }

                $mainImage->update([
                    'path' => $path,
                    'alt' => $title,
                    'is_main' => true,
                    'order' => 0,
                ]);
            } else {
                PostImage::create([
                    'post_id' => $post->id,
                    'path' => $path,
                    'alt' => $title,
                    'description' => null,
                    'is_main' => true,
                    'order' => 0,
                ]);
            }
        }

        $post->tags()->sync($request->tags ?? []);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $img) {
                $filename = $slug.'-'.time().'-'.$index.'.'.$img->getClientOriginalExtension();
                $path = "posts/$filename";
                $img->storeAs('posts', $filename, 'public');

                $order = (PostImage::where('post_id', $post->id)->max('order') ?? -1) + 1;
                PostImage::create([
                    'post_id' => $post->id,
                    'path' => $path,
                    'alt' => $post->title,
                    'description' => null,
                    'is_main' => false,
                    'order' => $order,
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

    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'posts' => 'sometimes|array|min:1',
            'posts.*' => 'exists:posts,id',
            'ids' => 'sometimes|array|min:1',
            'ids.*' => 'exists:posts,id'
        ]);

        $postIds = $request->posts ?? $request->ids;

        if (empty($postIds)) {
            Session::flash('info', [
                'type' => 'danger',
                'header' => 'Error',
                'title' => 'Sin selección',
                'message' => 'No se seleccionaron posts para eliminar.',
            ]);
            return redirect()->route('admin.posts.index');
        }

        $posts = Post::with('images')->whereIn('id', $postIds)->get();

        if ($posts->isEmpty()) {
            Session::flash('info', [
                'type' => 'danger',
                'header' => 'Error',
                'title' => 'No encontrados',
                'message' => 'Los posts seleccionados no existen.',
            ]);
            return redirect()->route('admin.posts.index');
        }

        // ===============================
        //   🔥 Eliminación profesional
        // ===============================

        // Guarda títulos para el mensaje final
        $titles = [];

        foreach ($posts as $post) {
            $titles[] = $post->title;

            // 🖼️ Eliminar imágenes adicionales
            foreach ($post->images as $img) {
                if (Storage::disk('public')->exists($img->path)) {
                    Storage::disk('public')->delete($img->path);
                }
                $img->delete();
            }

            // Registrar quién eliminó
            $post->deleted_by = Auth::id();
            $post->save();

            // Eliminar registro (soft delete)
            $post->delete();
        }

        $count = count($titles);

        Session::flash('info', [
            'type' => 'danger',
            'header' => 'Registros eliminados',
            'title' => "Se eliminaron <strong>{$count}</strong> " . ($count === 1 ? 'post' : 'posts'),
            'message' => "Lista de posts eliminados:",
            'list' => $titles,
        ]);

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
            'reviewer:id,name,last_name',
            'tags:id,name',
            'images' => fn ($query) => $query->orderByDesc('is_main')->orderBy('order'),
        ])->where('slug', $slug)->firstOrFail();


        return response()->json([
            'id' => '#'.$post->id,
            'slug' => $post->slug,
            'title' => $post->title,
            'content' => $post->content,
            'status' => $post->status,
            'visibility' => $post->visibility,
            'views' => $post->views,
            'allow_comments' => $post->allow_comments,
            'published_at' => $post->published_at?->format('d/m/Y H:i') ?? '—',
            'image' => $post->main_image_path,
            'main_image' => $post->main_image_path,
            'tags' => $post->tags->pluck('name'),
            'images' => $post->images,
            'created_by_name' => $post->creator ? $post->creator->name.' '.$post->creator->last_name : 'Sistema',
            'updated_by_name' => $post->updater ? $post->updater->name.' '.$post->updater->last_name : '—',
            'reviewed_by_name' => $post->reviewer ? $post->reviewer->name.' '.$post->reviewer->last_name : 'Sin revisión',
            'created_at' => $post->created_at->format('d/m/Y H:i') ?? '—',
            'updated_at' => $post->updated_at->format('d/m/Y H:i') ?? '—',
            'reviewed_at' => $post->reviewed_at?->format('d/m/Y H:i') ?? '—',
        ]);
    }

    public function exportExcel(Request $request)
    {
        $ids = $request->input('ids');
        $filename = 'posts_'.now()->format('Y-m-d_H-i-s').'.xlsx';

        return Excel::download(new PostsExcelExport($ids), $filename);
    }

    public function exportCsv(Request $request)
    {
        $ids = $request->has('export_all') ? null : $request->input('ids');
        $filename = 'posts_'.now()->format('Y-m-d_H-i-s').'.csv';

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

        $filename = 'posts_'.now()->format('Y-m-d_H-i-s').'.pdf';

        return Pdf::view('admin.export.posts-pdf', compact('posts'))
            ->format('a4')
            ->name($filename)
            ->download();
    }

    public function approve(Post $post)
    {
        if ($post->status !== 'pending') {
            return back()->with('toast', [
                'type' => 'warning',
                'title' => 'No permitido',
                'message' => "El post ya fue revisado previamente.",
            ]);
        }

        $post->update([
            'status' => 'published',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Post aprobado',
            'message' => "El post <strong>{$post->title}</strong> ha sido aprobado.",
        ]);
        Session::flash('highlightRow', $post->id);

        return back();
    }

    public function reject(Post $post)
    {
        if ($post->status !== 'pending') {
            return back()->with('toast', [
                'type' => 'warning',
                'title' => 'No permitido',
                'message' => "El post ya fue revisado previamente.",
            ]);
        }

        $post->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        Session::flash('toast', [
            'type' => 'danger',
            'title' => 'Post rechazado',
            'message' => "El post <strong>{$post->title}</strong> ha sido rechazado.",
        ]);
        Session::flash('highlightRow', $post->id);

        return back();
    }
}
