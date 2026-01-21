
﻿# Guía para Agentes IA en GECKОМERCE

Esta guía resume los patrones reales del proyecto para que un agente IA pueda tocar código productivamente sin romper convenciones.

## Arquitectura y Dominios

- **Stack:** Laravel 12, PHP 8.2, Livewire 3, Jetstream, TailwindCSS 3, Flowbite, DataTables, Spatie Permission, Maatwebsite Excel, Spatie Laravel PDF.
- **Catálogo:** Familias → Categorías (anidables) → Productos → Variantes, con opciones y características (Options/Features). Ver modelos en `app/Models` y docs en `docs/category-*` y `docs/product-variants-manager.md`.
- **Auditoría:** Modelos principales usan `App\Traits\Auditable` con campos `created_by`, `updated_by`, `deleted_by` y soft deletes; no reimplementar a mano (tomar como referencia [app/Models/Family.php](../../app/Models/Family.php)).
- **Panel Admin:** Controladores en [app/Http/Controllers/Admin](../../app/Http/Controllers/Admin), vistas en [resources/views/admin](../../resources/views/admin), JS modular en `resources/js` (subcarpetas `dashboard`, `modals`, `utils`).

## Rutas y Backend

- **Rutas Admin:** Definidas en [routes/admin.php](../../routes/admin.php), montadas bajo `/admin` con middlewares `web`, `auth`, `verified`. No añadir nuevos prefijos globales `->name('admin.')` fuera de este archivo.
- **Nombres de rutas:** Se sigue el esquema `admin.entity.action`, p.ej. `admin.families.index`, `admin.products.export.excel`, `admin.profile.update`.
- **Slugs y Route Model Binding:** Modelos como `Family` usan `generateUniqueSlug()` y `getRouteKeyName()` para trabajar con `slug` en URLs. Reutilizar este patrón al crear nuevos modelos con slugs.
- **Scopes para tablas:** Usar scopes como `scopeForTable()` / `scopeForSelect()` para DataTables o selects reutilizables (ver [app/Models/Family.php](../../app/Models/Family.php)).
- **Permisos:** Controladores Admin aplican middleware `can:*` en el constructor (ver [app/Http/Controllers/Admin/FamilyController.php](../../app/Http/Controllers/Admin/FamilyController.php)); al crear nuevos CRUD, seguir esta estructura.

## Patrones de CRUD Admin

- **CRUD de referencia:** `FamilyController` es el patrón recomendado para listados, formulario create/edit, exportaciones y eliminación (incluida eliminación múltiple con validaciones de relaciones y auditoría).
- **Exportaciones:** Métodos `exportExcel`, `exportPdf`, `exportCsv` usan `Maatwebsite\Excel` y `Spatie\LaravelPdf` más registros en `Audit`. Replicar este flujo en nuevos módulos.
- **Toggle de estado rápido:** Endpoints tipo `updateStatus` devuelven JSON y usan `saveQuietly()` para evitar doble auditoría; ver `updateStatus` en `FamilyController` y docs en [docs/quick-status-toggle.md](../../docs/quick-status-toggle.md).
- **Eliminación múltiple:** Usar `destroyMultiple` con validaciones, mensajes tipo `Session::flash('info', [...])` y auditoría; patrón documentado en [docs/multiple-delete-global.md](../../docs/multiple-delete-global.md).
- **Respuestas JSON de detalle:** Métodos `show($slug)` en controladores como `FamilyController` devuelven JSON formateado para modales de detalle; seguir el mismo formato de campos y fechas.

## Frontend, Layouts y JS

- **Layout Admin:** Usar el componente Blade `<x-admin-layout>` definido en [resources/views/layouts/admin.blade.php](../../resources/views/layouts/admin.blade.php) como base de nuevas pantallas admin.
- **Alertas y toasts:** Preferir el componente `<x-alert>` (ver [docs/alert-component.md](../../docs/alert-component.md)) y los flashes tipo `Session::flash('toast', [...])` / `Session::flash('info', [...])` como en `FamilyController`.
- **Iconos:** Solo Remix Icon (`ri-*`) en vistas Blade y JS.
- **DataTables:** La configuración base (columnas, export, responsive, filtros) está en [resources/views/admin/families/index.blade.php](../../resources/views/admin/families/index.blade.php) y docs `docs/datatable-manager-usage.md`; reutilizar ese patrón al crear nuevas tablas.
- **JS modular:** El punto de entrada es `resources/js/index.js`, que importa módulos de `dashboard`, `modals`, `utils`, etc. Cualquier nueva funcionalidad JS debe registrarse ahí.

## Exportación de Datos

- **Excel/CSV:** Usar `maatwebsite/excel` con clases en [app/Exports](../../app/Exports) (`*ExcelExport`, `*CsvExport`).
- **PDF:** Usar `spatie/laravel-pdf` con vistas en [resources/views/admin/export](../../resources/views/admin/export) para mantener un estilo consistente de reportes.

## Flujos de Desarrollo

- **Setup rápido:** `composer setup` según [README.md](../../README.md) para instalación completa.
- **Setup manual:** `composer install`, `npm install`, copiar `.env`, `php artisan key:generate`, `php artisan migrate --seed`, luego `npm run build`.
- **Desarrollo local:** `composer dev` arranca servidor PHP, Vite, queue listener y logs (`pail`) en paralelo.
- **Testing:** `composer test` o `php artisan test` para el suite de PHPUnit.
- **Calidad:** `./vendor/bin/pint` para formateo y `php artisan pail` para logs en tiempo real.

## Helpers, Localización y Estilo

- **Helpers globales:** Ver [app/Helpers/helpers.php](../../app/Helpers/helpers.php); preferir helpers existentes (por ejemplo `fecha_hoy()`) antes de duplicar lógica.
- **Idiomas:** Texto de interfaz en español usando [lang/es.json](../../lang/es.json) y `laravel-lang/common`; mantener consistencia en mensajes, títulos y validaciones.

## Anti-Patrones y Cambios Sensibles

- No introducir nuevas dependencias de Livewire, Vue, React ni Bootstrap; el front actual se basa en Blade + Tailwind + JS modular.
- No usar iconos distintos a Remix Icon.
- No modificar el sistema de autenticación ni `vite.config.js` sin ajustar en paralelo las importaciones en `resources/js/index.js` y revisar el flujo de build descrito en [README.md](../../README.md).

## Referencias Rápidas

- Modelo base y auditoría: [app/Models/Family.php](../../app/Models/Family.php)
- CRUD de referencia: [app/Http/Controllers/Admin/FamilyController.php](../../app/Http/Controllers/Admin/FamilyController.php)
- Vista index + DataTable: [resources/views/admin/families/index.blade.php](../../resources/views/admin/families/index.blade.php)
- Rutas admin completas: [routes/admin.php](../../routes/admin.php)
- Docs clave: [docs/multiple-delete-global.md](../../docs/multiple-delete-global.md), [docs/quick-status-toggle.md](../../docs/quick-status-toggle.md), [docs/datatable-manager-usage.md](../../docs/datatable-manager-usage.md), [docs/notifications-module.md](../../docs/notifications-module.md)
