# Módulo de notificaciones (Panel Admin)

Este documento describe cómo está implementado el sistema de notificaciones internas del panel de administración, usando el sistema de **Laravel Notifications** con canal `database` y la interfaz de **campana + sidebar derecho**.

## Infraestructura base

### Tabla `notifications`

Laravel utiliza una tabla estándar para el canal `database`. En este proyecto se creó la migración:

- [database/migrations/2026_01_05_000000_create_notifications_table.php](database/migrations/2026_01_05_000000_create_notifications_table.php)

Estructura principal:

- `id` (uuid, PK)
- `type` (clase de la notificación)
- `notifiable_type` / `notifiable_id` (morph hacia el modelo que recibe la notificación, en este caso `App\Models\User`)
- `data` (payload en JSON)
- `read_at` (marca si la notificación fue leída)
- `created_at` / `updated_at`

Para aplicar la migración:

```bash
php artisan migrate
```

### Trait `Notifiable` en `User`

El modelo de usuario ya utiliza el trait `Notifiable`, lo que habilita métodos como:

- `$user->notify($notification)`
- `$user->notifications`
- `$user->unreadNotifications`

Archivo relevante:

- [app/Models/User.php](app/Models/User.php)

## Clase base de notificaciones de admin

Se creó una notificación genérica para el panel admin que se almacena únicamente en base de datos.

Archivo:

- [app/Notifications/AdminDatabaseNotification.php](app/Notifications/AdminDatabaseNotification.php)

Canal utilizado:

- Solo `database`.

Campos que se guardan en `data`:

- `title` (string, obligatorio): título corto de la notificación.
- `body` (string, opcional): descripción o detalle.
- `url` (string, opcional): URL a la que se redirige al hacer clic.
- `icon` (string, opcional): clase de icono Remix (ej. `ri-mail-line`).
- `level` (string, opcional): nivel semántico (`info`, `success`, `warning`, `error`, etc.).

### Ejemplo de uso en PHP

```php
use App\Models\User;
use App\Notifications\AdminDatabaseNotification;

$user = User::first();

$user->notify(new AdminDatabaseNotification(
    title: 'Nuevo mensaje de soporte',
    body: 'Tienes un ticket pendiente de revisión.',
    url: route('admin.dashboard'),
    icon: 'ri-customer-service-2-line',
    level: 'info',
));
```

## Integración con la interfaz del panel

### Campana en la barra superior

La campana en la topbar muestra el contador real de notificaciones **no leídas** del usuario autenticado.

Archivo:

- [resources/views/partials/admin/navigation.blade.php](resources/views/partials/admin/navigation.blade.php)

Comportamiento:

- Calcula `auth()->user()->unreadNotifications()->count()`.
- Si el conteo es mayor que 0, se muestra el badge en la campana.
- Al hacer clic en la campana se abre el sidebar derecho enfocado en la pestaña **Notificaciones**.

### Sidebar derecho (Perfil / Notificaciones)

El sidebar derecho tiene dos pestañas: **Perfil** y **Notificaciones**.

Archivo:

- [resources/views/partials/admin/sidebar-right.blade.php](resources/views/partials/admin/sidebar-right.blade.php)

En la pestaña **Notificaciones**:

- Se cargan las últimas 15 notificaciones del usuario autenticado:
  - `$notifications = $user->notifications()->latest()->limit(15)->get();`
- Se muestra un contador en el pill de la pestaña con el número de no leídas (`$unreadCount`).
- Cada ítem de la lista muestra:
  - Icono (campo `icon` o `ri-notification-3-line` por defecto).
  - Título (`title`).
  - Texto opcional (`body`).
  - Tiempo relativo (`created_at->diffForHumans()`).
- Las notificaciones no leídas se destacan visualmente con la clase `is-unread`.
- Si no hay notificaciones, se muestra un mensaje "No tienes notificaciones por ahora.".
- Cuando hay no leídas, aparece un botón **"Marcar todas como leídas"** que dispara una acción backend.

Estilos asociados:

- [resources/css/layout-admin.css](resources/css/layout-admin.css)

Clases clave:

- `.sidebar-notification-item.is-unread`
- `.sidebar-notification-text`
- `.sidebar-notification-title`
- `.sidebar-notification-body`
- `.sidebar-notification-meta`

