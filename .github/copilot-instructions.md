# Guía para Agentes IA en GECKОМERCE

## Arquitectura y Componentes Clave

- **Stack:** Laravel 12, Livewire 3, Jetstream, TailwindCSS 3, Flowbite, DataTables
- **Estructura de Catálogo:**
    - Familias → Categorías (anidables) → Productos → Variantes
    - Auditoría automática en modelos principales (`created_by`, `updated_by`, `deleted_by`)
- **Rutas Admin:**
    - Definidas en `routes/admin.php` con prefijo `/admin` y middleware `['web', 'auth', 'verified']`
    - No usar `->name('admin.')` en prefijos

## Convenciones y Patrones

- **Slugs únicos:** Usa `Model::generateUniqueSlug()` (ver `Family.php`)
- **Route Model Binding:** URLs usan `slug` en vez de `id`
- **Scopes reutilizables:** Ejemplo: `scopeForTable()` para DataTables
- **Auditoría:** No implementar manualmente, seguir patrón de `Family.php`

## Componentes Frontend

- **Layout Admin:**
    - Blade: `<x-admin-layout>` en `resources/views/layouts/admin.blade.php`
    - Iconos: Remix Icon (`ri-*`), no usar otros
- **Alertas:**
    - Componente `<x-alert>` (ver `docs/alert-component.md`)
    - Parámetros: `type`, `title`, `items`, `dismissible`, `icon`, `data-persist-key`, `data-auto-dismiss`

## JavaScript y DataTables

- **Eliminación múltiple:**
    - Usa `handleMultipleDelete` (ver `resources/js/modals/modal-confirm.js` y `docs/multiple-delete-global.md`)
- **Toggle de estado rápido:**
    - Patrón en `docs/quick-status-toggle.md`
- **DataTables:**
    - Patrón en `views/admin/families/index.blade.php` (export, responsive)

## Exportación de Datos

- **Excel/CSV:** `maatwebsite/excel` (ver `app/Exports/*Export.php`)
- **PDF:** `spatie/laravel-pdf` (ver `admin/export/*.blade.php`)

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

- **Helpers globales:** `app/Helpers/helpers.php` (ej: `fecha_hoy()`)
- **Localización:** Español (`lang/es.json`, `laravel-lang/common`)

## Anti-Patrones

- No usar Livewire, Vue, React, Bootstrap, ni iconos distintos a Remix Icon
- No modificar autenticación ni `vite.config.js` sin actualizar `resources/js/index.js`

## Referencias Clave

- CRUD: `app/Http/Controllers/Admin/FamilyController.php`
- Modelo base: `app/Models/Family.php`
- Vista index: `resources/views/admin/families/index.blade.php`
- Docs técnicas: `docs/multiple-delete-global.md`, `docs/quick-status-toggle.md`
