
# Guía para Agentes IA en GECKОМERCE

Esta guía documenta los patrones reales del proyecto para que un agente IA pueda tocar código productivamente sin romper convenciones.

## 🎯 Stack Tecnológico

**Backend:** Laravel 12, PHP 8.2, MySQL  
**Frontend:** Blade + Livewire 3 + TailwindCSS 3 + Flowbite  
**Auth:** Laravel Jetstream (perfiles + 2FA)  
**Tablas:** DataTables con filtros personalizados y responsive  
**Export:** Maatwebsite Excel + Spatie Laravel PDF  
**Permisos:** Spatie Laravel Permission  
**Iconos:** Solo Remix Icon (`ri-*`)  
**Build:** Vite 7  
**Dependencias JS:** Sortable.js (drag & drop), Puppeteer, Axios, Flowbite  
**Color Picker:** Coloris (CDN)

## 🏗️ Arquitectura General

### Sistema de Catálogo Jerárquico
```
Familias → Categorías (anidables) → Productos → Variantes
                                         ↓
                               Options + Features
```

**Familias** son los contenedores principales (ej: "Ropa", "Electrónica").  
**Categorías** soportan anidación ilimitada con drag-&-drop para reordenar (ver `docs/category-hierarchy-manager.md`).  
**Productos** tienen opciones configurables (talla, color) y características descriptivas.  
**Variantes** son combinaciones de opciones con SKU, precio y stock independientes (ver `docs/product-variants-manager.md`).

### Auditoría Automática
Todos los modelos principales usan `App\Traits\Auditable` que registra:
- `created_by`, `updated_by`, `deleted_by` (user_id)
- Snapshots de valores antiguos/nuevos en tabla `audits`
- IP y User-Agent del request

**No reimplementar auditoría manualmente.** Usar el trait. Ver patrón en `app/Models/Family.php` y docs en `docs/auditoria.md`.

**Nota:** No todos los modelos usan Soft Deletes. Solo `Post` y `CompanySetting` implementan `SoftDeletes` actualmente. Verificar antes de asumir su presencia en nuevos modelos.

### Slugs y Route Model Binding
Modelos con URLs amigables implementan:
```php
public function getRouteKeyName() { return 'slug'; }

public static function generateUniqueSlug($name, $id = null) {
    // Auto-incremental: "nombre", "nombre-2", "nombre-3"...
}
```
Reutilizar este patrón en nuevos modelos. Ver `Family::generateUniqueSlug()`.

## 📁 Estructura de Directorios

```
app/
  ├─ Http/Controllers/Admin/  # CRUD controllers para panel admin
  │   ├─ FamilyController      # Patrón de referencia para nuevos CRUD
  │   ├─ CategoryController    # Gestión de categorías jerárquicas
  │   ├─ ProductController     # Gestión de productos con variantes
  │   ├─ OptionController      # Options y Features de productos
  │   ├─ UserController        # Gestión de usuarios
  │   ├─ RoleController        # Roles y permisos
  │   ├─ AuditController       # Historial de auditoría
  │   └─ AccessLogController   # Logs de acceso
  ├─ Models/                   # Eloquent models con traits y scopes
  ├─ Exports/                  # Clases para Excel/CSV export
  ├─ Traits/                   # Auditable, otros traits reutilizables
  ├─ View/Components/          # Componentes Blade (Alert, etc.)
  └─ Helpers/helpers.php       # Funciones globales (fecha_hoy, etc.)
resources/
  ├─ views/
  │   ├─ admin/                # Vistas del panel de administración
  │   ├─ layouts/              # admin.blade.php, app.blade.php, guest.blade.php
  │   ├─ partials/
  │   │   ├─ admin/            # navigation, sidebar-left, sidebar-right, etc.
  │   │   └─ components/       # alert.blade.php
  │   └─ components/           # Componentes Jetstream estándar
  ├─ js/
  │   ├─ index.js              # Entry point, importa todos los módulos
  │   ├─ modules/              # Lógica de negocio (categorías, variantes)
  │   ├─ utils/                # Utilidades reutilizables (DataTableManager)
  │   └─ components/           # Alert, modal-confirm, etc.
  └─ css/
      ├─ app.css               # Tailwind base (legacy)
      ├─ base.css              # Variables globales compartidas
      ├─ admin.css             # Entry point panel admin
      ├─ site.css              # Entry point sitio público
      ├─ admin/                # CSS exclusivo del panel admin
      │   ├─ layout.css
      │   ├─ modules/          # dashboard, categories, roles, etc.
      │   └─ components/       # table, form, validation, etc.
      ├─ site/                 # CSS exclusivo del sitio público
      │   ├─ layout.css
      │   ├─ modules/          # home, products, cart, checkout
      │   └─ components/       # navigation, product-card, filters
      └─ shared/               # Componentes compartidos (alert, button)
routes/
  ├─ admin.php                 # Todas las rutas del panel admin (middlewares auth+verified)
  ├─ web.php                   # Rutas públicas
  └─ api.php
docs/                          # Documentación técnica de módulos JS y patrones
```

