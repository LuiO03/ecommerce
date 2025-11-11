// ========================================
// 📋 EJEMPLO DE USO - ELIMINACIÓN MÚLTIPLE
// ========================================

/**
 * Ejemplo de cómo usar handleMultipleDelete() en cualquier módulo
 * 
 * Requisitos previos:
 * 1. Incluir modal-confirm.js en tu vista
 * 2. Tener un Set o Array con los IDs seleccionados
 * 3. Crear una función que obtenga el nombre por ID
 * 4. Definir la ruta de eliminación múltiple
 */

// EJEMPLO 1: Para módulo de usuarios
function deleteSelectedUsers() {
    // Función para obtener nombre del usuario por ID
    function getUserNameById(id) {
        const checkbox = $(`input[value="${id}"]`);
        const row = checkbox.closest('tr');
        return row.find('.column-name-td').text().trim();
    }

    // Llamar a la función global
    handleMultipleDelete({
        selectedIds: selectedUserIds, // Set o Array con IDs
        getNameCallback: getUserNameById,
        entityName: 'usuario',
        deleteRoute: '/admin/users/destroy-multiple',
        csrfToken: document.querySelector('meta[name="csrf-token"]').content,
        buttonSelector: '#deleteSelectedUsers'
    });
}

// EJEMPLO 2: Para módulo de productos  
function deleteSelectedProducts() {
    function getProductNameById(id) {
        const checkbox = $(`input[value="${id}"]`);
        const row = checkbox.closest('tr');
        return row.find('.product-name').text().trim();
    }

    handleMultipleDelete({
        selectedIds: selectedProductIds,
        getNameCallback: getProductNameById, 
        entityName: 'producto',
        deleteRoute: '/admin/products/destroy-multiple',
        csrfToken: $('meta[name="csrf-token"]').attr('content'),
        buttonSelector: '#deleteSelectedProducts'
    });
}

// EJEMPLO 3: Para módulo de categorías
function deleteSelectedCategories() {
    function getCategoryNameById(id) {
        const row = $(`.check-row[value="${id}"]`).closest('tr');
        return row.find('[data-category-name]').text().trim();
    }

    handleMultipleDelete({
        selectedIds: Array.from(selectedCategoryIds), // Convertir Set a Array
        getNameCallback: getCategoryNameById,
        entityName: 'categoría', 
        deleteRoute: route('admin.categories.destroy-multiple'), // Si usas Ziggy
        csrfToken: Laravel.csrfToken, // Si usas Laravel global
        buttonSelector: '#deleteCategoriesBtn'
    });
}

// ========================================
// 🎯 CONTROLADOR BACKEND REQUERIDO
// ========================================

/**
 * Tu controlador debe tener un método como este:
 * 
 * public function destroyMultiple(Request $request)
 * {
 *     $request->validate([
 *         'ids' => 'required|array|min:1',
 *         'ids.*' => 'exists:tu_tabla,id'
 *     ]);
 * 
 *     $ids = $request->ids;
 *     $entities = TuModelo::whereIn('id', $ids)->get();
 *     $count = $entities->count();
 * 
 *     if ($count === 0) {
 *         Session::flash('info', [
 *             'type' => 'danger',
 *             'header' => 'Error',
 *             'title' => 'Error en la eliminación',
 *             'message' => 'No se encontraron registros para eliminar.',
 *         ]);
 *         return redirect()->back();
 *     }
 * 
 *     // Obtener nombres para el mensaje
 *     $names = $entities->pluck('name')->toArray();
 *     
 *     // Eliminar
 *     TuModelo::whereIn('id', $ids)->delete();
 * 
 *     // Crear mensaje
 *     if ($count === 1) {
 *         $message = "El registro \"{$names[0]}\" ha sido eliminado correctamente.";
 *     } else {
 *         $nameList = implode('', array_map(fn($name) => "<li><strong>{$name}</strong></li>", $names));
 *         $message = "Se han eliminado {$count} registros correctamente:<br><ul>{$nameList}</ul>";
 *     }
 * 
 *     Session::flash('info', [
 *         'type' => 'danger',
 *         'header' => 'Registro eliminado',
 *         'title' => $count === 1 ? 'Registro eliminado' : 'Registros eliminados',
 *         'message' => $message,
 *     ]);
 * 
 *     return redirect()->back();
 * }
 */

// ========================================
// 📝 RUTA REQUERIDA  
// ========================================

/**
 * En tu archivo de rutas (web.php o admin.php):
 * 
 * Route::delete('/tu-modulo/destroy-multiple', [TuController::class, 'destroyMultiple'])
 *     ->name('admin.tu-modulo.destroy-multiple');
 */