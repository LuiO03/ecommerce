# Checklist Niubiz Sandbox

Usa esta lista cuando `php artisan niubiz:check` devuelva `401 Unauthorized access`.

## Datos que debes confirmar

- `NIUBIZ_MERCHANT_ID` correcto para el comercio de pruebas de soles.
- `NIUBIZ_USER` y `NIUBIZ_PASSWORD` habilitados para ese mismo comercio.
- `NIUBIZ_URL_API` apuntando a sandbox:
  - `https://apisandbox.vnforappstest.com`
- `NIUBIZ_URL_JS` apuntando al checkout sandbox.

## Verificación mínima

1. Limpiar caché de Laravel:
   - `php artisan config:clear`
   - `php artisan cache:clear`
2. Ejecutar diagnóstico:
   - `php artisan niubiz:check`
3. Esperar estos resultados:
   - `security.status: 201`
   - `Access token OK.`
   - `session.status: 200`
   - `Session token OK. Integracion Niubiz operativa.`

## Si sigue saliendo 401

- El usuario/password no pertenecen al merchant sandbox activo.
- Las credenciales pueden estar deshabilitadas o rotadas.
- Debes pedir a Niubiz:
  - credenciales sandbox activas para el merchant de soles,
  - confirmación de que el usuario tiene acceso al API Security,
  - confirmación de que el merchant está habilitado para Ecommerce/Web.

## Si llega a 201 pero falla session token

- Revisar el payload de sesión:
  - `channel = web`
  - `amount` con 2 decimales
  - `antifraud.clientIp`
  - `merchantDefineData`
  - `dataMap`
- Confirmar que el monto y el merchantId coinciden con el comercio de prueba.