## 🚀 Workflows de Desarrollo

### Setup Inicial
```bash
composer setup    # Instala deps, genera .env, key, migra DB, build assets
```
Equivalente a: `composer install` + `npm install` + `cp .env.example .env` + `php artisan key:generate` + `php artisan migrate` + `npm run build`.

### Desarrollo Local
```bash
composer dev      # Corre en paralelo: server, queue, logs (pail), vite
```
Usa `concurrently` para ejecutar simultáneamente:
- `php artisan serve` (puerto 8000)
- `php artisan queue:listen` (jobs en background)
- `php artisan pail` (logs en tiempo real)
- `npm run dev` (Vite hot reload)

### Testing y Calidad
```bash
composer test           # PHPUnit
./vendor/bin/pint       # Laravel Pint para formateo PSR-12
php artisan pail        # Logs en tiempo real con colores
```

## 🧩 Patrones de CRUD Admin

### Controlador de Referencia
**`FamilyController`** es el patrón canónico para nuevos CRUD. Incluye:

1. **Permisos en constructor:**
   ```php
   $this->middleware('can:familias.index')->only(['index']);
   $this->middleware('can:familias.create')->only(['create', 'store']);
   $this->middleware('can:familias.edit')->only(['edit', 'update', 'updateStatus']);
   $this->middleware('can:familias.delete')->only(['destroy', 'destroyMultiple']);
   ```
   **Convención de permisos:** `{entidad_plural}.{acción}` (ej: `familias.index`, `categorias.create`, `productos.edit`). Usar nombres en español para consistencia con el resto del proyecto.

2. **Scopes para optimizar queries:**
   ```php
   Family::forTable()->get(); // Solo columnas necesarias para tabla
   Family::forSelect()->get(); // Solo id + name para dropdowns
   ```

3. **Exportación con auditoría:**
   Métodos `exportExcel`, `exportPdf`, `exportCsv` registran el evento de exportación en `audits` con IDs exportados, filename e IP.

4. **Toggle de estado instantáneo:**
   ```php
   public function updateStatus(Request $request, Family $family) {
       $family->status = $request->status;
       $family->saveQuietly(); // Sin emitir evento de auditoría (ya se hizo)
       return response()->json(['success' => true, 'status' => $family->status]);
   }
   ```
   Ver docs en `docs/quick-status-toggle.md` para integración frontend.

5. **Eliminación múltiple:**
   ```php
   public function destroyMultiple(Request $request) {
       // Validar IDs, verificar relaciones, crear audit, eliminar
       Session::flash('info', [
           'type' => 'danger',
           'header' => 'Eliminación completada',
           'message' => "Se eliminaron $deletedCount registros.",
           'items' => ['Item 1', 'Item 2'] // Opcional
       ]);
   }
   ```
   Ver patrón completo en `docs/multiple-delete-global.md`.

