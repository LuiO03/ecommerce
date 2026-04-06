<x-app-layout>
    @include('partials.site.breadcrumb', [
        'items' => $breadcrumbItems ?? [
            [
                'label' => 'Carrito de compras',
                'icon' => 'ri-shopping-cart-fill',
                'url' => route('carts.show'),
            ],
            [
                'label' => 'Envío',
                'icon' => 'ri-truck-fill',
            ],
        ],
    ])

    @php
        $items = $cart?->items ?? collect();
        $hasItems = $items->isNotEmpty();
        $itemsCount = $items->count();
        $itemsQuantity = $items->sum('quantity');
        $subtotal = 0;
    @endphp

    <section class="site-container shipping-page">
        <div class="section-header">
            <h1>Completa tu orden</h1>
            <p>

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
                <a href="{{ route('welcome.index') }}" class="boton-form boton-success py-3 px-5">
                    <span class="boton-form-icon"><i class="ri-store-2-fill"></i></span>
                    <span class="boton-form-text">Ir a la tienda</span>
                </a>
            </div>
        @else
            <div class="shipping-layout">
                <div class="shipping-main">

                    @if ($errors->any())
                        <x-alert type="danger" title="Revisa los datos de tu dirección">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </x-alert>
                    @endif

                    @php
                        $hasAddress = isset($address) && $address;
                    @endphp

                    <form id="shippingAddressForm" class="form-body" method="POST"
                        action="{{ route('shipping.addresses.store') }}"
                        data-editing="{{ $hasAddress ? '1' : '0' }}" novalidate>
                        @csrf
                        <h3 class="card-title">
                            Dirección de envío
                        </h3>
                            <div class="form-row-fit">
                                <div class="input-group">
                                    <label class="label-form" for="address_type">
                                        Tipo de dirección <i class="ri-asterisk text-accent"></i>
                                    </label>
                                    <div class="input-icon-container">
                                        <i class="ri-home-4-line input-icon"></i>
                                        @php
                                            $typeOld = old('type', $hasAddress ? $address->type : 'home');
                                        @endphp
                                        <select id="address_type" class="select-form" name="type"
                                            data-validate="selected">
                                            <option value="home" {{ $typeOld === 'home' ? 'selected' : '' }}>Casa
                                            </option>
                                            <option value="office" {{ $typeOld === 'office' ? 'selected' : '' }}>
                                                Oficina</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="input-group">
                                    <label class="label-form" for="address_line">
                                        Dirección completa <i class="ri-asterisk text-accent"></i>
                                    </label>
                                    <div class="input-icon-container">
                                        <i class="ri-map-pin-line input-icon"></i>
                                        <input id="address_line" type="text" class="input-form" name="address_line"
                                            value="{{ old('address_line', $hasAddress ? $address->address_line : null) }}"
                                            placeholder="Av. Siempre Viva 742, Interior 3"
                                            data-validate="required|min:5|max:255" autocomplete="off" />
                                    </div>
                                </div>
                            </div>

                            <div class="form-row-fit">
                                <div class="input-group">
                                    <label class="label-form" for="district">
                                        Distrito / Ciudad <i class="ri-asterisk text-accent"></i>
                                    </label>
                                    <div class="input-icon-container">
                                        <i class="ri-building-2-line input-icon"></i>
                                        <input id="district" type="text" class="input-form" name="district"
                                            value="{{ old('district', $hasAddress ? $address->district : null) }}"
                                            placeholder="Ej: Miraflores, Lima" data-validate="required|min:3|max:120"
                                            autocomplete="off" />
                                    </div>
                                </div>

                                <div class="input-group">
                                    <label class="label-form" for="reference">
                                        Referencia <i class="ri-asterisk text-accent"></i>
                                    </label>
                                    <div class="input-icon-container">
                                        <i class="ri-map-pin-2-line input-icon"></i>
                                        <textarea id="reference" class="input-form" name="reference"
                                            placeholder="Ej: Casa de fachada azul, portón negro, cerca al parque" data-validate="required|max:255">{{ old('reference', !empty($editingAddress) ? $editingAddress->reference : null) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <h3 class="card-title">
                                Datos Personales
                            </h3>

                            <div class="form-row-fill">
                                <div class="input-group">
                                    <label class="label-form" for="receiver_name">
                                        Nombre completo del receptor <i class="ri-asterisk text-accent"></i>
                                    </label>
                                    <div class="input-icon-container">
                                        <i class="ri-user-3-line input-icon"></i>
                                        <input id="receiver_name" type="text" class="input-form"
                                            name="receiver_name"
                                            value="{{ old('receiver_name', $hasAddress ? $address->receiver_name : auth()->user()?->name) }}"
                                            placeholder="Nombre de quien recibirá"
                                            data-validate="required|min:3|max:255" autocomplete="off" />
                                    </div>
                                </div>
                                <div class="input-group">
                                    <label class="label-form" for="receiver_last_name">
                                        Apellido del receptor
                                    </label>
                                    <div class="input-icon-container">
                                        <i class="ri-user-3-line input-icon"></i>
                                        <input id="receiver_last_name" type="text" class="input-form"
                                            name="receiver_last_name"
                                            value="{{ old('receiver_last_name', $hasAddress ? $address->receiver_last_name : auth()->user()?->last_name) }}"
                                            placeholder="Apellido de quien recibirá" data-validate="min:2|max:255"
                                            autocomplete="off" />
                                    </div>
                                </div>
                            </div>

                            <div class="form-row-fill">
                                <div class="input-group">
                                    <label class="label-form" for="receiver_phone">
                                        Teléfono de contacto <i class="ri-asterisk text-accent"></i>
                                    </label>
                                    <div class="input-icon-container">
                                        <i class="ri-phone-line input-icon"></i>
                                        <input id="receiver_phone" type="text" class="input-form"
                                            name="receiver_phone"
                                            value="{{ old('receiver_phone', $hasAddress ? $address->receiver_phone : auth()->user()?->phone) }}"
                                            placeholder="Celular o teléfono de contacto"
                                            data-validate="required|phone|max:20" autocomplete="off" />
                                    </div>
                                </div>
                            </div>
                            <div class="address-form-footer mt-4">
                                <button type="submit" class="site-btn site-btn-primary"
                                    id="shippingAddressSubmitBtn">
                                    <i class="ri-save-line"></i>
                                    Guardar dirección
                                </button>
                            </div>
                        </form>
                </div>

                <aside class="shipping-summary">
                    <h2 class="shipping-summary-title">Resumen de compra</h2>
                    <div class="shipping-summary-body">
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
                                $subtotal += $lineTotal;

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

                            <article class="shipping-summary-item">
                                <div class="shipping-summary-thumb">
                                    @if ($image)
                                        <img src="{{ asset('storage/' . $image->path) }}"
                                            alt="{{ $image->alt ?? $product->name }}" loading="lazy">
                                    @else
                                        <div class="checkout-thumb-fallback">
                                            <i class="ri-image-line"></i>
                                        </div>
                                    @endif
                                </div>

                                <div class="shipping-summary-main">
                                    <div class="shipping-summary-name">{{ $product->name }}</div>
                                    <div class="shipping-summary-meta">
                                        <span
                                            class="shipping-summary-price">S/.{{ number_format($discounted, 2) }}</span>
                                        @if ($hasDiscount)
                                            <span
                                                class="shipping-summary-price-original">S/.{{ number_format($basePrice, 2) }}</span>
                                        @endif
                                        @if (!empty($variantLabels))
                                            <span class="shipping-summary-variant">
                                                {{ implode(' · ', $variantLabels) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="shipping-summary-qty">
                                    <span class="shipping-summary-qty-label">Cant.</span>
                                    <span class="shipping-summary-qty-value">x{{ $item->quantity }}</span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    <hr class="w-full my-0 border-default">
                    <div class="shipping-summary-footer">
                        <div class="shipping-summary-row">
                            <span>Total productos</span>
                            <span>{{ $itemsCount }}</span>
                        </div>
                        <div class="shipping-summary-row shipping-summary-row--total">
                            <span>Total a pagar</span>
                            <span>S/. {{ number_format($subtotal, 2) }}</span>
                        </div>
                    </div>
                    <div class="shipping-summary-actions">
                        <a href="{{ route('checkout.index') }}" class="site-btn site-btn-primary">
                            Continuar con el pago
                        </a>
                    </div>
                </aside>
            </div>
        @endif
    </section>
    @push('js')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('shippingAddressForm');
                const submitBtn = document.getElementById('shippingAddressSubmitBtn');

                if (form && submitBtn && typeof window.initSubmitLoader === 'function' && typeof window
                    .initFormValidator === 'function') {
                    window.initSubmitLoader({
                        formId: 'shippingAddressForm',
                        buttonId: 'shippingAddressSubmitBtn',
                        loadingText: form.getAttribute('data-editing') === '1' ? 'Actualizando dirección...' :
                            'Guardando dirección...'
                    });

                    window.initFormValidator('#shippingAddressForm', {
                        validateOnBlur: true,
                        validateOnInput: false,
                        scrollToFirstError: true,
                        showSuccessIndicators: true,
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>
