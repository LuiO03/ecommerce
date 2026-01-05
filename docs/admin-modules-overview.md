# Overview de Módulos del Admin

Este documento resume las funcionalidades actuales del panel de administración del ecommerce, cómo se organizan los módulos y qué patrones comunes se reutilizan.

## 1. Arquitectura General

- **Stack**: Laravel 12, Livewire 3, Jetstream, TailwindCSS 3, Flowbite, DataTables.
- **Modelo de datos principal**:
  - Familias → Categorías (anidables) → Productos → Variantes.
  - Opciones y características como atributos reutilizables de productos.
- **Rutas admin**: definidas en `routes/admin.php` con prefijo `/admin` y middleware `['web', 'auth', 'verified']`.
- **Auditoría**: modelos clave usan el trait `App\\Traits\\Auditable` y operaciones especiales registran auditorías manuales (exportaciones, eliminaciones masivas, cambios de configuración).
- **Exportaciones**: Excel/CSV vía `maatwebsite/excel` y PDF vía `spatie/laravel-pdf`.

## 2. Dashboard

**Controlador**: `app/Http/Controllers/Admin/AdminController.php`

- Muestra contadores de entidades principales: categorías, familias, productos, usuarios, roles, posts, opciones y logs de acceso.
- Recupera el nombre de la empresa desde `App\Models\CompanySetting` (con fallback a "Mi Empresa").
- Vista: `resources/views/admin/dashboard.blade.php` (usa layout `<x-admin-layout>` y componentes comunes).

## 3. Configuración de Empresa

**Controlador**: `app/Http/Controllers/Admin/CompanySettingController.php`

- Formulario único con pestañas/partials para:
  - **General**: nombre comercial, razón social, RUC, eslogan, texto "sobre la empresa".
  - **Identidad visual**: colores principales y logotipo (subida y eliminación de imagen en `storage/app/public/company`).
  - **Contacto**: emails, teléfonos, dirección física, sitio web.
  - **Redes sociales**: URLs y flags por red (facebook, instagram, twitter, youtube, tiktok, linkedin).
  - **Legal**: términos y condiciones, política de privacidad, información de libro de reclamaciones.
- Toda la configuración se cachea bajo la clave `company_settings` y se invalida al guardar.
- Cada sección registra una auditoría específica (`company_general_updated`, `company_identity_updated`, etc.) sobre el modelo `CompanySetting`.
- Vista principal: `resources/views/admin/company-settings/index.blade.php`.

## 4. Módulo de Accesos (Access Logs)

**Modelo**: `app/Models/AccessLog.php`

**Controlador**: `app/Http/Controllers/Admin/AccessLogController.php`

- Listado paginado de accesos con filtros por:
  - Acción (`action`).
  - Estado (`status`).
  - Usuario (`user_id`).
  - Rango de fechas (`from`, `to`).
- Relación con usuario (`user:id,name,last_name,email`).
- Exportaciones:
  - Excel: `AccessLogsExcelExport`.
  - CSV: `AccessLogsCsvExport`.
  - PDF: vista `resources/views/admin/export/access-logs-pdf.blade.php`.
- Rutas principales: grupo `admin.access-logs.*` en `routes/admin.php`.

Para detalles de auditoría de datos ver `docs/auditoria.md`.

## 5. Auditoría de Modelos

**Modelo**: `app/Models/Audit.php`

**Trait**: `app/Traits/Auditable.php`

- Registra automáticamente `created`, `updated` y `deleted` en la tabla `audits` para modelos que usan el trait.
- Modelos auditables actuales:
  - `Category`, `Product`, `Family`, `User`, `Post`, `Option`.
- Controlador de consulta: `app/Http/Controllers/Admin/AuditController.php` con listado en `admin.audits.index`.
- Muchas operaciones administrativas (exportaciones, eliminaciones masivas, cambios de configuración) crean registros adicionales en `audits` describiendo la acción.

Ver documentación detallada en `docs/auditoria.md`.

## 6. Catálogo: Familias y Categorías

### 6.1 Familias

**Modelo**: `app/Models/Family.php`

**Controlador**: `app/Http/Controllers/Admin/FamilyController.php`

