<x-mail::message>
# Resumen de tu compra

Hola {{ $user->name }}, gracias por tu pedido.

Hemos recibido tu orden y la estamos procesando. A continuación encontrarás un resumen claro de tu compra.

@php
  $items = $cart->items;
@endphp

@if($items->isNotEmpty())
<x-mail::table>
| Producto                                                 | Cant. | Precio U. | Importe   |
|----------------------------------------------------------|:-----:|-------------:|----------:|
@foreach($items as $item)
@php
  $productName = $item->product?->name ?? 'Producto eliminado';
  $variantName = $item->variant?->name ?? '';
  $basePrice = $item->variant?->price ?? $item->product?->price ?? 0;
  $unitPrice = (float) $basePrice;
  $lineTotal = $unitPrice * (int) $item->quantity;
@endphp
| {{ $productName }} {{ $variantName ? ' · ' . $variantName : '' }} |  {{ $item->quantity }}  | S/ {{ number_format($unitPrice, 2) }} | S/ {{ number_format($lineTotal, 2) }} |
@endforeach
</x-mail::table>
@endif

**Subtotal productos:** S/ {{ number_format($subtotal, 2) }}<br>
**Envío:** S/ {{ number_format($shipping, 2) }}<br>
**Total pagado:** **S/ {{ number_format($amount, 2) }}**

@if(!empty($purchaseNumber))
Número de pedido: **{{ $purchaseNumber }}**<br>
@endif

@php
  $brand = $paymentData['dataMap']['BRAND']
    ?? ($paymentData['dataMap']['BRAND_NAME'] ?? null
    ?? ($paymentData['data']['BRAND'] ?? ($paymentData['data']['BRAND_NAME'] ?? null) ?? null));
@endphp

@if(!empty($brand))
Método de pago: Tarjeta de crédito **{{ $brand }}**
@endif

<x-mail::button :url="url('/')">
Ir a la tienda
</x-mail::button>

Si tienes alguna duda sobre tu compra, solo responde a este correo y nuestro equipo de soporte te ayudará.

Gracias por confiar en nosotros,<br>
{{ config('app.name') }}
</x-mail::message>
