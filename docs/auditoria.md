# Módulo de Auditoría

Este documento describe cómo funciona el módulo de auditoría del panel admin: qué registra, qué archivos lo componen y cómo extenderlo a nuevos modelos.

## 1. Objetivo

Registrar de forma centralizada las acciones relevantes del sistema sobre los modelos de datos:

- Cambios en modelos Eloquent (crear, actualizar, eliminar).
- Información de contexto (usuario, IP, user agent, fecha y hora).

Todo se guarda en la tabla `audits` y se accede mediante el modelo `Audit`.

## 2. Estructura general

Archivos principales:

- Migración: `database/migrations/2026_01_02_000000_create_audits_table.php`
- Modelo: `app/Models/Audit.php`
- Trait reusable: `app/Traits/Auditable.php`
- Registro de eventos de autenticación (para logs de acceso, no auditoría de datos): `app/Providers/EventServiceProvider.php`
- Modelos que usan actualmente el trait `Auditable`:
  - `app/Models/Category.php`
  - `app/Models/Product.php`
  - `app/Models/Family.php`
  - `app/Models/User.php`
  - `app/Models/Post.php`
  - `app/Models/Option.php`
  - `app/Models/Role.php`

## 3. Tabla `audits`

Definida en `2026_01_02_000000_create_audits_table.php`:

Campos principales:

- `user_id` (nullable): usuario que realizó la acción.
- `event`: tipo de evento (`created`, `updated`, `deleted`, etc.).
- `auditable_type` / `auditable_id`: modelo y registro afectado (morph clásico de Laravel).
- `old_values` (json, nullable): valores anteriores.
- `new_values` (json, nullable): valores nuevos.
- `ip_address` (nullable): IP desde la que se ejecutó la acción.
- `user_agent` (nullable): navegador / dispositivo.
- Timestamps: `created_at`, `updated_at`.
- Índice: `audits_auditable_index` en (`auditable_type`, `auditable_id`) para consultas rápidas.

## 4. Modelo `Audit`

Ubicación: `app/Models/Audit.php`.

Responsabilidades:

- Representar cada fila de la tabla `audits`.
- `casts`:
  - `old_values` => `array`.
  - `new_values` => `array`.
- Relaciones:
  - `user()` → `belongsTo(User::class)`.
  - `auditable()` → `morphTo()` para acceder al modelo afectado (Category, Product, User, etc.).

Este modelo se usa para reportes, listados y análisis de cambios.

## 5. Trait `Auditable`

Ubicación: `app/Traits/Auditable.php`.

Este trait se agrega a cualquier modelo Eloquent que deba auditarse (por ejemplo Category, Product).

### 5.1. Hook de eventos Eloquent

El método `bootAuditable()` se ejecuta automáticamente cuando el modelo se inicializa y engancha estos eventos:

- `created`: registra una auditoría con `event = created`.
- `updated`: registra solo si hay cambios reales (`getChanges()` no está vacío).
- `deleted`: registra el estado completo del modelo antes de ser eliminado.

### 5.2. Registro de auditoría

Método clave: `recordAudit(string $event)`.

- Obtiene contexto de forma segura:
  - `user_id`: `auth()->id()` si hay usuario y **no** se está en consola; `null` en comandos artisan/seeders.
  - `ip_address` y `user_agent`: desde `request()`, solo en contexto HTTP.
- Resuelve los valores a guardar:
  - `created`: `old_values = null`, `new_values = atributos actuales`.
  - `updated`: se calculan `old_values` y `new_values` solo para los campos que cambiaron.
  - `deleted`: `old_values = atributos originales`, `new_values = null`.
- Crea el registro en la tabla `audits` usando el modelo `Audit`.
- Manejo de errores:
  - Envuelto en `try/catch`.
  - En consola no se lanzan errores que rompan migraciones/seeders.

### 5.3. Cómo activar auditoría en un modelo

Ejemplo: `app/Models/Product.php`.

1. Importar el trait:

```php
use App\Traits\Auditable;
```

2. Usar el trait en la clase:

```php
class Product extends Model
{
    use HasFactory, Auditable;
}
```

Lo mismo aplica para `Category` y cualquier otro modelo (ejemplo: Marca/Brand).

## 6. Tipos de eventos auditados

Actualmente, el módulo registra automáticamente los eventos de datos del modelo:

- `created`: cuando se crea un registro.
- `updated`: cuando se actualiza un registro.
- `deleted`: cuando se elimina un registro (soft o hard delete, según el modelo).

## 7. Flujo completo

