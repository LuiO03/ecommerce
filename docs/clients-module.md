# Módulo de Clientes (Panel Admin)

Este documento describe el módulo **Clientes** del panel de administración, pensado para gestionar de forma separada a los usuarios finales con rol `Cliente`, diferenciándolos de los usuarios internos del panel (administradores, editores, etc.).

## 1. Objetivo

- Centralizar la gestión de cuentas con rol **Cliente** (usuarios de la tienda) en un módulo dedicado.
- Mantener limpio el módulo de **Usuarios** del panel, que se enfoca en cuentas internas.
- Permitir **filtros avanzados**, **exportaciones** (Excel/CSV/PDF) y **acciones masivas** específicamente sobre clientes.

## 2. Archivos clave

- Controlador principal: [app/Http/Controllers/Admin/ClientController.php](app/Http/Controllers/Admin/ClientController.php)
- Modelo base: reutiliza [app/Models/User.php](app/Models/User.php) filtrado por rol `Cliente`.
- Exportaciones:
  - [app/Exports/ClientsExcelExport.php](app/Exports/ClientsExcelExport.php)
  - [app/Exports/ClientsCsvExport.php](app/Exports/ClientsCsvExport.php)
- Vistas:
  - Listado: [resources/views/admin/clients/index.blade.php](resources/views/admin/clients/index.blade.php)
  - Exportación PDF: [resources/views/admin/export/clients-pdf.blade.php](resources/views/admin/export/clients-pdf.blade.php)
- Rutas admin: [routes/admin.php](routes/admin.php)

## 3. Permisos y rutas

### 3.1 Permisos

En el seeder de roles y permisos se define un grupo específico para el módulo Clientes:

- `clientes.index` – Ver la lista de clientes.
- `clientes.delete` – Eliminar clientes (acciones individuales y masivas).
- `clientes.export` – Exportar clientes en distintos formatos.
- `clientes.update-status` – Activar / desactivar clientes.

Archivo relacionado:

- [database/seeders/RolePermissionSeeder.php](database/seeders/RolePermissionSeeder.php)

Asigna estos permisos a los roles administrativos que deban gestionar clientes (por ejemplo, Administrador o Superadministrador).

### 3.2 Rutas

Grupo registrado en `routes/admin.php`:

- `GET  /admin/clients` → `ClientController@index`  
  Nombre: `admin.clients.index`
- `POST /admin/clients/export/excel` → `ClientController@exportExcel`  
  Nombre: `admin.clients.export.excel`
- `POST /admin/clients/export/pdf` → `ClientController@exportPdf`  
  Nombre: `admin.clients.export.pdf`
- `POST /admin/clients/export/csv` → `ClientController@exportCsv`  
  Nombre: `admin.clients.export.csv`

El middleware de permisos se aplica en el `__construct` del controlador `ClientController`.

## 4. Listado y DataTableManager

La vista [admin/clients/index.blade.php](resources/views/admin/clients/index.blade.php) replica el patrón del módulo de usuarios, pero filtrando solo usuarios con rol `Cliente`.

Características principales del listado:

- **Búsqueda global** por nombre o email (`#customSearch`).
- **Paginación configurable** (`#entriesSelect`).
- **Ordenación rápida** (nombre asc/desc, fecha más recientes/antiguos).
- **Filtros adicionales**:
  - Estado (activos / inactivos).
  - Verificación de email (verificado / sin verificar).
  - Rol (`Cliente` o `sin-rol`).
- **Selección múltiple** con barra de acciones cuando hay filas seleccionadas.

El JS inicializa `DataTableManager` con una configuración específica:

- `moduleName: 'clients'` – Identificador del módulo (para claves de almacenamiento y mensajes).
- `entityNameSingular: 'cliente'`, `entityNamePlural: 'clientes'`.
- Rutas:
  - `deleteRoute: '/admin/users'` – Reutiliza el endpoint de usuarios para eliminar.
  - `statusRoute: '/admin/users/{id}/status'` – Cambio de estado para el mismo modelo `User`.
  - `exportRoutes` para Excel/CSV/PDF bajo `/admin/clients/export/*`.
- `features` activas: selección, exportación, filtros, status toggle, responsive y paginación personalizada.

