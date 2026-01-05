# CategoryHierarchy (nativo) - Gestor visual de árbol de categorías

## Descripción general

El módulo **category-hierarchy.js** implementa un gestor visual moderno para la jerarquía de categorías en el admin, sin depender de jsTree.

Permite:
- Mostrar familias y sus categorías en tarjetas tipo árbol.
- Seleccionar múltiples categorías para operaciones masivas.
- Soportar drag & drop de categorías entre familias y dentro de la misma familia (vía SortableJS).
- Disparar acciones de edición, eliminación y operaciones en lote.

**Archivo JS:** `resources/js/modules/category-hierarchy.js`

Se apoya en la configuración global `window.hierarchyConfig` (URLs de API y rutas de edición) y en endpoints del controlador `CategoryHierarchyController`.

## Requisitos de backend

- Controlador: `app/Http/Controllers/Admin/CategoryHierarchyController.php`.
- Rutas principales (ejemplo):
  - `admin.categories.hierarchy` → vista principal.
  - `admin.categories.hierarchy.tree-data` → JSON del árbol.
  - `admin.categories.hierarchy.bulk-move`, `bulk-delete`, `bulk-duplicate`, `drag-move`, `preview-move`.

El endpoint `tree-data` debe devolver un array de familias con esta estructura (similar al antiguo jsTree):

```json
[
  {
    "text": "Electrónica",
    "li_attr": {
      "data-id": 1,
      "data-slug": "electronica",
      "data-status": "1"
    },
    "children": [
      {
        "text": "Laptops (5)",
        "li_attr": {
          "data-id": 10,
          "data-slug": "laptops",
          "data-status": "1",
          "data-products-count": 5
        },
        "children": []
      }
    ]
  }
]
```

## Inicialización

En la vista Blade principal de jerarquía:

```blade
<script>
    window.hierarchyConfig = {
        treeDataUrl: '{{ route('admin.categories.hierarchy.tree-data') }}',
        editCategoryUrl: '{{ route('admin.categories.edit', ':id') }}',
        bulkMoveUrl: '{{ route('admin.categories.hierarchy.bulk-move') }}',
        bulkDeleteUrl: '{{ route('admin.categories.hierarchy.bulk-delete') }}',
        bulkDuplicateUrl: '{{ route('admin.categories.hierarchy.bulk-duplicate') }}',
        dragMoveUrl: '{{ route('admin.categories.hierarchy.drag-move') }}',
        previewMoveUrl: '{{ route('admin.categories.hierarchy.preview-move') }}',
        csrfToken: '{{ csrf_token() }}',
    };

    document.addEventListener('DOMContentLoaded', () => {
        window.hierarchyManager = new CategoryHierarchyManager();
    });
</script>
```

### Elementos HTML clave

- Contenedor principal del árbol:

```blade
<div id="categoryTree" class="category-tree"></div>
```

- Controles de filtros y operaciones masivas (según diseño de la vista):
  - Botones para expandir/colapsar familias.
  - Controles para mover/duplicar/eliminar selección.

## Componentes visuales

### Tarjeta de familia (`.family-card`)

- Muestra el nombre de la familia y el número de categorías.
- Contiene un contenedor `.family-children` donde se montan los items de categoría.
- Se inicializa `Sortable` sobre `.family-children` para aceptar drop de categorías (incluso si está vacío).

### Item de categoría (`.category-item`)

Cada categoría se representa como:

- `data-category-id` y `data-category-data` (JSON serializado del nodo).
- Tarjeta interna `.category-card` con:
  - Handle de drag (`.category-drag-handle`) con ícono `ri-draggable`.
  - Toggle de expandir/colapsar hijos.
  - Checkbox para selección múltiple.
  - Ícono de carpeta o archivo según tenga hijos o no.
  - Badges de número de productos y estado (activo / inactivo).
  - Botones de acción (editar, eliminar) que usan `editCategoryUrl` y `deleteCategory(...)`.

## Comportamiento principal

- **Carga de árbol** (`loadTreeData`):
  - Muestra un estado de carga (`showLoadingSpinner`).
  - Llama a `config.treeDataUrl` vía `fetch`.
  - En caso de éxito, construye tarjetas de familias + categorías (`renderTree`).
  - Si no hay datos, muestra estado vacío (`showEmptyState`).

- **Selección de categorías**:
  - Cada `category-item` tiene un checkbox.
  - El Manager mantiene un `Set` de IDs seleccionados (`selectedNodes`).
  - La selección se usa para operaciones masivas (mover, duplicar, eliminar).

- **Drag & Drop**:
  - Se usa **SortableJS** sobre contenedores de categorías.
  - Permite:
    - Reordenar dentro de la misma familia.
    - Mover categorías entre familias.
  - Los movimientos disparan llamadas a `dragMoveUrl` (o similar) con el nuevo `parent_id` y orden.

- **Acciones por categoría**:
  - Editar: redirige usando `editCategoryUrl.replace(':id', slug)`.
  - Eliminar: llama a `deleteCategory(slug, nombre, id)` que a su vez muestra un modal de confirmación y envía la petición al backend.

## Estados de UI

- Estado de carga: `tree-loading-state` con spinner y mensaje.
- Estado vacío: `tree-empty-state` con ícono y botón para ir a familias.
- Estado de error: `tree-error-state` con botón "Reintentar".

## Relación con otros docs

- Para la gestión de jerarquía en formularios (selects en cascada), ver
  `docs/category-hierarchy-manager.md`.
- Para drag & drop de categorías en vistas de lista/árbol adicionales, ver
  `docs/category-drag-drop.md`.
