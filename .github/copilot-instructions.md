# GECKОМERCE - Instrucciones para Agentes de IA

## Arquitectura del Proyecto

**Stack:** Laravel 12 + Livewire 3 + Jetstream + TailwindCSS 3 + Flowbite + DataTables  
**PHP:** ^8.2 | **Base de Datos:** MySQL

### Jerarquía del Catálogo
```
Family (Familias) 1:N → Category (Categorías) 1:N → Product (Productos) 1:N → Variant (Variantes)
```
- **Families**: Nivel superior (ej: "Electrónica", "Ropa")
- **Categories**: Subcategorías con soporte para anidación (`parent_id`)
- **Products**: Productos con SKU, precio, descuento, slug único
- **Variants**: Variantes de productos con características (`features`)

### Auditoría Automática en Modelos
Todos los modelos principales (`Family`, `Category`, etc.) incluyen:
```php
'created_by', 'updated_by', 'deleted_by' // Foreign keys a users table
```
**No implementes manualmente** - usa el patrón existente en `app/Models/Family.php`.

## Rutas y Middleware

### Admin Routes (`routes/admin.php`)
**Prefijo:** `/admin` | **Middleware:** `['web', 'auth', 'verified']` (configurado en `bootstrap/app.php`)
```php
// ✅ Patrón correcto para nuevos módulos
Route::get('/entities', [EntityController::class, 'index'])->name('admin.entities.index');
Route::delete('/entities', [EntityController::class, 'destroyMultiple'])->name('admin.entities.destroy-multiple');
Route::patch('/entities/{entity}/status', [EntityController::class, 'updateStatus'])->name('admin.entities.update-status');
```

**NO uses** `->name('admin.')` prefix - está comentado intencionalmente en `bootstrap/app.php`.

## Convenciones de Desarrollo

### 1. Slugs Únicos
**Siempre usa** `Family::generateUniqueSlug()` como referencia:
```php
public static function generateUniqueSlug($name, $id = null) {
    $slug = Str::slug($name);
    // Auto-incrementa si existe: producto-1, producto-2...
    while (self::where('slug', $slug)->when($id, fn($q) => $q->where('id', '!=', $id))->exists()) {
        $slug = $originalSlug . '-' . $count++;
    }
    return $slug;
}
```

### 2. Route Model Binding con Slug
```php
public function getRouteKeyName() {
    return 'slug'; // Usa slug en lugar de id en URLs
}
```

### 3. Query Scopes Reutilizables
Define scopes en modelos para consultas comunes:
```php
public function scopeForTable($query) {
    return $query->select('id', 'name', 'description', 'status', 'created_at')->orderByDesc('id');
}
```

## Componentes Frontend

### Admin Layout
**Ubicación:** `app/View/Components/AdminLayout.php` → `resources/views/layouts/admin.blade.php`
```blade
<x-admin-layout :showMobileFab="true" :useSlotContainer="false">
    <x-slot name="title">
        <div class="page-icon card-success"><i class="ri-apps-line"></i></div>
        Título de Página
    </x-slot>
    <x-slot name="action">
        <a href="#" class="boton boton-primary">
            <span class="boton-icon"><i class="ri-add-box-fill"></i></span>
            <span class="boton-text">Acción</span>
        </a>
    </x-slot>
    <!-- Contenido -->
</x-admin-layout>
```

### Iconos
**Sistema:** Remix Icon (clases `ri-*-line` y `ri-*-fill`)  
**NO uses** Font Awesome, Material Icons u otros.

## JavaScript Patterns

### Eliminación Múltiple Global
**Ver:** `docs/multiple-delete-global.md` | **Archivo:** `resources/js/modals/modal-confirm.js`

```javascript
// Llamada estándar desde cualquier vista
handleMultipleDelete({
    selectedIds: selectedIds,             // Set o Array
    getNameCallback: getEntityNameById,   // Función para obtener nombre por ID
    entityName: 'familia',                // Para mensajes en español
    deleteRoute: '/admin/families',       // Ruta destroy-multiple
    csrfToken: '{{ csrf_token() }}',
    buttonSelector: '#deleteSelectedBtn'
});
```

