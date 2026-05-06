<x-app-layout>
    @php
        $companySettings = function_exists('company_setting') ? company_setting() : null;
    @endphp
    @section('title', 'Contacto')

    <section class="contact-hero">
        <div class="site-container contact-hero__container">
            <div class="contact-hero__content">
                <span class="contact-hero__chip"><i class="ri-customer-service-2-line"></i> Estamos aquí para
                    ayudarte</span>
                <h1 class="contact-hero__title">Hablemos sobre tu próxima compra</h1>
                <p class="contact-hero__subtitle">Cuéntanos qué necesitas, en qué etapa estás o qué problema quieres
                    resolver. Te respondemos con información clara y accionable.</p>
                <ul class="contact-hero__highlights">
                    <li><i class="ri-time-line"></i> Respuestas en horario de oficina</li>
                    <li><i class="ri-shield-keyhole-line"></i> Datos protegidos y uso responsable</li>
                    <li><i class="ri-message-2-line"></i> Lenguaje claro, sin tecnicismos innecesarios</li>
                </ul>
            </div>
            <div class="contact-hero__card">
                <p class="contact-hero__label">Canales directos</p>
                <p class="contact-hero__line"><i class="ri-mail-line"></i>
                    <span>{{ $companySettings?->support_email ?? ($companySettings?->email ?? 'Pronto habilitaremos este canal.') }}</span>
                </p>
                <p class="contact-hero__line"><i class="ri-phone-line"></i>
                    <span>{{ $companySettings?->support_phone ?? ($companySettings?->phone ?? 'Pronto habilitaremos este canal.') }}</span>
                </p>
                <p class="contact-hero__note">Si tu consulta es sobre un pedido, incluye el número de pedido para
                    ayudarte más rápido.</p>
            </div>
        </div>
    </section>

    <section class="contact-section">
        <div class="site-container contact-section__container">
            <form id="contact-form" method="POST" action="" class="form-container" autocomplete="off">
                @csrf
                @if ($errors->hasBag('main') && $errors->main->any())
                    <div class="form-error-banner">
                        <i class="ri-error-warning-line form-error-icon"></i>
                        <div>
                            <h4 class="form-error-title">Se encontraron los siguientes errores:</h4>
                            <ul>
                                @foreach ($errors->main->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
                <div class="form-body">
                    <div class="card-header">
                        <h3 class="card-title">Envíanos un mensaje</h3>
                        <p class="card-description">Completa el formulario y cuéntanos en qué podemos ayudarte.
                            Intentaremos
                            responderte lo antes posible.</p>
                    </div>
                    <div class="form-row-fit">
                        <div class="input-group">
                            <label for="name" class="label-form">Nombre completo<i
                                    class="ri-asterisk text-accent"></i></label>
                            <div class="input-icon-container">
                                <i class="ri-user-line input-icon"></i>
                                <input type="text" id="name" name="name" class="input-form"
                                    placeholder="Cómo te llamas" data-validate="required|min:3"
                                    value="{{ old('name', auth()->user()->name ?? '') }}">
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="email" class="label-form">Correo electrónico<i
                                    class="ri-asterisk text-accent"></i></label>
                            <div class="input-icon-container">
                                <i class="ri-mail-line input-icon"></i>
                                <input type="email" id="email" name="email" class="input-form"
                                    placeholder="Donde podemos responderte" data-validate="required|email"
                                    value="{{ old('email', auth()->user()->email ?? '') }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-row-fit">
                        <div class="input-group">
                            <label for="topic" class="label-form">Motivo de contacto<i
                                    class="ri-asterisk text-accent"></i></label>
                            <div class="input-icon-container">
                                <i class="ri-question-line input-icon"></i>
                                <select id="topic" name="topic" class="input-form select-form"
                                    data-validate="required">
                                    <option value="">Selecciona una opción</option>
                                    <option value="order" @selected(old('topic') === 'order')>Consulta sobre un pedido
                                    </option>
                                    <option value="product" @selected(old('topic') === 'product')>Información de productos
                                    </option>
                                    <option value="account" @selected(old('topic') === 'account')>Problemas con mi cuenta
                                    </option>
                                    <option value="billing" @selected(old('topic') === 'billing')>Pagos y facturación</option>
                                    <option value="other" @selected(old('topic') === 'other')>Otro motivo</option>
                                </select>
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="order_number" class="label-form">N° de pedido (opcional)</label>
                            <div class="input-icon-container">
                                <i class="ri-file-list-3-line input-icon"></i>
                                <input type="text" id="order_number" name="order_number" class="input-form"
                                    placeholder="Si aplica, ingresa tu número de pedido"
                                    value="{{ old('order_number') }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-row-fit">
                        <div class="input-group">
                            <label for="message" class="label-form">Mensaje<i
                                    class="ri-asterisk text-accent"></i></label>
                            <div class="input-icon-container input-icon-container--textarea">
                                <i class="ri-chat-3-line input-icon"></i>
                                <textarea id="message" name="message" class="input-form textarea-autosize" rows="4"
                                    placeholder="Cuéntanos qué necesitas o qué problema estás teniendo" data-validate="required|min:10">{{ old('message') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="contact-form-footer">
                        <p class="contact-form-footer__note">
                            <i class="ri-shield-check-line"></i>
                            Usaremos tu información solo para responder esta consulta. Para más detalles revisa nuestra
                            <a href="{{ route('site.legal.privacy') }}" target="_blank">Política de Privacidad</a>.
                        </p>
                        <button type="submit" class="boton-form boton-accent" data-submit-loader>
                            <span class="boton-form-icon"><i class="ri-send-plane-line"></i></span>
                            <span class="boton-form-text">Enviar mensaje</span>
                        </button>
                    </div>
                </div>
            </form>

            <aside class="contact-info">
                <div class="contact-info__card">
                    <h3>Otras formas de contacto</h3>
                    <ul class="contact-info__list">
                        <li><i class="ri-map-pin-line"></i>
                            {{ $companySettings?->address ?? 'Dirección disponible en tu comprobante de pago' }}</li>
                        <li><i class="ri-time-line"></i> Horario de atención: Lunes a Viernes, 9:00 a 18:00</li>
                    </ul>
                </div>
                @php
                    $mapsUrl = $companySettings?->google_maps_url ?? null;
                    $isEmbedUrl =
                        $mapsUrl &&
                        (Str::startsWith($mapsUrl, 'https://www.google.com/maps/embed?pb=') ||
                            Str::startsWith($mapsUrl, 'https://maps.google.com/maps/embed?pb='));
                @endphp
                @if ($isEmbedUrl)
                    <div class="contact-info__card mt-4">
                        <h3>Ubicación en el mapa</h3>
                        <div class="rounded overflow-hidden border border-gray-200 shadow-sm"
                            style="min-height:220px;max-width:100%;">
                            <iframe src="{{ $mapsUrl }}" width="100%" height="220"
                                style="border:0;min-width:200px;" allowfullscreen loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>
                @endif
                <div class="contact-info__hint">
                    <p><i class="ri-lightbulb-flash-line"></i> Consejo: mientras más contexto nos des (capturas,
                        enlaces, número de pedido), más rápido podremos ayudarte.</p>
                </div>
            </aside>
        </div>
    </section>

    @push('js')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (window.initFormValidator) {
                    window.initFormValidator('#contact-form', {
                        validateOnBlur: true,
                        scrollToFirstError: true,
                    });
                }
                if (window.initSubmitLoader) {
                    window.initSubmitLoader('#contact-form [data-submit-loader]');
                }
                if (window.initTextareaAutosize) {
                    window.initTextareaAutosize('.textarea-autosize');
                }
            });
        </script>
    @endpush
</x-app-layout>
