<x-app-layout>
    @section('title', 'Términos y Condiciones')

    <section class="legal-hero legal-hero--terms">
        <div class="site-container">
            <div class="legal-hero__content">
                <span class="legal-hero__chip"><i class="ri-shield-check-line"></i> Marco legal de la tienda</span>
                <h1 class="legal-hero__title">Términos y Condiciones de Uso</h1>
                <p class="legal-hero__subtitle">Conoce las reglas claras de uso de GeckoMerce para que siempre tengas una experiencia de compra transparente y segura.</p>
            </div>
        </div>
    </section>

    <section class="legal-content">
        <div class="site-container legal-content__container">
            <aside class="legal-sidebar">
                <h2 class="legal-sidebar__title">Documentación legal</h2>
                <nav class="legal-sidebar__nav">
                    <a href="{{ route('site.legal.terms') }}" class="legal-sidebar__link legal-sidebar__link--active">
                        <i class="ri-file-list-3-line"></i>
                        <span>Términos y Condiciones</span>
                    </a>
                    <a href="{{ route('site.legal.privacy') }}" class="legal-sidebar__link">
                        <i class="ri-shield-keyhole-line"></i>
                        <span>Política de Privacidad</span>
                    </a>
                    <a href="{{ route('site.legal.claims') }}" class="legal-sidebar__link">
                        <i class="ri-file-damage-line"></i>
                        <span>Libro de Reclamaciones</span>
                    </a>
                </nav>

                <div class="legal-sidebar__card">
                    <p class="legal-sidebar__label">¿Necesitas ayuda?</p>
                    <p class="legal-sidebar__text">Escríbenos a <strong>{{ $companySettings->support_email ?? $companySettings->email }}</strong> o llámanos al <strong>{{ $companySettings->support_phone ?? $companySettings->phone }}</strong>.</p>
                </div>
            </aside>

            <article class="legal-article legal-article--terms">
                <div class="legal-meta">
                    <span class="legal-meta__item"><i class="ri-bank-line"></i> {{ $companySettings->legal_name }}</span>
                    @if($companySettings->ruc)
                        <span class="legal-meta__item"><i class="ri-id-card-line"></i> RUC {{ $companySettings->ruc }}</span>
                    @endif
                </div>

                <div class="legal-article__content legal-article__content--wysiwyg">
                    {!! $content !!}
                </div>

                <div class="legal-cta">
                    <div class="legal-cta__content">
                        <h2>Te acompañamos en todo el proceso</h2>
                        <p>Si tienes dudas sobre estos términos, nuestro equipo de soporte puede ayudarte a interpretarlos y aplicarlos a tus compras.</p>
                    </div>
                    <a href="mailto:{{ $companySettings->support_email ?? $companySettings->email }}" class="legal-cta__button">
                        <i class="ri-customer-service-2-line"></i>
                        Contactar soporte
                    </a>
                </div>
            </article>
        </div>
    </section>
</x-app-layout>
