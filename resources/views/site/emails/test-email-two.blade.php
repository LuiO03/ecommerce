<x-mail::message>
# ¡Bienvenido/a, Juan Pérez!

Gracias por registrarte en **Geckommerce**. Solo falta un paso para completar la creación de tu cuenta.

## Confirma tu correo electrónico

Para mantener tu cuenta segura y poder recuperar el acceso en el futuro, necesitamos que confirmes que este correo te pertenece.

<x-mail::button :url="url('/email/verify/123/fake-hash')" color="accent">
Verificar mi cuenta
</x-mail::button>

Si el botón no funciona, copia y pega este enlace en tu navegador:
http://ecommerce.com/preview-email

---
**Datos de tu cuenta (ejemplo)**

- Correo: juan.perez.ejemplo@correo.com
- Dirección: Av. Siempre Viva 123, Springfield

Si tú no creaste esta cuenta, puedes ignorar este mensaje y no se completará la activación.

Gracias,<br>
Geckommerce
</x-mail::message>