Para más detalles sobre las opciones comunes de tablas, ver:  
[docs/datatable-manager-usage.md](docs/datatable-manager-usage.md) y [docs/multiple-delete-global.md](docs/multiple-delete-global.md).

## 5. Exportaciones y auditoría

### 5.1 Excel y CSV

- **Excel**: [ClientsExcelExport](app/Exports/ClientsExcelExport.php)
  - Genera un reporte con cabecera estilizada, ancho de columnas ajustado y filas con formato alternado.
  - Soporta exportar **todos los clientes** o solo un subconjunto por `ids`.
- **CSV**: [ClientsCsvExport](app/Exports/ClientsCsvExport.php)
  - Exporta los mismos campos en formato plano (`.csv`).

Ambos métodos reciben opcionalmente una lista de IDs desde el front (selección múltiple en la tabla) o un flag `export_all` para exportar todo el módulo.

### 5.2 PDF

- **PDF**: [resources/views/admin/export/clients-pdf.blade.php](resources/views/admin/export/clients-pdf.blade.php)  
  Generado a través de `Spatie\LaravelPdf`, con tabla estilizada y resumen de totales.

### 5.3 Auditoría de exportaciones

Cada exportación crea un registro en la tabla `audits` usando el modelo [app/Models/Audit.php](app/Models/Audit.php):

- `auditable_type` = `App\Models\User`.
- `auditable_id` = `null` (exportación a nivel de módulo).
- `event` = `excel_exported`, `csv_exported` o `pdf_exported`.
- `new_values` incluye:
  - `ids` (si hubo selección puntual).
  - `export_all` (booleano).
  - `filename` (nombre del archivo generado).
  - `module` = `clientes` (para distinguirlo de otros usos de `User`).

El modelo `Audit` tiene lógica específica para mostrar estas acciones como parte del módulo **Clientes** en la interfaz de auditoría.

Para más detalles sobre el sistema de auditoría general, ver:  
[docs/auditoria.md](docs/auditoria.md).

## 6. Integración en el panel admin

El módulo Clientes se integra en varios puntos de la UI:

- **Dashboard**: tarjeta "Clientes" que muestra `totalClients` cuando el usuario tiene permiso `clientes.index`.  
  Vista: [resources/views/admin/dashboard.blade.php](resources/views/admin/dashboard.blade.php).
- **Sidebar izquierdo**: enlace bajo el submenú "Gestión de Acceso".
  - Archivo: [resources/views/partials/admin/sidebar-left.blade.php](resources/views/partials/admin/sidebar-left.blade.php).
- **Breadcrumb**: soporte para la clave `clients` con icono dedicado.  
  Archivo: [resources/views/partials/admin/breadcrumb.blade.php](resources/views/partials/admin/breadcrumb.blade.php).

## 7. Comportamientos especiales con Clientes

- El listado de **Usuarios** ([UserController](app/Http/Controllers/Admin/UserController.php)) **excluye** a los usuarios con rol `Cliente`; estos solo aparecen en el módulo Clientes.
- El listener de logins exitosos omite registrar accesos para usuarios con rol `Cliente` para no saturar el módulo de logs administrativos.  
  Ver [app/Listeners/LogSuccessfulLogin.php](app/Listeners/LogSuccessfulLogin.php).
- El trait de auditoría [app/Traits/Auditable.php](app/Traits/Auditable.php) ignora la creación de usuarios cuando se trata del **autoregistro público en `/register`** y el usuario está invitado, de modo que no se registren auditorías redundantes por cada alta de cliente.

## 8. Cómo probar el módulo

1. Ejecuta migraciones y seeders si aún no lo hiciste:
   ```bash
   php artisan migrate:fresh --seed
   ```
2. Inicia el entorno de desarrollo:
   ```bash
   composer dev
   ```
3. Entra al panel admin con un usuario que tenga permisos sobre clientes.
4. Navega a `/admin/clients` desde la sidebar o el dashboard.
5. Prueba:
   - Búsqueda, filtros y paginación.
   - Selección múltiple y barra de acciones.
   - Exportaciones (Excel, CSV, PDF), verificando que se descargan los archivos.
   - Cambios de estado (si el rol tiene `clientes.update-status`).
6. Revisa el módulo de auditorías `/admin/audits` para ver los registros generados por las exportaciones de clientes.
