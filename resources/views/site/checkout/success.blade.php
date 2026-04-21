<x-app-layout>
    @section('title', 'Pago exitoso')
    <section class="site-container checkout-payment-page">
        @if (!empty($response))
            <div class="checkout-payment-card payment-card-success">
                <div class="checkout-payment-icon payment-icon-success">
                    <i class="ri-newspaper-fill"></i>
                </div>

                <h2 class="checkout-payment-title">{{ Auth::user()->name }}, tu pago se realizó con éxito</h2>

                <div class="checkout-payment-meta">
                    @php
                        $dataMap = $response['dataMap'] ?? [];
                        $actionDescription = $dataMap['ACTION_DESCRIPTION'] ?? ($dataMap['ACTIONDESCRIPTION'] ?? null);
                        $purchaseNumber = $response['order']['purchaseNumber'] ?? null;
                        $amount = $response['order']['amount'] ?? null;
                        $currency = $response['order']['currency'] ?? null;

                        // Fecha/hora devuelta por Niubiz: formato tipo 260326131641 => dmyHis
                        $transactionDateRaw = $dataMap['TRANSACTION_DATE'] ?? null;
                        $transactionDate = null;
                        if (!empty($transactionDateRaw) && strlen($transactionDateRaw) === 12) {
                            try {
                                $transactionDate = \Carbon\Carbon::createFromFormat('dmyHis', $transactionDateRaw);
                            } catch (\Exception $e) {
                                $transactionDate = null;
                            }
                        }

                        $brand = $dataMap['BRAND'] ?? ($dataMap['BRAND_NAME'] ?? null);

                        // Tarjeta enmascarada (ej. 455170******8059)
                        $cardMasked = $dataMap['CARD'] ?? null;
                        $cardLast4 = $cardMasked ? substr($cardMasked, -4) : null;
                    @endphp

                    <div class="flex flex-col gap-3">
                        @if ($actionDescription)
                            <p class="checkout-payment-message">
                                {{ $actionDescription }}
                            </p>
                        @endif
                        <x-alert type="success" title="Tu pedido está confirmado">
                            En unos minutos recibirás un correo con el resumen de tu compra. También te avisaremos
                            cuando tu pedido
                            esté en camino.
                        </x-alert>

                        <div class="flex flex-wrap gap-2">
                            @if ($purchaseNumber)
                                <div class="checkout-payment-pill checkout-payment-pill--neutral">
                                    <i class="ri-hashtag"></i>
                                    <strong>Nro. de pedido:</strong>
                                    <span>{{ $purchaseNumber }}</span>
                                </div>
                            @endif

                            @if ($transactionDate)
                                <div class="checkout-payment-pill checkout-payment-pill--neutral">
                                    <i class="ri-calendar-event-line"></i>
                                    <strong>Fecha y hora:</strong>
                                    <span>{{ $transactionDate->format('d/m/Y H:i:s') }}</span>
                                </div>
                            @endif

                            @if ($brand || $cardLast4)
                                <div class="checkout-payment-pill checkout-payment-pill--neutral">
                                    <i class="ri-bank-card-2-line"></i>
                                    <strong>Tarjeta:</strong>
                                    <span>
                                        @if ($brand)
                                            {{ ucfirst($brand) }}
                                        @endif
                                        @if ($brand && $cardLast4)

                                        @endif
                                        @if ($cardLast4)
                                            terminada en {{ $cardLast4 }}
                                        @endif
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if ($amount)
                        <div class="checkout-payment-pill checkout-payment-pill--success mt-3">
                            <i class="ri-wallet-3-fill"></i>
                            <span>Total pagado:</span>
                            <strong>
                                S/.
                                {{ number_format((float) $amount, 2) }}
                                {{ $currency }}
                            </strong>
                        </div>
                    @endif

                </div>

                <div class="checkout-payment-actions">
                    <a href="{{ route('site.home') }}" class="boton-form boton-success py-3">
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
        @else
            <div class="checkout-payment-card payment-card-failure">
                <div class="checkout-payment-icon payment-icon-failure">
                    <i class="ri-error-warning-fill"></i>
                </div>

                <h2 class="checkout-payment-title">¡Ups! No encontramos los detalles de tu compra</h2>
                <p class="checkout-payment-message">
                    Sin embargo, tu pago fue procesado correctamente, no te preocupes, tu pedido está confirmado y
                    estamos trabajando para prepararlo y enviarlo lo antes posible. <br><br>
                    Si tienes alguna duda, no dudes
                    en contactarnos.
                </p>

                <div class="checkout-payment-actions">
                    <a href="{{ route('site.home') }}" class="boton-form boton-success py-3">
                        <span class="boton-form-icon">
                            <i class="ri-store-2-fill"></i>
                        </span>
                        <span class="boton-form-text">
                            <span>Volver a la tienda</span>
                        </span>
                    </a>
                    <a href="" class="boton-form boton-danger py-3">
                        <span class="boton-form-icon">
                            <i class="ri-mail-send-fill"></i>
                        </span>
                        <span class="boton-form-text">
                            <span>Contáctanos</span>
                        </span>
                    </a>
                </div>
            </div>
        @endif
    </section>
</x-app-layout>
