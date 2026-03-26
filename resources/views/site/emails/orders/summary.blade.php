<x-mail::message>
# ¡Gracias por tu compra, {{ $user->name }}!

Hemos recibido tu pedido y estamos procesándolo.

## Detalle de la compra

@php
    $items = $cart->items;
@endphp

@if($items->isNotEmpty())
| Producto | Variación | Cantidad | Precio |
| -------- | --------- | -------- | ------ |
@foreach($items as $item)
| {{ $item->product?->name ?? 'Producto eliminado' }} |
  {{ $item->variant?->name ?? '-' }} |
  {{ $item->quantity }} |
  S/ {{ number_format($item->quantity * ($item->variant?->price ?? $item->product?->price ?? 0), 2) }} |
@endforeach
@endif

**Subtotal:** S/ {{ number_format($subtotal, 2) }}
**Envío:** S/ {{ number_format($shipping, 2) }}
**Total:** **S/ {{ number_format($amount, 2) }}**

@if(!empty($purchaseNumber))
Número de pedido / compra: **{{ $purchaseNumber }}**
@endif

@if(!empty($paymentData['dataMap']['BRAND']))
Método de pago: **{{ $paymentData['dataMap']['BRAND'] }}**
@endif

<x-mail::button :url="url('/')">
Ir a la tienda
</x-mail::button>

Gracias por confiar en nosotros,<br>
{{ config('app.name') }}
</x-mail::message>
