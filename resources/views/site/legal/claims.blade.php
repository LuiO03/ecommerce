<x-app-layout>
    @section('title', 'Libro de Reclamaciones')

    <section class="legal-hero legal-hero--claims">
        <div class="site-container">
            <div class="legal-hero__content">
                <span class="legal-hero__chip"><i class="ri-feedback-line"></i> Queremos escucharte</span>
                <h1 class="legal-hero__title">Libro de Reclamaciones</h1>
                <p class="legal-hero__subtitle">Ponemos a tu disposición un canal claro y ordenado para registrar
                    reclamos y quejas conforme a la normativa vigente.</p>
            </div>
        </div>
    </section>

    <section class="legal-content">
        <div class="site-container legal-content__container">
            <aside class="legal-sidebar">
                <h2 class="legal-sidebar__title">Documentación legal</h2>
                <nav class="legal-sidebar__nav">
                    <a href="{{ route('site.legal.terms') }}" class="legal-sidebar__link">
                        <i class="ri-file-list-3-line"></i>
                        <span>Términos y Condiciones</span>
                    </a>
                    <a href="{{ route('site.legal.privacy') }}" class="legal-sidebar__link">
                        <i class="ri-shield-keyhole-line"></i>
                        <span>Política de Privacidad</span>
                    </a>
                    <a href="{{ route('site.legal.claims') }}" class="legal-sidebar__link legal-sidebar__link--active">
                        <i class="ri-file-damage-line"></i>
                        <span>Libro de Reclamaciones</span>
                    </a>
                </nav>

                <div class="legal-sidebar__card">
                    <p class="legal-sidebar__label">Atención al cliente</p>
                    <p class="legal-sidebar__text">Si tuviste un inconveniente con tu compra, estamos listos para
                        ayudarte a resolverlo de forma rápida y justa.</p>
                </div>
            </aside>

            <article class="legal-article legal-article--claims">
                <div class="legal-meta">
                    <span class="legal-meta__item"><i class="ri-bank-line"></i>
                        {{ $companySettings->legal_name }}</span>
                    @if ($companySettings->ruc)
                        <span class="legal-meta__item"><i class="ri-id-card-line"></i> RUC
                            {{ $companySettings->ruc }}</span>
                    @endif
                </div>

                <div class="legal-article__content legal-article__content--wysiwyg">
                    {!! $content !!}
                </div>

                <div class="legal-cta legal-cta--emphasis">
                    <div class="legal-cta__content">
                        <h2>Registrar un reclamo</h2>
                        <p>Puedes completar el formulario de Libro de Reclamaciones virtual para que nuestro equipo
                            revise tu caso y te responda dentro de los plazos establecidos.</p>
                    </div>
                    <a href="#form-libro-reclamaciones" class="legal-cta__button">
                        <i class="ri-arrow-down-circle-line"></i>
                        Ir al formulario
                    </a>
                </div>

                <section id="form-libro-reclamaciones" class="legal-form">
                    <h2 class="legal-form__title">Formulario de Libro de Reclamaciones</h2>
                    <p class="legal-form__subtitle">Completa los datos solicitados para registrar tu reclamo o queja. Te
                        contactaremos a través de los datos que nos proporciones.</p>

                    <form id="claims-form" class="legal-form__grid" action="#" method="POST" autocomplete="off">
                        @csrf
                        <div class="form-row-fit">
                            <!-- === Nombre === -->
                            <div class="input-group">
                                <label for="name" class="label-form">
                                    Nombre completo<i class="ri-asterisk text-accent"></i>
                                </label>
                                <div class="input-icon-container">
                                    <i class="ri-user-line input-icon"></i>
                                    <input id="claim-name" type="text" name="name" class="input-form"
                                        placeholder="Tu nombre y apellidos" autocomplete="off"
                                        data-validate="required|alpha|min:3|max:50" required>
                                </div>
                            </div>
                            <!-- === Correo === -->
                            <div class="input-group">
                                <label for="email" class="label-form">
                                    Correo electrónico <i class="ri-asterisk text-accent"></i>
                                </label>
                                <div class="input-icon-container">
                                    <i class="ri-mail-line input-icon"></i>
                                    <input type="email" id="email" name="email" class="input-form"
                                        placeholder="Ingresa tu correo electrónico"
                                        required autocomplete="off" data-validate="required|email">
                                </div>
                            </div>
                        </div>

                        <div class="form-row-fit">
                            <!-- === Teléfono === -->
                            <div class="input-group">
                                <label for="phone" class="label-form">Teléfono de contacto</label>
                                <div class="input-icon-container">
                                    <i class="ri-phone-line input-icon"></i>
                                    <input type="text" name="phone" id="claim-phone" class="input-form"
                                        placeholder="Número de contacto" data-validate="phone">
                                </div>
                            </div>
                            <!-- === Tipo de reclamo o queja === -->
                            <div class="input-group">
                                <label for="claim-type" class="label-form">
                                    Tipo de registro <i class="ri-asterisk text-accent"></i>
                                </label>
                                <div class="input-icon-container">
                                    <i class="ri-id-card-line input-icon"></i>
                                    <select id="claim-type" name="claim_type" class="select-form"
                                        data-validate="required|selected">
                                        <option value="">Selecciona una opción</option>
                                        <option value="reclamo">
                                            Reclamo (disconformidad relacionada al producto o
                                            servicio)
                                        </option>
                                        <option value="queja">
                                            Queja (malestar o descontento no relacionado al producto o servicio)
                                        </option>
                                    </select>
                                    <i class="ri-arrow-down-s-line select-arrow"></i>
                                </div>
                            </div>
                        </div>
                        <div class="form-row-fit">
                            <div class="input-group">
                                <label for="claim-detail" class="label-form label-textarea">
                                    Detalle del reclamo o queja <i class="ri-asterisk text-accent"></i>
                                </label>

                                <div class="input-icon-container">
                                    <textarea name="claim-detail" id="claim-detail" class="textarea-form"
                                        placeholder="Describe lo ocurrido de la forma más clara posible" rows="4" data-validate="min:10|max:250"></textarea>

                                    <i class="ri-file-text-line input-icon"></i>
                                </div>
                            </div>
                        </div>

                        <div class="form-footer-static">
                            <button class="boton-form boton-dark" type="submit" data-submit-loader>
                                <span class="boton-form-icon">
                                    <i class="ri-send-plane-fill"></i>
                                </span>
                                <span class="boton-form-text">Enviar registro</span>
                            </button>
                        </div>
                        <p class="legal-form__hint">Al enviar este formulario declaras que la información
                            proporcionada es verdadera y corresponde a una experiencia real.</p>
                    </form>
                </section>
            </article>
        </div>
    </section>

    @push('js')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (window.initFormValidator) {
                    window.initFormValidator('#claims-form', {
                        validateOnBlur: true,
                        scrollToFirstError: true,
                    });
                }

                if (window.initSubmitLoader) {
                    window.initSubmitLoader('#claims-form [data-submit-loader]');
                }
            });
        </script>
    @endpush
</x-app-layout>
