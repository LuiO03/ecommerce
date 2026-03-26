<section class="shipping-addresses-section">

    @if (!$newAddress)
        @if ($addresses->isEmpty())

            <div class="card-empty">
                <div class="address-empty-icon">
                    <i class="ri-map-pin-line"></i>
                </div>
                <p>No tienes direcciones de envío guardadas.</p>
            </div>
            <button class="site-btn site-btn-primary" wire:click="$set('newAddress', true)">
                <i class="ri-add-line"></i>
                Agregar nueva dirección
            </button>
        @else
            <div class="card-title">
                Selecciona una dirección de envío:
            </div>
            <ul class="shipping-addresses-list">
                @foreach ($addresses as $address)
                    <li class="shipping-address-item{{ $address->is_default ? ' shipping-address-item--default' : '' }}">
                        <div class="shipping-address-item-header">
                            @switch($address->type)
                                @case('home')
                                    <i class="ri-home-4-fill"></i>
                                @break

                                @case('office')
                                    <i class="ri-building-2-fill"></i>
                                @break

                                @default
                                    <i class="ri-map-pin-line"></i>
                            @endswitch
                            <span class="card-title">
                                @switch($address->type)
                                    @case('home')
                                        Casa
                                    @break

                                    @case('office')
                                        Oficina
                                    @break

                                    @default
                                        Otra dirección
                                @endswitch
                            </span>
                        </div>

                        <div class="shipping-address-meta">
                            @php($user = auth()->user())
                            <div class="card-title">
                                @if ((int) $address->receiver_type === 1)
                                    {{ $user?->name ?? 'Sin nombre registrado' }} {{ $user?->last_name ?? '' }}
                                @else
                                    {{ trim(($address->receiver_name ?? '') . ' ' . ($address->receiver_last_name ?? '')) ?: 'Sin nombre registrado' }}
                                @endif
                            </div>
                            <div>{{ $address->address_line }}</div>
                            <div>{{ $address->district }}</div>
                            @if ($address->reference)
                                <div>
                                    <span class="shipping-title">Referencia:</span> <br>
                                    {{ $address->reference }}
                                </div>
                            @endif

                        </div>

                        @if ($address->id)
                            <div class="shipping-address-actions">
                                @unless ($address->is_default)
                                    <button type="button" class="boton-form boton-success"
                                        title="Establecer como predeterminada" wire:click="setDefault({{ $address->id }})">
                                        <span class="boton-form-icon">
                                            <i class="ri-star-fill"></i>
                                        </span>
                                    </button>
                                @endunless

                                <button type="button" class="boton-form boton-warning"
                                    wire:click="editAddress({{ $address->id }})">
                                    <span class="boton-form-icon">
                                        <i class="ri-edit-circle-fill"></i>
                                    </span>
                                </button>

                                <button type="button" class="boton-form boton-danger"
                                    data-delete-address-id="{{ $address->id }}"
                                    data-delete-address-label="{{ $address->address_line }}"
                                    title="Eliminar dirección de envío">
                                    <span class="boton-form-icon">
                                        <i class="ri-delete-bin-fill"></i>
                                    </span>
                                </button>
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    @endif

    @if ($newAddress)
        <div class="shipping-address-form">
            <form id="shippingAddressForm" class="form-body" wire:submit.prevent="saveAddress" novalidate>
                {{-- Banner de errores de backend (solo si JS fue omitido o falló) --}}
                @if ($errors->any())
                    <div class="form-error-banner">
                        <i class="ri-error-warning-line form-error-icon"></i>
                        <div>
                            <h4 class="form-error-title">Se encontraron los siguientes errores:</h4>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
                <div class="form-row-fit">
                    <div class="input-group">
                        <label class="label-form" for="address_type">
                            Tipo de dirección <i class="ri-asterisk text-accent"></i>
                        </label>
                        <div class="input-icon-container">
                            <i class="ri-home-4-line input-icon"></i>
                            <select id="address_type" class="select-form" wire:model="type" data-validate="selected">
                                <option value="home">Casa</option>
                                <option value="office">Oficina</option>
                            </select>
                        </div>
                    </div>

                    <div class="input-group">
                        <label class="label-form" for="address_line">
                            Dirección completa <i class="ri-asterisk text-accent"></i>
                        </label>
                        <div class="input-icon-container">
                            <i class="ri-map-pin-line input-icon"></i>
                            <input id="address_line" type="text" class="input-form" wire:model="address_line"
                                placeholder="Av. Siempre Viva 742, Interior 3" data-validate="required|min:5|max:255"
                                autocomplete="off" />
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
                            <input id="district" type="text" class="input-form" wire:model="district"
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
                            <textarea id="reference" class="input-form" wire:model="reference"
                                placeholder="Ej: Casa de fachada azul, portón negro, cerca al parque" data-validate="required|max:255"></textarea>
                        </div>
                    </div>
                </div>

                <hr class="w-full my-0 border-default">

                <div class="form-row-fill">
                    <div class="input-group">
                        <label class="label-form" for="receiver_type_owner">¿Quién recibirá el pedido?</label>
                        <div class="option-pill-group">
                            <label class="option-pill" title="titular de la cuenta">
                                <input type="radio" name="receiver_type" id="receiver_type_owner" value="owner"
                                    wire:click="chooseReceiverType('owner')" @checked($receiver_type === 'owner')>
                                <div class="option-pill-body">
                                    <div class="option-pill-main">
                                        <i class="ri-user-3-line option-pill-icon"></i>
                                        <span class="option-pill-title">Yo</span>
                                    </div>
                                </div>
                            </label>

                            <label class="option-pill" title="Indica quien recibirá el pedido.">
                                <input type="radio" name="receiver_type" id="receiver_type_other" value="other"
                                    wire:click="chooseReceiverType('other')" @checked($receiver_type === 'other')>
                                <div class="option-pill-body">
                                    <div class="option-pill-main">
                                        <i class="ri-user-line option-pill-icon"></i>
                                        <span class="option-pill-title">Otra persona</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <p class="input-help-text">
                            Si eliges "Yo", usaremos tu nombre y teléfono de cuenta. Si eliges "Otra persona", podrás
                            ingresar el nombre y teléfono del receptor.
                        </p>
                    </div>
                    @if ($receiver_type === 'owner')
                        @php($user = auth()->user())
                        <div class="input-group" wire:key="receiver-owner-name">
                            <label class="label-form" for="receiver_name">
                                Nombre<i class="ri-asterisk text-accent"></i>
                            </label>
                            <div class="input-icon-container">
                                <i class="ri-user-3-line input-icon"></i>
                                <input id="receiver_name" type="text" class="input-form"
                                    value="{{ $user->name ?? '' }}" placeholder="Nombre del titular"
                                    autocomplete="off" disabled />
                            </div>
                        </div>
                        <div class="input-group" wire:key="receiver-owner-last-name">
                            <label class="label-form" for="receiver_last_name">
                                Apellido <i class="ri-asterisk text-accent"></i>
                            </label>
                            <div class="input-icon-container">
                                <i class="ri-user-3-line input-icon"></i>
                                <input id="receiver_last_name" type="text" class="input-form"
                                    value="{{ $user->last_name ?? '' }}" placeholder="Apellido del titular"
                                    autocomplete="off" disabled />
                            </div>
                        </div>
                        <div class="input-group" wire:key="receiver-owner-phone">
                            <label class="label-form" for="receiver_phone">
                                Teléfono
                            </label>
                            <div class="input-icon-container">
                                <i class="ri-phone-line input-icon"></i>
                                <input id="receiver_phone" type="text" class="input-form"
                                    value="{{ $user->phone ?? '' }}" placeholder="Teléfono del titular"
                                    autocomplete="off" disabled />
                            </div>
                        </div>
                    @else
                        <div class="input-group" wire:key="receiver-other-name">
                            <label class="label-form" for="receiver_name">
                                Nombre del receptor <i class="ri-asterisk text-accent"></i>
                            </label>
                            <div class="input-icon-container">
                                <i class="ri-user-3-line input-icon"></i>
                                <input id="receiver_name" type="text" class="input-form"
                                    wire:model="receiver_name" placeholder="Nombre de quien recibirá"
                                    data-validate="required|min:3|max:255" autocomplete="off"/>
                            </div>
                        </div>
                        <div class="input-group" wire:key="receiver-other-last-name">
                            <label class="label-form" for="receiver_last_name">
                                Apellido del receptor <i class="ri-asterisk text-accent"></i>
                            </label>
                            <div class="input-icon-container">
                                <i class="ri-user-3-line input-icon"></i>
                                <input id="receiver_last_name" type="text" class="input-form"
                                    wire:model="receiver_last_name" placeholder="Apellido de quien recibirá"
                                    data-validate="required|min:2|max:255" autocomplete="off"/>
                            </div>
                        </div>
                        <div class="input-group" wire:key="receiver-other-phone">
                            <label class="label-form" for="receiver_phone">
                                Teléfono de contacto <i class="ri-asterisk text-accent"></i>
                            </label>
                            <div class="input-icon-container">
                                <i class="ri-phone-line input-icon"></i>
                                <input id="receiver_phone" type="text" class="input-form"
                                    wire:model="receiver_phone" placeholder="Celular o teléfono de contacto"
                                    data-validate="required|phone|max:20" autocomplete="off"/>
                            </div>
                        </div>
                    @endif
                </div>

                @if (auth()->check() && $receiver_type === 'owner')
                    <div class="form-row-fit">
                        <div class="input-group">
                            <label class="label-form" for="owner_document_type">
                                Documento del titular
                            </label>
                            <div class="input-icon-container">
                                <i class="ri-id-card-line input-icon"></i>
                                @php($user = auth()->user())
                                <input id="owner_document_type" type="text" class="input-form"
                                    value="{{ $user->document_type ?? 'No registrado' }}" disabled>
                            </div>
                        </div>
                        <div class="input-group">
                            <label class="label-form" for="owner_document_number">
                                Número de documento
                            </label>
                            <div class="input-icon-container">
                                <i class="ri-hashtag input-icon"></i>
                                @php($user = auth()->user())
                                <input id="owner_document_number" type="text" class="input-form"
                                    value="{{ $user->document_number ?? 'No registrado' }}" disabled>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="input-checkbox">
                    <input id="is_default" type="checkbox" wire:model="is_default">
                    <label for="is_default">Usar como dirección predeterminada</label>
                </div>

                <div class="address-form-footer">
                    <button type="button" class="site-btn site-btn-outline" wire:click="cancelNewAddress">
                        <i class="ri-close-line"></i>
                        Cancelar
                    </button>

                    <button type="submit" class="site-btn site-btn-primary" id="shippingAddressSubmitBtn">
                        <i class="ri-save-line"></i>
                        {{ $editingAddressId ? 'Actualizar dirección' : 'Guardar dirección' }}
                    </button>
                </div>
            </form>
        </div>
    @endif

    @if (!$newAddress && $addresses->isNotEmpty())
        <button class="site-btn site-btn-primary" wire:click="$set('newAddress', true)">
            <i class="ri-add-line"></i>
            Agregar nueva dirección
        </button>
    @endif
