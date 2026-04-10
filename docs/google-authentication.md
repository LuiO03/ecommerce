# Autenticación con Google (Socialite)

## Objetivo

Permitir que los usuarios inicien sesión y se registren en el ecommerce usando su cuenta de Google, integrando Laravel Socialite con el modelo `User`, el panel público y el panel administrativo, sin romper el flujo actual de roles y permisos.

---

## Requisitos

- Laravel 12
- Paquete Socialite instalado en `composer.json`:

  - `laravel/socialite` (ya agregado como dependencia)

- Configuración de credenciales OAuth de Google en la consola de Google Cloud.

---

## Variables de entorno

En `.env` deben existir estas variables (valores de ejemplo):

```env
GOOGLE_CLIENT_ID=tu_client_id_de_google
GOOGLE_CLIENT_SECRET=tu_client_secret_de_google
GOOGLE_REDIRECT_URI=http://ecommerce.com/google-auth/callback
```

> **Importante:**
> - `GOOGLE_REDIRECT_URI` debe coincidir **exactamente** con la URL registrada como "URI de redirección autorizada" en la consola de Google.
> - El dominio (`http://ecommerce.com`) debe apuntar a tu aplicación Laravel.

---

## Configuración de servicios

Archivo: `config/services.php`

Se añadió la sección `google` para que Socialite lea las credenciales desde `.env`:

```php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI'),
],
```

---

## Migración de `users`

Archivo base: `database/migrations/0001_01_01_000000_create_users_table.php`

Se adaptó la tabla `users` para soportar usuarios sin contraseña local y con proveedor externo:

- `password` nullable para permitir cuentas solo-OAuth.
- Campos de proveedor:
  - `provider` (por ejemplo `google`).
  - `provider_id` (ID único que devuelve Google).

```php
$table->string('password')->nullable();
$table->string('provider')->nullable();
$table->string('provider_id')->nullable();
```

Los campos adicionales como `last_name`, `address`, `dni`, `phone`, `image`, `slug`, auditoría, etc. se agregan en la migración `2025_11_30_155038_add_extra_fields_to_users_table.php`.

---

## Cambios en el modelo `User`

Archivo: `app/Models/User.php`

En `$fillable` se añadieron los campos necesarios para Socialite:

- `email_verified_at`
- `provider`
- `provider_id`

Ejemplo (resumido):

```php
protected $fillable = [
    'name',
    'last_name',
    'email',
    'email_verified_at',
    'slug',
    'password',
    'provider',
    'provider_id',
    'address',
    'dni',
    'phone',
    'image',
    // ... campos de auditoría y estado
];
```

Además, el modelo ya cuenta con:

- `generateUniqueSlug($name, $id = null)` para construir el `slug` del usuario.
- Trait `Auditable` y campos `created_by`, `updated_by`, `deleted_by` (añadidos vía migración extra).

---

## Rutas públicas

Archivo: `routes/web.php`

Se definieron dos rutas para el flujo de Google:

```php
use App\Http\Controllers\Auth\GoogleController;

Route::get('/google-auth/redirect', [GoogleController::class, 'redirectToGoogle'])
    ->name('google.redirect');

Route::get('/google-auth/callback', [GoogleController::class, 'handleGoogleCallback'])
    ->name('google.callback');
```

- `/google-auth/redirect` inicia el flujo OAuth en Google.
- `/google-auth/callback` es el endpoint al que Google redirige después de la autenticación.

> Asegúrate de que `GOOGLE_REDIRECT_URI` en `.env` sea exactamente:
>
> `http://ecommerce.com/google-auth/callback`

---

## Controlador `GoogleController`

Archivo: `app/Http/Controllers/Auth/GoogleController.php`

Responsabilidades principales:

1. Redirigir al usuario a Google (`redirectToGoogle`).
2. Procesar el callback (`handleGoogleCallback`).
3. Crear o actualizar el usuario local.
4. Asignar rol, marcar verificación y guardar avatar.

### 1. Redirección a Google

```php
public function redirectToGoogle()
{
    return Socialite::driver('google')->redirect();
}
```

### 2. Manejo del callback

Lógica principal (resumen):

```php
public function handleGoogleCallback()
{
    // 1) Obtener datos de Google (stateless)
    $googleUser = Socialite::driver('google')->stateless()->user();

    // 2) Extraer nombre, apellido y avatar
    $raw       = $googleUser->user ?? [];
    $firstName = $raw['given_name'] ?? $googleUser->name;
    $lastName  = $raw['family_name'] ?? null;
    $avatarUrl = $googleUser->avatar ?? null;

    // 3) Buscar usuario local por email
    $user = User::where('email', $googleUser->email)->first();

    if (!$user) {
        // 3a) Crear usuario nuevo como Cliente
        $slug      = User::generateUniqueSlug($firstName);
        $imagePath = $this->storeGoogleAvatar($avatarUrl, $slug);

        $user = User::create([
            'name'             => $firstName,
            'last_name'        => $lastName,
            'email'            => $googleUser->email,
            'slug'             => $slug,
            'provider'         => 'google',
            'provider_id'      => $googleUser->id,
            'email_verified_at'=> now(),
            'password'         => null,
            'status'           => true,
            'image'            => $imagePath,
        ]);

        $user->assignRole('Cliente');
    } else {
        // 3b) Usuario existente: vincular Google sin tocar roles
        if (!$user->provider_id) {
            $user->update([
                'provider'    => 'google',
                'provider_id' => $googleUser->id,
            ]);
        }

        if (!$user->image && $avatarUrl) {
            $slug      = $user->slug ?: User::generateUniqueSlug($user->name, $user->id);
            $imagePath = $this->storeGoogleAvatar($avatarUrl, $slug);
            if ($imagePath) {
                $user->forceFill(['image' => $imagePath])->save();
            }
        }

        if (!$user->last_name && $lastName) {
            $user->forceFill(['last_name' => $lastName])->save();
        }

        if (!$user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }
    }

    Auth::login($user);

    return redirect()->route('site.home');
}
```

