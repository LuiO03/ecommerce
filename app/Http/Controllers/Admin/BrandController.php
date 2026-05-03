<?php

namespace App\Http\Controllers\Admin;

use App\Exports\BrandsCsvExport;
use App\Exports\BrandsExcelExport;
use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Models\Brand;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;

class BrandController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:marcas.index')->only(['index', 'show']);
        $this->middleware('can:marcas.create')->only(['create', 'store']);
        $this->middleware('can:marcas.edit')->only(['edit', 'update', 'updateStatus']);
        $this->middleware('can:marcas.delete')->only(['destroy', 'destroyMultiple']);
        $this->middleware('can:reportes.export')->only(['exportExcel', 'exportCsv', 'exportPdf']);
        $this->middleware('can:marcas.update-status')->only(['updateStatus']);
    }

    public function index()
    {
        $brands = Brand::select([
            'id',
            'name',
            'slug',
            'description',
            'status',
            'created_at',
        ])
            ->withCount('products')
            ->orderByDesc('id')
            ->get();

        return view('admin.brands.index', compact('brands'));
    }

    /* ======================================================
     |  EXPORTS
     ====================================================== */
    public function exportExcel(Request $request)
    {
        $ids = $request->input('ids');
        $filename = 'marcas_'.now()->format('Y-m-d_H-i-s').'.xlsx';

        Audit::create([
            'user_id' => Auth::id(),
            'event' => 'excel_exported',
            'auditable_type' => Brand::class,
            'auditable_id' => null,
            'old_values' => null,
            'new_values' => [
                'ids' => $ids,
                'export_all' => $request->boolean('export_all', false),
                'filename' => $filename,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return Excel::download(new BrandsExcelExport($ids), $filename);
    }

    public function exportCsv(Request $request)
    {
        $ids = $request->has('export_all') ? null : $request->input('ids');
        $filename = 'marcas_'.now()->format('Y-m-d_H-i-s').'.csv';

        Audit::create([
            'user_id' => Auth::id(),
            'event' => 'csv_exported',
            'auditable_type' => Brand::class,
            'auditable_id' => null,
            'old_values' => null,
            'new_values' => [
                'ids' => $ids,
                'export_all' => $request->boolean('export_all', false),
                'filename' => $filename,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return Excel::download(
            new BrandsCsvExport($ids),
            $filename,
            \Maatwebsite\Excel\Excel::CSV
        );
    }

    public function exportPdf(Request $request)
    {
        $query = Brand::query()
            ->select([
                'id',
                'name',
                'slug',
                'status',
                'created_at',
                'updated_at',
            ])
            ->withCount('products');

        $isSelectedExport = false;

        if ($request->filled('ids')) {
            $query->whereIn('id', (array) $request->ids);
            $isSelectedExport = true;
        } elseif ($request->boolean('export_all')) {
            // exportación total
        } else {
            Session::flash('info', [
                'type'    => 'danger',
                'header'  => 'Error',
                'title'   => 'Sin selección',
                'message' => 'No se seleccionaron marcas para exportar.',
            ]);

            return back();
        }

        $brands = $query->orderBy('name')->get();

        if ($brands->isEmpty()) {
            Session::flash('info', [
                'type'    => 'danger',
                'header'  => 'Error',
                'title'   => 'Sin datos',
                'message' => 'No hay marcas disponibles para exportar.',
            ]);

            return back();
        }

        $filename = 'marcas_' . now()->format('Y-m-d_H-i-s') . '.pdf';

        Audit::create([
            'user_id'        => Auth::id(),
            'event'          => 'pdf_exported',
            'auditable_type' => Brand::class,
            'auditable_id'   => null,
            'old_values'     => null,
            'new_values'     => [
                'filename'   => $filename,
                'ids'        => $request->ids ?? null,
                'export_all' => $request->boolean('export_all'),
                'selected'   => $isSelectedExport,
                'total'      => $brands->count(),
            ],
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
        ]);

        $pdf = Pdf::loadView('admin.export.brands-pdf', [
            'brands'         => $brands,
            'isSelectedExport' => $isSelectedExport,
            'exportedBy'       => Auth::user()?->name . ' ' . Auth::user()?->last_name,
        ])->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

    public function create()
    {
        return view('admin.brands.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|min:2|unique:brands,name|regex:/[a-zA-ZáéíóúÁÉÍÓÚñÑ]/',
            'description' => 'nullable|string',
            'status' => 'required|boolean',
        ], [
            'name.regex' => 'El nombre debe contener al menos una letra.',
        ]);

        $slug = Brand::generateUniqueSlug($request->name);

        $brand = Brand::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'status' => (bool) $request->status,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Marca creada',
            'message' => "La marca <strong>{$brand->name}</strong> se ha creado correctamente.",
        ]);

        Session::flash('highlightRow', $brand->id);

        return redirect()->route('admin.brands.index');
    }

    public function edit(Brand $brand)
    {
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        $request->validate([
            'name' => 'required|string|max:255|min:2|unique:brands,name,'.$brand->id.'|regex:/[a-zA-ZáéíóúÁÉÍÓÚñÑ]/',
            'description' => 'nullable|string',
            'status' => 'required|boolean',
        ], [
            'name.regex' => 'El nombre debe contener al menos una letra.',
        ]);

        $slug = Brand::generateUniqueSlug($request->name, $brand->id);

        $brand->update([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'status' => (bool) $request->status,
            'updated_by' => Auth::id(),
        ]);

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Marca actualizada',
            'message' => "La marca <strong>{$brand->name}</strong> ha sido actualizada correctamente.",
        ]);

        Session::flash('highlightRow', $brand->id);

        return redirect()->route('admin.brands.index');
    }

    public function destroy(Brand $brand)
    {
        if ($brand->products()->exists()) {
            Session::flash('info', [
                'type' => 'warning',
                'header' => 'Acción no permitida',
                'title' => 'Marca con productos',
                'message' => "La marca <strong>{$brand->name}</strong> tiene productos asociados.",
            ]);

            return redirect()->back();
        }

        $name = $brand->name;

        $brand->deleted_by = Auth::id();
        $brand->saveQuietly();

        $brand->delete();

        Session::flash('info', [
            'type' => 'danger',
            'header' => 'Registro eliminado',
            'title' => 'Marca eliminada',
            'message' => "La marca <strong>{$name}</strong> ha sido eliminada del sistema.",
        ]);

        return redirect()->back();
    }

    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:brands,id',
        ]);

        $brands = Brand::whereIn('id', $request->ids)->get();

        if ($brands->isEmpty()) {
            Session::flash('info', [
                'type' => 'danger',
                'header' => 'Error',
                'title' => 'No encontradas',
                'message' => 'Las marcas seleccionadas no existen.',
            ]);

            return redirect()->back();
        }

        $restricted = $brands->filter(fn ($b) => $b->products()->exists());

        if ($restricted->isNotEmpty()) {
            $blocked = $restricted->pluck('name')->implode(', ');

            Session::flash('info', [
                'type' => 'warning',
                'header' => 'Acción restringida',
                'title' => 'Marcas no eliminables',
                'message' => "Estas marcas no se pueden eliminar: <strong>{$blocked}</strong>.",
            ]);

            return redirect()->back();
        }

        $names = [];

        foreach ($brands as $brand) {
            $names[] = $brand->name;

            $brand->deleted_by = Auth::id();
            $brand->saveQuietly();
            $brand->deleteQuietly();
        }

        Audit::create([
            'user_id' => Auth::id(),
            'event' => 'bulk_deleted',
            'auditable_type' => Brand::class,
            'auditable_id' => null,
            'old_values' => [
                'ids' => $request->ids,
                'names' => $names,
            ],
            'new_values' => null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $count = count($names);

        Session::flash('info', [
            'type' => 'danger',
            'header' => 'Registros eliminados',
            'title' => "Se eliminaron <strong>{$count}</strong> marcas",
            'message' => 'Lista de marcas eliminadas:',
            'list' => $names,
        ]);

        return redirect()->back();
    }

    public function show($slug)
    {
        $brand = Brand::where('slug', $slug)
            ->withCount('products')
            ->firstOrFail();

        $createdBy = $brand->created_by ? User::find($brand->created_by) : null;
        $updatedBy = $brand->updated_by ? User::find($brand->updated_by) : null;

        return response()->json([
            'id' => $brand->id,
            'slug' => $brand->slug,
            'name' => $brand->name,
            'description' => $brand->description,
            'status' => $brand->status,
            'products_count' => $brand->products_count,
            'created_by_name' => $createdBy ? trim($createdBy->name.' '.$createdBy->last_name) : 'Sistema',
            'updated_by_name' => $updatedBy ? trim($updatedBy->name.' '.$updatedBy->last_name) : '—',
            'created_at' => $brand->created_at ? $brand->created_at->format('d/m/Y H:i') : '—',
            'updated_at' => $brand->updated_at ? $brand->updated_at->format('d/m/Y H:i') : '—',
            'updated_at_human' => $brand->updated_at ? $brand->updated_at->diffForHumans() : '—',
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);

        $request->validate([
            'status' => 'required|boolean',
        ]);

        $oldStatus = (bool) $brand->status;

        $brand->status = (bool) $request->status;
        $brand->updated_by = Auth::id();
        $brand->saveQuietly();

        Audit::create([
            'user_id' => Auth::id(),
            'event' => 'status_updated',
            'auditable_type' => Brand::class,
            'auditable_id' => $brand->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => (bool) $brand->status],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente',
            'status' => $brand->status,
        ]);
    }
}
