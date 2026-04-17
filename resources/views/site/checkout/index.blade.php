<x-app-layout>
    @section('title', 'Checkout')
    @include('partials.site.breadcrumb', [
        'items' => $breadcrumbItems ?? [
            [
                'label' => 'Carrito de compras',
                'icon' => 'ri-shopping-cart-fill',
                'url' => route('carts.show'),
            ],
            [
                'label' => 'Checkout',
                'icon' => 'ri-wallet-3-fill',
            ],
        ],
    ])

    @php
        $items = $cart?->items ?? collect();
        $hasItems = $items->isNotEmpty();
        $itemsCount = $items->count();
        $itemsQuantity = $items->sum('quantity');
    @endphp

    <section class="site-container checkout-page">
        <div class="section-header">
            <h1 class="section-title">Checkout</h1>
            <p>
                Completa los pasos para confirmar tu pedido: entrega, dirección o tienda, pago y resumen final.
            </p>

        </div>

        @if (!Auth::check() || !$hasItems)
            <div class="card-empty">
                <div class="cart-empty-icon">
                    <i class="ri-shopping-cart-line"></i>
                </div>
                <h2 class="card-title">Tu carrito está vacío</h2>
                <p>
                    Aún no has agregado productos. Explora nuestro catálogo y empieza tu compra.
                </p>
                <a href="{{ route('site.home') }}" class="boton-form boton-success py-3 px-5">
                    <span class="boton-form-icon"><i class="ri-store-2-fill"></i></span>
                    <span class="boton-form-text">Ir a la tienda</span>
                </a>
            </div>
        @else
            @php
                $addresses = $addresses ?? collect();
                $hasAddresses = $addresses->isNotEmpty();
                $defaultAddressId = optional($addresses->first())->id;
                $addressesPayload = $addresses
                    ->map(function ($address) {
                        return [
                            'id' => $address->id,
                            'type' => $address->type,
                            'address_line' => $address->address_line,
                            'district' => $address->district,
                            'reference' => $address->reference,
                            'receiver_name' => $address->receiver_name,
                            'receiver_last_name' => $address->receiver_last_name,
                            'receiver_phone' => $address->receiver_phone,
                            'update_url' => route('site.profile.addresses.update', $address),
                            'delete_url' => route('site.profile.addresses.destroy', $address),
                        ];
                    })
                    ->values();
            @endphp

            <div class="checkout-layout">
                <div class="checkout-flow-main">
                    {{-- PASO 1: Tipo de entrega --}}
                    <section class="checkout-section" id="checkoutStepDelivery">
                        <article class="checkout-progress-step is-active" data-step="1">
                            <span class="checkout-progress-index">1</span>
                            <span class="checkout-progress-label">Tipo de entrega</span>
                            <div class="checkout-progress-separator"></div>
                        </article>
                        <article class="w-full">
                            <div class="card-header-container">
                                <div class="card-header">
                                    <span class="card-title">¿Cómo quieres recibir tu pedido?</span>
                                    <p class="card-description">Selecciona una opción para continuar.</p>
                                </div>
                            </div>

                            <div class="checkout-cards-grid" data-delivery-type-root>
                                <label class="checkout-card" data-delivery-option="delivery">
                                    <input type="radio" name="delivery_type" value="delivery" class="sr-only"
                                        {{ old('delivery_type', 'delivery') === 'delivery' ? 'checked' : '' }}>
                                    <div class="checkout-card-icon">
                                        <i class="ri-truck-line"></i>
                                    </div>
                                    <div class="checkout-card-body">
                                        <span class="checkout-card-title">Delivery a domicilio</span>
                                        <span class="checkout-card-helper">Recibe tus productos en la dirección que
                                            elijas.</span>
                                    </div>
                                </label>

                                <label class="checkout-card" data-delivery-option="pickup">
                                    <input type="radio" name="delivery_type" value="pickup" class="sr-only"
                                        {{ old('delivery_type') === 'pickup' ? 'checked' : '' }}>
                                    <div class="checkout-card-icon">
                                        <i class="ri-store-2-line"></i>
                                    </div>
                                    <div class="checkout-card-body">
                                        <span class="checkout-card-title">Recojo en tienda</span>
                                        <span class="checkout-card-helper">Pasa por uno de nuestros locales
                                            autorizados.</span>
                                    </div>
                                </label>
                            </div>
                        </article>
                    </section>

                    {{-- PASO 2A: Dirección (solo delivery) --}}
                    <section class="checkout-section" id="checkoutStepAddress" data-step-dependent="delivery"
                        data-checkout-addresses-root data-store-url="{{ route('site.profile.addresses.store') }}"
                        data-initial-addresses='@json($addressesPayload)'
                        data-selected-address-id="{{ $defaultAddressId }}">
                        <article class="checkout-progress-step" data-step="2">
                            <span class="checkout-progress-index">2</span>
                            <span class="checkout-progress-label">Dirección o tienda</span>
                            <div class="checkout-progress-separator"></div>
                        </article>
                        <article class="w-full">
                            <div class="card-header-container">
                                <div class="card-header">
                                    <span class="card-title">Dirección de envío</span>
                                    <p class="card-description">Selecciona, crea, edita o elimina direcciones sin salir
                                        del
                                        checkout.</p>
                                </div>
                                @if ($hasAddresses)
                                    <div class="card-header-actions">
                                        <button type="button" class="boton-form boton-success" data-address-form-open>
                                            <span class="boton-form-icon"><i class="ri-map-pin-2-fill"></i></span>
                                            <span class="boton-form-text">Agregar dirección</span>
                                        </button>
                                    </div>
                                @endif
                            </div>

                            <div class="checkout-cards-grid {{ $hasAddresses ? '' : 'is-hidden' }}" data-address-list>
                                @foreach ($addresses as $address)
                                    <label class="checkout-card">
                                        <input type="radio" name="address_id" value="{{ $address->id }}"
                                            class="sr-only" {{ $address->id === $defaultAddressId ? 'checked' : '' }}>

                                        <div class="checkout-card-icon"
                                            title="{{ $address->type === 'office' ? 'Dirección de oficina' : 'Dirección de casa' }}">

                                            @if ($address->type === 'office')
                                                <i class="ri-building-2-line"></i>
                                                <span class="address-card-title">Oficina</span>
                                            @else
                                                <i class="ri-home-2-line"></i>
                                                <span class="address-card-title">Casa</span>
                                            @endif
                                        </div>
                                        <div class="checkout-card-body">
                                            <span class="card-title">
                                                {{ $address->receiver_name }}
                                                {{ $address->receiver_last_name }}
                                            </span>
                                            <ul>
                                                <li class="address-line">{{ $address->address_line }}</li>
                                                <li class="address-city">{{ $address->district }}</li>
                                                <li class="address-reference">{{ $address->reference }}</li>
                                                <li class="address-phone">{{ $address->receiver_phone }}</li>
                                            </ul>
                                        </div>
                                        <div class="address-card-actions">
                                            <button type="button" class="boton-pastel card-warning address-edit-btn"
                                                title="Editar dirección" aria-label="Editar dirección"
                                                data-address-id="{{ $address->id }}"
                                                data-address-type="{{ $address->type }}"
                                                data-address-line="{{ e($address->address_line) }}"
                                                data-address-district="{{ e($address->district) }}"
                                                data-address-reference="{{ e($address->reference) }}"
                                                data-address-receiver-name="{{ e($address->receiver_name) }}"
                                                data-address-receiver-last-name="{{ e($address->receiver_last_name) }}"
                                                data-address-receiver-phone="{{ e($address->receiver_phone) }}"
                                                data-update-url="{{ route('site.profile.addresses.update', $address) }}">
                                                <i class="ri-pencil-fill"></i>
                                            </button>
                                            <button type="button" class="boton-pastel card-danger address-delete-btn"
                                                title="Eliminar dirección" aria-label="Eliminar dirección"
                                                data-address-delete-url="{{ route('site.profile.addresses.destroy', $address) }}">
                                                <i class="ri-delete-bin-5-fill"></i>
                                            </button>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            <p class="checkout-addresses-feedback card-description is-hidden" data-address-feedback>
                            </p>

                            <div class="checkout-address-form-panel {{ $hasAddresses ? 'is-hidden' : '' }}"
                                data-address-form-panel>
                                <div class="card-header mb-2">
                                    <span class="card-title"
                                        data-address-form-title>{{ $hasAddresses ? 'Agregar dirección' : 'Nueva dirección' }}</span>
                                    <p class="card-description" data-address-form-description>Completa los datos de
                                        entrega.</p>
                                </div>

                                <form id="checkoutInlineAddressForm" class="checkout-inline-address-form" novalidate>
                                    @csrf
                                    <input type="hidden" name="_method" value="POST" data-address-form-method>
                                    <div class="form-row-fit">
                                        <div class="input-group">
                                            <label class="label-form" for="checkout_inline_type">
                                                Tipo de dirección <i class="ri-asterisk text-accent"></i>
                                            </label>
                                            <div class="input-icon-container">
                                                <i class="ri-home-4-line input-icon"></i>
                                                <select id="checkout_inline_type" class="select-form" name="type"
                                                    data-validate="selected">
                                                    <option value="home">Casa</option>
                                                    <option value="office">Oficina</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="input-group">
                                            <label class="label-form" for="checkout_inline_address_line">
                                                Dirección completa <i class="ri-asterisk text-accent"></i>
                                            </label>
                                            <div class="input-icon-container">
                                                <i class="ri-map-pin-line input-icon"></i>
                                                <input id="checkout_inline_address_line" type="text"
                                                    class="input-form" name="address_line"
                                                    placeholder="Av. Siempre Viva 742, Interior 3"
                                                    data-validate="required|min:5|max:255" autocomplete="off" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row-fit">
                                        <div class="input-group">
                                            <label class="label-form" for="checkout_inline_district">
                                                Distrito / Ciudad <i class="ri-asterisk text-accent"></i>
                                            </label>
                                            <div class="input-icon-container">
                                                <i class="ri-building-2-line input-icon"></i>
                                                <input id="checkout_inline_district" type="text"
                                                    class="input-form" name="district"
                                                    placeholder="Ej: Miraflores, Lima"
                                                    data-validate="required|min:3|max:120" autocomplete="off" />
                                            </div>
                                        </div>

                                        <div class="input-group">
                                            <label class="label-form" for="checkout_inline_reference">
                                                Referencia <i class="ri-asterisk text-accent"></i>
                                            </label>
                                            <div class="input-icon-container">
                                                <i class="ri-map-pin-2-line input-icon"></i>
                                                <textarea id="checkout_inline_reference" class="input-form" name="reference"
                                                    placeholder="Ej: Casa de fachada azul, portón negro, cerca al parque" data-validate="required|max:255"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row-fill">
                                        <div class="input-group">
                                            <label class="label-form" for="checkout_inline_receiver_name">
                                                Nombre destinatario <i class="ri-asterisk text-accent"></i>
                                            </label>
                                            <div class="input-icon-container">
                                                <i class="ri-user-3-line input-icon"></i>
                                                <input id="checkout_inline_receiver_name" type="text"
                                                    class="input-form" name="receiver_name"
                                                    placeholder="Nombre de quien recibirá"
                                                    data-validate="required|min:3|max:255" autocomplete="off" />
                                            </div>
                                        </div>

                                        <div class="input-group">
                                            <label class="label-form" for="checkout_inline_receiver_last_name">
                                                Apellido destinatario
                                            </label>
                                            <div class="input-icon-container">
                                                <i class="ri-user-3-line input-icon"></i>
                                                <input id="checkout_inline_receiver_last_name" type="text"
                                                    class="input-form" name="receiver_last_name"
                                                    placeholder="Apellido de quien recibirá"
                                                    data-validate="min:2|max:255" autocomplete="off" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row-fill">
                                        <div class="input-group">
                                            <label class="label-form" for="checkout_inline_receiver_phone">
                                                Teléfono <i class="ri-asterisk text-accent"></i>
                                            </label>
                                            <div class="input-icon-container">
                                                <i class="ri-phone-line input-icon"></i>
                                                <input id="checkout_inline_receiver_phone" type="text"
                                                    class="input-form" name="receiver_phone"
                                                    placeholder="Celular o teléfono de contacto"
                                                    data-validate="required|phone|max:20" autocomplete="off" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="checkout-inline-actions">
                                        <button type="button" class="site-btn site-btn-outline"
                                            data-address-form-cancel>
                                            Cancelar
                                        </button>
                                        <button type="submit" class="site-btn site-btn-primary"
                                            data-inline-address-submit>
                                            Guardar dirección
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </article>
                    </section>

                    {{-- PASO 2B: Recojo en tienda (solo pickup) --}}
                    <section class="checkout-section" id="checkoutStepPickup" data-step-dependent="pickup">
                        <article class="checkout-progress-step" data-step="2">
                            <span class="checkout-progress-index">2</span>
                            <span class="checkout-progress-label">Dirección o tienda</span>
                            <div class="checkout-progress-separator"></div>
                        </article>
                        <article class="w-full">
                            <div class="card-header-container">
                                <div class="card-header">
                                    <span class="card-title">Punto de recojo</span>
                                    <p class="card-description">Selecciona la tienda donde recogerás tu pedido.</p>
                                </div>
                            </div>
                            <div class="checkout-cards-grid" data-store-list>
                                {{-- Por ahora, locales estáticos; se puede migrar a tabla stores luego --}}
                                <label class="checkout-card">
                                    <input type="radio" name="store_id" value="store_central" class="sr-only">
                                    <div class="checkout-card-icon">
                                        <i class="ri-store-2-line"></i>
                                        <span class="store-name">Tienda Central</span>
                                    </div>
                                    <div class="checkout-card-body">
                                        <span class="checkout-card-title">
                                            <i class="ri-map-pin-2-line"></i>
                                            Dirección:
                                        </span>
                                        <p class="checkout-card-helper">Av. Principal 123, Miraflores</p>
                                        <span class="checkout-card-title">
                                            <i class="ri-time-line"></i>
                                            Horario de atención:
                                        </span>
                                        <p class="checkout-card-helper">Lun - Sáb: 9:00 am - 8:00 pm</p>
                                    </div>
                                </label>

                                <label class="checkout-card">
                                    <input type="radio" name="store_id" value="store_sucursal_1" class="sr-only">
                                    <div class="checkout-card-icon">
                                        <i class="ri-store-2-line"></i>
                                        <span class="store-name">Sucursal Norte</span>
                                    </div>
                                    <div class="checkout-card-body">
                                        <span class="checkout-card-title">
                                            <i class="ri-map-pin-2-line"></i>
                                            Dirección:
                                        </span>
                                        <p class="checkout-card-helper">Av. Las Flores 456, Los Olivos</p>
                                        <span class="checkout-card-title">
                                            <i class="ri-time-line"></i>
                                            Horario de atención:
                                        </span>
                                        <p class="checkout-card-helper">Lun - Sáb: 10:00 am - 7:00 pm</p>
                                    </div>
                                </label>
                            </div>
                        </article>
                    </section>

                    {{-- PASOS 4 y 5: Método de pago + Resumen (contenido existente) --}}
                    <section class="checkout-section" id="checkoutStepPayment">
                        <article class="checkout-progress-step" data-step="3">
                            <span class="checkout-progress-index">3</span>
                            <span class="checkout-progress-label">Pago y resumen</span>
                        </article>
                        <article class="w-full">
                            <div class="card-header">
                                <span class="card-title">Método de pago</span>
                                <p class="card-description">Selecciona cómo deseas pagar tu pedido.</p>
                            </div>
                            <form class="payment-methods-form" id="paymentMethodsForm">
                                <div class="payment-method-option">
                                    <input type="radio" value="card" name="payment_method"
                                        id="payment_method_card" class="payment-method-radio" checked>
                                    <label for="payment_method_card" class="payment-method-card">
                                        <div class="payment-method-header">
                                            <div class="payment-method-icon">
                                                <i class="ri-bank-card-line"></i>
                                            </div>
                                            <div class="payment-method-text">
                                                <span class="card-title">Tarjeta de crédito/débito</span>
                                                <span class="payment-method-helper">Paga con Visa, Mastercard u otras
                                                    tarjetas.</span>
                                            </div>
                                        </div>
                                        <img class="payment-method-img"
                                            src="{{ asset('images/checkout/cards_pay.png') }}"
                                            alt="Formas de pago con tarjeta">
                                    </label>


                                    <div class="payment-method-body">
                                        <p class="input-help-text mb-2">
                                            Luego de hacer click en "Pagar ahora" se abrira el checkout de Niubiz para
                                            que
                                            completes los datos de tu tarjeta y finalices tu compra de forma segura.
                                        </p>
                                        <ul class="payment-method-info">
                                            <li>Aceptamos Visa, Mastercard, American Express y otras tarjetas.</li>
                                            <li>El pago se procesa de forma segura a través de Niubiz.</li>
                                            <li>Tiempo de validación del pago: 5-15 minutos hábiles.</li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="payment-method-option">
                                    <input type="radio" value="bank" name="payment_method"
                                        id="payment_method_bank" class="payment-method-radio">
                                    <label for="payment_method_bank" class="payment-method-card">
                                        <div class="payment-method-header">
                                            <div class="payment-method-icon">
                                                <i class="ri-exchange-dollar-line"></i>
                                            </div>
                                            <div class="payment-method-text">
                                                <span class="card-title">Depósito bancario o Yape</span>
                                                <span class="payment-method-helper">Transfiere desde tu banco o paga
                                                    con
                                                    Yape.</span>
                                            </div>
                                        </div>
                                        <img class="payment-method-img"
                                            src="{{ asset('images/checkout/yape-pay.png') }}"
                                            alt="Depósito bancario o Yape">
                                    </label>

                                    <div class="payment-method-body">
                                        <p class="input-help-text mb-2">
                                            Al confirmar tu pedido, te mostraremos los datos de la cuenta bancaria o el
                                            número
                                            de
                                            Yape para completar el pago.
                                        </p>
                                        <ul class="payment-method-info">
                                            <li>Tiempo de validación del pago: 5-15 minutos hábiles.</li>
                                            <li>Envía el comprobante para acelerar la confirmación.</li>
                                        </ul>
                                    </div>
                                </div>
                            </form>
                        </article>
                    </section>
                </div>

                <aside class="checkout-summary">
                    <h2 class="checkout-summary-title">Resumen de compra</h2>
                    <div class="checkout-summary-body">
                        @foreach ($items as $item)
                            @php
                                $product = $item->product;
                                if (!$product) {
                                    continue;
                                }

                                $variant = $item->variant;
                                $image = $variant?->images->first() ?? $product->images->sortBy('order')->first();

                                $discountPercent = !is_null($product->discount)
                                    ? min(max((float) $product->discount, 0), 100)
                                    : 0;
                                $hasDiscount = $discountPercent > 0;

                                $basePrice =
                                    $variant && $variant->price && $variant->price > 0
                                        ? (float) $variant->price
                                        : (float) $product->price;
                                $discounted = $hasDiscount
                                    ? max($basePrice * (1 - $discountPercent / 100), 0)
                                    : $basePrice;

                                $lineTotal = $discounted * (int) $item->quantity;

                                $variantLabels = [];

                                if ($variant && $variant->features->isNotEmpty()) {
                                    foreach ($variant->features as $feature) {
                                        $option = $feature->option;
                                        $optionName = $option->name ?? ($option->slug ?? null);
                                        $label = $optionName ? $optionName . ': ' . $feature->value : $feature->value;
                                        $variantLabels[] = $label;
                                    }
                                }
                            @endphp

                            <article class="checkout-summary-item">
                                <div class="checkout-summary-thumb">
                                    @if ($image)
                                        <img src="{{ asset('storage/' . $image->path) }}"
                                            alt="{{ $image->alt ?? $product->name }}" loading="lazy">
                                    @else
                                        <div class="checkout-thumb-fallback">
                                            <i class="ri-image-line"></i>
                                        </div>
                                    @endif
                                </div>

                                <div class="checkout-summary-main">
                                    <div class="checkout-summary-name">{{ $product->name }}</div>
                                    <div class="checkout-summary-meta">
                                        <span
                                            class="checkout-summary-price">S/.{{ number_format($discounted, 2) }}</span>
                                        @if ($hasDiscount)
                                            <span
                                                class="checkout-summary-price-original">S/.{{ number_format($basePrice, 2) }}</span>
                                        @endif
                                        @if (!empty($variantLabels))
                                            <span class="checkout-summary-variant">
                                                {{ implode(' · ', $variantLabels) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="checkout-summary-qty">
                                    <span class="checkout-summary-qty-label">Cant.</span>
                                    <span class="checkout-summary-qty-value">x{{ $item->quantity }}</span>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="checkout-summary-footer" id="checkoutSummaryMeta">
                        <div class="checkout-summary-row">
                            <span>Total productos</span>
                            <span>{{ $itemsCount }}</span>
                        </div>
                        <div class="checkout-summary-row">
                            <span>Unidades totales</span>
                            <span>{{ $itemsQuantity }}</span>
                        </div>
                    </div>
                    <hr class="w-full my-0 border-default">
                    <div class="checkout-summary-footer">
                        <div class="checkout-summary-row">
                            <span>Subtotal</span>
                            <span>S/. {{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="checkout-summary-row">
                            <span>Precio de envío</span>
                            <span>S/. {{ number_format($shipping, 2) }}</span>
                        </div>
                        <div class="checkout-summary-row checkout-summary-row--total">
                            <span>Total a pagar</span>
                            <span>S/. {{ number_format($amount, 2) }}</span>
                        </div>
                    </div>

                    <div class="checkout-summary-actions">
                        <button type="button" id="payButton" class="boton-form boton-primary w-full py-3" disabled>
                            <span class="boton-form-icon"><i class="ri-wallet-3-fill"></i>
                            </span>
                            <span class="boton-form-text">Pagar ahora</span>
                        </button>
                    </div>

                    @if (session('niubiz'))
                        @php
                            $niubiz = session('niubiz');
                            $response = $niubiz['response'] ?? null;
                            $purchaseNumber = $niubiz['purchaseNumber'] ?? null;
                            $actionCode = $niubiz['actionCode'] ?? null;
                            $friendlyMessage = $niubiz['friendlyMessage'] ?? null;
                            $transactionDateFormatted = $niubiz['transactionDate'] ?? null;
                            $brand = $niubiz['brand'] ?? null;
                            $cardLast4 = $niubiz['cardLast4'] ?? null;
                        @endphp

                        @isset($response['data'])
                            <x-alert type="danger" title="No pudimos procesar tu pago">
                                @if ($friendlyMessage)
                                    <p class="mb-2"></p>
                                @endif

                                <li>
                                    <strong>Detalle del banco:</strong>
                                    <span>
                                        {{ $friendlyMessage ?? $response['data']['ACTION_DESCRIPTION'] }}
                                    </span>
                                </li>
                                @if ($actionCode)
                                    <li>
                                        <strong>Código de respuesta:</strong>
                                        <span>{{ $actionCode }}</span>
                                    </li>
                                @endif
                                @if ($brand || $cardLast4)
                                    <li>
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
                                    </li>
                                @endif
                                <li>
                                    <strong>Número del pedido:</strong>
                                    <span>{{ $purchaseNumber }}</span>
                                </li>
                                @if ($transactionDateFormatted)
                                    <li>
                                        <strong>Fecha y hora:</strong>
                                        <span>{{ $transactionDateFormatted }}</span>
                                    </li>
                                @endif

                                <p class="mt-2 text-xs text-muted">
                                    Puedes intentar nuevamente con otra tarjeta o seleccionar otro método de pago
                                    como depósito bancario o Yape.
                                </p>
                            </x-alert>
                        @endisset
                    @endif

                </aside>
            </div>
        @endif
    </section>
    @push('js')
        <script type="text/javascript" src="{{ config('services.niubiz.url_js') }}"></script>
        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function() {
                const rootDelivery = document.querySelector('[data-delivery-type-root]');
                const deliveryRadios = rootDelivery ? rootDelivery.querySelectorAll('input[name="delivery_type"]') : [];
                const deliverySection = document.getElementById('checkoutStepDelivery');
                const addressSection = document.getElementById('checkoutStepAddress');
                const pickupSection = document.getElementById('checkoutStepPickup');
                const paymentSection = document.getElementById('checkoutStepPayment');
                const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
                const payButton = document.getElementById('payButton');
                const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
                const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';

                const addressesRoot = document.querySelector('[data-checkout-addresses-root]');
                const addressList = addressesRoot ? addressesRoot.querySelector('[data-address-list]') : null;
                const addressFormPanel = addressesRoot ? addressesRoot.querySelector('[data-address-form-panel]') :
                    null;
                const addressFeedback = addressesRoot ? addressesRoot.querySelector('[data-address-feedback]') : null;
                const inlineAddressForm = document.getElementById('checkoutInlineAddressForm');
                const inlineAddressSubmitBtn = inlineAddressForm ?
                    inlineAddressForm.querySelector('[data-inline-address-submit]') :
                    null;
                const addressFormTitle = addressesRoot ? addressesRoot.querySelector('[data-address-form-title]') :
                    null;
                const addressFormDescription = addressesRoot ? addressesRoot.querySelector(
                    '[data-address-form-description]') : null;
                const addressFormMethodInput = inlineAddressForm ? inlineAddressForm.querySelector(
                    '[data-address-form-method]') : null;
                const addressFormCancelBtn = inlineAddressForm ? inlineAddressForm.querySelector(
                    '[data-address-form-cancel]') : null;
                const addressFormOpenButtons = addressesRoot ? addressesRoot.querySelectorAll(
                    '[data-address-form-open]') : [];

                const amount = '{{ number_format($amount, 2, '.', '') }}';
                const merchantId = '{{ config('services.niubiz.merchant_id') }}';
                const sessionToken = '{{ $session_token }}';

                const storeAddressUrl = addressesRoot ? addressesRoot.getAttribute('data-store-url') : '';
                const initialAddressesRaw = addressesRoot ? addressesRoot.getAttribute('data-initial-addresses') : '[]';
                const initialSelectedAddressId = addressesRoot ? addressesRoot.getAttribute(
                    'data-selected-address-id') : '';

                let checkoutAddresses = [];
                let selectedAddressId = initialSelectedAddressId ? Number(initialSelectedAddressId) : null;
                let addressMode = 'create';
                let addressUpdateUrl = null;
                let isAddressSubmitting = false;
                let inlineAddressValidator = null;

                try {
                    const parsed = JSON.parse(initialAddressesRaw || '[]');
                    checkoutAddresses = Array.isArray(parsed) ? parsed : [];
                } catch (error) {
                    checkoutAddresses = [];
                }

                if (!selectedAddressId && checkoutAddresses.length) {
                    selectedAddressId = Number(checkoutAddresses[0].id);
                }

                function getCurrentDeliveryType() {
                    let value = null;
                    deliveryRadios.forEach((input) => {
                        if (input.checked) {
                            value = input.value;
                        }
                    });
                    return value;
                }

                function updateSectionVisibility() {
                    const type = getCurrentDeliveryType();

                    const hide = (el, shouldHide) => {
                        if (!el) return;
                        el.classList.toggle('is-hidden', shouldHide);
                    };

                    hide(deliverySection, false);
                    hide(addressSection, type !== 'delivery');
                    hide(pickupSection, type !== 'pickup');
                    hide(paymentSection, false);
                }

                function isFlowComplete() {
                    const type = getCurrentDeliveryType();
                    if (!type) return false;

                    if (type === 'delivery') {
                        const addressChecked = document.querySelector('input[name="address_id"]:checked');
                        return !!addressChecked;
                    }

                    if (type === 'pickup') {
                        const storeChecked = document.querySelector('input[name="store_id"]:checked');
                        return !!storeChecked;
                    }

                    return false;
                }

                function updatePayButtonState() {
                    if (!payButton) return;
                    const ready = isFlowComplete();
                    payButton.disabled = !ready;
                }

                function ensureSelectedBadge(label) {
                    if (!label) return;

                    let badgeWrapper = label.querySelector(':scope > .store-badge');
                    if (!badgeWrapper) {
                        badgeWrapper = document.createElement('div');
                        badgeWrapper.className = 'store-badge';
                        badgeWrapper.innerHTML =
                            '<span class="badge bg-success"><i class="ri-checkbox-circle-fill"></i> Seleccionado</span>';
                        label.appendChild(badgeWrapper);
                    }
                }

                function removeSelectedBadge(label) {
                    if (!label) return;
                    const badgeWrapper = label.querySelector(':scope > .store-badge');
                    if (badgeWrapper) {
                        badgeWrapper.remove();
                    }
                }

                function updateSelectedCardsBadges() {
                    const groups = [{
                            name: 'delivery_type',
                            selector: '.checkout-card'
                        },
                        {
                            name: 'address_id',
                            selector: '.checkout-card'
                        },
                        {
                            name: 'store_id',
                            selector: '.checkout-card'
                        },
                        {
                            name: 'payment_method',
                            selector: '.payment-method-card'
                        },
                    ];

                    groups.forEach((group) => {
                        const radios = document.querySelectorAll(`input[name="${group.name}"]`);

                        radios.forEach((input) => {
                            const label = input.closest(group.selector) || document.querySelector(
                                `label[for="${input.id}"]`);
                            if (!label) return;

                            if (input.checked) {
                                ensureSelectedBadge(label);
                            } else {
                                removeSelectedBadge(label);
                            }
                        });
                    });
                }

                function clearRadioSelectionByName(name) {
                    const radios = document.querySelectorAll(`input[name="${name}"]`);
                    radios.forEach((radio) => {
                        radio.checked = false;
                    });
                }

                function refreshCheckoutUI() {
                    updateSectionVisibility();
                    updatePayButtonState();
                    updateSelectedCardsBadges();
                }

                function escapeHtml(value) {
                    return String(value ?? '')
                        .replaceAll('&', '&amp;')
                        .replaceAll('<', '&lt;')
                        .replaceAll('>', '&gt;')
                        .replaceAll('"', '&quot;')
                        .replaceAll("'", '&#039;');
                }

                function fillAddressForm(address = null) {
                    if (!inlineAddressForm) return;

                    inlineAddressForm.querySelector('#checkout_inline_type').value = address ? (address.type ||
                            'home') :
                        'home';
                    inlineAddressForm.querySelector('#checkout_inline_address_line').value = address ? (address
                        .address_line || '') : '';
                    inlineAddressForm.querySelector('#checkout_inline_district').value = address ? (address
                        .district || '') : '';
                    inlineAddressForm.querySelector('#checkout_inline_reference').value = address ? (address
                        .reference || '') : '';
                    inlineAddressForm.querySelector('#checkout_inline_receiver_name').value = address ? (address
                        .receiver_name || '') : '';
                    inlineAddressForm.querySelector('#checkout_inline_receiver_last_name').value = address ? (address
                        .receiver_last_name || '') : '';
                    inlineAddressForm.querySelector('#checkout_inline_receiver_phone').value = address ? (address
                        .receiver_phone || '') : '';
                }

                function resetAddressForm() {
                    if (!inlineAddressForm) return;

                    inlineAddressForm.reset();
                    if (addressFormMethodInput) {
                        addressFormMethodInput.value = 'POST';
                    }
                    if (inlineAddressValidator && typeof inlineAddressValidator.reset === 'function') {
                        inlineAddressValidator.reset();
                    }
                }

                function openAddressForm(mode = 'create', address = null) {
                    if (!addressFormPanel || !inlineAddressForm) return;

                    addressMode = mode;
                    addressUpdateUrl = mode === 'edit' && address ? address.update_url : null;
                    if (addressFormMethodInput) {
                        addressFormMethodInput.value = mode === 'edit' ? 'PUT' : 'POST';
                    }

                    if (addressFormTitle) {
                        addressFormTitle.textContent = mode === 'edit' ? 'Editar dirección' : 'Agregar dirección';
                    }
                    if (addressFormDescription) {
                        addressFormDescription.textContent = mode === 'edit' ?
                            'Modifica los datos de la dirección seleccionada.' :
                            'Completa los datos de entrega.';
                    }
                    if (inlineAddressSubmitBtn) {
                        inlineAddressSubmitBtn.textContent = mode === 'edit' ? 'Actualizar dirección' :
                            'Guardar dirección';
                    }

                    fillAddressForm(address);

                    if (inlineAddressValidator && typeof inlineAddressValidator.reset === 'function') {
                        inlineAddressValidator.reset();
                    }

                    addressFormPanel.classList.remove('is-hidden');
                    addressFormPanel.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }

                function closeAddressForm() {
                    if (!addressFormPanel) return;

                    addressFormPanel.classList.add('is-hidden');
                    resetAddressForm();
                    addressMode = 'create';
                    addressUpdateUrl = null;
                    isAddressSubmitting = false;
                }

                function setAddressFeedback(message, type = 'info') {
                    if (!addressFeedback) return;

                    if (!message) {
                        addressFeedback.textContent = '';
                        addressFeedback.classList.add('is-hidden');
                        addressFeedback.classList.remove('text-danger', 'text-success');
                        return;
                    }

                    addressFeedback.textContent = message;
                    addressFeedback.classList.remove('is-hidden', 'text-danger', 'text-success');
                    if (type === 'error') {
                        addressFeedback.classList.add('text-danger');
                    }
                    if (type === 'success') {
                        addressFeedback.classList.add('text-success');
                    }
                }

                function renderAddressCards() {
                    if (!addressList) return;

                    const hasAddresses = checkoutAddresses.length > 0;
                    addressList.classList.toggle('is-hidden', !hasAddresses);

                    if (!hasAddresses) {
                        addressList.innerHTML = '';
                        selectedAddressId = null;
                        addressMode = 'create';
                        addressUpdateUrl = null;
                        if (addressFormMethodInput) {
                            addressFormMethodInput.value = 'POST';
                        }
                        if (addressFormTitle) {
                            addressFormTitle.textContent = 'Nueva dirección';
                        }
                        if (addressFormDescription) {
                            addressFormDescription.textContent = 'Completa los datos de entrega.';
                        }
                        if (inlineAddressSubmitBtn) {
                            inlineAddressSubmitBtn.textContent = 'Guardar dirección';
                        }
                        if (addressFormPanel) {
                            addressFormPanel.classList.remove('is-hidden');
                        }
                        refreshCheckoutUI();
                        return;
                    }

                    if (addressFormPanel && addressMode === 'create' && !addressUpdateUrl) {
                        addressFormPanel.classList.add('is-hidden');
                    }

                    const existsSelected = checkoutAddresses.some((address) => Number(address.id) === Number(
                        selectedAddressId));
                    if (!existsSelected) {
                        selectedAddressId = Number(checkoutAddresses[0].id);
                    }

                    const html = checkoutAddresses.map((address) => {
                        const addressId = Number(address.id);
                        const isSelected = addressId === Number(selectedAddressId);
                        const typeLabel = address.type === 'office' ? 'Oficina' : 'Casa';
                        const typeIcon = address.type === 'office' ? 'ri-building-2-line' : 'ri-home-2-line';
                        const receiverFullName =
                            `${address.receiver_name || ''} ${address.receiver_last_name || ''}`.trim();

                        return `
                            <label class="checkout-card">
                                <input type="radio" name="address_id" value="${addressId}" class="sr-only" ${isSelected ? 'checked' : ''}>
                                <div class="checkout-card-icon" title="Dirección de ${address.type === 'office' ? 'oficina' : 'casa'}">
                                    <i class="${typeIcon}"></i>
                                    <span class="address-card-title">${typeLabel}</span>
                                </div>
                                <div class="checkout-card-body">
                                    <span class="card-title">${escapeHtml(receiverFullName)}</span>
                                    <ul>
                                        <li class="address-line">${escapeHtml(address.address_line)}</li>
                                        <li class="address-city">${escapeHtml(address.district)}</li>
                                        <li class="address-reference">${escapeHtml(address.reference)}</li>
                                        <li class="address-phone">${escapeHtml(address.receiver_phone)}</li>
                                    </ul>
                                </div>
                                <div class="address-card-actions">
                                    <button
                                        type="button"
                                        class="boton-pastel card-warning address-edit-btn"
                                        title="Editar dirección"
                                        aria-label="Editar dirección"
                                        data-address-id="${addressId}"
                                    >
                                        <i class="ri-pencil-fill"></i>
                                    </button>
                                    <button
                                        type="button"
                                        class="boton-pastel card-danger address-delete-btn"
                                        title="Eliminar dirección"
                                        aria-label="Eliminar dirección"
                                        data-address-delete-url="${escapeHtml(address.delete_url)}"
                                        data-address-id="${addressId}"
                                    >
                                        <i class="ri-delete-bin-5-fill"></i>
                                    </button>
                                </div>
                            </label>
                        `;
                    }).join('');

                    addressList.innerHTML = html;

                    refreshCheckoutUI();
                }

                function normalizeAddressCollection(payload) {
                    if (!Array.isArray(payload)) {
                        return [];
                    }

                    return payload.map((address) => ({
                        id: Number(address.id),
                        type: address.type || 'home',
                        address_line: address.address_line || '',
                        district: address.district || '',
                        reference: address.reference || '',
                        receiver_name: address.receiver_name || '',
                        receiver_last_name: address.receiver_last_name || '',
                        receiver_phone: address.receiver_phone || '',
                        update_url: address.update_url || '',
                        delete_url: address.delete_url || '',
                    }));
                }

                async function submitAddressRequest({
                    url,
                    formData,
                    method = 'POST',
                    successMessage = '',
                }) {
                    if (!url || !csrfToken) {
                        setAddressFeedback('No se pudo procesar la operación de direcciones.', 'error');
                        return false;
                    }

                    if (method !== 'POST') {
                        formData.set('_method', method);
                    }

                    if (!formData.has('_token')) {
                        formData.set('_token', csrfToken);
                    }

                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json, text/plain, */*',
                            },
                            body: formData,
                        });

                        const data = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            const firstError = data && data.errors ?
                                Object.values(data.errors)[0][0] :
                                (data.message || 'No se pudo guardar la dirección.');
                            setAddressFeedback(firstError, 'error');
                            return false;
                        }

                        if (data.status !== 'success' || !Array.isArray(data.addresses)) {
                            setAddressFeedback(
                                'No se pudo actualizar la lista de direcciones. Recarga la página e intenta nuevamente.',
                                'error');
                            return false;
                        }

                        checkoutAddresses = normalizeAddressCollection(data.addresses);

                        if (method === 'POST' && checkoutAddresses.length > 0) {
                            selectedAddressId = Number(checkoutAddresses[0].id);
                        } else if (!checkoutAddresses.some((item) => Number(item.id) === Number(
                                selectedAddressId))) {
                            selectedAddressId = checkoutAddresses.length ? Number(checkoutAddresses[0].id) : null;
                        }

                        renderAddressCards();
                        if (successMessage) {
                            setAddressFeedback(successMessage, 'success');
                        }

                        return true;
                    } catch (error) {
                        setAddressFeedback('Error de red. Intenta nuevamente.', 'error');
                        return false;
                    }
                }

                if (addressesRoot) {
                    renderAddressCards();

                    addressFormOpenButtons.forEach((button) => {
                        button.addEventListener('click', () => {
                            setAddressFeedback('');
                            openAddressForm('create');
                        });
                    });

                    addressesRoot.addEventListener('click', async (event) => {
                        const editButton = event.target.closest('.address-edit-btn');
                        if (editButton && addressesRoot.contains(editButton)) {
                            event.preventDefault();
                            event.stopPropagation();

                            const addressId = Number(editButton.getAttribute('data-address-id'));
                            const address = checkoutAddresses.find((item) => Number(item.id) === addressId);
                            if (!address) return;

                            setAddressFeedback('');
                            openAddressForm('edit', address);
                            return;
                        }

                        const deleteButton = event.target.closest('.address-delete-btn');
                        if (deleteButton && addressesRoot.contains(deleteButton)) {
                            event.preventDefault();
                            event.stopPropagation();

                            const deleteUrl = deleteButton.getAttribute('data-address-delete-url');
                            if (!deleteUrl) return;

                            deleteButton.disabled = true;
                            await submitAddressRequest({
                                url: deleteUrl,
                                formData: new FormData(),
                                method: 'DELETE',
                                successMessage: 'Dirección eliminada correctamente.',
                            });
                            deleteButton.disabled = false;
                        }
                    });

                    if (typeof window.initFormValidator === 'function' && inlineAddressForm) {
                        inlineAddressValidator = window.initFormValidator('#checkoutInlineAddressForm', {
                            validateOnBlur: true,
                            validateOnInput: false,
                            scrollToFirstError: true,
                            showSuccessIndicators: true,
                        });
                    }

                    if (addressFormCancelBtn) {
                        addressFormCancelBtn.addEventListener('click', () => {
                            closeAddressForm();
                        });
                    }

                    if (inlineAddressForm) {
                        inlineAddressForm.addEventListener('submit', async (event) => {
                            event.preventDefault();

                            if (isAddressSubmitting) return;
                            if (inlineAddressValidator && !inlineAddressValidator.validateAll()) return;

                            const targetUrl = addressMode === 'edit' ? addressUpdateUrl : storeAddressUrl;
                            if (!targetUrl) {
                                setAddressFeedback('No se pudo detectar la URL de la operación.', 'error');
                                return;
                            }

                            isAddressSubmitting = true;
                            if (inlineAddressSubmitBtn) inlineAddressSubmitBtn.disabled = true;

                            const ok = await submitAddressRequest({
                                url: targetUrl,
                                formData: new FormData(inlineAddressForm),
                                method: addressMode === 'edit' ? 'PUT' : 'POST',
                                successMessage: addressMode === 'edit' ?
                                    'Dirección actualizada correctamente.' :
                                    'Dirección guardada correctamente.',
                            });

                            if (ok) {
                                closeAddressForm();
                            }

                            isAddressSubmitting = false;
                            if (inlineAddressSubmitBtn) inlineAddressSubmitBtn.disabled = false;
                        });
                    }
                }

                // Eventos de cambio en tipo de entrega
                deliveryRadios.forEach((input) => {
                    input.addEventListener('change', () => {
                        if (input.value === 'delivery') {
                            clearRadioSelectionByName('store_id');
                        }
                        if (input.value === 'pickup') {
                            clearRadioSelectionByName('address_id');
                        }
                        refreshCheckoutUI();
                    });
                });

                paymentRadios.forEach((input) => {
                    input.addEventListener('change', () => {
                        refreshCheckoutUI();
                    });
                });

                // Escuchar selección de dirección o tienda
                document.addEventListener('change', (event) => {
                    const target = event.target;
                    if (!target) return;
                    if (target.name === 'address_id' || target.name === 'store_id') {
                        if (target.name === 'address_id') {
                            selectedAddressId = Number(target.value);
                        }
                        refreshCheckoutUI();
                    }
                });

                // Estado inicial
                refreshCheckoutUI();

                // Click en pagar ahora: configurar Niubiz con los parámetros de entrega seleccionados
                if (payButton) {
                    payButton.addEventListener('click', function() {
                        if (payButton.disabled) return;

                        const type = getCurrentDeliveryType();
                        let addressId = null;
                        let storeId = null;

                        if (type === 'delivery') {
                            const checked = document.querySelector('input[name="address_id"]:checked');
                            addressId = checked ? checked.value : null;
                        } else if (type === 'pickup') {
                            const checked = document.querySelector('input[name="store_id"]:checked');
                            storeId = checked ? checked.value : null;
                        }

                        const purchaseNumber = Math.floor(Math.random() * 1000000000);
                        let action = '{{ route('checkout.paid') }}' +
                            '?amount=' + amount +
                            '&purchaseNumber=' + purchaseNumber +
                            '&delivery_type=' + encodeURIComponent(type || '') +
                            '&address_id=' + encodeURIComponent(addressId || '') +
                            '&store_id=' + encodeURIComponent(storeId || '');

                        VisanetCheckout.configure({
                            sessiontoken: sessionToken,
                            channel: 'web',
                            merchantid: merchantId,
                            purchasenumber: purchaseNumber,
                            amount: amount,
                            expirationminutes: '20',
                            timeouturl: 'about:blank',
                            merchantlogo: 'img/comercio.png',
                            formbuttoncolor: '#000000',
                            action: action,
                            complete: function(params) {
                                alert(JSON.stringify(params));
                            }
                        });

                        VisanetCheckout.open();
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>
