<x-mail::message>
# Hola Luis 👋

Este es un correo de prueba de **Geckommerce**.

Si estás viendo esto significa que:

- Laravel envía correos correctamente
- Brevo está configurado
- Tu sistema funciona

<x-mail::button :url="url('/')">
Ir a Geckommerce
</x-mail::button>

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>
