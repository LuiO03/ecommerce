<x-app-layout>

    <section class="site-container checkout-payment-page">

        <div class="checkout-payment-card payment-card-success">
            <div class="checkout-payment-icon payment-icon-success">
                <i class="ri-check-line"></i>
            </div>

            <h2 class="checkout-payment-title">Tu pago se realizó con éxito</h2>
            <p class="checkout-payment-message">
                En unos minutos recibirás un correo con el resumen de tu compra. También te avisaremos cuando tu pedido
                esté en camino.
            </p>

            <div class="checkout-payment-meta">

                @if (session('niubiz'))
                    <div>
                        <x-alert type="success" title="¡Éxito!">
                            {{ $response['dataMap']['ACTION_DESCRIPTION'] }}
                        </x-alert>
                        <div class="checkout-payment-pill">
                            <i class="ri-hashtag"></i>
                            <span>Nro. de pedido: {{ $response['order']['PURCHASENUMBER'] }}</span>
                        </div>
                        <p class="checkout-payment-message">
                            <b>Fecha y hora del pedido</b>
                            {{ now()->createFromFormat('Y-m-d H:i:s', $response['dataMap']['TRANSACTIONDATE'])->locale('es')->format('d/m/Y H:i:s') }}
                        </p>
                    </div>

                    <div class="checkout-payment-pill">
                        <i class="ri-wallet-3-fill"></i>
                        <span>Total pagado: S/. {{ number_format((float) $response['order']['AMOUNT'], 2) }} {{ $response['order']['CURRENCY'] }}</span>
                    </div>
                @endif
            </div>

            <div class="checkout-payment-actions">
                <a href="{{ route('welcome.index') }}" class="boton-form boton-success py-3">
                    <span class="boton-form-icon">
                        <i class="ri-store-2-fill"></i>
                    </span>
                    <span class="boton-form-text">
                        <span>Volver a la tienda</span>
                    </span>
                </a>
                <a href="{{ route('carts.show') }}" class="boton-form boton-info py-3">
                    <span class="boton-form-icon">
                        <i class="ri-shopping-bag-3-line"></i>
                    </span>
                    <span class="boton-form-text">
                        <span>Ver carrito</span>
                    </span>
                </a>
            </div>
        </div>
    </section>
</x-app-layout>
