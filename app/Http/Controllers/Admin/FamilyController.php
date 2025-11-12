<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Family;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FamiliesExport;
use Spatie\LaravelPdf\Facades\Pdf;


class FamilyController extends Controller
{
    public function index()
    {
        $families = Family::select(['id', 'name', 'slug', 'description', 'status', 'created_at'])
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.families.index', compact('families'));
    }

    public function exportExcel(Request $request)
    {
        $ids = $request->input('ids');
        $filename = 'familias_' . now()->format('Y-m-d_H-i-s') . '.xlsx';


        return Excel::download(new FamiliesExport($ids), $filename);
    }

    public function exportPdf(Request $request)
    {
        if ($request->has('ids')) {
            $families = Family::whereIn('id', $request->ids)->get();
        } elseif ($request->has('export_all')) {
            $families = Family::all();
        } else {
            return back()->with('error', 'No se seleccionaron familias para exportar.');
        }

        if ($families->isEmpty()) {
            return back()->with('error', 'No hay familias disponibles para exportar.');
        }

        // 📄 Nombre del archivo dinámico con fecha y hora
        $filename = 'familias_' . now()->format('Y-m-d_H-i-s') . '.pdf';

        // Generar y descargar PDF
        return Pdf::view('admin.export.families-pdf', compact('families'))
            ->format('a4')
            ->name($filename)
            ->download();
    }

    public function create()
    {
        return view('admin.families.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|min:3|unique:families,name',
            'status' => 'required|boolean',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $slug = Family::generateUniqueSlug($request->name);

        $family = Family::create([
            'name'        => $request->name,
            'slug'        => $slug,
            'description' => $request->description,
            'status'      => (bool) $request->status,
            'image'       => $request->image,
            'created_by'  => Auth::id(), // 🔹 registra el usuario que creó
            'updated_by'  => Auth::id(),
        ]);

        Session::flash('info', [
            'type'    => 'success',
            'header'  => 'Registro exitoso',
            'title'   => 'Familia creada',
            'message' => "La familia <strong>{$family->name}</strong> se ha creado correctamente.",
        ]);

        return redirect()->route('admin.families.index');
    }

    public function edit(Family $family)
    {
        return view('admin.families.edit', compact('family'));
    }

    public function update(Request $request, Family $family)
    {
        $request->validate([
            'name' => 'required|string|max:255|min:3|unique:families,name,' . $family->id,
            'status' => 'required|boolean',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $slug = Family::generateUniqueSlug($request->name, $family->id);

        $family->update([
            'name'        => $request->name,
            'slug'        => $slug,
            'description' => $request->description,
            'status'      => (bool) $request->status,
            'image'       => $request->image,
            'updated_by'  => Auth::id(), // 🔹 registra el usuario que editó
        ]);

        Session::flash('info', [
            'type'    => 'success',
            'header'  => 'Actualización exitosa',
            'title'   => 'Familia actualizada',
            'message' => "La familia <strong>{$family->name}</strong> ha sido actualizada correctamente.",
        ]);

        return redirect()->route('admin.families.index');
    }

    public function destroy(Family $family)
    {
        // 🔹 Restringir eliminación si tiene categorías
        if ($family->categories()->exists()) {
            Session::flash('info', [
                'type' => 'warning',
                'header' => 'Acción no permitida',
                'title' => 'Familia con categorías asociadas',
                'message' => "No se puede eliminar la familia <strong>{$family->name}</strong> porque tiene categorías asociadas.",
            ]);
            return redirect()->route('admin.families.index');
        }

        $name = $family->name;

        // Registrar el usuario que la eliminó
        $family->deleted_by = Auth::id();
        $family->save();

        // Soft delete (si usas SoftDeletes)
        $family->delete();

        Session::flash('info', [
            'type' => 'danger',
            'header' => 'Registro eliminado',
            'title' => 'Familia eliminada',
            'message' => "La familia <strong>{$name}</strong> ha sido eliminada del sistema.",
        ]);

        return redirect()->route('admin.families.index');
    }

    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'families' => 'sometimes|array|min:1',
            'families.*' => 'exists:families,id',
            'ids' => 'sometimes|array|min:1',
            'ids.*' => 'exists:families,id'
        ]);

        $familyIds = $request->families ?? $request->ids;

        if (empty($familyIds)) {
            Session::flash('info', [
                'type' => 'danger',
                'header' => 'Error',
                'title' => 'Error en la eliminación',
                'message' => 'No se recibieron familias para eliminar.',
            ]);
            return redirect()->route('admin.families.index');
        }

        $families = Family::whereIn('id', $familyIds)->get();
        $count = $families->count();

        if ($count === 0) {
            Session::flash('info', [
                'type' => 'danger',
                'header' => 'Error',
                'title' => 'Error en la eliminación',
                'message' => 'No se encontraron familias para eliminar.',
            ]);
            return redirect()->route('admin.families.index');
        }

        // 🔹 Filtrar familias con categorías asociadas
        $restricted = $families->filter(fn($f) => $f->categories()->exists());

        if ($restricted->isNotEmpty()) {
            $blockedNames = $restricted->pluck('name')->implode(', ');

            Session::flash('info', [
                'type' => 'warning',
                'header' => 'Acción restringida',
                'title' => 'Familias con relaciones activas',
                'message' => "No se pueden eliminar las siguientes familias porque tienen categorías asociadas: <strong>{$blockedNames}</strong>.",
            ]);
            return redirect()->route('admin.families.index');
        }

        // 🔹 Registrar quién eliminó cada familia
        foreach ($families as $family) {
            $family->deleted_by = Auth::id();
            $family->save();
            $family->delete();
        }

        // Mensaje final con lista de nombres
        $namesList = $families->map(function($family) {
            return $family->name;
        })->toArray();

        Session::flash('info', [
            'type' => 'danger',
            'header' => 'Registros eliminados',
            'title' => "Se eliminaron <strong>{$count}</strong> " . ($count === 1 ? 'familia' : 'familias'),
            'message' => "Se " . ($count === 1 ? 'eliminó la siguiente familia' : 'eliminaron las siguientes familias') . ":",
            'list' => $namesList, // 🔹 Agregar lista de nombres
        ]);

        return redirect()->route('admin.families.index');
    }

    public function updateStatus(Request $request, $id)
    {
        $family = Family::findOrFail($id);

        $request->validate([
            'status' => 'required|boolean',
        ]);

        $family->update([
            'status' => $request->status,
            'updated_by' => Auth::id(), // 🔹 también se registra quién cambió el estado
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente',
            'status' => $family->status,
        ]);
    }
}