1. **Operaciones sobre modelos auditables (Category, Product, etc.):**
   - El controlador/servicio crea, actualiza o elimina un modelo.
   - Eloquent dispara el evento (`created`, `updated`, `deleted`).
   - El trait `Auditable` intercepta el evento y calcula cambios + contexto.
   - Se crea un registro en `audits`.

2. **Eventos de datos en modelos:**
  - Se crean, actualizan o eliminan registros en modelos que usan el trait `Auditable`.
  - El trait captura el contexto (usuario, IP, user agent) y guarda `created` / `updated` / `deleted` en `audits`.

## 8. Cómo consultar auditorías

Uso típico desde código:

```php
use App\Models\Audit;

// Últimas 50 auditorías
$auditLogs = Audit::with('user')
    ->latest()
    ->limit(50)
    ->get();
```

Filtros comunes:

- Por usuario: `Audit::where('user_id', $userId)`.
- Por modelo: `Audit::where('auditable_type', App\Models\Product::class)`.
- Por registro específico: añadir `->where('auditable_id', $productId)`.
- Por rango de fechas: `whereBetween('created_at', [$from, $to])`.

Para vistas de administración, se recomienda crear un controlador dedicado y usar DataTables/filtrado siguiendo los patrones ya usados en el proyecto.

## 9. Controlador y vista de auditorías (panel admin)

Para consultar las auditorías desde el panel se creó un módulo sencillo de solo lectura.

### 9.1. Controlador `AuditController`

Ubicación: `app/Http/Controllers/Admin/AuditController.php`.

- Middleware de permisos:
  - En `__construct()` se aplica `can:auditorias.index` al método `index`.
- Método `index(Request $request)`:
  - Carga las auditorías con el usuario relacionado: `Audit::with('user')->orderByDesc('id')->get();`.
  - Retorna la vista `admin.audits.index` con la colección `$audits`.

Este controlador está pensado como listado global de auditoría, sin crear/editar/borrar registros manualmente.

### 9.2. Rutas admin

Archivo: `routes/admin.php`.

Se añadió un grupo de rutas para auditoría:

```php
use App\Http\Controllers\Admin\AuditController;

Route::controller(AuditController::class)->name('admin.audits.')->group(function () {
    Route::get('/audits', 'index')->name('index');
});
```

La URL de acceso al listado es `/admin/audits` con nombre de ruta `admin.audits.index`.

### 9.3. Vista `admin.audits.index`

Ubicación: `resources/views/admin/audits/index.blade.php`.

- Usa el layout principal: `<x-admin-layout>`.
- Título: "Auditoría del Sistema" con icono `ri-history-line`.
- Controles superiores:
  - Buscador (`#customSearch`) para filtrar por texto.
  - Selector de filas por página (`#entriesSelect`).
  - Filtro por tipo de evento (`#eventFilter`): created, updated, deleted.
- Tabla compatible con `DataTableManager`:
  - ID (`column-id-th/td`).
  - Usuario (badge con nombre o "Sistema / Invitado").
  - Evento (columna con `data-event` y badges por tipo).
  - Modelo (`class_basename(auditable_type)`).
  - ID del modelo (`auditable_id`).
  - IP de origen.
  - Fecha (`created_at` formateado `d/m/Y H:i`).
- JS de inicialización:
  - Crea una instancia de `DataTableManager` para `#tabla` con:
    - `moduleName: 'audits'`.
    - `selection: false`, `statusToggle: false`, `export: false` (solo lectura).
  - Registra un filtro personalizado de DataTables para `#eventFilter`, que compara el valor con `data-event` de cada fila.

Este módulo de interfaz permite a los administradores revisar rápidamente el historial de acciones sin exponer operaciones de escritura sobre la tabla `audits`.

## 10. Extender el módulo

- **Agregar nuevos modelos auditables**:
  - Solo necesitas añadir el trait `Auditable` al modelo.
- **Registrar otros eventos personalizados**:
  - Desde cualquier punto del código puedes llamar directamente a `Audit::create([...])` siguiendo la misma estructura de campos.
- **Relación inversa (opcional)**:
  - Si quieres acceder fácilmente a auditorías desde un modelo, puedes agregar en el modelo:

```php
public function audits()
{
    return $this->morphMany(\App\Models\Audit::class, 'auditable');
}
```

Con esto, podrás hacer `$product->audits` o `$category->audits` para listar sus cambios históricos.

---

Este módulo está diseñado para ser ligero, sin paquetes externos y seguro en contextos de consola (`artisan migrate`, `seeders`), manteniendo un historial consistente de las acciones críticas del sistema.
