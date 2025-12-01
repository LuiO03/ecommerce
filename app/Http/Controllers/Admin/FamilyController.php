<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Family;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FamiliesExcelExport;
use App\Exports\FamiliesCsvExport;
use Spatie\LaravelPdf\Facades\Pdf;
use Illuminate\Support\Facades\Storage;

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


        return Excel::download(new FamiliesExcelExport($ids), $filename);
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

    public function exportCsv(Request $request)
    {
        $ids = $request->has('export_all')
            ? null
            : $request->input('ids');

        $filename = 'familias_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return Excel::download(
            new FamiliesCsvExport($ids),
            $filename,
            \Maatwebsite\Excel\Excel::CSV
        );
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

        // Capitalizar nombre y descripción
        $name = ucwords(mb_strtolower($request->name));
        $description = $request->description ? ucfirst(mb_strtolower($request->description)) : null;

        $slug = Family::generateUniqueSlug($name);

        // 📌 Subida profesional de imagen con nombre descriptivo
        $imagePath = null;
        if ($request->hasFile('image')) {
            $extension = $request->file('image')->getClientOriginalExtension();
            $filename = $slug . '-' . time() . '.' . $extension;
            $imagePath = 'families/' . $filename;
            $request->file('image')->storeAs('families', $filename, 'public');
        }

        $family = Family::create([
            'name'        => $name,
            'slug'        => $slug,
            'description' => $description,
            'status'      => (bool) $request->status,
            'image'       => $imagePath,
            'created_by'  => Auth::id(),
            'updated_by'  => Auth::id(),
        ]);

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Familia creada',
            'message' => "La familia <strong>{$family->name}</strong> se ha creado correctamente.",
        ]);

        Session::flash('highlightRow', $family->id);

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

        $imagePath = $family->image; // Mantener la imagen actual

        // 📌 Si marca para eliminar imagen
        if ($request->input('remove_image') == '1') {
            if ($family->image && Storage::disk('public')->exists($family->image)) {
                Storage::disk('public')->delete($family->image);
            }
            $imagePath = null;
        }
        // 📌 Si sube una nueva, eliminar la anterior y subir la nueva
        elseif ($request->hasFile('image')) {

            if ($family->image && Storage::disk('public')->exists($family->image)) {
                Storage::disk('public')->delete($family->image);
            }

            $extension = $request->file('image')->getClientOriginalExtension();
            $filename = $slug . '-' . time() . '.' . $extension;
            $imagePath = 'families/' . $filename;
            $request->file('image')->storeAs('families', $filename, 'public');
        }

        $request->validate([
            'name' => 'required|string|max:255|min:3|unique:families,name,' . $family->id,
            'status' => 'required|boolean',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Capitalizar nombre y descripción
        $name = ucwords(mb_strtolower($request->name));
        $description = $request->description ? ucfirst(mb_strtolower($request->description)) : null;

        $slug = Family::generateUniqueSlug($name, $family->id);

        $imagePath = $family->image; // Mantener la imagen actual

        // 📌 Si marca para eliminar imagen
        if ($request->input('remove_image') == '1') {
            if ($family->image && Storage::disk('public')->exists($family->image)) {/* Lines 142-143 omitted */
            }
            $imagePath = null;
        }
        // 📌 Si sube una nueva, eliminar la anterior y subir la nueva
        elseif ($request->hasFile('image')) {

            if ($family->image && Storage::disk('public')->exists($family->image)) {/* Lines 150-151 omitted */
            }

            $extension = $request->file('image')->getClientOriginalExtension();
            $filename = $slug . '-' . time() . '.' . $extension;
            $imagePath = 'families/' . $filename;
            $request->file('image')->storeAs('families', $filename, 'public');
        }

        $family->update([
            'name'        => $name,
            'slug'        => $slug,
            'description' => $description,
            'status'      => (bool) $request->status,
            'image'       => $imagePath,
            'updated_by'  => Auth::id(),
        ]);

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Familia actualizada',
            'message' => "La familia <strong>{$family->name}</strong> se ha actualizado correctamente.",
        ]);
        Session::flash('highlightRow', $family->id);
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
                'title' => 'Sin selección',
                'message' => 'No se seleccionaron familias para eliminar.',
            ]);
            return redirect()->route('admin.families.index');
        }

        $families = Family::whereIn('id', $familyIds)->get();

        if ($families->isEmpty()) {
            Session::flash('info', [
                'type' => 'danger',
                'header' => 'Error',
                'title' => 'No encontradas',
                'message' => 'Las familias seleccionadas no existen.',
            ]);
            return redirect()->route('admin.families.index');
        }

        // 🔒 Bloqueo por relaciones
        $restricted = $families->filter(fn($f) => $f->categories()->exists());

        if ($restricted->isNotEmpty()) {

            $blocked = $restricted->pluck('name')->implode(', ');

            Session::flash('info', [
                'type' => 'warning',
                'header' => 'Acción restringida',
                'title' => 'Familias con categorías',
                'message' => "Estas familias no se pueden eliminar porque tienen categorías asociadas: <strong>{$blocked}</strong>.",
            ]);

            return redirect()->route('admin.families.index');
        }

        // ===============================
        //   🔥 Eliminación profesional
        // ===============================

        // Guarda nombres para el mensaje final
        $names = [];

        foreach ($families as $family) {

            $names[] = $family->name;

            // 🖼️ Eliminar imagen si existe
            if ($family->image && Storage::disk('public')->exists($family->image)) {
                Storage::disk('public')->delete($family->image);
            }

            // Registrar quién eliminó
            $family->deleted_by = Auth::id();
            $family->save();

            // Eliminar registro definitivo
            $family->delete();
        }

        $count = count($names);

        Session::flash('info', [
            'type' => 'danger',
            'header' => 'Registros eliminados',
            'title' => "Se eliminaron <strong>{$count}</strong> " . ($count === 1 ? 'familia' : 'familias'),
            'message' => "Lista de familias eliminadas:",
            'list' => $names,
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

    public function destroy(Family $family)
    {
        // 🔒 Validar relaciones bloqueantes (igual que destroyMultiple)
        if ($family->categories()->exists()) {
            Session::flash('info', [
                'type' => 'warning',
                'header' => 'Acción no permitida',
                'title' => 'Familia con categorías asociadas',
                'message' => "La familia <strong>{$family->name}</strong> no se puede eliminar porque tiene categorías registradas.",
            ]);

            return redirect()->route('admin.families.index');
        }

        $name = $family->name;

        // 🖼️ Eliminar imagen si existe
        if ($family->image && Storage::disk('public')->exists($family->image)) {
            Storage::disk('public')->delete($family->image);
        }

        // Registrar quién eliminó
        $family->deleted_by = Auth::id();
        $family->save();

        // Eliminar registro definitivo
        $family->delete(); // o forceDelete()

        Session::flash('info', [
            'type' => 'danger',
            'header' => 'Registro eliminado',
            'title' => 'Familia eliminada',
            'message' => "La familia <strong>{$name}</strong> ha sido eliminada del sistema.",
        ]);

        return redirect()->route('admin.families.index');
    }

    /**
     * Devuelve los datos completos de una familia por slug (JSON)
     */
    public function show($slug)
    {
        $family = Family::with([
            'creator:id,name,last_name',
            'updater:id,name,last_name'
        ])
        ->select('id', 'slug', 'name', 'description', 'status', 'image', 'created_by', 'updated_by', 'created_at', 'updated_at')
        ->where('slug', $slug)
        ->firstOrFail();

        // Definir correctamente
        $createdBy = $family->creator;
        $updatedBy = $family->updater;

        return response()->json([
            'id' => '#' . $family->id,
            'slug' => $family->slug,
            'name' => $family->name,
            'description' => $family->description,
            'status' => $family->status,
            'image' => $family->image,
            'created_by' => $family->created_by,
            'updated_by' => $family->updated_by,

            // Nombres
            'created_by_name' => $createdBy
                ? trim($createdBy->name . ' ' . $createdBy->last_name)
                : 'Sistema',

            'updated_by_name' => $updatedBy
                ? trim($updatedBy->name . ' ' . $updatedBy->last_name)
                : '—',

            // Fechas
            'created_at' => $family->created_at
                ? $family->created_at->format('d/m/Y H:i')
                : '-',

            'updated_at' => $family->updated_at
                ? $family->updated_at->format('d/m/Y H:i')
                : '-',
        ]);
    }

}
