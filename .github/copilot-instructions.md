# Instrucciones para Agentes IA - GECKOMERCE

Ecommerce profesional en Laravel 12. Guía práctica para productividad inmediata.

## Stack & Arquitectura

**Backend:** Laravel 12 + PHP 8.2 + MySQL  
**Frontend:** Blade + Livewire 3 + TailwindCSS 3 + Flowbite  
**Auth:** Jetstream (2FA) | **Permisos:** Spatie Permission | **Exports:** Maatwebsite Excel + Spatie PDF  
**UI:** Remix Icon, DataTables 2.3.4, Flowbite | **Build:** Vite 7 | **JS:** Sortable.js, Axios  

**Catálogo jerárquico:** Familias → Categorías (anidables) → Productos → Variantes con Options/Features.  
**Auditoría:** Trait `Auditable` (created_by, updated_by, deleted_by + tabla audits). Automática en created/updated/deleted.

## Flujos de Desarrollo

- **Setup:** `composer setup` (configura .env, migraciones, npm, build)
- **Dev local:** `composer dev` (serve + queue + pail + vite, con concurrently)
- **Tests:** `composer test` (artisan test)
- **Format:** `./vendor/bin/pint` (Laravel Pint)

## Convenciones Backend

**Rutas admin:** Solo en `routes/admin.php`. Prefijo `/admin`, nombre `admin.entidad.accion`. Protegidas en bootstrap/app.php con `web,auth:sanctum,jetstream.auth_session,verified`.

**Permisos:** Middleware en __construct del controlador: `can:{entidad_plural}.{accion}` (ej: `familias.index`, `productos.edit`). Ver `FamilyController.php` línea 15-20.

**Slugs:** getRouteKeyName() => 'slug'. Static method `generateUniqueSlug($name, $id)` maneja duplicados. Route binding automático por slug.

**Auditoría:** `use Auditable` trait en modelo. Registra event (created/updated/deleted), valores antiguos/nuevos, IP, user_agent. No requiere código adicional en controlador.

**SoftDeletes:** Solo en Post y CompanySetting (NO aplicar globalmente).

**Exportación:** Controlador usa `Excel::store(new FamiliesExcelExport, ...)`. Clases export en `app/Exports/*`. Vistas en `resources/views/admin/export/`.

## Convenciones Frontend

**Entry points:** `resources/js/admin.js` (admin), `site.js` (público), `app.js` (bootstrap compartido). Layout admin carga @vite(['css/app.css', 'js/admin.js']). Actualizar vite.config.js si se agregan entry points.

**JS módulos:** En `resources/js/modules/*` o `utils/*`. Importar en `index.js` o `admin.js`. Exportar a `window` si se usan desde Blade (ej: `window.DataTableManager = DataTableManager`).

**DataTables:** Config en data-* de tabla HTML. Maneja selección múltiple, filtros, status toggle, paginación. Lógica en `DataTableManager` class. Ver `datatable-manager-usage.md`.

**Status toggle:** En tablas → `DataTableManager` (property `statusRoute`). En galerías/tarjetas → script inline en vista (evita timing issues). Ejemplo: `covers/index.blade.php`.

**Alertas:** Component `<x-alert type="success|danger|warning">`. Archivo: `resources/views/partials/components/alert.blade.php`.

**Validación:** Client (FormValidator module) + Server (Request class validation).

**Iconos:** Solo Remix Icon (clases `ri-*`).

## Patrones Específicos

**CategoryHierarchyManager:** Selección jerárquica en cascada. Datos jerárquicos (create) o planos (edit). Auto-reconstruye ruta en edición. Módulo: `resources/js/modules/category-hierarchy-manager.js`.

**GalleryManager:** Upload múltiple, reorder (Sortable.js), crop canvas. Almacena en storage, retorna JSON a controlador. Módulo: `resources/js/modules/gallery-manager.js`.

**Eliminación múltiple:** DataTableManager recoge IDs checkeados, POST a ruta `destroy-multiple`. Ver `docs/multiple-delete-global.md`.

**Session flash:** Session::flash('info'/'toast'). Blade incluye partials que leen y muestran. Resaltar fila: Session::flash('highlightRow', $id).

## Referencias Rápidas

- **Controlador CRUD:** `app/Http/Controllers/Admin/FamilyController.php` (permisos, exports, status toggle)
- **Modelo:** `app/Models/Family.php` (Auditable, slug, getRouteKeyName)
- **Admin layout:** `resources/views/layouts/admin.blade.php` (sidebar, modals, breadcrumb)
- **Docs:** Carpeta `docs/` (~30 archivos técnicos por módulo)

## ⚠️ No Hacer

- No crear permisos en middleware de ruta; usar __construct del controlador
- No agregar JS directo en Blade si puede ser módulo
- No usar SoftDeletes en nuevos modelos sin validar
- No modificar vite.config.js sin actualizar @vite en layouts
