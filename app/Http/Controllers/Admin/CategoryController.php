<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Family;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelPdf\Facades\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CategoriesExcelExport;
use App\Exports\CategoriesCsvExport;

class CategoryController extends Controller
{
    /* ======================================================
     |  INDEX
     ====================================================== */
    public function index()
    {
        $categories = Category::select([
            'id', 'name', 'slug', 'description', 'status',
            'family_id', 'parent_id', 'created_at'
        ])
        ->orderBy('id', 'desc')
        ->with(['family:id,name', 'parent:id,name'])
        ->get();

        $families = Family::select('id', 'name')
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return view('admin.categories.index', compact('categories', 'families'));
    }

    /* ======================================================
     |  EXPORTS
     ====================================================== */
    public function exportExcel(Request $request)
    {
        $ids = $request->input('ids');
        $filename = 'categorias_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new CategoriesExcelExport($ids), $filename);
    }

    public function exportCsv(Request $request)
    {
        $ids = $request->has('export_all') ? null : $request->input('ids');

        $filename = 'categorias_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return Excel::download(
            new CategoriesCsvExport($ids),
            $filename,
            \Maatwebsite\Excel\Excel::CSV
        );
    }

    public function exportPdf(Request $request)
    {
        if ($request->has('ids')) {
            $categories = Category::whereIn('id', $request->ids)
                ->with('family:id,name')
                ->get();
        } elseif ($request->has('export_all')) {
            $categories = Category::with('family:id,name')->get();
        } else {
            return back()->with('error', 'No se seleccionaron categorías para exportar.');
        }

        if ($categories->isEmpty()) {
            return back()->with('error', 'No hay categorías disponibles para exportar.');
        }

        $filename = 'categorias_' . now()->format('Y-m-d_H-i-s') . '.pdf';

        return Pdf::view('admin.export.categories-pdf', compact('categories'))
            ->format('a4')
            ->name($filename)
            ->download();
    }

    /* ======================================================
     |  CREATE
     ====================================================== */
    public function create()
    {
        $families = Family::forSelect()->get();
        $parents = Category::forSelect()->get();

        return view('admin.categories.create', compact('families', 'parents'));
    }

    /* ======================================================
     |  STORE
     ====================================================== */
    public function store(Request $request)
    {
        $request->validate([
            'family_id' => 'required|exists:families,id',
            'parent_id' => 'nullable|exists:categories,id',
            'name'      => 'required|string|max:255|min:3|unique:categories,name',
            'description' => 'nullable|string',
            'status'    => 'required|boolean',
            'image'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $slug = Category::generateUniqueSlug($request->name);

        /* ---- Imagen ---- */
        $imagePath = null;
        if ($request->hasFile('image')) {
            $ext = $request->file('image')->getClientOriginalExtension();
            $filename = $slug . '-' . time() . '.' . $ext;
            $imagePath = 'categories/' . $filename;
            $request->file('image')->storeAs('categories', $filename, 'public');
        }

        $category = Category::create([
            'family_id'   => $request->family_id,
            'parent_id'   => $request->parent_id,
            'name'        => $request->name,
            'slug'        => $slug,
            'description' => $request->description,
            'status'      => (bool) $request->status,
            'image'       => $imagePath,
            'created_by'  => Auth::id(),
            'updated_by'  => Auth::id(),
        ]);

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Categoría creada',
            'message' => "La categoría <strong>{$category->name}</strong> se ha creado correctamente.",
        ]);

        Session::flash('highlightRow', $category->id);

        return redirect()->route('admin.categories.index');
    }

    /* ======================================================
     |  EDIT
     ====================================================== */
    public function edit(Category $category)
    {
        $families = Family::forSelect()->get();
        $parents = Category::where('id', '!=', $category->id)
            ->forSelect()
            ->get();

        return view('admin.categories.edit', compact('category', 'families', 'parents'));
    }

    /* ======================================================
     |  UPDATE
     ====================================================== */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'family_id' => 'required|exists:families,id',
            'parent_id' => 'nullable|exists:categories,id|not_in:' . $category->id,
            'name'      => 'required|string|max:255|min:3|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'status'    => 'required|boolean',
            'image'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $slug = Category::generateUniqueSlug($request->name, $category->id);

        $imagePath = $category->image;

        /* ---- Eliminar imagen ---- */
        if ($request->input('remove_image') == '1') {
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }
            $imagePath = null;
        }
        /* ---- Nueva imagen ---- */
        elseif ($request->hasFile('image')) {

            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }

            $ext = $request->file('image')->getClientOriginalExtension();
            $filename = $slug . '-' . time() . '.' . $ext;
            $imagePath = 'categories/' . $filename;
            $request->file('image')->storeAs('categories', $filename, 'public');
        }

        $category->update([
            'family_id'   => $request->family_id,
            'parent_id'   => $request->parent_id,
            'name'        => $request->name,
            'slug'        => $slug,
            'description' => $request->description,
            'status'      => (bool) $request->status,
            'image'       => $imagePath,
            'updated_by'  => Auth::id(),
        ]);

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Categoría actualizada',
            'message' => "La categoría <strong>{$category->name}</strong> ha sido actualizada correctamente.",
        ]);

        Session::flash('highlightRow', $category->id);

        return redirect()->route('admin.categories.index');
    }

    /* ======================================================
     |  DELETE (SINGLE)
     ====================================================== */
    public function destroy(Category $category)
    {
        if ($category->children()->exists()) {
            Session::flash('info', [
                'type' => 'warning',
                'header' => 'Acción no permitida',
                'title' => 'Categoría con subcategorías',
                'message' => "No se puede eliminar la categoría <strong>{$category->name}</strong> porque tiene subcategorías.",
            ]);
            return redirect()->route('admin.categories.index');
        }

        if ($category->products()->exists()) {
            Session::flash('info', [
                'type' => 'warning',
                'header' => 'Acción no permitida',
                'title' => 'Categoría con productos',
                'message' => "La categoría <strong>{$category->name}</strong> tiene productos asociados.",
            ]);
            return redirect()->route('admin.categories.index');
        }

        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }

        $name = $category->name;

        $category->deleted_by = Auth::id();
        $category->save();

        $category->delete();

        Session::flash('info', [
            'type' => 'danger',
            'header' => 'Registro eliminado',
            'title' => 'Categoría eliminada',
            'message' => "La categoría <strong>{$name}</strong> ha sido eliminada del sistema.",
        ]);

        return redirect()->route('admin.categories.index');
    }

    /* ======================================================
     |  DELETE MULTIPLE
     ====================================================== */
    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'exists:categories,id',
        ]);

        $categories = Category::whereIn('id', $request->ids)->get();

        if ($categories->isEmpty()) {
            Session::flash('info', [
                'type' => 'danger',
                'header' => 'Error',
                'title' => 'No encontradas',
                'message' => 'Las categorías seleccionadas no existen.',
            ]);
            return redirect()->route('admin.categories.index');
        }

        $restricted = $categories->filter(fn($c) =>
            $c->children()->exists() || $c->products()->exists()
        );

        if ($restricted->isNotEmpty()) {
            $blocked = $restricted->pluck('name')->implode(', ');

            Session::flash('info', [
                'type' => 'warning',
                'header' => 'Acción restringida',
                'title' => 'Categorías no eliminables',
                'message' => "Estas categorías no se pueden eliminar: <strong>{$blocked}</strong>.",
            ]);

            return redirect()->route('admin.categories.index');
        }

        $names = [];

        foreach ($categories as $category) {

            $names[] = $category->name;

            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }

            $category->deleted_by = Auth::id();
            $category->save();

            $category->delete();
        }

        $count = count($names);

        Session::flash('info', [
            'type' => 'danger',
            'header' => 'Registros eliminados',
            'title' => "Se eliminaron <strong>{$count}</strong> categorías",
            'message' => "Lista de categorías eliminadas:",
            'list' => $names,
        ]);

        return redirect()->route('admin.categories.index');
    }

    /* ======================================================
     |  STATUS UPDATE (AJAX)
     ====================================================== */
    public function updateStatus(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'status' => 'required|boolean',
        ]);

        $category->update([
            'status'     => $request->status,
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente',
            'status'  => $category->status,
        ]);
    }
}
