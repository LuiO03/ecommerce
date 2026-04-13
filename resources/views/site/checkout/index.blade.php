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
            <h1>Checkout</h1>
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
                $defaultAddressId = optional($addresses->firstWhere('is_default', true))->id
                    ?? optional($addresses->first())->id;
            @endphp

            {{-- Indicador de progreso (solo UI, 3 pasos principales) --}}
            <div class="checkout-progress">
                <div class="checkout-progress-step is-active" data-step="1">
                    <span class="checkout-progress-index">1</span>
                    <span class="checkout-progress-label">Tipo de entrega</span>
                </div>
                <div class="checkout-progress-separator"></div>
                <div class="checkout-progress-step" data-step="2">
                    <span class="checkout-progress-index">2</span>
                    <span class="checkout-progress-label">Dirección o tienda</span>
                </div>
                <div class="checkout-progress-separator"></div>
                <div class="checkout-progress-step" data-step="3">
                    <span class="checkout-progress-index">3</span>
                    <span class="checkout-progress-label">Pago y resumen</span>
                </div>
            </div>

            <div class="checkout-flow">
                <div class="checkout-flow-main">
                    {{-- PASO 1: Tipo de entrega --}}
                    <section class="checkout-section" id="checkoutStepDelivery">
                        <h2 class="checkout-section-title">1. ¿Cómo quieres recibir tu pedido?</h2>
                        <p class="checkout-section-subtitle">Selecciona una opción para continuar.</p>

                        <div class="delivery-type-grid" data-delivery-type-root>
                            <label class="delivery-type-card" data-delivery-option="delivery">
                                <input type="radio" name="delivery_type" value="delivery" class="sr-only"
                                    {{ old('delivery_type', 'delivery') === 'delivery' ? 'checked' : '' }}>
                                <div class="delivery-type-icon">
                                    <i class="ri-truck-line"></i>
                                </div>
                                <div class="delivery-type-body">
                                    <span class="delivery-type-title">Delivery a domicilio</span>
                                    <span class="delivery-type-helper">Recibe tus productos en la dirección que elijas.</span>
                                </div>
                            </label>

                            <label class="delivery-type-card" data-delivery-option="pickup">
                                <input type="radio" name="delivery_type" value="pickup" class="sr-only"
                                    {{ old('delivery_type') === 'pickup' ? 'checked' : '' }}>
                                <div class="delivery-type-icon">
                                    <i class="ri-store-2-line"></i>
                                </div>
                                <div class="delivery-type-body">
                                    <span class="delivery-type-title">Recojo en tienda</span>
                                    <span class="delivery-type-helper">Pasa por uno de nuestros locales autorizados.</span>
                                </div>
                            </label>
                        </div>

                        <div class="checkout-wizard-actions">
                            <button type="button" class="site-btn site-btn-primary" data-checkout-next="1">
                                Continuar
                            </button>
                        </div>
                    </section>

                    {{-- PASO 2A: Dirección (solo delivery) --}}
                    <section class="checkout-section" id="checkoutStepAddress" data-step-dependent="delivery">
                        <h2 class="checkout-section-title">2. Dirección de envío</h2>
                        @if ($hasAddresses)
                            <p class="checkout-section-subtitle">Selecciona una de tus direcciones guardadas.</p>

                            <div class="address-list" data-address-list>
                                @foreach ($addresses as $address)
                                    <label class="address-card">
                                        <input type="radio" name="address_id" value="{{ $address->id }}"
                                            class="sr-only" {{ $address->id === $defaultAddressId ? 'checked' : '' }}>
                                        <div class="address-card-header">
                                            <span class="address-alias">
                                                {{ $address->type === 'office' ? 'Trabajo' : 'Casa' }}
                                                @if ($address->is_default)
                                                    <span class="address-badge">Predeterminada</span>
                                                @endif
                                            </span>
                                            <span class="address-city">{{ $address->district }}</span>
                                        </div>
                                        <div class="address-card-body">
                                            <p class="address-line">{{ $address->address_line }}</p>
                                            <p class="address-reference">{{ $address->reference }}</p>
                                            <p class="address-contact">
                                                <i class="ri-user-3-line"></i>
                                                {{ $address->receiver_name }}
                                                @if ($address->receiver_last_name)
                                                    {{ $address->receiver_last_name }}
                                                @endif
                                                · <i class="ri-phone-line"></i> {{ $address->receiver_phone }}
                                            </p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            <div class="checkout-inline-actions">
                                <a href="{{ route('site.profile.addresses') }}" class="link-inline">
                                    <i class="ri-edit-2-line"></i>
                                    Gestionar direcciones
                                </a>
                            </div>
                        @else
                            <p class="checkout-section-subtitle">
                                Aún no tienes direcciones guardadas. Antes de pagar, registra al menos una.
                            </p>
                            <div class="checkout-inline-actions">
                                <a href="{{ route('site.profile.addresses') }}" class="site-btn site-btn-outline">
                                    <i class="ri-map-pin-add-line"></i>
                                    Agregar nueva dirección
                                </a>
                            </div>
                        @endif

                        <div class="checkout-wizard-actions">
                            <button type="button" class="site-btn site-btn-outline" data-checkout-prev="2">
                                Volver al paso anterior
                            </button>
                            <button type="button" class="site-btn site-btn-primary" data-checkout-next="2">
                                Continuar a pago
                            </button>
                        </div>
                    </section>

                    {{-- PASO 2B: Recojo en tienda (solo pickup) --}}
                    <section class="checkout-section" id="checkoutStepPickup" data-step-dependent="pickup">
                        <h2 class="checkout-section-title">2. Punto de recojo</h2>
                        <p class="checkout-section-subtitle">Selecciona la tienda donde recogerás tu pedido.</p>

                        <div class="store-list" data-store-list>
                            {{-- Por ahora, locales estáticos; se puede migrar a tabla stores luego --}}
                            <label class="store-card">
                                <input type="radio" name="store_id" value="store_central" class="sr-only">
                                <div class="store-card-header">
                                    <span class="store-name">Tienda Central</span>
                                    <span class="store-badge">Recomendado</span>
                                </div>
                                <div class="store-card-body">
                                    <p class="store-address">Av. Principal 123, Miraflores</p>
                                    <p class="store-schedule">Lun - Sáb: 9:00 am - 8:00 pm</p>
                                </div>
                            </label>

                            <label class="store-card">
                                <input type="radio" name="store_id" value="store_sucursal_1" class="sr-only">
                                <div class="store-card-header">
                                    <span class="store-name">Sucursal Norte</span>
                                </div>
                                <div class="store-card-body">
                                    <p class="store-address">Av. Las Flores 456, Los Olivos</p>
                                    <p class="store-schedule">Lun - Sáb: 10:00 am - 7:00 pm</p>
                                </div>
                            </label>
                        </div>

                        <div class="checkout-wizard-actions">
                            <button type="button" class="site-btn site-btn-outline" data-checkout-prev="2">
                                Volver al paso anterior
                            </button>
                            <button type="button" class="site-btn site-btn-primary" data-checkout-next="2">
                                Continuar a pago
                            </button>
                        </div>
                    </section>

                    {{-- PASOS 4 y 5: Método de pago + Resumen (contenido existente) --}}
                    <section class="checkout-section" id="checkoutStepPayment">
                        <h2 class="checkout-section-title">3. Método de pago</h2>

                        <form class="payment-methods-form" id="paymentMethodsForm">
                        <div class="payment-method-option">
                            <input type="radio" value="card" name="payment_method" id="payment_method_card"
                                class="payment-method-radio" checked>
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
                                <img class="payment-method-img" src="{{ asset('images/checkout/cards_pay.png') }}"
                                    alt="Formas de pago con tarjeta">
                            </label>


                            <div class="payment-method-body">
                                <p class="input-help-text mb-2">
                                    Luego de hacer click en "Pagar ahora" se abrira el checkout de Niubiz para que
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
                            <input type="radio" value="bank" name="payment_method" id="payment_method_bank"
                                class="payment-method-radio">
                            <label for="payment_method_bank" class="payment-method-card">
                                <div class="payment-method-header">
                                    <div class="payment-method-icon">
                                        <i class="ri-exchange-dollar-line"></i>
                                    </div>
                                    <div class="payment-method-text">
                                        <span class="card-title">Depósito bancario o Yape</span>
                                        <span class="payment-method-helper">Transfiere desde tu banco o paga con
                                            Yape.</span>
                                    </div>
                                </div>
                                <img class="payment-method-img" src="{{ asset('images/checkout/yape-pay.png') }}"
                                    alt="Depósito bancario o Yape">
                            </label>

                            <div class="payment-method-body">
                                <p class="input-help-text mb-2">
                                    Al confirmar tu pedido, te mostraremos los datos de la cuenta bancaria o el número
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

                    <div class="checkout-wizard-actions">
                        <button type="button" class="site-btn site-btn-outline" data-checkout-prev="3">
                            Volver al paso anterior
                        </button>
                    </div>
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
                        <button type="button" id="payButton" class="boton-form boton-primary w-full py-3" disabled
                            >
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
                const progressSteps = document.querySelectorAll('.checkout-progress-step');
                const nextButtons = document.querySelectorAll('[data-checkout-next]');
                const prevButtons = document.querySelectorAll('[data-checkout-prev]');
                const payButton = document.getElementById('payButton');
                const amount = '{{ number_format($amount, 2, '.', '') }}';
                const merchantId = '{{ config('services.niubiz.merchant_id') }}';
                const sessionToken = '{{ $session_token }}';

                let currentStep = 1;

                function getCurrentDeliveryType() {
                    let value = null;
                    deliveryRadios.forEach((input) => {
                        if (input.checked) {
                            value = input.value;
                        }
                    });
                    return value;
                }

                function getCurrentStep() {
                    return currentStep;
                }

                function updateSectionVisibility() {
                    const step = getCurrentStep();
                    const type = getCurrentDeliveryType();

                    const hide = (el, shouldHide) => {
                        if (!el) return;
                        el.classList.toggle('is-hidden', shouldHide);
                    };

                    // Paso 1: solo tipo de entrega
                    if (step === 1) {
                        hide(deliverySection, false);
                        hide(addressSection, true);
                        hide(pickupSection, true);
                        hide(paymentSection, true);
                        return;
                    }

                    // Paso 2: dirección o tienda según tipo
                    if (step === 2) {
                        hide(deliverySection, true);
                        if (type === 'delivery') {
                            hide(addressSection, false);
                            hide(pickupSection, true);
                        } else if (type === 'pickup') {
                            hide(addressSection, true);
                            hide(pickupSection, false);
                        } else {
                            hide(addressSection, true);
                            hide(pickupSection, true);
                        }
                        hide(paymentSection, true);
                        return;
                    }

                    // Paso 3: método de pago + resumen
                    if (step === 3) {
                        hide(deliverySection, true);
                        hide(addressSection, true);
                        hide(pickupSection, true);
                        hide(paymentSection, false);
                    }
                }

                function updateProgressBar() {
                    if (!progressSteps.length) return;

                    const currentStep = getCurrentStep();

                    progressSteps.forEach((stepEl) => {
                        const stepIndex = parseInt(stepEl.getAttribute('data-step'), 10) || 0;
                        stepEl.classList.remove('is-active', 'is-completed');

                        if (stepIndex < currentStep) {
                            stepEl.classList.add('is-completed');
                        } else if (stepIndex === currentStep) {
                            stepEl.classList.add('is-active');
                        }
                    });
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

                function updateNextButtonsState() {
                    const type = getCurrentDeliveryType();

                    const step1Next = document.querySelector('[data-checkout-next="1"]');
                    const step2NextButtons = document.querySelectorAll('[data-checkout-next="2"]');

                    // Paso 1: requiere elegir tipo de entrega
                    if (step1Next) {
                        step1Next.disabled = !type;
                    }

                    // Paso 2: requiere dirección (delivery) o tienda (pickup)
                    let canGoToStep3 = false;
                    if (type === 'delivery') {
                        canGoToStep3 = !!document.querySelector('input[name="address_id"]:checked');
                    } else if (type === 'pickup') {
                        canGoToStep3 = !!document.querySelector('input[name="store_id"]:checked');
                    }

                    step2NextButtons.forEach((btn) => {
                        btn.disabled = !canGoToStep3;
                    });
                }

                function goToStep(targetStep) {
                    if (targetStep < 1 || targetStep > 3) return;
                    currentStep = targetStep;
                    updateSectionVisibility();
                    updateProgressBar();
                    updatePayButtonState();
                    updateNextButtonsState();
                }

                // Navegación con botones Siguiente
                nextButtons.forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const fromStep = parseInt(btn.getAttribute('data-checkout-next'), 10) || 1;
                        if (fromStep === 1) {
                            goToStep(2);
                            return;
                        }

                        if (fromStep === 2) {
                            goToStep(3);
                        }
                    });
                });

                // Navegación con botones Atrás
                prevButtons.forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const fromStep = parseInt(btn.getAttribute('data-checkout-prev'), 10) || 2;

                        if (fromStep === 2) {
                            goToStep(1);
                            return;
                        }

                        if (fromStep === 3) {
                            goToStep(2);
                        }
                    });
                });

                // Eventos de cambio en tipo de entrega
                deliveryRadios.forEach((input) => {
                    input.addEventListener('change', () => {
                        updateSectionVisibility();
                        updatePayButtonState();
                        updateProgressBar();
                        updateNextButtonsState();
                    });
                });

                // Escuchar selección de dirección o tienda
                document.addEventListener('change', (event) => {
                    const target = event.target;
                    if (!target) return;
                    if (target.name === 'address_id' || target.name === 'store_id') {
                        updateSectionVisibility();
                        updatePayButtonState();
                        updateProgressBar();
                        updateNextButtonsState();
                    }
                });

                // Estado inicial
                updateSectionVisibility();
                updatePayButtonState();
                updateProgressBar();
                updateNextButtonsState();

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
                        let action = '{{ route('checkout.paid') }}'
                            + '?amount=' + amount
                            + '&purchaseNumber=' + purchaseNumber
                            + '&delivery_type=' + encodeURIComponent(type || '')
                            + '&address_id=' + encodeURIComponent(addressId || '')
                            + '&store_id=' + encodeURIComponent(storeId || '');

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
