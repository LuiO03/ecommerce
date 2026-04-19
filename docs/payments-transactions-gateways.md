# Modulos de Pagos, Transacciones y Pasarelas

Este documento resume la arquitectura y el flujo funcional de pagos implementado recientemente en el ecommerce, incluyendo:

- Modulo admin de Pagos.
- Modulo admin de Transacciones.
- Flujo de checkout con pasarelas (Niubiz, Culqi, Mercado Pago).
- Control de idempotencia y conciliacion basica.
- Cambios recientes (agregado, actualizado y retirado).

## 1. Resumen de cambios recientes

### 1.1 Agregado

- Nuevas tablas:
  - payments
  - transactions
  - payment_attempts
- Nuevos modulos admin:
  - /admin/payments
  - /admin/transactions
- Nuevos componentes del flujo de pago:
  - PaymentGatewayManager
  - Gateways: NiubizGatewayService, CulqiGatewayService, MercadoPagoGatewayService
  - DTOs y contrato comun para gateways
- Integracion de DataTableManager en listado admin de pagos.
- Resumen y ranking de comisiones por pasarela en admin.

### 1.2 Actualizado

- CheckoutController:
  - Validacion de delivery_type.
  - Monto dinamico segun tipo de entrega.
  - Renovacion de session token via endpoint de backend.
  - Uso de idempotency_key con registro en payment_attempts.
- OrderPlacementService:
  - Crea Payment asociado a la orden.
  - Crea Transaction tipo fee solo si fee > 0.
- Vista admin de orden:
  - Incluye bloque de pagos/transacciones con enlaces a los nuevos modulos.

### 1.3 Retirado o ajustado por negocio

- Recojo en tienda queda con costo fijo 0.00 en backend.
- Se removio la configuracion de shipping_cost_pickup como ajuste administrativo.

## 2. Modelo de datos

## 2.1 Tabla payments

Campos principales:

- order_id
- provider
- transaction_id
- amount (bruto)
- fee (comision)
- net_amount (neto)
- status: pending, paid, failed, refunded
- paid_at
- response (json completo de pasarela)

Indices y unicidad:

- index(order_id, status)
- index(provider, status)
- unique(provider, transaction_id)

## 2.2 Tabla transactions

Campos principales:

- payment_id
- type (fee, refund, adjustment, etc.)
- amount
- description

Uso actual:

- Registro de movimientos financieros secundarios del pago.
- El tipo fee se usa para representar comision del proveedor cuando aplica.

## 2.3 Tabla payment_attempts

Objetivo:

- Evitar doble procesamiento de un intento de pago.
- Trazabilidad tecnica del intento previo a la orden final.

Campos clave:

- idempotency_key (unique)
- payment_method
- purchase_number
- request_hash
- status: processing, approved, failed, conflict
- order_id
- payment_record_id
- result_payload

## 3. Flujo de checkout implementado

## 3.1 Inicio y preparacion

En checkout/index:

- Se calcula subtotal del carrito.
- Se calcula shipping:
  - pickup => 0.0
  - delivery => company_setting(shipping_cost_delivery, config fallback)
- Se arma monto final mostrado en frontend.

## 3.2 Renovacion de token de sesion

Endpoint:

- POST /checkout/session-token

Comportamiento:

- Resuelve pasarela desde payment_method.
- Recalcula amount desde servidor (subtotal + shipping).
- Invoca createSessionToken del gateway.
- Devuelve:
  - session_token
  - amount
  - delivery_type

Nota:

- Niubiz usa token real de backend.
- Culqi y Mercado Pago devuelven token marcador porque la tokenizacion real ocurre en frontend.

## 3.3 Pago final

Endpoint:

- POST /checkout/paid

Pasos clave:

1. Valida usuario autenticado y carrito activo.
2. Normaliza delivery_type y payment_method.
3. Recalcula amount en servidor.
4. Registra o consulta payment_attempt por idempotency_key.
5. Autoriza en pasarela via PaymentGatewayManager.
6. Si aprobado:
   - crea Order + OrderItems
   - crea Payment
   - crea Transaction fee si corresponde
   - desactiva carrito
   - genera PDF de comprobante
   - envia correo resumen
   - marca attempt como approved
7. Si falla:
   - marca attempt como failed
   - persiste payload de respuesta

## 4. Criterios de aprobacion por pasarela

## 4.1 Niubiz

- Exito por ACTION_CODE 000/010, o STATUS autorizado.
- Incluye mapeo de codigos de error a mensajes amigables.
- Guarda respuesta completa en session para feedback al usuario.

## 4.2 Culqi

- Exito si outcome type compatible (venta_exitosa/authorized) o status capturado/pagado.
- Crea cargo backend usando secret key.

## 4.3 Mercado Pago

- Exito con status approved/authorized.
- Crea pago backend con token generado en frontend.

## 4.4 Metodos declarados pero no resueltos actualmente

- En checkout se permiten pagoefectivo y yape como opcion.
- PaymentGatewayManager actual no expone gateway para esos codigos.
- Resultado actual: no disponible en backend hasta implementar servicio concreto.

## 5. Modulo admin de pagos

Rutas:

- GET /admin/payments
- GET /admin/payments/{payment}

Permisos:

- Protegido actualmente con permiso de ordenes.index.

Pantallas:

- Lista:
  - DataTableManager
  - Busqueda y filtros client-side (pasarela, estado, fecha)
  - Resumen financiero
  - Ranking de comisiones por pasarela
- Detalle:
  - bruto, comision, porcentaje, neto
  - relacion con orden y cliente
  - listado de transacciones asociadas

## 6. Modulo admin de transacciones

Rutas:

- GET /admin/transactions
- GET /admin/transactions/{transaction}

Pantallas:

- Lista:
  - resumen de movimientos y comisiones type=fee
  - relacion con pago, orden y cliente
- Detalle:
  - datos del movimiento
  - contexto financiero del pago relacionado

## 7. Integracion con modulo de ordenes

- En detalle de orden se integra bloque pagos/transacciones.
- Se muestran:
  - provider
  - monto
  - fee
  - porcentaje fee
  - neto
  - status
  - transaccion gateway
- Se incluyen enlaces directos a detalle de pago y transaccion.

## 8. Consideraciones operativas

- El campo fee existe y ya se visualiza en admin, pero la logica de calculo automatico de comision por pasarela aun depende del origen de datos que se guarde al crear Payment.
- La estructura actual soporta conciliacion basica por provider + transaction_id unico.
- payment_attempts permite investigar conflictos y evitar doble cargo por reintentos.

## 9. Recomendaciones siguientes

1. Crear permisos dedicados para modulos financieros:
   - pagos.index
   - transacciones.index
2. Implementar gateways para pagoefectivo y yape o retirar opciones del checkout hasta su implementacion.
3. Definir calculo estandar de fee por pasarela (si no viene en respuesta del proveedor).
4. Agregar exportaciones para pagos/transacciones si se requiere conciliacion externa.
