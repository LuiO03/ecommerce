# 🗑️ Guía de Eliminación Múltiple Global

## 📋 Descripción
Se ha extraído la lógica de eliminación múltiple al archivo `modal-confirm.js` para que funcione como una utilidad global reutilizable en cualquier módulo del sistema.

## ✅ Características
- **Reutilizable**: Funciona con cualquier entidad (familias, usuarios, productos, etc.)
- **Inteligente**: Manejo automático de géneros y plurales en español
- **Consistente**: Mantiene el mismo UX que la eliminación individual
- **Segura**: Validaciones y confirmaciones antes de eliminar
- **Flexible**: Acepta tanto `Set` como `Array` de IDs

## 🚀 Cómo usar en cualquier módulo

### 1. **Incluir el archivo JS**
El archivo `modal-confirm.js` debe estar incluido en tu vista (ya está configurado globalmente).

### 2. **Preparar los datos**
```javascript
// Tener los IDs seleccionados (Set o Array)
let selectedIds = new Set(); // o Array

// Función para obtener el nombre por ID
function getEntityNameById(id) {
    const checkbox = $(`input[value="${id}"]`);
    const row = checkbox.closest('tr');
    return row.find('.column-name-td').text().trim();
}
```

### 3. **Llamar a la función global**
```javascript
$('#deleteSelectedBtn').on('click', function() {
    handleMultipleDelete({
        selectedIds: selectedIds,                    // Set/Array con IDs
        getNameCallback: getEntityNameById,         // Función para obtener nombres  
        entityName: 'usuario',                      // Nombre de la entidad
        deleteRoute: '/admin/users/destroy-multiple', // Ruta del controlador
        csrfToken: '{{ csrf_token() }}',           // Token CSRF
        buttonSelector: '#deleteSelectedBtn'        // Botón para deshabilitar (opcional)
    });
});
```

## 🎯 Controlador Backend

### Método requerido en tu controlador:
```php
public function destroyMultiple(Request $request)
{
    $request->validate([
        'ids' => 'required|array|min:1',
        'ids.*' => 'exists:tu_tabla,id'  // Cambiar 'tu_tabla' por tu tabla
    ]);

    $ids = $request->ids;
    $entities = TuModelo::whereIn('id', $ids)->get(); // Cambiar TuModelo
    $count = $entities->count();

    if ($count === 0) {
        Session::flash('info', [
            'type' => 'danger',
            'header' => 'Error',
            'title' => 'Error en la eliminación',
            'message' => 'No se encontraron registros para eliminar.',
        ]);
        return redirect()->back();
    }

    // Obtener nombres para el mensaje
    $names = $entities->pluck('name')->toArray();
    
    // Eliminar
    TuModelo::whereIn('id', $ids)->delete();

    // Crear mensaje
    if ($count === 1) {
        $message = "El registro \"{$names[0]}\" ha sido eliminado correctamente.";
    } else {
        $nameList = implode('', array_map(fn($name) => "<li><strong>{$name}</strong></li>", $names));
        $message = "Se han eliminado {$count} registros correctamente:<br><ul>{$nameList}</ul>";
    }

    Session::flash('info', [
        'type' => 'danger', 
        'header' => 'Registro eliminado',
        'title' => $count === 1 ? 'Registro eliminado' : 'Registros eliminados',
        'message' => $message,
    ]);

    return redirect()->back();
}
```

### Ruta requerida:
```php
// En routes/admin.php o web.php
Route::delete('/tu-modulo/destroy-multiple', [TuController::class, 'destroyMultiple'])
    ->name('admin.tu-modulo.destroy-multiple');
```

## 🔤 Entidades Soportadas

La función maneja automáticamente el género y plurales de estas entidades:
- `familia` → `familias` (femenino)
- `usuario` → `usuarios` (masculino)  
- `producto` → `productos` (masculino)
- `categoría` → `categorías` (femenino)
- `subcategoría` → `subcategorías` (femenino)
- `característica` → `características` (femenino)
- `opción` → `opciones` (femenino)
- `variante` → `variantes` (femenino)
- `imagen` → `imágenes` (femenino)
- `registro` → `registros` (masculino)

Para nuevas entidades, se agregará automáticamente una `s` al final.

## 💡 Ventajas

1. **DRY**: No repetir código de eliminación múltiple en cada módulo
2. **Consistencia**: Misma UX en toda la aplicación
3. **Mantenibilidad**: Un solo lugar para actualizar la lógica
4. **Flexibilidad**: Funciona con cualquier estructura de datos
5. **Localización**: Manejo correcto del español (géneros, plurales)

## ⚠️ Notas Importantes

- La función **NO elimina** la lógica de eliminación individual existente
- Requiere que el controlador acepte el parámetro `ids[]`
- El botón se deshabilita automáticamente durante el proceso
- Fallback a `ID: xxx` si no se puede obtener el nombre de un elemento