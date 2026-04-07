<x-app-layout>
    @section('title', 'Política de Privacidad')

    <section class="legal-hero legal-hero--privacy">
        <div class="site-container">
            <div class="legal-hero__content">
                <span class="legal-hero__chip"><i class="ri-lock-line"></i> Protección de datos</span>
                <h1 class="legal-hero__title">Política de Privacidad</h1>
                <p class="legal-hero__subtitle">Descubre cómo protegemos tu información personal y cómo puedes gestionar tus datos dentro de GeckoMerce.</p>
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
                    <a href="{{ route('site.legal.privacy') }}" class="legal-sidebar__link legal-sidebar__link--active">
                        <i class="ri-shield-keyhole-line"></i>
                        <span>Política de Privacidad</span>
                    </a>
                    <a href="{{ route('site.legal.claims') }}" class="legal-sidebar__link">
                        <i class="ri-file-damage-line"></i>
                        <span>Libro de Reclamaciones</span>
                    </a>
                </nav>

                <div class="legal-sidebar__card">
                    <p class="legal-sidebar__label">Controla tus datos</p>
                    <p class="legal-sidebar__text">Puedes ejercer tus derechos de acceso, rectificación o eliminación escribiéndonos a <strong>{{ $companySettings->support_email ?? $companySettings->email }}</strong>.</p>
                </div>
            </aside>

            <article class="legal-article legal-article--privacy">
                <div class="legal-meta">
                    <span class="legal-meta__item"><i class="ri-bank-line"></i> {{ $companySettings->legal_name }}</span>
                    @if($companySettings->ruc)
                        <span class="legal-meta__item"><i class="ri-id-card-line"></i> RUC {{ $companySettings->ruc }}</span>
                    @endif
                </div>

                <div class="legal-article__content legal-article__content--wysiwyg">
                    {!! $content !!}
                </div>

                <div class="legal-cta legal-cta--subtle">
                    <div class="legal-cta__content">
                        <h2>¿Quieres actualizar tus preferencias?</h2>
                        <p>Puedes modificar tus datos de contacto y preferencias de comunicación desde tu cuenta o contactando con nuestro equipo.</p>
                    </div>
                    <a href="mailto:{{ $companySettings->support_email ?? $companySettings->email }}" class="legal-cta__button">
                        <i class="ri-mail-line"></i>
                        Gestionar mis datos
                    </a>
                </div>
            </article>
        </div>
    </section>
</x-app-layout>
