# CategoryHierarchyManager - Documentación

## Descripción General

El módulo **CategoryHierarchyManager** proporciona un sistema completo de selección jerárquica de categorías en cascada. Permite a los usuarios navegar por una jerarquía multinivel de categorías mediante selects dinámicos que se generan según la selección previa.

**Ubicación:** `resources/js/modules/category-hierarchy-manager.js`

## Características Principales

- ✅ **Selección Jerárquica en Cascada**: Genera selects dinámicos para cada nivel de la jerarquía
- ✅ **Modo Creación y Edición**: Soporta tanto creación de nuevas categorías como edición de existentes
- ✅ **Reconstrucción de Jerarquía**: En modo edición, reconstruye automáticamente el camino de selección
- ✅ **Exclusión Automática**: Previene que una categoría sea su propio padre
- ✅ **Breadcrumb Visual**: Muestra la ruta completa de selección en tiempo real
- ✅ **Animaciones Suaves**: Transiciones CSS para mejor UX
- ✅ **Múltiples Formatos de Datos**: Soporta datos jerárquicos (create) y planos (edit)

## Instalación

El módulo está exportado globalmente en `resources/js/index.js`:

```javascript
import { initCategoryHierarchy } from './modules/category-hierarchy-manager.js';
window.initCategoryHierarchy = initCategoryHierarchy;
```

## Uso Básico

### Modo Creación

```javascript
document.addEventListener('DOMContentLoaded', function() {
    const hierarchyManager = initCategoryHierarchy({
        categoriesData: categoriesArray, // Array de categorías (ver estructura)
        initialFamilyId: 5,              // ID de familia preseleccionada (opcional)
        initialParentId: 12              // ID de padre preseleccionado (opcional)
    });
});
```

### Modo Edición

```javascript
document.addEventListener('DOMContentLoaded', function() {
    const hierarchyManager = initCategoryHierarchy({
        categoriesData: categoriesArray,  // Array de categorías (ver estructura)
        currentCategoryId: 25,            // ID de la categoría actual (EXCLUIR)
        initialFamilyId: 5,               // ID de familia actual
        initialParentId: 12               // ID de padre actual (para reconstruir)
    });
});
```

## Configuración

### Objeto de Configuración

```javascript
{
    categoriesData: Array,      // REQUERIDO - Array de objetos de categorías
    currentCategoryId: Number,  // OPCIONAL - ID de categoría actual (modo edición)
    initialFamilyId: Number,    // OPCIONAL - ID de familia inicial
    initialParentId: Number     // OPCIONAL - ID de padre inicial
}
```

## Estructura de Datos

El módulo acepta dos tipos de estructura de datos:

### 1. Jerárquica (para Create)

Estructura anidada donde `children` contiene subcategorías:

```javascript
[
    {
        id: 1,
        name: "Electrónica",
        family_id: 5,
        parent_id: null,
        children: [
            {
                id: 10,
                name: "Computadoras",
                family_id: 5,
                parent_id: 1,
                children: [
                    {
                        id: 20,
                        name: "Laptops",
                        family_id: 5,
                        parent_id: 10,
                        children: []
                    }
                ]
            }
        ]
    }
]
```

### 2. Plana (para Edit)

Array plano donde `parent_id` establece la relación:

```javascript
[
    {
        id: 1,
        name: "Electrónica",
        family_id: 5,
        parent_id: null
    },
    {
        id: 10,
        name: "Computadoras",
        family_id: 5,
        parent_id: 1
    },
    {
        id: 20,
        name: "Laptops",
        family_id: 5,
        parent_id: 10
    }
]
```

## Integración con Laravel Blade

### Vista Create (`resources/views/admin/categories/create.blade.php`)

```blade
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const hierarchyManager = initCategoryHierarchy({
            categoriesData: {!! json_encode($allCategories) !!},
            initialFamilyId: '{{ old("family_id") }}' ? parseInt('{{ old("family_id") }}') : null,
            initialParentId: '{{ old("parent_id") }}' ? parseInt('{{ old("parent_id") }}') : null
        });
    });
</script>
```

**Controlador (Create):**

```php
public function create()
{
    $families = Family::where('status', true)->orderBy('name')->get();
    
    // Datos jerárquicos con children
    $allCategories = Category::with('childrenRecursive')
        ->whereNull('parent_id')
        ->where('status', true)
        ->orderBy('name')
        ->get()
        ->toArray();

    return view('admin.categories.create', compact('families', 'allCategories'));
}
```

### Vista Edit (`resources/views/admin/categories/edit.blade.php`)

