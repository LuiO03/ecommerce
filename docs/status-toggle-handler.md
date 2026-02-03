# Status Toggle Handler - Manejo Global de Switch de Estado

## 📋 Descripción

`StatusToggleHandler` es una clase JavaScript reutilizable que gestiona el cambio de estado (activado/desactivado) mediante switches en cualquier vista, funcionando independientemente de DataTables.

Ubicación: `resources/js/utils/status-toggle-handler.js`

## ✨ Características

- ✅ Funciona sin necesidad de DataTables
- ✅ Delegación de eventos para elementos dinámicos
- ✅ Manejo automático de errores con reversión del switch
- ✅ Callbacks personalizables
- ✅ Notificaciones toast integradas
- ✅ Soporte para rutas con `{id}` o `{key}`
- ✅ Logs detallados en consola para debugging
- ✅ Restauración de scroll opcional

## 🚀 Uso Básico

### Caso 1: Uso Directo en Vista

```javascript
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar el manejador de switches
    const statusHandler = new StatusToggleHandler({
        selector: '.switch-status',
        routePattern: '/admin/covers/{key}/status'
    });
});
```

### Caso 2: Integrado en una Clase Manager

```javascript
class MyGalleryManager {
    constructor() {
        // ... otras propiedades
        this.init();
    }

    init() {
        this.bindEvents();
        this.initStatusToggle();
    }

    initStatusToggle() {
        this.statusHandler = new StatusToggleHandler({
            selector: '.switch-status',
            routePattern: '/admin/covers/{key}/status',
            scrollRestoration: false,
            onSuccess: (id, status, response, switchElement) => {
                // Lógica personalizada después del éxito
                const card = switchElement.closest('.cover-card');
                if (card) {
                    card.dataset.status = status.toString();
                    card.classList.toggle('active', status === 1);
                }
            }
        });
    }
}
```

## ⚙️ Opciones de Configuración

```javascript
{
    // Selector CSS de los switches (requerido)
    selector: '.switch-status',
    
    // Patrón de ruta con placeholder {key} o {id} (requerido)
    routePattern: '/admin/covers/{key}/status',
    
    // Token CSRF (opcional, se obtiene automáticamente del meta tag)
    csrfToken: document.querySelector('meta[name="csrf-token"]').content,
    
    // Callback antes de hacer el toggle (puede cancelar la operación)
    beforeToggle: (id, status, switchElement) => {
        // return false; // Para cancelar
        return true; // Para continuar
    },
    
    // Callback después del éxito
    onSuccess: (id, status, response, switchElement) => {
        console.log(`Estado actualizado: ${id} -> ${status}`);
    },
    
    // Callback en caso de error
    onError: (xhr, id, switchElement) => {
        console.error('Error al actualizar:', id);
    },
    
    // Restaurar posición del scroll después del toggle
    scrollRestoration: true, // false para deshabilitar
}
```

## 📝 HTML Requerido

### Estructura del Switch

El switch debe tener las siguientes características:

```html
<label class="switch-tabla">
    <input type="checkbox" 
           class="switch-status" 
           data-id="{{ $item->id }}" 
           data-key="{{ $item->slug }}" 
           {{ $item->status ? 'checked' : '' }}>
    <span class="slider"></span>
</label>
```

**Atributos importantes:**
- `class="switch-status"` - Clase para el selector (puede personalizarse)
- `data-id` - ID del registro (obligatorio)
- `data-key` - Slug o clave alternativa para la ruta (opcional, se usa `data-id` si no existe)
- `checked` - Estado inicial del switch

## 🔧 Método del Controlador (Backend)

El controlador debe tener un método `updateStatus` compatible:

```php
public function updateStatus(Request $request, Cover $cover)
{
    $request->validate([
        'status' => 'required|boolean',
    ]);

    $oldStatus = (bool) $cover->status;

    // Actualizar estado sin disparar eventos updated
    $cover->status = (bool) $request->status;
    $cover->updated_by = Auth::id();
    $cover->saveQuietly();

    // Auditoría de cambio de estado
    Audit::create([
        'user_id'        => Auth::id(),
        'event'          => 'status_updated',
        'auditable_type' => Cover::class,
        'auditable_id'   => $cover->id,
        'old_values'     => ['status' => $oldStatus],
        'new_values'     => ['status' => (bool) $cover->status],
        'ip_address'     => $request->ip(),
        'user_agent'     => $request->userAgent(),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Estado actualizado correctamente',
        'status' => $cover->status,
    ]);
}
```

