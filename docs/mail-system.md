# Sistema de mensajería por correo

Este documento describe cómo está implementado el envío de correos en GECKOMERCE y cómo reutilizarlo en eventos típicos como **registro de usuarios**, **verificación de correo** y **restablecimiento de contraseña**, además de pruebas y configuración básica.

---

## 1. Configuración base de correo

### 1.1 Variables de entorno

En `.env` debes definir el driver y credenciales de tu proveedor SMTP (por ejemplo, Mailtrap, Gmail, SendGrid, etc.):

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=tu_usuario
MAIL_PASSWORD=tu_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@ecommerce.com
MAIL_FROM_NAME="Geckomerce"
```

Laravel usará estos valores para todos los correos enviados mediante `Mail::to(...)` o `Mailable`.

> **Importante:**
> - Asegúrate de que `APP_URL` y `APP_NAME` estén correctos; se usan en las plantillas Markdown por defecto y en algunos enlaces de verificación.

### 1.2 Driver y entorno local

En desarrollo puedes usar Mailtrap u otro sandbox para evitar enviar correos reales:

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=... 
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
```

También puedes usar `log` como mailer para registrar los correos en los logs:

```env
MAIL_MAILER=log
```

---

## 2. Clases `Mailable` disponibles

Todas las clases de correo están en `app/Mail/`.

### 2.1 `App\Mail\UserRegistered`

Se envía cuando un usuario se registra en la plataforma pública y se quiere enviar un **correo de bienvenida + verificación**.

Archivo: `app/Mail/UserRegistered.php`

Responsabilidades:

- Recibe una instancia de `User`.
- Genera una **URL firmada de verificación de correo** usando la ruta `verification.verify`.
- Renderiza la vista Markdown `site.emails.users.registered`.

Fragmento clave:

```php
$verificationUrl = URL::temporarySignedRoute(
    'verification.verify',
    now()->addMinutes((int) Config::get('auth.verification.expire', 60)),
    [
        'id' => $this->user->getKey(),
        'hash' => sha1($this->user->email),
    ]
);

return $this
    ->subject('Bienvenido a ' . config('app.name'))
    ->markdown('site.emails.users.registered', [
        'user' => $this->user,
        'verificationUrl' => $verificationUrl,
    ]);
```

### 2.2 `App\Mail\UserPasswordReset`

Pensado para **restablecimiento de contraseña** cuando se desea personalizar el correo más allá del correo por defecto de Laravel/Fortify.

Archivo: `app/Mail/UserPasswordReset.php`

Responsabilidades:

- Recibe una instancia de `User` y una `resetUrl` (enlace único para resetear la contraseña).
- Renderiza la vista Markdown `site.emails.users.password-reset`.

Uso típico:

```php
Mail::to($user->email)->send(
    new UserPasswordReset($user, $resetUrl)
);
```

> La URL de reset puede ser generada a partir del token y el email que Fortify almacena en la tabla `password_resets`, o usando rutas personalizadas.

### 2.3 `App\Mail\TestEmail`

Correo de **prueba rápida** para validar la configuración de envío.

Archivo: `app/Mail/TestEmail.php`

- Usa la plantilla Markdown `site.emails.test-email-two`.
- Permite verificar que el servidor SMTP está bien configurado.

Ruta de prueba en `routes/web.php`:

```php
Route::get('/send-test-email', function () {
    Mail::to('tu-correo@ejemplo.com')
        ->send(new TestEmail());

    return 'Correo enviado';
})->name('send-test-email');

Route::get('/preview-email', function () {
    return new TestEmail();
});
```

---

## 3. Flujo de registro de usuario + verificación

### 3.1 Registro desde el sitio público

Controlador: `App\Http\Controllers\Site\RegisteredUserController`

- Valida los datos del formulario de registro.
- Crea al usuario con rol `Cliente`.
- Puede enviar el correo de bienvenida/verificación usando `UserRegistered`.

Ejemplo de envío (resumen):

```php
use App\Mail\UserRegistered;
use Illuminate\Support\Facades\Mail;

$user = User::create([...]);

Mail::to($user->email)->queue(new UserRegistered($user));
```

> Se recomienda usar `queue()` en lugar de `send()` en producción para no bloquear la respuesta HTTP.

### 3.2 Verificación del correo

Ruta: `GET /email/verify/{id}/{hash}` (nombre: `verification.verify`)

Definida en `routes/web.php`:

- Busca el usuario por `id`.
- Valida el `hash` contra `sha1(email)`.
- Marca `email_verified_at` si aún no está verificado.
- Redirige al login con un mensaje de estado.

Esto es coherente con la URL generada por `UserRegistered`.

---

## 4. Flujo de restablecimiento de contraseña

El proyecto utiliza **Laravel Fortify** para el flujo estándar de reset de contraseña.

### 4.1 Envío del enlace de reset

Vista de formulario: `resources/views/auth/admin-forgot-password.blade.php`

- El usuario ingresa el correo.
- Se envía la petición a la ruta `password.email` (Fortify).
- Si el correo es válido, se genera un token en la tabla `password_resets` y se envía un email con enlace.

Además, se ha creado una vista de **confirmación**:

- `resources/views/auth/admin-forgot-password-sent.blade.php`

Esta vista se muestra tras el envío exitoso del email para mejorar la UX.

### 4.2 Correo de reset personalizado (opcional)

Si quieres reemplazar el correo por defecto de Fortify y usar `UserPasswordReset`, puedes generar la URL y enviar el `Mailable` manualmente dentro de tu lógica de envío.

Ejemplo conceptual:

```php
use App\Mail\UserPasswordReset;
use Illuminate\Support\Facades\Mail;

$resetUrl = url("/reset-password/{$token}?email=" . urlencode($user->email));

Mail::to($user->email)->queue(
    new UserPasswordReset($user, $resetUrl)
);
```

> Fortify ya maneja el almacenamiento del token y la validación posterior. Solo necesitas respetar la estructura estándar de la URL de reset.

---

## 5. Plantillas de correo (Markdown)

Las vistas Markdown de correo se encuentran en `resources/views/site/emails/` y en los overrides de vendor:

- `resources/views/site/emails/users/registered.blade.php`
- `resources/views/site/emails/users/password-reset.blade.php`
- `resources/views/site/emails/test-email-two.blade.php`
- Overrides de Laravel Mail: `resources/views/vendor/mail/html/*.blade.php`

Estas plantillas usan componentes de Mail Markdown de Laravel (`@component('mail::message')`, `<x-mail::button>`, etc.) y leen `config('app.url')` y `config('app.name')` para enlaces y textos.

---

## 6. Prácticas recomendadas

- Mantener `APP_URL` y `MAIL_FROM_ADDRESS` coherentes con el dominio real de producción.
- Usar `queue()` en los `Mailable` para flujos masivos o críticos (registro, reset).
- Centralizar cualquier lógica de construcción de URLs de verificación o reset para evitar inconsistencias.
- Probar siempre con `/send-test-email` después de cambiar proveedor SMTP.
- Mantener las plantillas de correo en español y alineadas con el branding (logo, colores, textos) de GECKOMERCE.
