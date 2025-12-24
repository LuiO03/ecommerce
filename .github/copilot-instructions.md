
# Guía para Agentes IA en GECKОМERCE

## Arquitectura y Componentes Clave

- **Stack:** Laravel 12, Livewire 3, Jetstream, TailwindCSS 3, Flowbite, DataTables
- **Catálogo:** Familias → Categorías (anidables) → Productos → Variantes. Auditoría automática en modelos principales (`created_by`, `updated_by`, `deleted_by`).
- **Rutas Admin:** Definidas en [routes/admin.php](../../routes/admin.php) con prefijo `/admin` y middleware `['web', 'auth', 'verified']`. No usar `->name('admin.')` en prefijos.

## Convenciones y Patrones

- **Slugs únicos:** Usa `Model::generateUniqueSlug()` (ver [app/Models/Family.php](../../app/Models/Family.php)).
- **Route Model Binding:** URLs usan `slug` en vez de `id`.
- **Scopes reutilizables:** Ejemplo: `scopeForTable()` para DataTables.
- **Auditoría:** No implementar manualmente, seguir patrón de [Family.php](../../app/Models/Family.php).

## Componentes Frontend

- **Layout Admin:** Blade `<x-admin-layout>` ([resources/views/layouts/admin.blade.php](../../resources/views/layouts/admin.blade.php)).
- **Iconos:** Solo Remix Icon (`ri-*`).
- **Alertas:** Componente `<x-alert>` ([docs/alert-component.md](../../docs/alert-component.md)). Parámetros: `type`, `title`, `items`, `dismissible`, `icon`, `data-persist-key`, `data-auto-dismiss`.

## JavaScript y DataTables

- **Eliminación múltiple:** Usa `handleMultipleDelete` ([resources/js/modals/modal-confirm.js](../../resources/js/modals/modal-confirm.js), [docs/multiple-delete-global.md](../../docs/multiple-delete-global.md)).
- **Toggle de estado rápido:** Patrón en [docs/quick-status-toggle.md](../../docs/quick-status-toggle.md).
- **DataTables:** Patrón en [resources/views/admin/families/index.blade.php](../../resources/views/admin/families/index.blade.php) (export, responsive).

## Exportación de Datos

- **Excel/CSV:** `maatwebsite/excel` ([app/Exports/*Export.php](../../app/Exports)).
- **PDF:** `spatie/laravel-pdf` ([resources/views/admin/export/*.blade.php](../../resources/views/admin/export)).

## Flujos de Desarrollo

- **Setup:**
  - `composer install; npm install; cp .env.example .env; php artisan key:generate; php artisan migrate --seed; npm run build`
- **Desarrollo local:**
  - `composer dev` (servidor, queue, logs, vite concurrentes)
- **Testing:**
  - `composer test` (PHPUnit)
- **Calidad:**
  - `php artisan pail` (logs), `./vendor/bin/pint` (formatter)

## Helpers y Localización

- **Helpers globales:** [app/Helpers/helpers.php](../../app/Helpers/helpers.php) (ej: `fecha_hoy()`)
- **Localización:** Español ([lang/es.json](../../lang/es.json), `laravel-lang/common`)

## Anti-Patrones

- No usar Livewire, Vue, React, Bootstrap, ni iconos distintos a Remix Icon
- No modificar autenticación ni `vite.config.js` sin actualizar [resources/js/index.js](../../resources/js/index.js)

## Referencias Clave

- CRUD: [app/Http/Controllers/Admin/FamilyController.php](../../app/Http/Controllers/Admin/FamilyController.php)
- Modelo base: [app/Models/Family.php](../../app/Models/Family.php)
- Vista index: [resources/views/admin/families/index.blade.php](../../resources/views/admin/families/index.blade.php)
- Docs técnicas: [docs/multiple-delete-global.md](../../docs/multiple-delete-global.md), [docs/quick-status-toggle.md](../../docs/quick-status-toggle.md)
