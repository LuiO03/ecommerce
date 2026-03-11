<x-mail::message>
# Hola, {{ $user->name ?? $user->email }}

Hemos recibido una solicitud para **restablecer la contraseña** de tu cuenta en **{{ config('app.name') }}**.

Si fuiste tú, puedes crear una nueva contraseña usando el siguiente botón:

<x-mail::button :url="$resetUrl" color="accent">
CAMBIAR LA CONTRASEÑA
</x-mail::button>

Este enlace de restablecimiento de contraseña expirará en 60 minutos.

Si **no** solicitaste este cambio, puedes ignorar este correo y tu contraseña actual seguirá siendo válida.

Gracias,
{{ config('app.name') }}

---
Si tienes problemas al hacer clic en el botón "Restablecer contraseña", copia y pega esta URL en tu navegador:

{{ $resetUrl }}
</x-mail::message>