**Backend requerido:**
```php
public function destroyMultiple(Request $request) {
    $request->validate(['ids' => 'required|array']);
    Entity::whereIn('id', $request->ids)->delete();
    return response()->json(['success' => true]);
}
```

### Toggle de Estado Rápido
**Ver:** `docs/quick-status-toggle.md`

```html
<label class="switch-tabla">
    <input type="checkbox" class="toggle-estado" 
           {{ $entity->status ? 'checked' : '' }}
           data-entity-id="{{ $entity->id }}">
    <span class="slider"></span>
</label>
```

Controlador:
```php
public function updateStatus(Request $request, Entity $entity) {
    $request->validate(['status' => 'required|boolean']);
    $entity->status = $request->status;
    $entity->save();
    return response()->json(['success' => true, 'status' => $entity->status]);
}
```

## DataTables Integration

**Patrón estándar:** Ver `resources/views/admin/families/index.blade.php` (líneas 1-100)
- Custom search, sort, status filters
- Multi-select con checkboxes
- Export buttons (Excel, CSV, PDF)
- Responsive design

**JS Global:** `resources/js/utils/datatable.js` - cargado automáticamente.

## Exportación de Datos

### Excel/CSV
**Paquete:** `maatwebsite/excel`
```php
use App\Exports\EntitiesExport;

public function exportExcel(Request $request) {
    $ids = $request->input('ids');
    $filename = 'entidades_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
    return Excel::download(new EntitiesExport($ids), $filename);
}
```

**Clase Export:** Implementa `FromArray`, `WithStyles`, `WithColumnWidths`, `WithEvents` (ver `app/Exports/FamiliesExport.php`).

### PDF
**Paquete:** `spatie/laravel-pdf` (usa Puppeteer)
```php
use Spatie\LaravelPdf\Facades\Pdf;

return Pdf::view('admin.export.entities-pdf', compact('entities'))
    ->format('a4')
    ->name('entidades_' . now()->format('Y-m-d_H-i-s') . '.pdf')
    ->download();
```

## Comandos de Desarrollo

### Setup Inicial
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
```

### Desarrollo Local
```bash
composer dev  # Ejecuta servidor, queue, logs y vite concurrentemente
```
**O manualmente:**
```bash
php artisan serve
npm run dev
php artisan queue:listen
```

### Testing
```bash
composer test  # Limpia config y ejecuta PHPUnit
```

### Code Quality
```bash
php artisan pail          # Log viewer en tiempo real
./vendor/bin/pint         # Laravel Pint (PSR-12 formatter)
```

## Helpers Globales

**Ubicación:** `app/Helpers/helpers.php` (auto-cargado en `composer.json`)

```php
fecha_hoy() // Retorna fecha formateada en español: "viernes, 15 de noviembre de 2025"
```

## Migraciones

**Patrón estándar:** Ver `database/migrations/2025_10_15_233139_create_families_table.php`
```php
$table->string('slug')->unique()->index();
$table->boolean('status')->default(false)->index();
$table->softDeletes();

// Auditoría
$table->unsignedBigInteger('created_by')->nullable();
$table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
```

## Permisos y Roles

**Paquete:** `spatie/laravel-permission`  
**Configuración:** `config/permission.php`  
**Seeder:** `database/seeders/RolePermissionSeeder.php`

## Localización

**Idioma principal:** Español (`es`)  
**Archivos:** `lang/es.json`, `lang/es/**`  
**Paquete:** `laravel-lang/common` + `laravel-lang/publisher`

## Anti-Patrones ❌

- **NO uses Livewire** actualmente (carpeta `app/Livewire/Admin` vacía)
- **NO uses Vue/React** - solo Vanilla JS + jQuery (por DataTables)
- **NO implementes autenticación custom** - usa Jetstream/Fortify
- **NO modifiques `vite.config.js`** sin actualizar `resources/js/index.js`
- **NO uses Bootstrap** - TailwindCSS + Flowbite exclusivamente

## Referencias Clave

- **CRUD completo:** `app/Http/Controllers/Admin/FamilyController.php`
- **Modelo base:** `app/Models/Family.php`
- **Vista index:** `resources/views/admin/families/index.blade.php`
- **Docs técnicas:** `docs/multiple-delete-global.md`, `docs/quick-status-toggle.md`
