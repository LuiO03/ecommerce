# 🎯 Category Drag & Drop - Documentación

## Descripción General

Sistema de arrastrar y soltar (drag & drop) para reorganizar categorías en el **Administrador Jerárquico** usando **SortableJS**.

## Características

✅ **Arrastrar categorías** entre diferentes familias  
✅ **Crear subcategorías** arrastrando dentro de otras categorías  
✅ **Validación de ciclos** - previene referencias circulares  
✅ **Persistencia automática** - guarda cambios en el backend  
✅ **Feedback visual** con toasts de éxito/error  
✅ **Recarga automática** del árbol después de mover

---

## Funcionamiento

### 1. Frontend (JavaScript)

**Archivo:** `resources/js/modules/category-hierarchy.js`

```javascript
initSortable(container) {
    const sortable = new Sortable(container, {
        group: 'categories',           // Permite drag entre contenedores
        animation: 200,                // Animación suave
        handle: '.category-drag-handle', // Solo arrastra desde el icono
        ghostClass: 'dragging',        // Clase durante el drag
        dragClass: 'drag-over',        // Clase en el destino
        onEnd: async (evt) => {
            // Detecta nueva posición y persiste al backend
        }
    });
}
```

### 2. Backend (Laravel)

**Controlador:** `app/Http/Controllers/Admin/CategoryHierarchyController.php`

**Método:** `dragMove(Request $request)`

**Parámetros:**
- `category_id` - ID de la categoría a mover
- `family_id` - ID de la familia destino
- `parent_id` - ID del padre (null = raíz)

**Validaciones:**
- ✅ Verifica que la categoría existe
- ✅ Previene ciclos (categoría padre de sí misma)
- ✅ Actualiza `family_id` y `parent_id`
- ✅ Registra `updated_by` para auditoría

---

## Ruta API

```php
POST /admin/categories/hierarchy/drag-move
```

**Request:**
```json
{
    "category_id": 5,
    "family_id": 2,
    "parent_id": null  // null = raíz, número = subcategoría
}
```

**Response (Éxito):**
```json
{
    "success": true,
    "message": "'Laptops' movida a Electrónica → raíz",
    "category": {
        "id": 5,
        "name": "Laptops",
        "family_id": 2,
        "parent_id": null
    }
}
```

**Response (Error):**
```json
{
    "success": false,
    "message": "No se puede mover: se crearía una referencia circular"
}
```

---

## Estilos CSS

**Archivo:** `resources/css/modules/category-hierarchy.css`

### Estados Visuales

```css
/* Durante el arrastre */
.category-card.dragging {
    opacity: 0.5;
    cursor: grabbing;
    transform: rotate(2deg);
}

/* Zona de destino válida */
.category-card.drag-over {
    border-color: var(--color-success);
    background: var(--color-success-pastel);
}

/* Elemento fantasma */
.sortable-ghost {
    opacity: 0.4;
    background: var(--color-primary-pastel);
    border: 2px dashed var(--color-primary);
}
```

### Handle (Icono de arrastre)

```css
.category-drag-handle {
    opacity: 0;  /* Oculto por defecto */
    cursor: grab;
}

.category-card:hover .category-drag-handle {
    opacity: 1;  /* Visible al pasar el mouse */
}
```

---

## Toasts de Notificación

El sistema usa el componente global de toasts:

**Éxito:**
```javascript
window.showToast({
    type: 'success',
    title: 'Categoría movida',
    message: "'Laptops' movida a Electrónica → raíz",
    duration: 3000
});
```

**Error:**
```javascript
window.showToast({
    type: 'danger',
    title: 'Error',
    message: 'No se pudo mover la categoría',
    duration: 4000
});
```

---

## Flujo Completo

### 1️⃣ Usuario arrastra categoría
```
Usuario → Toma el icono de arrastre (ri-draggable)
         ↓
SortableJS → Detecta inicio de drag
         ↓
Aplica clase "dragging" al elemento
```

### 2️⃣ Usuario suelta en destino
```
SortableJS → Evento onEnd
         ↓
JavaScript → Calcula nuevo parent_id y family_id
         ↓
Fetch → POST /admin/categories/hierarchy/drag-move
```

### 3️⃣ Backend procesa
```
Laravel → Valida datos
       → Previene ciclos
       → Actualiza BD
       → Retorna JSON
```

### 4️⃣ Feedback al usuario
```
JavaScript → Muestra toast de éxito/error
          → Recarga árbol para reflejar cambios
```

---

## Configuración en Blade

**Vista:** `resources/views/admin/categories/hierarchy.blade.php`

```blade
@push('scripts')
<script>
window.hierarchyConfig = {
    dragMoveUrl: '{{ route('admin.categories.hierarchy.drag-move') }}',
    csrfToken: '{{ csrf_token() }}'
};
</script>
@endpush
```

---

## Casos de Uso

### ✅ Caso 1: Mover a otra familia
```
Antes: Laptops → Familia "Ropa"
Después: Laptops → Familia "Electrónica" (raíz)
```

### ✅ Caso 2: Convertir en subcategoría
```
Antes: Gaming → Raíz de "Electrónica"
Después: Gaming → Subcategoría de "Laptops"
```

### ✅ Caso 3: Cambiar de padre
```
Antes: Teclados → Subcategoría de "Accesorios"
Después: Teclados → Subcategoría de "Gaming"
```

### ❌ Caso 4: Ciclo detectado (bloqueado)
```
❌ NO permitido:
Laptops (id: 5)
  └─ Gaming (id: 8)
  
Intentar: Hacer a "Laptops" hijo de "Gaming"
Resultado: Error - "se crearía una referencia circular"
```

---

## Troubleshooting

### ❌ No aparece el icono de arrastre
**Solución:** Verifica que el CSS esté compilado
```bash
npm run build
```

### ❌ El drag no funciona
**Solución:** Verifica que SortableJS esté instalado
```bash
npm install sortablejs
```

### ❌ No se guardan los cambios
**Solución:** Revisa la consola del navegador
- Verifica que `dragMoveUrl` esté definido
- Confirma que el CSRF token es correcto
- Revisa los logs de Laravel: `php artisan pail`

### ❌ Error 422 (Validación)
**Causas comunes:**
- ID de categoría inválido
- ID de familia no existe
- Se intenta crear un ciclo

---

## Dependencias

### NPM
```json
{
  "dependencies": {
    "sortablejs": "^1.15.6"
  }
}
```

### PHP/Laravel
```bash
composer require laravel/framework
```

---

## Extensiones Futuras

🔮 **Posibles mejoras:**

1. **Ordenamiento persistente** - Guardar orden específico de categorías
2. **Drag entre pestañas** - Mover entre vista tabla y jerarquía
3. **Undo/Redo** - Deshacer movimientos
4. **Preview visual** - Mostrar vista previa antes de soltar
5. **Animación del árbol** - Expandir automáticamente destino

---

## Referencias

- 📦 [SortableJS Documentación](https://github.com/SortableJS/Sortable)
- 🎨 [Remix Icon](https://remixicon.com/)
- 🚀 [Laravel Validation](https://laravel.com/docs/validation)
- 🎯 [Fetch API](https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API)

---

**Última actualización:** 20 de noviembre de 2025  
**Versión:** 1.0.0  
**Autor:** GECKОМERCE Team