#### Notas clave

- Usa `stateless()` para evitar problemas de sesión en algunos entornos.
- Extrae `given_name` y `family_name` desde la respuesta raw de Google.
- Si el usuario **no existe**:
  - Se crea con rol `Cliente`.
  - Se marca como verificado (`email_verified_at = now()`).
  - Se guarda el avatar de Google en `users.image`.
- Si el usuario **ya existe** (incluyendo admins u otros roles):
  - Solo se llenan `provider`, `provider_id`, `last_name`, `image` (si falta) y `email_verified_at`.
  - **No se modifica el/los rol(es)** del usuario.

### 3. Guardar el avatar de Google

Método helper dentro del mismo controlador:

```php
protected function storeGoogleAvatar(?string $avatarUrl, string $slug): ?string
{
    if (!$avatarUrl) {
        return null;
    }

    try {
        $contents = @file_get_contents($avatarUrl);
        if ($contents === false) {
            return null;
        }

        $path = parse_url($avatarUrl, PHP_URL_PATH) ?: '';
        $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION) ?: 'jpg');

        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $ext = 'jpg';
        }

        $filename     = $slug.'-google-'.time().'.'.$ext;
        $relativePath = 'users/'.$filename;

        Storage::disk('public')->put($relativePath, $contents);

        return $relativePath;
    } catch (\Throwable $e) {
        Log::warning('No se pudo descargar el avatar de Google', [
            'message' => $e->getMessage(),
        ]);

        return null;
    }
}
```

- Guarda la imagen en `storage/app/public/users` usando el disco `public`.
- Devuelve una ruta relativa (`users/...`) compatible con el resto del proyecto.

---

## Comportamiento de roles y visibilidad en el admin

- Usuarios creados desde el **admin** (`UserController@store`):
  - Se crean con el rol que el admin seleccione.
  - Se marcan automáticamente como verificados (`email_verified_at = now()`).

- Usuarios creados por **Google** (nuevo email):
  - Se crean con el rol `Cliente`.
  - Se marcan como verificados.
  - Se excluyen del listado principal de usuarios internos (el módulo Users filtra los que tienen rol `Cliente`).

- Usuarios existentes con otros roles (por ejemplo, Admin) que inician con Google:
  - No se cambia su rol.
  - Se vincula Google (`provider`, `provider_id`) y se completan `last_name` / `image` si faltan.

---

## Integración con la interfaz

### Login admin

Vista: `resources/views/auth/admin-login.blade.php`

Se añadió un botón para iniciar sesión con Google:

```blade
<a href="{{ route('google.redirect') }}" class="boton-form boton-google">
    <i class="ri-google-line boton-icon"></i>
    Iniciar con Google
</a>
```

Este botón inicia el flujo en `/google-auth/redirect`.

### Avatares

- Las vistas de navegación y sidebar (`partials/admin/navigation.blade.php`, `partials/admin/sidebar-right.blade.php`) usan `user.image`:
  - Si existe `image` y el archivo está en `storage/app/public`, se muestra la foto.
  - Si no, se generan iniciales y colores desde `avatar_colors`.
- Gracias a `storeGoogleAvatar`, los usuarios que llegan por Google tienen una imagen local válida en `users.image`.

---

## Cómo probar

1. **Configurar credenciales Google**
   - Crear un proyecto en Google Cloud.
   - Habilitar OAuth 2.0 para aplicación web.
   - Registrar el redirect URI: `http://ecommerce.com/google-auth/callback`.
   - Copiar `client_id` y `client_secret` a `.env`.

2. **Verificar rutas**
   - Visitar `/login` (vista admin-login) y pulsar **Iniciar con Google**.
   - Comprobar que se redirige a la pantalla de selección de cuenta de Google.

3. **Primer login con un email nuevo**
   - Usar una cuenta de Google cuyo email **no exista aún** en `users`.
   - Tras el callback:
     - El usuario debe ser creado con rol `Cliente`.
    - Debe ser redirigido a `site.home`.
     - Debe tener `email_verified_at` no nulo.
     - Debe tener `image` apuntando a una ruta `users/...` en `storage`.

4. **Login con un usuario admin existente**
   - En el admin, crear un usuario interno con un email (ej. `admin@demo.com`).
   - Asignarle rol de Admin.
   - Crear en Google una cuenta con el **mismo email**.
   - Iniciar sesión con Google usando ese email.
   - Verificar:
     - Que no se cambia su rol a `Cliente`.
     - Que solo se completan `provider`, `provider_id`, `last_name` (si faltaba) y `image`.

5. **Verificación visual**
   - Entrar al panel admin y comprobar que el avatar en el topbar/sidebar muestra la foto de Google si está disponible.

---

## Posibles extensiones (otros providers)

La implementación está pensada para poder añadir otros proveedores (Facebook, GitHub, etc.) siguiendo el mismo patrón:

1. Añadir credenciales en `.env` y `config/services.php`.
2. Crear métodos equivalentes en el controlador (por ejemplo `redirectToGithub`, `handleGithubCallback`).
3. Reutilizar la misma lógica para:
   - Buscar/crear usuario por email.
   - Asignar `provider` / `provider_id`.
   - Descargar avatar y guardarlo en `users.image`.
   - Asignar rol `Cliente` solo a nuevos registros.

Con esto, el sistema de autenticación social se mantiene consistente con el modelo de usuarios y con la separación entre usuarios internos y clientes.
