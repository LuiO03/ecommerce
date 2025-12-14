<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProductsCsvExport;
use App\Exports\ProductsExcelExport;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\LaravelPdf\Facades\Pdf;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::select([
            'id',
            'sku',
            'name',
            'slug',
            'price',
            'discount',
            'status',
            'category_id',
            'created_by',
            'created_at',
        ])
            ->with(['category:id,name', 'creator:id,name,last_name'])
            ->withCount(['variants', 'images'])
            ->orderByDesc('id')
            ->get();

        $categories = Category::where('status', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('status', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'sku' => 'required|string|max:100|unique:products,sku',
            'name' => 'required|string|max:255|min:3',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'status' => 'required|boolean',
            'main_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $slug = Product::generateUniqueSlug($validated['name']);

        $product = Product::create([
            'category_id' => $validated['category_id'],
            'sku' => $validated['sku'],
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'discount' => $validated['discount'] ?? null,
            'status' => (bool) $validated['status'],
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        $this->handleMainImageUpload($request, $product, $slug);
        $this->handleGalleryUpload($request, $product, $slug);

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Producto creado',
            'message' => "El producto <strong>{$product->name}</strong> se ha creado correctamente.",
        ]);

        Session::flash('highlightRow', $product->id);

        return redirect()->route('admin.products.index');
    }

    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)
            ->with([
                'category:id,name,slug',
                'variants:id,product_id,sku,price,stock,status',
                'images' => fn ($query) => $query->orderBy('order'),
                'creator:id,name,last_name',
                'updater:id,name,last_name',
            ])
            ->firstOrFail();

        $gallery = $product->images->map(fn ($image) => [
            'id' => $image->id,
            'path' => $image->path,
            'url' => Storage::disk('public')->exists($image->path)
                ? Storage::disk('public')->url($image->path)
                : null,
            'alt' => $image->alt,
            'is_main' => (bool) $image->is_main,
            'order' => $image->order,
        ]);

        return response()->json([
            'id' => $product->id,
            'slug' => $product->slug,
            'sku' => $product->sku,
            'name' => $product->name,
            'description' => $product->description,
            'status' => $product->status,
            'category' => $product->category ? [
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ] : null,
            'price' => $product->price,
            'discount' => $product->discount,
            'variants' => $product->variants->map(fn ($variant) => [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'price' => $variant->price,
                'stock' => $variant->stock,
                'status' => $variant->status,
            ]),
            'images' => $gallery,
            'created_by_name' => $product->creator
                ? trim($product->creator->name . ' ' . ($product->creator->last_name ?? ''))
                : 'Sistema',
            'updated_by_name' => $product->updater
                ? trim($product->updater->name . ' ' . ($product->updater->last_name ?? ''))
                : '—',
            'created_at' => optional($product->created_at)?->format('d/m/Y H:i') ?? '—',
            'updated_at' => optional($product->updated_at)?->format('d/m/Y H:i') ?? '—',
        ]);
    }

    public function edit(Product $product)
    {
        $categories = Category::where('status', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $product->load(['images' => fn ($query) => $query->orderBy('order')]);

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'sku' => 'required|string|max:100|unique:products,sku,' . $product->id,
            'name' => 'required|string|max:255|min:3',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'status' => 'required|boolean',
            'main_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_main_image' => 'sometimes|boolean',
            'remove_gallery' => 'sometimes|array',
            'remove_gallery.*' => 'integer|exists:product_images,id',
        ]);

        $slug = Product::generateUniqueSlug($validated['name'], $product->id);

        $product->update([
            'category_id' => $validated['category_id'],
            'sku' => $validated['sku'],
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'discount' => $validated['discount'] ?? null,
            'status' => (bool) $validated['status'],
            'updated_by' => Auth::id(),
        ]);

        if ($request->boolean('remove_main_image')) {
            $this->removeMainImage($product);
        }

        $this->handleMainImageUpload($request, $product, $slug);
        $this->handleGalleryRemovals($request, $product);
        $this->handleGalleryUpload($request, $product, $slug);

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Producto actualizado',
            'message' => "El producto <strong>{$product->name}</strong> ha sido actualizado correctamente.",
        ]);

        Session::flash('highlightRow', $product->id);

        return redirect()->route('admin.products.index');
    }

    public function destroy(Product $product)
    {
        $this->removeAllImages($product);

        $name = $product->name;

        $product->deleted_by = Auth::id();
        $product->save();
        $product->delete();

        Session::flash('info', [
            'type' => 'danger',
            'header' => 'Registro eliminado',
            'title' => 'Producto eliminado',
            'message' => "El producto <strong>{$name}</strong> ha sido eliminado.",
        ]);

        return redirect()->route('admin.products.index');
    }

    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:products,id',
        ]);

        $products = Product::with('images')->whereIn('id', $request->ids)->get();

        if ($products->isEmpty()) {
            Session::flash('info', [
                'type' => 'danger',
                'header' => 'Error',
                'title' => 'No encontrados',
                'message' => 'Los productos seleccionados no existen.',
            ]);

            return redirect()->route('admin.products.index');
        }

        $names = [];

        foreach ($products as $product) {
            $names[] = $product->name;
            $this->removeAllImages($product);

            $product->deleted_by = Auth::id();
            $product->save();
            $product->delete();
        }

        $count = count($names);

        Session::flash('info', [
            'type' => 'danger',
            'header' => 'Registros eliminados',
            'title' => "Se eliminaron <strong>{$count}</strong> productos",
            'message' => 'Lista de productos eliminados:',
            'list' => $names,
        ]);

        return redirect()->route('admin.products.index');
    }

    public function exportExcel(Request $request)
    {
        $ids = $request->input('ids');
        $filename = 'productos_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new ProductsExcelExport($ids), $filename);
    }

    public function exportCsv(Request $request)
    {
        $ids = $request->has('export_all') ? null : $request->input('ids');
        $filename = 'productos_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return Excel::download(new ProductsCsvExport($ids), $filename, \Maatwebsite\Excel\Excel::CSV);
    }

    public function exportPdf(Request $request)
    {
        if ($request->has('ids')) {
            $products = Product::whereIn('id', $request->ids)
                ->with(['category:id,name'])
                ->get();
        } elseif ($request->has('export_all')) {
            $products = Product::with(['category:id,name'])->get();
        } else {
            return back()->with('error', 'No se seleccionaron productos para exportar.');
        }

        if ($products->isEmpty()) {
            return back()->with('error', 'No hay productos disponibles para exportar.');
        }

        $filename = 'productos_' . now()->format('Y-m-d_H-i-s') . '.pdf';

        return Pdf::view('admin.export.products-pdf', compact('products'))
            ->format('a4')
            ->name($filename)
            ->download();
    }

    public function updateStatus(Request $request, Product $product)
    {
        $request->validate([
            'status' => 'required|boolean',
        ]);

        $product->update([
            'status' => $request->status,
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente',
            'status' => $product->status,
        ]);
    }

    protected function handleMainImageUpload(Request $request, Product $product, string $slug): void
    {
        if (!$request->hasFile('main_image')) {
            return;
        }

        $this->removeMainImage($product);

        $file = $request->file('main_image');
        $extension = $file->getClientOriginalExtension();
        $filename = $slug . '-main.' . $extension;
        $path = 'products/' . $filename;

        $file->storeAs('products', $filename, 'public');

        ProductImage::create([
            'product_id' => $product->id,
            'path' => $path,
            'alt' => $product->name,
            'is_main' => true,
            'order' => 0,
        ]);
    }

    protected function handleGalleryUpload(Request $request, Product $product, string $slug): void
    {
        if (!$request->hasFile('gallery')) {
            return;
        }

        foreach ($request->file('gallery') as $index => $file) {
            if (!$file->isValid()) {
                continue;
            }

            $extension = $file->getClientOriginalExtension();
            $filename = $slug . '-' . time() . '-' . $index . '.' . $extension;
            $path = 'products/' . $filename;
            $file->storeAs('products', $filename, 'public');

            $nextOrder = ProductImage::where('product_id', $product->id)->max('order');
            $order = is_numeric($nextOrder) ? ((int) $nextOrder + 1) : 1;

            ProductImage::create([
                'product_id' => $product->id,
                'path' => $path,
                'alt' => $product->name,
                'is_main' => false,
                'order' => $order,
            ]);
        }
    }

    protected function handleGalleryRemovals(Request $request, Product $product): void
    {
        $removals = $request->input('remove_gallery', []);

        if (!is_array($removals) || empty($removals)) {
            return;
        }

        $images = $product->images()->whereIn('id', $removals)->get();

        foreach ($images as $image) {
            if (Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }

            $image->delete();
        }
    }

    protected function removeMainImage(Product $product): void
    {
        $mainImage = $product->images()->where('is_main', true)->first();

        if (!$mainImage) {
            return;
        }

        if (Storage::disk('public')->exists($mainImage->path)) {
            Storage::disk('public')->delete($mainImage->path);
        }

        $mainImage->delete();
    }

    protected function removeAllImages(Product $product): void
    {
        $images = $product->images;

        foreach ($images as $image) {
            if (Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }

            $image->delete();
        }
    }
}