</section>

@push('js')
    <script>
        (function() {
            function initShippingAddressForm() {
                const form = document.getElementById('shippingAddressForm');

                // Si el formulario no está visible (newAddress = false) o ya tiene validador, no hacemos nada
                if (!form || form.__validator) {
                    return;
                }

                if (typeof window.initFormValidator !== 'function' || typeof window.initSubmitLoader !== 'function') {
                    return;
                }

                // 1) Submit loader primero
                window.initSubmitLoader({
                    formId: 'shippingAddressForm',
                    buttonId: 'shippingAddressSubmitBtn',
                    loadingText: 'Guardando dirección...'
                });

                // 2) Form validator después
                window.initFormValidator('#shippingAddressForm', {
                    validateOnBlur: true,
                    validateOnInput: false,
                    scrollToFirstError: true,
                    showSuccessIndicators: true,
                });

                // 3) Integración específica con Livewire: bloquear submit si hay errores
                // usando un listener en fase de captura que corre ANTES que Livewire.
                if (form.__validator && !form.__livewireValidationBound) {
                    form.__livewireValidationBound = true;

                    form.addEventListener('submit', function (e) {
                        // Ejecutar validación completa manualmente
                        const isValid = form.__validator.validateAll();

                        if (!isValid) {
                            e.preventDefault();
                            e.stopImmediatePropagation();
                        }
                        // Si es válido, dejamos continuar: Livewire + submitLoader manejarán el envío
                    }, true); // true => fase de captura, antes que Livewire
                }
            }

            function initDeleteAddressButtons() {
                if (typeof window.showConfirm !== 'function' || !window.Livewire || typeof window.Livewire.dispatch !== 'function') {
                    return;
                }

                const buttons = document.querySelectorAll('[data-delete-address-id]');

                buttons.forEach((btn) => {
                    if (btn.__deleteHandlerBound) {
                        return;
                    }
                    btn.__deleteHandlerBound = true;

                    btn.addEventListener('click', (event) => {
                        event.preventDefault();

                        const id = btn.getAttribute('data-delete-address-id');
                        const label = btn.getAttribute('data-delete-address-label') || '';

                        window.showConfirm({
                            type: 'danger',
                            header: 'Eliminar dirección',
                            title: '¿Eliminar esta dirección de envío?',
                            message: 'Se eliminará la siguiente dirección:<br><strong>' + label + '</strong><br>Esta acción no se puede deshacer.',
                            confirmText: 'Sí, eliminar',
                            cancelText: 'No, mantener',
                            onConfirm: () => {
                                window.Livewire.dispatch('delete-address-confirmed', {
                                    id: parseInt(id, 10)
                                });
                            },
                        });
                    });
                });
            }

            // Inicialización en carga inicial del DOM
            document.addEventListener('DOMContentLoaded', function() {
                initShippingAddressForm();
                initDeleteAddressButtons();
            });

            // Reintentar después de navegación Livewire (Livewire 3)
            document.addEventListener('livewire:navigated', function() {
                initShippingAddressForm();
                initDeleteAddressButtons();
            });

            // Reintentar después de cada morph/update de Livewire (cuando se abre/cierra el formulario)
            if (window.Livewire && typeof window.Livewire.hook === 'function') {
                window.Livewire.hook('morph.updated', () => {
                    setTimeout(() => {
                        initShippingAddressForm();
                        initDeleteAddressButtons();
                    }, 100);
                });
            }
        })();
    </script>
@endpush
