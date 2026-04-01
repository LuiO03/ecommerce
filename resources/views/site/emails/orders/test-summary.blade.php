<x-mail::message>
# Resumen de tu compra

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
| Producto                                                 | Cant. |  Precio U. | Importe   |
|----------------------------------------------------------|:-----:|-------------:|----------:|
@foreach($fakeItems as $item)
| {{ $item->productName }} {{ $item->variantName }} |  {{ $item->quantity }}  | S/ {{ number_format($item->unitPrice, 2) }} | S/ {{ number_format($item->unitPrice * $item->quantity, 2) }} |
@endforeach
</x-mail::table>

**Subtotal productos:** S/ {{ number_format($fakeSubtotal, 2) }}<br>
**Envío:** S/ {{ number_format($fakeShipping, 2) }}<br>
**Total pagado:** **S/ {{ number_format($fakeTotal, 2) }}**

Número de pedido: **#GC-000123**<br>
Método de pago: **Tarjeta de crédito (VISA)**

<x-mail::button :url="url('/')">
Ver más productos
</x-mail::button>

Si tienes alguna duda sobre tu compra, solo responde a este correo y nuestro equipo de soporte te ayudará.

Gracias por comprar en {{ config('app.name') }},
<br>
El equipo de {{ config('app.name') }}

</x-mail::message>
