<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Family;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategoryHierarchyController extends Controller
{
    /* ======================================================
     |  INDEX - Vista principal del Administrador Jerárquico
     ====================================================== */
    public function index()
    {
        $families = Family::where('status', true)
            ->orderBy('name')
            ->get();

        $stats = [
            'total_categories' => Category::count(),
            'root_categories' => Category::whereNull('parent_id')->count(),
            'subcategories' => Category::whereNotNull('parent_id')->count(),
            'categories_with_products' => Category::has('products')->count(),
        ];

        return view('admin.categories.hierarchy', compact('families', 'stats'));
    }

    /* ======================================================
     |  GET TREE DATA - Datos en formato jsTree
     ====================================================== */
    public function getTreeData(Request $request)
    {
        try {
            $families = Family::with(['categories' => function ($query) {
                $query->whereNull('parent_id')
                    ->with(['children' => function ($q) {
                        $q->withCount('products')
                            ->with(['children' => function ($q2) {
                                $q2->withCount('products');
                            }]);
                    }])
                    ->withCount('products');
            }])
            ->where('status', true)
            ->orderBy('name')
            ->get();

            $treeData = [];

            foreach ($families as $family) {
                // Nodo de Familia
                $familyNode = [
                    'id' => 'f_' . $family->id,
                    'text' => $family->name,
                    'icon' => 'ri-folder-3-fill',
                    'state' => [
                        'opened' => false,
                        'selected' => false
                    ],
                    'li_attr' => [
                        'data-type' => 'family',
                        'data-id' => $family->id,
                        'data-status' => $family->status ? '1' : '0'
                    ],
                    'a_attr' => [
                        'class' => 'tree-family'
                    ],
                    'children' => []
                ];

                // Categorías raíz de esta familia
                foreach ($family->categories as $category) {
                    $familyNode['children'][] = $this->buildCategoryNode($category);
                }

                $treeData[] = $familyNode;
            }

            return response()->json($treeData);

        } catch (\Exception $e) {
            Log::error('Error en getTreeData: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar datos del árbol'], 500);
        }
    }

    /* ======================================================
     |  BUILD CATEGORY NODE - Construir nodo recursivo
     ====================================================== */
    private function buildCategoryNode(Category $category)
    {
        $productsCount = $category->products_count ?? 0;
        $hasChildren = $category->children && $category->children->count() > 0;

        $node = [
            'id' => 'c_' . $category->id,
            'text' => $category->name . ($productsCount > 0 ? " ({$productsCount})" : ''),
            'icon' => $hasChildren ? 'ri-folder-line' : 'ri-file-list-3-line',
            'state' => [
                'opened' => false,
                'selected' => false
            ],
            'li_attr' => [
                'data-type' => 'category',
                'data-id' => $category->id,
                'data-parent-id' => $category->parent_id ?? '',
                'data-family-id' => $category->family_id,
                'data-status' => $category->status ? '1' : '0',
                'data-products-count' => $productsCount,
                'data-slug' => $category->slug
            ],
            'a_attr' => [
                'class' => 'tree-category' . ($category->status ? '' : ' inactive')
            ],
            'children' => []
        ];

        // Recursión para hijos
        if ($hasChildren) {
            foreach ($category->children as $child) {
                $node['children'][] = $this->buildCategoryNode($child);
            }
        }

        return $node;
    }

    /* ======================================================
     |  PREVIEW MOVE - Preview de operación de movimiento
     ====================================================== */
    public function previewMove(Request $request)
    {
        $request->validate([
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id',
            'target_type' => 'required|in:family,category',
            'target_id' => 'required|integer'
        ]);

        try {
            $categoryIds = $request->category_ids;
            $targetType = $request->target_type;
            $targetId = $request->target_id;

            $categories = Category::whereIn('id', $categoryIds)
                ->with(['products', 'children'])
                ->get();

            // Validar ciclos
            if ($targetType === 'category') {
                $targetCategory = Category::find($targetId);
                if (!$targetCategory) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Categoría destino no encontrada'
                    ], 404);
                }

                foreach ($categoryIds as $catId) {
                    if ($this->wouldCreateCycle($catId, $targetId)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'No se puede mover: crearía una referencia circular'
                        ], 422);
                    }
                }
            }

            // Calcular impacto
            $totalProducts = 0;
            $totalSubcategories = 0;
            $slugsToChange = [];

            foreach ($categories as $category) {
                $totalProducts += $category->products->count();
                $totalSubcategories += $this->countAllChildren($category);
                $slugsToChange[] = $category->slug;
            }

            $targetName = $targetType === 'family' 
                ? Family::find($targetId)->name 
                : Category::find($targetId)->name;

            return response()->json([
                'success' => true,
                'preview' => [
                    'categories_count' => count($categoryIds),
                    'target_type' => $targetType,
                    'target_name' => $targetName,
                    'total_products' => $totalProducts,
                    'total_subcategories' => $totalSubcategories,
                    'slugs_to_change' => $slugsToChange,
                    'will_affect_seo' => count($slugsToChange) > 0
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en previewMove: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar preview'
            ], 500);
        }
    }

    /* ======================================================
     |  BULK MOVE - Mover categorías en masa
     ====================================================== */
    public function bulkMove(Request $request)
    {
        $request->validate([
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id',
            'target_type' => 'required|in:family,category,root',
            'target_id' => 'nullable|integer'
        ]);

        DB::beginTransaction();

        try {
            $categoryIds = $request->category_ids;
            $targetType = $request->target_type;
            $targetId = $request->target_id;

            $categories = Category::whereIn('id', $categoryIds)->get();

            foreach ($categories as $category) {
                if ($targetType === 'family') {
                    // Mover a familia (como categoría raíz)
                    $category->family_id = $targetId;
                    $category->parent_id = null;
                } elseif ($targetType === 'category') {
                    // Mover como subcategoría
                    $targetCategory = Category::find($targetId);
                    $category->family_id = $targetCategory->family_id;
                    $category->parent_id = $targetId;
                } elseif ($targetType === 'root') {
                    // Convertir en raíz manteniendo familia actual
                    $category->parent_id = null;
                }

                $category->updated_by = Auth::id();
                $category->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($categoryIds) === 1 
                    ? 'Categoría movida correctamente' 
                    : count($categoryIds) . ' categorías movidas correctamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en bulkMove: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al mover categorías: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ======================================================
     |  BULK DELETE - Eliminar categorías en masa
     ====================================================== */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id'
        ]);

        DB::beginTransaction();

        try {
            $categoryIds = $request->category_ids;
            
            // Verificar si tienen productos
            $categoriesWithProducts = Category::whereIn('id', $categoryIds)
                ->has('products')
                ->count();

            if ($categoriesWithProducts > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "No se pueden eliminar {$categoriesWithProducts} categorías porque tienen productos asociados"
                ], 422);
            }

            // Eliminar categorías y sus hijos
            foreach ($categoryIds as $categoryId) {
                $category = Category::find($categoryId);
                if ($category) {
                    $this->deleteWithChildren($category);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($categoryIds) === 1 
                    ? 'Categoría eliminada correctamente' 
                    : count($categoryIds) . ' categorías eliminadas correctamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en bulkDelete: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar categorías: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ======================================================
     |  BULK DUPLICATE - Duplicar categorías con su estructura
     ====================================================== */
    public function bulkDuplicate(Request $request)
    {
        $request->validate([
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id'
        ]);

        DB::beginTransaction();

        try {
            $categoryIds = $request->category_ids;
            $duplicatedCount = 0;

            foreach ($categoryIds as $categoryId) {
                $category = Category::find($categoryId);
                if ($category) {
                    $this->duplicateCategory($category);
                    $duplicatedCount++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $duplicatedCount === 1 
                    ? 'Categoría duplicada correctamente' 
                    : $duplicatedCount . ' categorías duplicadas correctamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en bulkDuplicate: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al duplicar categorías: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ======================================================
     |  HELPERS - Funciones auxiliares
     ====================================================== */

    private function wouldCreateCycle($categoryId, $targetParentId)
    {
        if ($categoryId == $targetParentId) {
            return true;
        }

        $parent = Category::find($targetParentId);
        while ($parent) {
            if ($parent->id == $categoryId) {
                return true;
            }
            $parent = $parent->parent;
        }

        return false;
    }

    private function countAllChildren(Category $category)
    {
        $count = $category->children->count();
        foreach ($category->children as $child) {
            $count += $this->countAllChildren($child);
        }
        return $count;
    }

    private function deleteWithChildren(Category $category)
    {
        foreach ($category->children as $child) {
            $this->deleteWithChildren($child);
        }
        $category->delete();
    }

    private function duplicateCategory(Category $original, $newParentId = null)
    {
        $duplicate = $original->replicate();
        $duplicate->name = $original->name . ' (Copia)';
        $duplicate->slug = Category::generateUniqueSlug($duplicate->name);
        $duplicate->parent_id = $newParentId ?? $original->parent_id;
        $duplicate->created_by = Auth::id();
        $duplicate->updated_by = Auth::id();
        $duplicate->save();

        // Duplicar hijos recursivamente
        foreach ($original->children as $child) {
            $this->duplicateCategory($child, $duplicate->id);
        }

        return $duplicate;
    }

    /* ======================================================
     |  DRAG MOVE - Mover categoría mediante drag & drop
     ====================================================== */
    public function dragMove(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'family_id' => 'required|exists:families,id',
            'parent_id' => 'nullable|exists:categories,id'
        ]);

        DB::beginTransaction();

        try {
            $category = Category::findOrFail($request->category_id);
            $oldFamilyId = $category->family_id;
            $oldParentId = $category->parent_id;

            // Validar que no se cree un ciclo
            if ($request->parent_id) {
                if ($this->wouldCreateCycle($request->category_id, $request->parent_id)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se puede mover: se crearía una referencia circular'
                    ], 422);
                }
            }

            // Actualizar la categoría
            $category->family_id = $request->family_id;
            $category->parent_id = $request->parent_id;
            $category->updated_by = Auth::id();
            $category->save();

            DB::commit();

            // Obtener nombres para el mensaje
            $family = Family::find($request->family_id);
            $parentName = $request->parent_id 
                ? Category::find($request->parent_id)->name 
                : 'raíz';

            return response()->json([
                'success' => true,
                'message' => "'{$category->name}' movida a {$family->name} → {$parentName}",
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'family_id' => $category->family_id,
                    'parent_id' => $category->parent_id
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en dragMove: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al mover la categoría: ' . $e->getMessage()
            ], 500);
        }
    }
}
