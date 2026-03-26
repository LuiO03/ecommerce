<x-app-layout>

    @php
        $response = $niubiz['response'] ?? null;
        $dataMap = $response['dataMap'] ?? [];
        $actionCode = $dataMap['ACTIONCODE'] ?? null;
        $actionDescription = $dataMap['ACTIONDESCRIPTION'] ?? null;
    @endphp

    <section class="site-container checkout-payment-page">

        <div class="checkout-payment-card payment-card-failure">
            <div class="checkout-payment-icon payment-icon-failure">
                <i class="ri-newspaper-fill"></i>
            </div>

            <h2 class="checkout-payment-title">Tu pago no fue procesado</h2>
            <p class="checkout-payment-message">
                Esto puede deberse a un problema con la tarjeta, con la entidad emisora o con la conexión. Te sugerimos
                revisar los datos e intentarlo otra vez.
            </p>

            @if ($actionDescription)
                <p class="checkout-payment-detail">
                    Detalle técnico: {{ $actionDescription }}@if ($actionCode)
                        ({{ $actionCode }})
                    @endif
                </p>
            @endif

            <div class="checkout-payment-actions">
                <a href="{{ route('checkout.index') }}" class="boton-form boton-purple py-3">
                    <span class="boton-form-icon">
                        <i class="ri-arrow-go-back-line"></i>
                    </span>
                    <span class="boton-form-text">
                        <span>Volver al pago</span>
                    </span>
                </a>
                <a href="{{ route('carts.show') }}" class="boton-form boton-danger py-3">
                    <span class="boton-form-icon">
                        <i class="ri-shopping-cart-line"></i>
                    </span>
                    <span class="boton-form-text">
                        <span>Revisar mi carrito</span>
                    </span>
                </a>
            </div>
        </div>
    </section>
</x-app-layout>
