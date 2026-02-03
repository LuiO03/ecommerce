
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
# Instrucciones para agentes IA (GECKOMERCE)

## Panorama y arquitectura
- Stack: Laravel 12 + PHP 8.2 + MySQL; Blade + Livewire 3 + TailwindCSS 3 + Flowbite; Spatie Permission; Maatwebsite Excel + Spatie Laravel PDF.
- Catálogo jerárquico: Familias → Categorías (anidables) → Productos → Variantes, con Options/Features. Ver docs/category-hierarchy-manager.md y docs/product-variants-manager.md.
- Auditoría automática: usar App\Traits\Auditable (created_by/updated_by/deleted_by + audits). Patrón en app/Models/Family.php y docs/auditoria.md.

## Flujos de desarrollo
- Setup completo: composer setup.
- Dev local: composer dev (serve + queue:listen + pail + vite).
- Tests/formato: composer test y ./vendor/bin/pint.

## Convenciones clave
- Rutas admin solo en routes/admin.php con nombre admin.entidad.accion y prefijo /admin (bootstrap/app.php).
- Permisos en controladores admin: {entidad_plural}.{accion} (ej: familias.index). Ver app/Http/Controllers/Admin/FamilyController.php.
- Slugs y route model binding: getRouteKeyName() => 'slug' y generateUniqueSlug() como en app/Models/Family.php.
- SoftDeletes no es global: solo Post y CompanySetting actualmente.

## Frontend (Blade/JS/CSS)
- Layout base: resources/views/layouts/admin.blade.php con <x-admin-layout>.
- Assets: entry point único resources/js/app.js + resources/css/app.css; CSS específico admin/site se carga con @vite() en layouts. No agregar nuevos entry points sin revisar vistas.
- JS modular: módulos en resources/js/modules o resources/js/utils e importados en app.js (no solo en index.js). Exportar a window si se usan desde Blade.
- DataTables: estructura en resources/views/admin/families/index.blade.php; lógica en resources/js/utils/datatable-manager.js y docs/datatable-manager-usage.md.
- Alertas: <x-alert> (app/View/Components/Alert.php y resources/views/partials/components/alert.blade.php).
- Iconos: solo Remix Icon (ri-*).

## Integraciones y patterns
- Export Excel/PDF: app/Exports/* + vistas en resources/views/admin/export/.
- Toggle de estado: En **tablas DataTable** se usa resources/js/utils/datatable-manager.js (config.statusRoute). En **galerías/tarjetas** se usa script inline en la vista (evita problemas de timing). Ver ejemplo en resources/views/admin/covers/index.blade.php.
- Eliminación múltiple: docs/multiple-delete-global.md.

## Referencias rápidas
- Controlador CRUD canónico: app/Http/Controllers/Admin/FamilyController.php.
- Modelo de referencia: app/Models/Family.php.
- Documentación técnica: carpeta docs/.
```
