<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Family;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;

class FamilyController extends Controller
{
    public function index()
    {
        $families = Family::select(['id', 'name', 'slug', 'description', 'status', 'created_at'])
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.families.index', compact('families'));
    }

    public function create()
    {
        return view('admin.families.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|min:3',
            'status' => 'required|boolean',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $slug = Family::generateUniqueSlug($request->name);

        $family = Family::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'status' => (bool) $request->status,
            'image' => $request->image,
        ]);

        Session::flash('info', [
            'type' => 'success',
            'header' => 'Registro exitoso',
            'title' => 'Familia creada',
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
            'name' => 'required|string|max:255|min:3',
            'status' => 'required|boolean',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $slug = Family::generateUniqueSlug($request->name, $family->id);

        $family->update([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'status' => (bool) $request->status,
            'image' => $request->image,
        ]);

        Session::flash('info', [
            'type' => 'success',
            'header' => 'Actualización exitosa',
            'title' => 'Familia actualizada',
            'message' => "La familia <strong>{$family->name}</strong> ha sido actualizada correctamente.",
        ]);

        return redirect()->route('admin.families.index');
    }

    public function destroy(Family $family)
    {
        $name = $family->name;
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
            'families' => 'required|array|min:1',
            'families.*' => 'exists:families,id'
        ]);

        $familyIds = $request->families;
        $families = Family::whereIn('id', $familyIds)->get();
        $count = $families->count();

        if ($count === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron familias para eliminar.'
            ]);
        }

        // Obtener nombres para el mensaje
        $familyNames = $families->pluck('name')->toArray();
        
        // Eliminar las familias
        Family::whereIn('id', $familyIds)->delete();

        if ($count === 1) {
            $message = "La familia <strong>\"{$familyNames[0]}\"</strong> ha sido eliminada correctamente.";
        } else {
            $familyList = implode('', array_map(function($name) {
                return "<li><strong>{$name}</strong></li>";
            }, $familyNames));
            
            $message = "Se han eliminado <strong>{$count} familias</strong> correctamente:<br><br><strong>Familias eliminadas:</strong><ul>{$familyList}</ul>";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'count' => $count
        ]);
    }
}
