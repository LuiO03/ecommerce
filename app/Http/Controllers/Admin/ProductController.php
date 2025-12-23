<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProductsCsvExport;
use App\Exports\ProductsExcelExport;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Option;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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

        $options = Option::with(['features' => fn ($query) => $query->orderBy('id')])
            ->orderBy('name')
            ->get();

        return view('admin.products.create', compact('categories', 'options'));
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
            'gallery' => 'required|array|min:1',
            'gallery.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'primary_image' => 'nullable|string',
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

        $this->syncVariants($request, $product);

        $newImages = $this->handleGalleryUpload($request, $product, $slug);
        $this->finalizeProductImages($product, $request->input('primary_image'), $newImages);

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
            'url' => $this->resolveProductImageUrl($image->path),
            'alt' => $image->alt,
            'is_main' => (bool) $image->is_main,
            'order' => $image->order,
        ])->values();

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
            'updated_at_human' => optional($product->updated_at)?->diffForHumans() ?? '—',
        ]);
    }

    public function edit(Product $product)
    {
        $categories = Category::where('status', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $product->load([
            'images' => fn ($query) => $query->orderBy('order'),
            'variants' => fn ($query) => $query->orderBy('id')->with(['features.option']),
        ]);

        $options = Option::with(['features' => fn ($query) => $query->orderBy('id')])
            ->orderBy('name')
            ->get();

        return view('admin.products.edit', compact('product', 'categories', 'options'));
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
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_gallery' => 'sometimes|array',
            'remove_gallery.*' => 'integer|exists:product_images,id',
            'primary_image' => 'nullable|string',
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

        $this->syncVariants($request, $product);

        $this->handleGalleryRemovals($request, $product);
        $newImages = $this->handleGalleryUpload($request, $product, $slug);
        $this->finalizeProductImages($product, $request->input('primary_image'), $newImages);

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
    protected function handleGalleryUpload(Request $request, Product $product, string $slug): array
    {
        if (!$request->hasFile('gallery')) {
            return [];
        }

        $created = [];
        $currentMaxOrder = ProductImage::where('product_id', $product->id)->max('order');
        $orderCounter = is_numeric($currentMaxOrder) ? (int) $currentMaxOrder : -1;

        foreach ($request->file('gallery') as $index => $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $extension = $file->getClientOriginalExtension();
            $filename = $slug . '-' . time() . '-' . $index . '.' . $extension;
            $path = 'products/' . $filename;
            $file->storeAs('products', $filename, 'public');

            $order = ++$orderCounter;

            $image = ProductImage::create([
                'product_id' => $product->id,
                'path' => $path,
                'alt' => $product->name,
                'is_main' => false,
                'order' => $order,
            ]);

            $created[(int) $index] = $image;
        }

        return $created;
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

    protected function finalizeProductImages(Product $product, ?string $primaryReference, array $newImages): void
    {
        $images = $product->images()->orderBy('order')->get();

        if ($images->isEmpty()) {
            throw ValidationException::withMessages([
                'gallery' => 'Debe proporcionar al menos una imagen para el producto.',
            ]);
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

        if (!$mainImage) {
            $mainImage = $images->first();
        }

        if (!$mainImage) {
            throw ValidationException::withMessages([
                'gallery' => 'Ocurrió un problema al establecer la imagen principal.',
            ]);
        }

        $product->images()->where('id', '!=', $mainImage->id)->update(['is_main' => false]);

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

    protected function resolveProductImageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $normalized = ltrim(str_replace('\\', '/', $path), '/');

        if (str_starts_with($normalized, 'public/')) {
            $normalized = substr($normalized, 7);
        }

        if (str_starts_with($normalized, 'storage/')) {
            $normalized = substr($normalized, 8);
        }

        return asset('storage/' . ltrim($normalized, '/'));
    }

    /**
     * Sincroniza las variantes del formulario con la base de datos.
     */
    protected function syncVariants(Request $request, Product $product): void
    {
        $variantsInput = $request->input('variants', []);

        if (!is_array($variantsInput)) {
            return;
        }

        // Validación dinámica solo para filas con SKU informado
        $rules = [];
        foreach ($variantsInput as $key => $row) {
            $sku = $row['sku'] ?? null;
            if (!$sku) {
                continue;
            }

            $variantId = $row['id'] ?? null;

            $rules["variants.$key.sku"] = [
                'required',
                'string',
                'max:100',
                Rule::unique('variants', 'sku')->ignore($variantId),
            ];
            $rules["variants.$key.price"] = ['nullable', 'numeric', 'min:0'];
            $rules["variants.$key.stock"] = ['nullable', 'integer', 'min:0'];
            $rules["variants.$key.status"] = ['nullable', 'boolean'];
            $rules["variants.$key.features"] = ['nullable', 'array'];
            $rules["variants.$key.features.*"] = ['integer', Rule::exists('features', 'id')];
        }

        if (!empty($rules)) {
            Validator::make($request->all(), $rules, [], [
                'variants.*.sku' => 'SKU de variante',
                'variants.*.price' => 'precio de variante',
                'variants.*.stock' => 'stock de variante',
                'variants.*.status' => 'estado de variante',
                'variants.*.features' => 'valores de opción de la variante',
                'variants.*.features.*' => 'valor de opción de la variante',
            ])->validate();
        }

        $existing = $product->variants()->get()->keyBy('id');
        $retainedIds = [];

        foreach ($variantsInput as $row) {
            $sku = $row['sku'] ?? null;
            if (!$sku) {
                continue;
            }

            $variantId = isset($row['id']) && $row['id'] !== '' ? (int) $row['id'] : null;

            $payload = [
                'sku' => $sku,
                'price' => array_key_exists('price', $row) && $row['price'] !== null && $row['price'] !== ''
                    ? (float) $row['price']
                    : null,
                'stock' => array_key_exists('stock', $row) && $row['stock'] !== null && $row['stock'] !== ''
                    ? (int) $row['stock']
                    : 0,
                'status' => array_key_exists('status', $row)
                    ? (bool) $row['status']
                    : true,
            ];

            if ($variantId && $existing->has($variantId)) {
                $variant = $existing->get($variantId);
                $variant->fill($payload);
                $variant->updated_by = Auth::id();
                $variant->save();
            } else {
                $variant = new Variant($payload);
                $variant->product_id = $product->id;
                $variant->created_by = Auth::id();
                $variant->updated_by = Auth::id();
                $variant->save();
            }

            // Sincronizar features (valores de opciones) con la variante
            $featureIds = collect($row['features'] ?? [])
                ->filter(fn ($id) => $id !== null && $id !== '')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            $variant->features()->sync($featureIds);
            $retainedIds[] = $variant->id;
        }

        if ($existing->isNotEmpty()) {
            $idsToDelete = $existing->keys()->diff($retainedIds);
            if ($idsToDelete->isNotEmpty()) {
                Variant::whereIn('id', $idsToDelete)->delete();
            }
        }

        $this->syncProductOptionsFromVariants($product);
    }

    /**
     * Sincroniza las opciones asociadas al producto a partir de las variantes y sus valores.
     */
    protected function syncProductOptionsFromVariants(Product $product): void
    {
        $variants = $product->variants()
            ->with(['features:id,option_id'])
            ->get();

        $optionIds = $variants
            ->flatMap(fn($variant) => $variant->features->pluck('option_id'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $syncPayload = [];
        foreach ($optionIds as $optionId) {
            $syncPayload[$optionId] = ['value' => null];
        }

        $product->options()->sync($syncPayload);
    }
}