```blade
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const hierarchyManager = initCategoryHierarchy({
            categoriesData: {!! json_encode($parents->map(function($cat) {
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'family_id' => $cat->family_id,
                    'parent_id' => $cat->parent_id,
                ];
            })) !!},
            currentCategoryId: {{ $category->id }},
            initialFamilyId: parseInt('{{ old("family_id", $category->family_id) }}'),
            initialParentId: parseInt('{{ old("parent_id", $category->parent_id ?? 0) }}') || null
        });
    });
</script>
```

**Controlador (Edit):**

```php
public function edit(Category $category)
{
    $families = Family::where('status', true)->orderBy('name')->get();
    
    // Datos planos excluyendo categoría actual y sus descendientes
    $excludedIds = [$category->id];
    $this->getDescendantIds($category->id, $excludedIds);
    
    $parents = Category::whereNotIn('id', $excludedIds)
        ->where('status', true)
        ->orderBy('name')
        ->get();

    return view('admin.categories.edit', compact('category', 'families', 'parents'));
}

private function getDescendantIds($categoryId, &$excludedIds)
{
    $children = Category::where('parent_id', $categoryId)->pluck('id');
    foreach ($children as $childId) {
        $excludedIds[] = $childId;
        $this->getDescendantIds($childId, $excludedIds);
    }
}
```

## Elementos HTML Requeridos

El módulo espera la siguiente estructura HTML en la vista:

```blade
<!-- Select de Familia (trigger) -->
<select name="family_id" id="family_select" class="select-form" required>
    <option value="">Seleccione una familia</option>
    @foreach ($families as $family)
        <option value="{{ $family->id }}">{{ $family->name }}</option>
    @endforeach
</select>

<!-- Hidden input para almacenar parent_id seleccionado -->
<input type="hidden" name="parent_id" id="parent_id" value="">

<!-- Contenedor dinámico donde se generarán los selects -->
<div id="categoryHierarchySelects" style="display: none;"></div>

<!-- Mensaje cuando no hay familia seleccionada -->
<span id="noFamilyMessage" class="label-hint">
    Primero selecciona una familia para ver las categorías disponibles
</span>

<!-- Breadcrumb visual de la ruta -->
<div id="hierarchyBreadcrumb" style="display: none;">
    <i class="ri-route-line"></i>
    <strong>Ruta seleccionada:</strong>
    <span id="breadcrumbPath"></span>
</div>
```

## Funcionamiento Interno

### 1. Inicialización

Al seleccionar una familia en `#family_select`:

1. Filtra categorías raíz (`parent_id = null`) de esa familia
2. Si hay categorías disponibles, genera el primer select
3. Actualiza el `#noFamilyMessage` según disponibilidad

### 2. Selección en Cascada

Al seleccionar una categoría en cualquier nivel:

1. Elimina todos los selects de niveles posteriores
2. Actualiza el hidden input `#parent_id` con el ID seleccionado
3. Si la categoría tiene hijos, genera el siguiente nivel de select
4. Actualiza el breadcrumb visual con la ruta completa

### 3. Reconstrucción (Modo Edición)

Si se proporciona `initialParentId`:

1. Construye el camino completo desde la raíz hasta el padre
2. Simula la selección en cascada con delays (100ms entre niveles)
3. Restaura el estado visual exacto de la jerarquía

## Métodos de la Clase

### `CategoryHierarchyManager`

| Método | Descripción |
|--------|-------------|
| `constructor(config)` | Inicializa el manager con configuración |
| `loadCategoriesForFamily(familyId)` | Carga categorías raíz de una familia |
| `createLevelSelect(level, categories, parentName)` | Genera un select para un nivel específico |
| `handleLevelChange(level, selectedId)` | Maneja el cambio de selección en un nivel |
| `removeSelectsAfterLevel(level)` | Elimina selects de niveles posteriores |
| `resetCategorySelects()` | Resetea toda la jerarquía |
| `updateBreadcrumb(path)` | Actualiza la visualización del breadcrumb |
| `findCategoryById(id)` | Busca una categoría por ID |
| `findCategoryByIdHierarchical(id, categories)` | Búsqueda recursiva en estructura jerárquica |
| `hasChildren(parentId)` | Verifica si una categoría tiene hijos |
| `hasChildrenHierarchical(category)` | Verifica hijos en estructura jerárquica |
| `getChildren(parentId)` | Obtiene hijos directos de una categoría |
| `getChildrenHierarchical(parentId, categories)` | Obtiene hijos en estructura jerárquica |
| `reconstructHierarchy(targetParentId)` | Reconstruye la selección jerárquica |

## Personalización CSS

El módulo aplica estilos inline pero respeta variables CSS globales:

```css
/* Variables recomendadas */
:root {
    --color-text-light: #6b7280;
    --color-info: #3b82f6;
    --color-info-pastel: #dbeafe;
}

/* Clase generada dinámicamente */
.hierarchy-select-wrapper {
    margin-top: 0.75rem;
    opacity: 0;
    transform: translateY(-10px);
    transition: all 0.3s ease;
}
```

