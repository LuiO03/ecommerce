# Instrucciones para Agentes IA - GECKOMERCE

## Panorama del proyecto
- Ecommerce en Laravel 12 (PHP 8.2, MySQL). Frontend con Blade + Livewire 3 + TailwindCSS 3 + Flowbite; build con Vite 7.
- Catálogo jerárquico: Familias → Categorías (anidables) → Productos → Variantes con Options/Features.
- Auditoría automática con el trait `Auditable` (created_by/updated_by/deleted_by + tabla audits).

## Flujos de desarrollo (README)
- Setup completo: `composer setup`.
- Dev local: `composer dev` (serve + queue + pail + vite con concurrently).
- Tests: `composer test`.
- Formato: `./vendor/bin/pint`.

## Convenciones backend (Laravel)
- Rutas admin SOLO en [routes/admin.php](routes/admin.php), prefijo `/admin` y nombres `admin.entidad.accion`. Protegidas en [bootstrap/app.php](bootstrap/app.php).
- Permisos con Spatie: middleware en `__construct` del controlador `can:{entidad_plural}.{accion}` (ej.: ver FamilyController).
- Slugs: `getRouteKeyName()` => `slug` y `generateUniqueSlug($name, $id)` en modelos (route binding por slug).
- Exportaciones: `Excel::store(new *Export, ...)` con clases en [app/Exports/](app/Exports/) y vistas en [resources/views/admin/export/](resources/views/admin/export/).

## Convenciones frontend (JS/Blade)
- Entry points: [resources/js/admin.js](resources/js/admin.js), [resources/js/site.js](resources/js/site.js), [resources/js/app.js](resources/js/app.js); layout admin usa `@vite(['css/app.css','js/admin.js'])`.
- Módulos en [resources/js/modules/](resources/js/modules/) y utils en [resources/js/utils/](resources/js/utils/). Exporta a `window` si se usa desde Blade.
- DataTables: configuración vía data-* en la tabla; lógica en `DataTableManager` (ver docs/datatable-manager-usage.md).
- Status toggle: en tablas via `DataTableManager.statusRoute`; en galerías/tarjetas se usa script inline en la vista (ej. covers).
- Alertas: componente `<x-alert type="success|danger|warning">` en [resources/views/partials/components/alert.blade.php](resources/views/partials/components/alert.blade.php).
- Validación: cliente con `FormValidator` + Requests en servidor.
- Iconos: solo Remix Icon (`ri-*`).

## Patrones específicos
- `CategoryHierarchyManager` para selección jerárquica (ver [resources/js/modules/category-hierarchy-manager.js](resources/js/modules/category-hierarchy-manager.js) y docs/category-hierarchy-manager.md).
- `GalleryManager` para upload múltiple + reorder + crop (ver [resources/js/modules/gallery-manager.js](resources/js/modules/gallery-manager.js)).
- Eliminación múltiple: DataTableManager envía POST a `destroy-multiple` (docs/multiple-delete-global.md).
- Flash sessions: `Session::flash('info'|'toast')` y `highlightRow` para resaltar filas.

## Referencias clave
- Controlador CRUD modelo: [app/Http/Controllers/Admin/FamilyController.php](app/Http/Controllers/Admin/FamilyController.php)
- Modelo ejemplo con slug/auditoría: [app/Models/Family.php](app/Models/Family.php)
- Layout admin: [resources/views/layouts/admin.blade.php](resources/views/layouts/admin.blade.php)
- Documentación técnica por módulo: [docs/](docs/)

## No hacer
- No definir permisos en middleware de ruta; usar `__construct` en controladores.
- No agregar JS directo en Blade si puede ir como módulo.
- No modificar [vite.config.js](vite.config.js) sin actualizar `@vite` en layouts.
