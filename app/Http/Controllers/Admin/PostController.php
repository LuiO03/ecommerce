<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PostsCsvExport;
use App\Exports\PostsExcelExport;
use App\Http\Controllers\Controller;
use App\Models\Audit;
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
    public function __construct()
    {
        $this->middleware('can:posts.index')->only(['index']);
        $this->middleware('can:posts.create')->only(['create', 'store']);
        $this->middleware('can:posts.edit')->only(['edit', 'update']);
        $this->middleware('can:posts.delete')->only(['destroy', 'destroyMultiple']);
        $this->middleware('can:posts.export')->only(['exportExcel', 'exportPdf', 'exportCsv']);
        $this->middleware('can:posts.review')->only(['approve', 'reject']);
    }

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
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'primary_image' => 'nullable|string',
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

        $newImages = $this->handleAdditionalImagesUpload($request, $post, $slug);
        $this->finalizePostImages($post, $request->input('primary_image'), $newImages);

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
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_image' => 'sometimes|boolean',
            'primary_image' => 'nullable|string',
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

        // Eliminar imágenes adicionales marcadas desde la galería
        if ($request->filled('deletedImages')) {
            $ids = json_decode($request->input('deletedImages'), true) ?? [];
            if (is_array($ids) && !empty($ids)) {
                $images = PostImage::where('post_id', $post->id)
                    ->whereIn('id', $ids)
                    ->get();

                foreach ($images as $image) {
                    if (Storage::disk('public')->exists($image->path)) {
                        Storage::disk('public')->delete($image->path);
                    }
                    $image->delete();
                }
            }
        }

        $newImages = $this->handleAdditionalImagesUpload($request, $post, $slug);
        $this->finalizePostImages($post, $request->input('primary_image'), $newImages);

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Post actualizado',
            'message' => "El post <strong>{$post->title}</strong> se ha actualizado correctamente.",
        ]);

        Session::flash('highlightRow', $post->id);

        return redirect()->route('admin.posts.index');
    }

    /**
     * Maneja la subida de imágenes adicionales (galería) y devuelve un mapa index => PostImage
     * para poder resolver referencias "new:X" provenientes del front.
     */
    protected function handleAdditionalImagesUpload(Request $request, Post $post, string $slug): array
    {
        if (! $request->hasFile('images')) {
            return [];
        }

        $created = [];
        $currentMaxOrder = PostImage::where('post_id', $post->id)->max('order');
        $orderCounter = is_numeric($currentMaxOrder) ? (int) $currentMaxOrder : -1;

        foreach ($request->file('images') as $index => $img) {
            if (! $img || ! $img->isValid()) {
                continue;
            }

            $filename = $slug.'-'.time().'-'.$index.'.'.$img->getClientOriginalExtension();
            $path = "posts/$filename";
            $img->storeAs('posts', $filename, 'public');

            $order = ++$orderCounter;

            $image = PostImage::create([
                'post_id' => $post->id,
                'path' => $path,
                'alt' => $post->title,
                'description' => null,
                'is_main' => false,
                'order' => $order,
            ]);

            $created[(int) $index] = $image;
        }

        return $created;
    }

    /**
     * Ajusta la imagen principal del post y normaliza el orden de las imágenes
     * usando la referencia "primary_image" enviada desde la galería.
     */
    protected function finalizePostImages(Post $post, ?string $primaryReference, array $newImages): void
    {
        $images = $post->images()->orderBy('order')->get();

        if ($images->isEmpty()) {
            return;
        }

        $mainImage = null;

        if ($primaryReference) {
            if (str_starts_with($primaryReference, 'existing:')) {
                $id = (int) substr($primaryReference, 9);
                $mainImage = $images->firstWhere('id', $id);
            } elseif (str_starts_with($primaryReference, 'new:')) {
                $index = (int) substr($primaryReference, 4);
                $mainImage = $newImages[$index] ?? null;
            }
        }

        if (! $mainImage) {
            $mainImage = $images->firstWhere('is_main', true) ?: $images->first();
        }

        if (! $mainImage) {
            return;
        }

        $post->images()->where('id', '!=', $mainImage->id)->update(['is_main' => false]);

        $mainImage->is_main = true;
        $mainImage->order = 0;
        $mainImage->save();

        $order = 1;
        foreach ($images->where('id', '!=', $mainImage->id) as $image) {
            if ($image->order !== $order) {
                $image->order = $order;
                $image->save();
            }
            $order++;
        }
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
                $img->deleteQuietly();
            }

            // Registrar quién eliminó sin disparar eventos deleted (evita doble auditoría)
            $post->deleted_by = Auth::id();
            $post->saveQuietly();

            // Eliminar registro (soft delete) sin eventos
            $post->deleteQuietly();
        }

        $count = count($titles);

        // Auditoría de eliminación múltiple
        Audit::create([
            'user_id'        => Auth::id(),
            'event'          => 'bulk_deleted',
            'auditable_type' => Post::class,
            'auditable_id'   => null,
            'old_values'     => [
                'ids'    => $postIds,
                'titles' => $titles,
            ],
            'new_values'     => null,
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
        ]);

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
        $post->saveQuietly();;
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
            'updated_at_human' => $post->updated_at?->diffForHumans() ?? '—',
            'reviewed_at' => $post->reviewed_at?->format('d/m/Y H:i') ?? '—',
        ]);
    }

    public function exportExcel(Request $request)
    {
        $ids = $request->input('ids');
        $filename = 'posts_'.now()->format('Y-m-d_H-i-s').'.xlsx';

        // Auditoría de exportación Excel
        Audit::create([
            'user_id'        => Auth::id(),
            'event'          => 'excel_exported',
            'auditable_type' => Post::class,
            'auditable_id'   => null,
            'old_values'     => null,
            'new_values'     => [
                'ids'        => $ids,
                'export_all' => $request->boolean('export_all', false),
                'filename'   => $filename,
            ],
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
        ]);

        return Excel::download(new PostsExcelExport($ids), $filename);
    }

    public function exportCsv(Request $request)
    {
        $ids = $request->has('export_all') ? null : $request->input('ids');
        $filename = 'posts_'.now()->format('Y-m-d_H-i-s').'.csv';

        // Auditoría de exportación CSV
        Audit::create([
            'user_id'        => Auth::id(),
            'event'          => 'csv_exported',
            'auditable_type' => Post::class,
            'auditable_id'   => null,
            'old_values'     => null,
            'new_values'     => [
                'ids'        => $ids,
                'export_all' => $request->boolean('export_all', false),
                'filename'   => $filename,
            ],
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
        ]);

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

        // Auditoría de exportación PDF
        Audit::create([
            'user_id'        => Auth::id(),
            'event'          => 'pdf_exported',
            'auditable_type' => Post::class,
            'auditable_id'   => null,
            'old_values'     => null,
            'new_values'     => [
                'ids'        => $request->ids ?? null,
                'export_all' => $request->boolean('export_all', false),
                'filename'   => $filename,
            ],
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
        ]);

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
