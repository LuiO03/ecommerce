<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Models\Cover;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class CoverController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:portadas.index')->only(['index']);
        $this->middleware('can:portadas.create')->only(['create', 'store']);
        $this->middleware('can:portadas.edit')->only(['edit', 'update', 'updateStatus']);
        $this->middleware('can:portadas.delete')->only(['destroy', 'destroyMultiple']);
        $this->middleware('can:portadas.update-status')->only(['updateStatus']);
    }

    public function index()
    {
        $covers = Cover::forTable()->get();
        return view('admin.covers.index', compact('covers'));
    }

    public function create()
    {
        return view('admin.covers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255|min:3',
            'overlay_text' => 'nullable|string|max:500',
            'overlay_subtext' => 'nullable|string|max:500',
            'text_position' => 'nullable|in:top-left,top-center,top-right,center-left,center-center,center-right,bottom-left,bottom-center,bottom-right',
            'text_color' => 'nullable|string|size:7|starts_with:#',
            'overlay_bg_enabled' => 'nullable|boolean',
            'overlay_bg_opacity' => 'nullable|numeric|min:0|max:1',
            'button_text' => 'nullable|string|max:100',
            'button_link' => 'nullable|url',
            'button_style' => 'nullable|in:primary,secondary,outline,white',
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'status' => 'required|boolean',
        ]);

        $title = ucwords(mb_strtolower($request->title));
        $slug = Cover::generateUniqueSlug($title);

        // Subida de imagen
        $imagePath = null;
        if ($request->hasFile('image')) {
            $extension = $request->file('image')->getClientOriginalExtension();
            $filename = $slug . '-' . time() . '.' . $extension;
            $imagePath = 'covers/' . $filename;
            $request->file('image')->storeAs('covers', $filename, 'public');
        }

        $cover = Cover::create([
            'slug'            => $slug,
            'title'           => $title,
            'overlay_text'    => $request->overlay_text,
            'overlay_subtext' => $request->overlay_subtext,
            'text_position'   => $request->text_position ?? 'center-center',
            'text_color'      => $request->text_color ?? '#FFFFFF',
            'overlay_bg_enabled' => (bool) $request->overlay_bg_enabled,
            'overlay_bg_opacity' => $request->overlay_bg_opacity ?? 0.35,
            'button_text'     => $request->button_text,
            'button_link'     => $request->button_link,
            'button_style'    => $request->button_style ?? 'primary',
            'image_path'      => $imagePath,
            'start_at'        => $request->start_at,
            'end_at'          => $request->end_at,
            'status'          => (bool) $request->status,
            'created_by'      => Auth::id(),
            'updated_by'      => Auth::id(),
        ]);

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Portada creada',
            'message' => "La portada <strong>{$cover->title}</strong> se ha creado correctamente.",
        ]);

        Session::flash('highlightRow', $cover->id);

        return redirect()->route('admin.covers.index');
    }

    public function edit(Cover $cover)
    {
        return view('admin.covers.edit', compact('cover'));
    }

    public function update(Request $request, Cover $cover)
    {
        $request->validate([
            'title' => 'required|string|max:255|min:3',
            'overlay_text' => 'nullable|string|max:500',
            'overlay_subtext' => 'nullable|string|max:500',
            'text_position' => 'nullable|in:top-left,top-center,top-right,center-left,center-center,center-right,bottom-left,bottom-center,bottom-right',
            'text_color' => 'nullable|string|size:7|starts_with:#',
            'overlay_bg_enabled' => 'nullable|boolean',
            'overlay_bg_opacity' => 'nullable|numeric|min:0|max:1',
            'button_text' => 'nullable|string|max:100',
            'button_link' => 'nullable|url',
            'button_style' => 'nullable|in:primary,secondary,outline,white',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'status' => 'required|boolean',
        ]);

        $title = ucwords(mb_strtolower($request->title));
        $slug = Cover::generateUniqueSlug($title, $cover->id);

        $imagePath = $cover->image_path;

        // Si marca para eliminar imagen
        if ($request->input('remove_image') == '1') {
            if ($cover->image_path && Storage::disk('public')->exists($cover->image_path)) {
                Storage::disk('public')->delete($cover->image_path);
            }
            $imagePath = null;
        }
        // Si sube una nueva, eliminar la anterior y subir la nueva
        elseif ($request->hasFile('image')) {
            if ($cover->image_path && Storage::disk('public')->exists($cover->image_path)) {
                Storage::disk('public')->delete($cover->image_path);
            }

            $extension = $request->file('image')->getClientOriginalExtension();
            $filename = $slug . '-' . time() . '.' . $extension;
            $imagePath = 'covers/' . $filename;
            $request->file('image')->storeAs('covers', $filename, 'public');
        }

        $cover->update([
            'slug'            => $slug,
            'title'           => $title,
            'overlay_text'    => $request->overlay_text,
            'overlay_subtext' => $request->overlay_subtext,
            'text_position'   => $request->text_position ?? 'center-center',
            'text_color'      => $request->text_color ?? '#FFFFFF',
            'overlay_bg_enabled' => (bool) $request->overlay_bg_enabled,
            'overlay_bg_opacity' => $request->overlay_bg_opacity ?? 0.35,
            'button_text'     => $request->button_text,
            'button_link'     => $request->button_link,
            'button_style'    => $request->button_style ?? 'primary',
            'image_path'      => $imagePath,
            'start_at'        => $request->start_at,
            'end_at'          => $request->end_at,
            'status'          => (bool) $request->status,
            'updated_by'      => Auth::id(),
        ]);

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Portada actualizada',
            'message' => "La portada <strong>{$cover->title}</strong> se ha actualizado correctamente.",
        ]);

        Session::flash('highlightRow', $cover->id);
        return redirect()->route('admin.covers.index');
    }

    public function updateStatus(Request $request, Cover $cover)
    {
        $request->validate([
            'status' => 'required|boolean',
        ]);

        $oldStatus = (bool) $cover->status;

        // Actualizar solo estado sin disparar eventos updated (evita doble auditoría)
        $cover->status = (bool) $request->status;
        $cover->updated_by = Auth::id();
        $cover->saveQuietly();

        // Auditoría de cambio de estado
        Audit::create([
            'user_id'        => Auth::id(),
            'event'          => 'status_updated',
            'auditable_type' => Cover::class,
            'auditable_id'   => $cover->id,
            'old_values'     => ['status' => $oldStatus],
            'new_values'     => ['status' => (bool) $cover->status],
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente',
            'status' => $cover->status,
        ]);
    }

    public function destroy(Cover $cover)
    {
        if ($cover->image_path && Storage::disk('public')->exists($cover->image_path)) {
            Storage::disk('public')->delete($cover->image_path);
        }

        $cover->deleted_by = Auth::id();
        $cover->saveQuietly();
        $cover->delete();

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Portada eliminada',
            'message' => "La portada <strong>{$cover->title}</strong> se ha eliminado correctamente.",
        ]);

        return redirect()->route('admin.covers.index');
    }

    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'covers' => 'sometimes|array|min:1',
            'covers.*' => 'exists:covers,id',
            'ids' => 'sometimes|array|min:1',
            'ids.*' => 'exists:covers,id'
        ]);

        $coverIds = $request->covers ?? $request->ids;

        if (empty($coverIds)) {
            Session::flash('info', [
                'type' => 'danger',
                'header' => 'Error',
                'title' => 'Sin selección',
                'message' => 'No se seleccionaron portadas para eliminar.',
            ]);
            return redirect()->route('admin.covers.index');
        }

        $covers = Cover::whereIn('id', $coverIds)->get();

        if ($covers->isEmpty()) {
            Session::flash('info', [
                'type' => 'danger',
                'header' => 'Error',
                'title' => 'No encontradas',
                'message' => 'Las portadas seleccionadas no existen.',
            ]);
            return redirect()->route('admin.covers.index');
        }

        $titles = [];

        foreach ($covers as $cover) {
            $titles[] = $cover->title;

            // Eliminar imagen si existe
            if ($cover->image_path && Storage::disk('public')->exists($cover->image_path)) {
                Storage::disk('public')->delete($cover->image_path);
            }

            $cover->deleted_by = Auth::id();
            $cover->saveQuietly();
            $cover->delete();
        }

        // Auditoría de eliminación múltiple
        Audit::create([
            'user_id'        => Auth::id(),
            'event'          => 'bulk_deleted',
            'auditable_type' => Cover::class,
            'auditable_id'   => null,
            'old_values'     => [
                'ids'    => $coverIds,
                'titles' => $titles,
            ],
            'new_values'     => null,
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
        ]);

        $count = count($titles);

        Session::flash('info', [
            'type' => 'danger',
            'header' => 'Registros eliminados',
            'title' => "Se eliminaron <strong>{$count}</strong> " . ($count === 1 ? 'portada' : 'portadas'),
            'message' => "Lista de portadas eliminadas:",
            'list' => $titles,
        ]);

        return redirect()->route('admin.covers.index');
    }
}