### Rutas Admin
Definidas en `routes/admin.php` con esquema `admin.entity.action`:
```php
Route::get('/families', [FamilyController::class, 'index'])->name('admin.families.index');
Route::post('/families/export/excel', [FamilyController::class, 'exportExcel'])->name('admin.families.export.excel');
```
**No añadir prefijos `->name('admin.')` fuera de este archivo.**

**Middlewares:** Todas las rutas admin están protegidas automáticamente por `['web', 'auth:sanctum', config('jetstream.auth_session'), 'verified']` y tienen prefijo `/admin` (configurado en `bootstrap/app.php`). Los permisos granulares se controlan en cada controlador con:
```php
$this->middleware('can:familias.index')->only(['index']);
$this->middleware('can:familias.create')->only(['create', 'store']);
```

### Modelos con Auditoría
```php
use App\Traits\Auditable;

class Family extends Model {
    use HasFactory, Auditable;
    
    protected $fillable = ['name', 'slug', 'description', 'status', 'created_by', 'updated_by', 'deleted_by'];
    
    public function scopeForTable($query) { /* optimizar columnas */ }
    public function scopeForSelect($query) { /* solo id + name */ }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
```

**Nota:** Solo agregar `SoftDeletes` si el modelo lo requiere explícitamente (como `Post` o `CompanySetting`). No todos los modelos lo necesitan.

## 🎨 Frontend y Componentes

### Layout Base
Usar `<x-admin-layout>` como base (definido en `resources/views/layouts/admin.blade.php`):
```blade
<x-admin-layout :showMobileFab="true">
    <x-slot name="title">
        <div class="page-icon card-success"><i class="ri-apps-line"></i></div>
        Título de la Página
    </x-slot>
    <x-slot name="action">
        <a href="..." class="boton boton-primary">
            <span class="boton-icon"><i class="ri-add-box-fill"></i></span>
            <span class="boton-text">Crear</span>
        </a>
    </x-slot>
    <!-- Contenido -->
</x-admin-layout>
```

**Carga de Assets:** El layout admin usa directivas `@vite` para cargar:
```blade
@vite(['resources/css/base.css', 'resources/css/admin.css', 'resources/js/app.js'])
```

El sitio público usa su propio entry point:
```blade
@vite(['resources/css/base.css', 'resources/css/site.css', 'resources/js/app.js'])
```

**Estructura CSS:** Separación completa entre admin y sitio público (ver `docs/css-structure.md`):
- `admin.css` → Panel de administración (importa `/admin/modules/` y `/admin/components/`)
- `site.css` → Sitio público (importa `/site/modules/` y `/site/components/`)
- `shared/` → Componentes compartidos (alert, button)

**No modificar esta estructura sin validar todos los layouts.**

### Alertas Contextuales
Usar `<x-alert>` para banners informativos (ver `docs/alert-component.md`):
```blade
<x-alert type="info" title="Instrucciones:" :items="[
    'Los campos con asterisco son obligatorios',
    'Guarda antes de continuar'
]" />

<x-alert type="warning" title="Advertencia">
    Esta acción <strong>no se puede deshacer</strong>.
</x-alert>
```

Componente definido en `app/View/Components/Alert.php` con vista en `resources/views/partials/components/alert.blade.php`.

### DataTables
Seguir estructura en `resources/views/admin/families/index.blade.php`:
- Clases CSS: `tabla-general display`
- Columnas con clases: `column-id-th`, `column-name-th`, `column-status-th`, `column-actions-th`
- Filas con `data-id` y `data-name` para JS
- Usar `DataTableManager` (ver `docs/datatable-manager-usage.md`) para lógica reutilizable

### JavaScript Modular
**Entry point:** `resources/js/index.js`

