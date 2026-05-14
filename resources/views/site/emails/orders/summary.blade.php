<x-mail::message>
## Resumen de tu compra

Hola {{ $user->name }}, gracias por tu pedido.

Hemos recibido tu orden y la estamos procesando. A continuación encontrarás un resumen claro de tu compra.

@php
  $items = $cart->items;
@endphp

@if($items->isNotEmpty())
<x-mail::table>
| Producto                                                 | Cant. | P.U. | Importe   |
|:----------------------------------------------------------|:-----:|:----:|:----------:|
@foreach($items as $item)
@php
  $productName = $item->product?->name ?? 'Producto eliminado';
  $variantName = $item->variant?->name ?? '';
  $basePrice = $item->variant?->price ?? $item->product?->price ?? 0;
  $unitPrice = (float) $basePrice;
  $lineTotal = $unitPrice * (int) $item->quantity;
@endphp
| {{ $productName }} {{ $variantName ? ' · ' . $variantName : '' }} | {{ $item->quantity }} | S/ {{ number_format($unitPrice, 2) }} | S/ {{ number_format($lineTotal, 2) }} |
@endforeach
</x-mail::table>
@endif

<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-top:20px;">
<tr>
<td>
    <strong>Subtotal productos:</strong>
</td>

<td align="right">
    S/ {{ number_format($subtotal, 2) }}
</td>
</tr>

<tr>
<td style="padding-top:8px;">
    <strong>Envío:</strong>
</td>

<td align="right" style="padding-top:8px;">
    S/ {{ number_format($shipping, 2) }}
</td>
</tr>

<tr>
<td style="padding-top:12px;">
    <strong>Total pagado:</strong>
</td>

<td align="right" style="padding-top:12px;">
    <strong>S/ {{ number_format($amount, 2) }}</strong>
</td>
</tr>

@if(!empty($purchaseNumber))
<tr>
<td style="padding-top:18px;">
    <strong>Número de pedido:</strong>
</td>

<td align="right" style="padding-top:18px;">
    {{ $purchaseNumber }}
</td>
</tr>
@endif

@php
  $brand = $paymentData['dataMap']['BRAND']
    ?? ($paymentData['dataMap']['BRAND_NAME'] ?? null
    ?? ($paymentData['data']['BRAND'] ?? ($paymentData['data']['BRAND_NAME'] ?? null) ?? null));
@endphp

@if(!empty($brand))
<tr>
<td style="padding-top:8px;">
    <strong>Método de pago:</strong>
</td>

<td align="right" style="padding-top:8px;">
    Tarjeta de crédito {{ $brand }}
</td>
</tr>
@endif
</table>

<x-mail::button :url="url('/')" color="primary">
Ir a la tienda
</x-mail::button>

Si tienes alguna duda sobre tu compra, solo responde a este correo y nuestro equipo de soporte te ayudará.

Gracias por confiar en nosotros,<br>
{{ $company?->name ?? config('app.name') }}
</x-mail::message>