- CRUD completo con slug único (`Family::generateUniqueSlug()`).
- Campos: nombre, descripción, estado, imagen opcional, auditoría (`created_by`, `updated_by`, `deleted_by`).
- Subida y sustitución segura de imágenes en `storage/app/public/families`.
- Eliminación múltiple con restricciones:
  - No permite eliminar familias que tengan categorías asociadas.
  - Registra auditoría tipo `bulk_deleted` con IDs y nombres.
- Exportaciones Excel/CSV/PDF (`FamiliesExcelExport`, `FamiliesCsvExport`, vista `admin.export.families-pdf`).
- Integrado con DataTables y utilidades globales (ver `docs/datatable-manager-usage.md` y `docs/multiple-delete-global.md`).

### 6.2 Categorías

**Modelo**: `app/Models/Category.php`

**Controlador**: `app/Http/Controllers/Admin/CategoryController.php`

- Categorías anidables con relación a **Familia** y **padre** (`parent_id`).
- Campos: nombre, descripción, estado, imagen, familia, padre, slug único.
- Endpoints clave:
  - `index`: listado con familia y padre.
  - `show`: devuelve JSON con datos completos, familia efectiva, padre, subcategorías recursivas y metadatos de auditoría.
  - `create`/`edit`: generan/consumen estructuras jerárquicas para el selector de jerarquía.
- Subida, reemplazo y eliminación de imagen de categoría.
- Exportaciones Excel/CSV/PDF con auditoría de exportación.

### 6.3 Gestor de Jerarquía de Categorías

**Controlador**: `app/Http/Controllers/Admin/CategoryHierarchyController.php`

- Endpoints para gestión avanzada del árbol de categorías:
  - `index`: vista principal del gestor.
  - `getTreeData`: devuelve el árbol de categorías para renderizar en el front.
  - `bulkMove`, `bulkDelete`, `bulkDuplicate`: operaciones masivas sobre ramas.
  - `previewMove`: previsualiza el impacto de un movimiento masivo.
  - `dragMove`: soporte para drag & drop.
- Frontend: módulo JS `resources/js/modules/category-hierarchy-manager.js`.

Ver documentación detallada en `docs/category-hierarchy-manager.md` y `docs/category-drag-drop.md`.

## 7. Catálogo: Productos, Variantes, Opciones y Características

### 7.1 Productos

**Modelo**: `app/Models/Product.php`

**Controlador**: `app/Http/Controllers/Admin/ProductController.php`

- CRUD de productos con slug único (`Product::generateUniqueSlug()`).
- Campos principales: SKU, nombre, descripción, precio, descuento, stock mínimo, estado, categoría.
- Relaciones:
  - `category()` → categoría a la que pertenece.
  - `variants()` → variantes de producto.
  - `images()` → galería de imágenes ordenadas.
  - `options()` → opciones/atributos asociados (pivot con `value`).
- Funcionalidades destacadas:
  - Listado con totales de variantes, imágenes y suma de stock.
  - Formulario de creación/edición con selección de categoría y opciones.
  - Gestión de galería de imágenes (subida, eliminación, imagen principal, orden).
  - Exposición de datos vía `show($slug)` como JSON rico (incluye categoría, variantes, características y metadatos de auditoría).
  - Ajuste de stock por variante (`adjustStock`, protegido por permiso `productos.adjust-stock`).
- Exportaciones Excel/CSV/PDF con auditorías de exportación.

### 7.2 Variantes

**Modelo**: `app/Models/Variant.php`

- Variante de producto con SKU propio, precio, stock y estado.
- Relación con producto y con características (features) para describir combinaciones (talla, color, etc.).

### 7.3 Opciones y Características

**Modelos**: `app/Models/Option.php`, `app/Models/Feature.php`

**Controladores**: `app/Http/Controllers/Admin/OptionController.php`, `OptionFeatureController.php`

- Opciones: definiciones genéricas de atributos (p.ej. "Color", "Talla").
- Características: valores concretos asociados a una opción (p.ej. "Rojo", "M / Grande").
- Uso principal:
  - Configurar conjuntos de atributos reutilizables.
  - Asociar combinaciones de características a variantes de producto.
- El módulo de opciones expone rutas para CRUD de opciones, añadir/eliminar características y renderizado de ítems en el formulario de producto.

## 8. Contenido: Posts y Tags

**Modelos**: `app/Models/Post.php`, `app/Models/PostImage.php`, `app/Models/Tag.php`

