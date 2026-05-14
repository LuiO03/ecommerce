<x-mail::message>
## Resumen de tu compra

Hola {{ $user->name ?? 'Cliente' }}, gracias por tu pedido.

Hemos recibido tu orden y la estamos procesando. A continuación encontrarás un resumen claro de tu compra.

@php
    // Datos de ejemplo para pruebas desde TestEmail
    $fakeItems = collect([
        (object) [
            'productName' => 'Zapatillas Running Pro',
            'variantName' => 'Talla 42 · Negro',
            'quantity' => 1,
            'unitPrice' => 249.90,
        ],
        (object) [
            'productName' => 'Camiseta Deportiva',
            'variantName' => 'Talla M · Azul',
            'quantity' => 2,
            'unitPrice' => 59.90,
        ],
    ]);

    $fakeSubtotal = $fakeItems->sum(fn ($item) => $item->unitPrice * $item->quantity);
    $fakeShipping = 15.00;
    $fakeTotal = $fakeSubtotal + $fakeShipping;
@endphp

<x-mail::table>
| Producto                                                 | Cant. |  P.U. | Importe   |
|:----------------------------------------------------------|:-----:|:-------------:|:----------:|
@foreach($fakeItems as $item)
| {{ $item->productName }} {{ $item->variantName }} |  {{ $item->quantity }}  | S/ {{ number_format($item->unitPrice, 2) }} | S/ {{ number_format($item->unitPrice * $item->quantity, 2) }} |
@endforeach
</x-mail::table>

<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-top:20px;">
<tr>
<td><strong>Subtotal productos:</strong></td>
<td align="right">S/ {{ number_format($fakeSubtotal, 2) }}</td>
</tr>

<tr>
<td style="padding-top:8px;"><strong>Envío:</strong></td>
<td align="right" style="padding-top:8px;">
    S/ {{ number_format($fakeShipping, 2) }}
</td>
</tr>

<tr>
<td style="padding-top:12px;">
    <strong>Total pagado:</strong>
</td>

<td align="right" style="padding-top:12px;">
    <strong>S/ {{ number_format($fakeTotal, 2) }}</strong>
</td>
</tr>
</table>

<x-mail::button :url="url('/')" color="primary">
Ver más productos
</x-mail::button>

Si tienes alguna duda sobre tu compra, solo responde a este correo y nuestro equipo de soporte te ayudará.

Gracias por comprar en {{ $company?->name ?? config('app.name') }}, esperamos verte pronto de nuevo.
<br>
El equipo de {{ $company?->name ?? config('app.name') }}

</x-mail::message>