## Controlador de notificaciones

La lógica backend para redirección y marcado como leído está centralizada en:

- [app/Http/Controllers/Admin/NotificationController.php](app/Http/Controllers/Admin/NotificationController.php)

Rutas registradas en:

- [routes/admin.php](routes/admin.php)

### Rutas disponibles

- `GET /admin/notifications/{notification}/redirect`
  - Nombre: `admin.notifications.redirect`.
  - Marca la notificación como leída y redirige a la URL definida en `data['url']`.
  - Si no hay `url`, redirige al dashboard (`admin.dashboard`).

- `POST /admin/notifications/mark-all-as-read`
  - Nombre: `admin.notifications.mark-all-as-read`.
  - Marca todas las notificaciones no leídas del usuario autenticado como leídas.
  - Si la petición espera JSON, responde `{ status: "ok" }`. En caso contrario, hace `back()`. 

### Seguridad

Antes de actuar sobre una notificación se comprueba que pertenece al usuario autenticado mediante:

- Comparación de `notifiable_id` y `notifiable_type` con el usuario actual.
- Si no coincide, se devuelve un `403`.

## Flujo completo

1. **Crear notificación**
   - Desde cualquier controlador/servicio del admin, se puede notificar a un usuario (o a varios):

   ```php
   $user->notify(new AdminDatabaseNotification(
       title: 'Producto actualizado',
       body: 'El producto XYZ fue modificado correctamente.',
       url: route('admin.products.index'),
       icon: 'ri-check-line',
       level: 'success',
   ));
   ```

2. **Almacenamiento**
   - La notificación se guarda en la tabla `notifications` con `read_at = null`.

3. **Visualización**
   - La campana de la topbar muestra el número de no leídas.
   - El sidebar derecho, pestaña **Notificaciones**, lista las últimas notificaciones.

4. **Interacción del usuario**
   - Al hacer clic en una notificación:
     - Se marca como leída.
     - Se redirige a la URL indicada en el campo `url` (o al dashboard si no hay).
   - Al pulsar **"Marcar todas como leídas"**:
     - Todas las notificaciones no leídas del usuario se marcan como leídas.

## Flujos implementados actualmente

Actualmente el proyecto utiliza el módulo de notificaciones en estos casos:

- **Posts**
  - Cuando se crea un post en estado `pending`:
    - Se notifica a los usuarios con roles `Administrador` y `Superadministrador` que hay un nuevo post pendiente de revisión.
  - Cuando un revisor **aprueba** un post pendiente:
    - Se notifica al autor (`created_by`) que su post ha sido aprobado y publicado.
  - Cuando un revisor **rechaza** un post pendiente:
    - Se notifica al autor que su post ha sido rechazado.

- **Roles y permisos**
  - Cuando se actualizan los permisos de los roles críticos `Administrador` o `Superadministrador`:
    - Se notifica a todos los usuarios con rol `Superadministrador`, incluyendo el detalle de permisos añadidos y removidos.

## Cómo probar el módulo

1. Asegúrate de tener la migración aplicada:

```bash
php artisan migrate
```

2. En un entorno local, abre Tinker:

```bash
php artisan tinker
```

3. Envía una notificación de prueba al usuario admin:

```php
use App\Models\User;
use App\Notifications\AdminDatabaseNotification;

$user = User::first();

$user->notify(new AdminDatabaseNotification(
    title: 'Notificación de prueba',
    body: 'Este es un mensaje de prueba desde Tinker.',
    url: route('admin.dashboard'),
    icon: 'ri-notification-3-line',
));
```

4. Actualiza el panel admin:
   - Verás el contador de la campana y de la pestaña **Notificaciones**.
   - Al abrir el sidebar derecho podrás ver la notificación, hacer clic en ella y verificar que se marca como leída.

## Extensiones futuras

- Definir notificaciones específicas para eventos clave (nuevos pedidos, errores de sincronización, mensajes de soporte, etc.).
- Añadir filtros en la vista de notificaciones (solo no leídas, por módulo, por nivel, etc.).
- Integrar un canal adicional (por ejemplo, email) para ciertos tipos de notificaciones críticas.