Exportar módulos a `window` para uso global:
```js
import { initImageUpload } from './utils/image-upload-handler.js';
window.initImageUpload = initImageUpload;
```
**Nota CSS:** `app.css` importa automáticamente `main.css` mediante `@import "./main.css";`, que a su vez importa todos los módulos CSS del dashboard. No es necesario importar `main.css` manualmente en otros archivos CSS.
**Módulos clave:**
- `utils/datatable-manager.js` - Configuración unificada de DataTables con filtros, export, selección múltiple
- `modals/modal-confirm.js` - Confirmaciones de eliminación (individual y múltiple)
- `utils/gallery-manager.js` - Drag-&-drop de imágenes con preview
- `modules/product-variants-manager.js` - Generador de variantes de productos
- `utils/form-validator.js` - Validación en tiempo real con indicadores visuales
- `utils/connection-status.js` - Barra de estado de conexión
- `utils/submit-button-loader.js` - Loaders en botones de envío

Al añadir nueva funcionalidad, crear el módulo en `resources/js/modules/` o `resources/js/utils/` e importarlo en `index.js`.

## 📊 Exportación de Datos

### Excel/CSV
Usar `Maatwebsite\Excel`:
```php
// app/Exports/FamiliesExcelExport.php
class FamiliesExcelExport implements FromCollection, WithHeadings, WithStyles {
    public function collection() { /* datos */ }
    public function headings(): array { /* encabezados */ }
    public function styles(Worksheet $sheet) { /* estilos */ }
}
```
Descargar: `Excel::download(new FamiliesExcelExport($ids), 'filename.xlsx')`.

### PDF
Usar `Spatie\LaravelPdf`:
```php
$pdf = Pdf::view('admin.export.families-pdf', ['families' => $families])
    ->format('a4')
    ->name('familias.pdf');
return $pdf->download();
```
Vistas en `resources/views/admin/export/` con estilos consistentes.

## 🌍 Localización y Helpers

**Idioma:** Todo el texto UI en español (`lang/es.json` + `laravel-lang/common`).

**Helpers globales** en `app/Helpers/helpers.php` (autoloaded en `composer.json`):
- `fecha_hoy()` - Fecha actual formateada en español (ej: "Martes, 28 de enero de 2026")
- `company_setting($key, $default)` - Obtiene configuración de la empresa desde caché (30 min)

Verificar archivo antes de duplicar lógica existente.

## ⚠️ Anti-Patrones

**No hacer:**
- ❌ Añadir dependencias de Vue, React, Bootstrap (proyecto usa Blade + Tailwind + JS vanilla)
- ❌ Usar iconos que no sean Remix Icon
- ❌ Reimplementar auditoría manualmente (usar `Auditable` trait)
- ❌ Modificar `vite.config.js` sin revisar impacto en build (actualmente incluye `admin.css` y `site.css` como entry points separados)
- ❌ Mezclar CSS del admin con el sitio público (usar estructura `/admin/` y `/site/` respectivamente)
- ❌ Crear rutas admin fuera de `routes/admin.php`
- ❌ Usar `saveQuietly()` sin auditoría previa (solo para updates rápidos como status toggle)
- ❌ Asumir que todos los modelos tienen Soft Deletes (solo `Post` y `CompanySetting` lo implementan actualmente)

## 📚 Documentación Clave

**Modelos y Backend:**
- `app/Models/Family.php` - Modelo de referencia con auditoría, slugs y scopes
- `app/Http/Controllers/Admin/FamilyController.php` - Controlador CRUD completo
- `app/Traits/Auditable.php` - Trait de auditoría automática

**Frontend:**
- `resources/views/admin/families/index.blade.php` - Vista index con DataTable completo
- `resources/js/index.js` - Entry point JS

**Documentos técnicos:**
- `docs/css-structure.md` - Estructura CSS completa (admin vs sitio público)
- `docs/multiple-delete-global.md` - Eliminación múltiple con validaciones
- `docs/quick-status-toggle.md` - Toggle de estado sin modales
- `docs/datatable-manager-usage.md` - Configuración de DataTables
- `docs/product-variants-manager.md` - Generador de variantes
- `docs/alert-component.md` - Componente de alertas contextuales
- `docs/auditoria.md` - Sistema de auditoría completo