## 🛣️ Configuración de Rutas

```php
Route::patch('/covers/{cover}/status', 'updateStatus')
    ->name('admin.covers.updateStatus');
```

**Nota:** El placeholder en la ruta debe coincidir con el placeholder en `routePattern`.

## 📊 Respuesta JSON Esperada

### Respuesta Exitosa

```json
{
    "success": true,
    "message": "Estado actualizado correctamente",
    "status": 1
}
```

### Respuesta con Error

```json
{
    "success": false,
    "message": "No se pudo actualizar el estado"
}
```

## 🎯 Casos de Uso

### 1. Vista con Galería de Tarjetas (como Covers)

```javascript
class CoversGalleryManager {
    initStatusToggle() {
        this.statusHandler = new StatusToggleHandler({
            selector: '.switch-status',
            routePattern: '/admin/covers/{key}/status',
            scrollRestoration: false,
            onSuccess: (id, status, response, switchElement) => {
                const card = switchElement.closest('.cover-card');
                card.dataset.status = status.toString();
                card.classList.toggle('cover-active', status === 1);
            }
        });
    }
}
```

### 2. Vista Simple sin Manager

```javascript
document.addEventListener('DOMContentLoaded', () => {
    new StatusToggleHandler({
        selector: '.switch-status',
        routePattern: '/admin/posts/{id}/status'
    });
});
```

### 3. Confirmación Antes del Cambio

```javascript
new StatusToggleHandler({
    selector: '.switch-status',
    routePattern: '/admin/users/{id}/status',
    beforeToggle: (id, status, switchElement) => {
        if (status === 0) {
            return confirm('¿Estás seguro de desactivar este usuario?');
        }
        return true;
    }
});
```

## 🐛 Debugging

El manejador incluye logs detallados en la consola:

```
🔄 Actualizando estado... {url, id, routeKey, newStatus}
✅ Respuesta exitosa: {success, message, status}
✅ Estado actualizado: ID 5 -> Activo
❌ Error AJAX: {xhr details}
```

## 🔒 Permisos

Asegurar que el usuario tenga el permiso correspondiente en el backend:

```php
$this->middleware('can:portadas.update-status')->only(['updateStatus']);
```

Y en la vista:

```blade
@can('portadas.update-status')
    <label class="switch-tabla">
        <input type="checkbox" class="switch-status" ...>
        <span class="slider"></span>
    </label>
@else
    <span class="status-badge">{{ $item->status ? 'Activo' : 'Inactivo' }}</span>
@endcan
```

## ♻️ Destrucción de la Instancia

```javascript
// Si necesitas destruir la instancia
statusHandler.destroy();
```

## 🔗 Integración Global

El módulo se carga automáticamente en `resources/js/index.js`:

```javascript
import './utils/status-toggle-handler.js';
```

Y está disponible globalmente como `window.StatusToggleHandler`.

## 📦 Comparación con DataTableManager

| Característica | StatusToggleHandler | DataTableManager |
|---------------|---------------------|------------------|
| **Requiere DataTables** | ❌ No | ✅ Sí |
| **Vistas soportadas** | Cualquiera | Solo tablas |
| **Delegación eventos** | ✅ Sí | ✅ Sí |
| **Callbacks** | ✅ Sí | ✅ Sí |
| **Restauración scroll** | ✅ Configurable | ✅ Automático |
| **Toast notifications** | ✅ Sí | ✅ Sí |

## ✅ Ventajas

1. **Reutilizable** - Funciona en cualquier vista (galerías, listas, cards, etc.)
2. **Independiente** - No depende de DataTables
3. **Ligero** - Solo maneja el toggle de estado
4. **Flexible** - Callbacks personalizables para cada caso
5. **Robusto** - Manejo automático de errores y reversión
6. **Compatible** - Usa la misma estructura que DataTableManager

## 📚 Ejemplo Completo

Ver implementación en: `resources/views/admin/covers/index.blade.php`