## Eventos del DOM

| Evento | Elemento | Descripción |
|--------|----------|-------------|
| `change` | `#family_select` | Dispara `loadCategoriesForFamily()` |
| `change` | `.category-level-select` | Dispara `handleLevelChange()` |
| `DOMContentLoaded` | `document` | Punto de entrada recomendado |

## Debugging

Para activar logs de debug, agrega al inicio del módulo:

```javascript
const DEBUG = true;

if (DEBUG) {
    console.log('[CategoryHierarchy] Initialized with config:', config);
}
```

## Limitaciones Conocidas

1. **Profundidad Ilimitada**: No hay límite de niveles, pero más de 5 puede afectar UX
2. **Performance**: Con +1000 categorías, considera lazy loading
3. **Validación**: El módulo no valida datos, asume estructura correcta

## Migración desde Código Inline

### Antes (200+ líneas inline)

```javascript
// Código duplicado en create.blade.php y edit.blade.php
const categoriesData = @json($categories);
const familySelect = document.getElementById('family_select');
// ... 200 líneas de lógica ...
```

### Después (20 líneas)

```javascript
document.addEventListener('DOMContentLoaded', function() {
    const hierarchyManager = initCategoryHierarchy({
        categoriesData: {!! json_encode($categories) !!},
        initialFamilyId: {{ $initialFamilyId ?? 'null' }},
        initialParentId: {{ $initialParentId ?? 'null' }}
    });
});
```

## Solución de Problemas

### Los selects no aparecen

**Causa:** Familia no seleccionada o sin categorías.  
**Solución:** Verifica que `family_id` tenga categorías asociadas.

### Error "Cannot read property 'id' of undefined"

**Causa:** Estructura de datos incorrecta.  
**Solución:** Asegúrate de que cada categoría tenga `id`, `name`, `family_id`, `parent_id`.

### Reconstrucción no funciona en Edit

**Causa:** `initialParentId` es `0` o `null`.  
**Solución:** Usa `parseInt('{{ $category->parent_id ?? 0 }}') || null`.

### Categoría se autoselecciona como padre

**Causa:** No se pasó `currentCategoryId` en modo edición.  
**Solución:** Siempre incluye `currentCategoryId: {{ $category->id }}` en edit.

## Ejemplos Completos

### Ejemplo 1: Crear Categoría en Familia "Ropa"

```javascript
// Usuario selecciona "Ropa" (ID: 3)
// → Se cargan: ["Hombre", "Mujer", "Niños"]

// Usuario selecciona "Mujer" (ID: 15)
// → Se cargan: ["Vestidos", "Pantalones", "Blusas"]

// Usuario selecciona "Vestidos" (ID: 45)
// → Se cargan: ["Casual", "Formal", "Deportivo"]

// Usuario selecciona "Formal" (ID: 78)
// → parent_id = 78
// → Breadcrumb: [Ropa] Mujer → Vestidos → Formal
```

### Ejemplo 2: Editar Categoría "Laptops Gaming"

```javascript
// Categoría actual: "Laptops Gaming" (ID: 99, parent: "Laptops" ID: 20)
// Family: "Electrónica" (ID: 5)

initCategoryHierarchy({
    categoriesData: flatCategoriesArray,
    currentCategoryId: 99,          // Excluye "Laptops Gaming" y sus hijos
    initialFamilyId: 5,             // Preselecciona "Electrónica"
    initialParentId: 20             // Reconstruye hasta "Laptops"
});

// Resultado:
// → Select Nivel 1: "Computadoras" seleccionado
// → Select Nivel 2: "Laptops" seleccionado
// → Breadcrumb: [Electrónica] Computadoras → Laptops
```

## Roadmap / Mejoras Futuras

- [ ] Búsqueda de categorías con autocomplete
- [ ] Drag & drop para reordenar jerarquía
- [ ] Caché de queries para mejor performance
- [ ] Modo "multi-padre" (una categoría en varias rutas)
- [ ] Export/import de jerarquías en JSON

## Créditos

**Autor:** Sistema GECKОМERCE  
**Versión:** 1.0.0  
**Última Actualización:** {{ now()->format('Y-m-d') }}  
**Licencia:** MIT

## Soporte

Para reportar bugs o solicitar features:

1. Documentar caso de uso específico
2. Incluir estructura de datos de prueba
3. Adjuntar logs de consola si aplica

---

**Nota Final:** Este módulo reemplaza completamente el sistema inline de jerarquía anterior, reduciendo ~400 líneas de código duplicado a un módulo reutilizable de ~400 líneas centralizado.