**Controlador**: `app/Http/Controllers/Admin/PostController.php`

- Blog o sección de contenidos con:
  - Estados: `draft`, `pending`, `published`, `rejected`.
  - Visibilidad: `public`, `private`, `registered`.
  - Control de comentarios (`allow_comments`).
  - Conteo de vistas (`views`).
- Gestión de imágenes:
  - Imagen principal opcional.
  - Galería adicional con orden e imagen principal seleccionable.
  - Eliminación segura de archivos en `storage/app/public/posts`.
- Tags: asignación múltiple de etiquetas.
- Flujo de revisión:
  - Rutas `approve` y `reject` protegidas por permiso `posts.review`.
- Eliminación múltiple con limpieza de imágenes, soft delete silencioso y auditoría `bulk_deleted`.
- Exportaciones Excel/CSV/PDF con auditoría.

## 9. Usuarios, Roles y Permisos

### 9.1 Usuarios

**Modelo**: `app/Models/User.php`

**Controlador**: `app/Http/Controllers/Admin/UserController.php`

- Basado en Jetstream + Fortify, extendido con:
  - Slug por nombre (`User::generateUniqueSlug()`).
  - Campos adicionales: apellido, DNI, teléfono, dirección, avatar, estado.
  - Metadatos de seguridad (intentos fallidos, bloqueos, último login, etc.).
- Panel admin:
  - Listado de usuarios con roles, estado y verificación de email.
  - CRUD con subida de foto de perfil (carpeta `users/`).
  - Sincronización de roles Spatie.
  - Eliminación múltiple y simple con limpieza de archivos.
  - Exportaciones Excel/CSV/PDF con auditoría.

### 9.2 Roles y Permisos

**Modelos**: `Spatie\Permission\Models\Role`, `Spatie\Permission\Models\Permission` (modelo local `app/Models/Permission.php` para conveniencia).

**Controladores**: `app/Http/Controllers/Admin/RoleController.php`, `PermissionController.php`

- Roles:
  - Listado, creación, edición y eliminación de roles.
  - Gestión de permisos asignados a cada rol (`permissions`, `updatePermissions`).
  - Exportaciones Excel/CSV/PDF de roles.
- Permisos:
  - CRUD básico de permisos.
- Integración completa con middleware `can:*` en todos los controladores admin.

### 9.3 Perfil de Usuario

**Controlador**: `app/Http/Controllers/Admin/ProfileController.php`

- Permite al usuario autenticado:
  - Actualizar datos de perfil.
  - Cambiar contraseña.
  - Eliminar su foto de perfil.
  - Cerrar sesión en otros dispositivos (`logoutSession`).
  - Exportar su propia información en Excel/PDF/CSV.

## 10. Patrones de Listado, Estado y Eliminación

El sistema reutiliza varios componentes front y patrones JS comunes (ver carpeta `docs/`).

- **DataTableManager** (`docs/datatable-manager-usage.md`):
  - Gestiona selección múltiple, filtros, paginación y exportación.
- **Quick Status Toggle** (`docs/quick-status-toggle.md`):
  - Cambio de estado rápido vía AJAX con feedback visual.
- **Eliminación múltiple global** (`docs/multiple-delete-global.md`):
  - Lógica centralizada de confirmación y envío de `ids[]`.
- **Validación y feedback de formularios**:
  - `docs/form-validator-usage.md`, `docs/validation-visual-indicators.md`, `docs/submit-button-loader.md`, `docs/form-validator-submit-loader-integration.md`.
- **Subida de imágenes**:
  - `docs/image-upload-handler.md` documenta el patrón general de previsualización y gestión de archivos.

## 11. Componentes UI Compartidos

- **Componente de Alertas**: ver `docs/alert-component.md`.
- **Colores de badges y estados**: ver `docs/badge-colors.md`.
- **Gestión visual de jerarquía de categorías**: ver `docs/category-hierarchy-manager.md` y `docs/category-drag-drop.md`.

---

Este overview sirve como mapa de funcionalidades del sistema. Para extender un módulo existente, se recomienda:

1. Revisar su controlador en `app/Http/Controllers/Admin`.
2. Revisar su modelo asociado en `app/Models` y, si aplica, el trait `Auditable`.
3. Seguir los patrones de DataTableManager, Quick Status Toggle y eliminación múltiple descritos en la carpeta `docs/`.
