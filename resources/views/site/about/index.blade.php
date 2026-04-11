<x-app-layout>
    @section('title', 'Nosotros')

    <section class="about-hero">
        <div class="site-container about-hero__container">
            <div class="about-hero__content">
                <span class="about-hero__chip"><i class="ri-team-line"></i> Conoce a nuestro equipo</span>
                <h1 class="about-hero__title">Una tienda pensada para personas reales</h1>
                <p class="about-hero__subtitle">Combinamos tecnología, diseño y cercanía para que comprar sea simple, seguro y hasta un poco emocionante.</p>
                <div class="about-hero__highlights">
                    <div class="about-hero__highlight">
                        <span class="about-hero__number">24/7</span>
                        <span class="about-hero__label">Tienda siempre disponible</span>
                    </div>
                    <div class="about-hero__highlight">
                        <span class="about-hero__number">+5K</span>
                        <span class="about-hero__label">Pedidos entregados</span>
                    </div>
                    <div class="about-hero__highlight">
                        <span class="about-hero__number">4.9</span>
                        <span class="about-hero__label">Satisfacción promedio</span>
                    </div>
                </div>
            </div>
            <div class="about-hero__media">
                <div class="about-hero__card about-hero__card--gradient">
                    <p class="about-hero__quote">
                        "Creemos en compras sin fricción, con información clara y soporte real cuando más lo necesitas."
                    </p>
                    <p class="about-hero__quote-author">Equipo GeckoMerce</p>
                </div>
                <div class="about-hero__grid">
                    <div class="about-hero__pill">
                        <i class="ri-shield-check-line"></i>
                        Pagos 100% seguros
                    </div>
                    <div class="about-hero__pill">
                        <i class="ri-truck-line"></i>
                        Envíos rápidos y trazables
                    </div>
                    <div class="about-hero__pill">
                        <i class="ri-customer-service-2-line"></i>
                        Soporte cercano
                    </div>
                    <div class="about-hero__pill">
                        <i class="ri-recycle-line"></i>
                        Procesos responsables
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="about-section">
        <div class="site-container about-section__container">
            <div class="about-section__intro">
                <h2 class="about-section__title">Nuestra misión</h2>
                <p class="about-section__text">
                    Hacer que comprar online sea tan simple como conversar con un amigo que conoce exactamente lo que necesitas. Construimos una experiencia que prioriza la confianza, la transparencia y el detalle en cada paso del camino.
                </p>
            </div>
            <div class="about-values">
                <article class="about-value-card">
                    <div class="about-value-card__icon card-primary">
                        <i class="ri-emotion-happy-line"></i>
                    </div>
                    <div class="about-value-card__body">
                        <h3>Personas al centro</h3>
                        <p>Diseñamos pensando en usuarios reales: navegación clara, estados visibles, mensajes útiles y cero sorpresas en el checkout.</p>
                    </div>
                </article>
                <article class="about-value-card">
                    <div class="about-value-card__icon card-success">
                        <i class="ri-magic-line"></i>
                    </div>
                    <div class="about-value-card__body">
                        <h3>Experiencia cuidada</h3>
                        <p>Cuidamos microdetalles: loaders suaves, skeletons, mensajes de error humanos y flujos que se sienten naturales.</p>
                    </div>
                </article>
                <article class="about-value-card">
                    <div class="about-value-card__icon card-warning">
                        <i class="ri-pantone-line"></i>
                    </div>
                    <div class="about-value-card__body">
                        <h3>Marca consistente</h3>
                        <p>Una identidad visual coherente apoyada en nuestra paleta principal y componentes reutilizables para todo el sitio.</p>
                    </div>
                </article>
                <article class="about-value-card">
                    <div class="about-value-card__icon card-info">
                        <i class="ri-line-chart-line"></i>
                    </div>
                    <div class="about-value-card__body">
                        <h3>Mejora continua</h3>
                        <p>Escuchamos feedback, medimos, iteramos y lanzamos mejoras constantes para que cada visita se sienta un poco mejor.</p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section class="about-section about-section--split">
        <div class="site-container about-section__container about-section__container--split">
            <div class="about-story">
                <h2>Cómo trabajamos</h2>
                <p>
                    Detrás de cada producto, banner y mensaje hay un equipo multidisciplinario que combina data, diseño y empatía. Nos aseguramos de que cada parte del recorrido —desde la búsqueda hasta la postventa— esté cuidada.
                </p>
                <ul class="about-list">
                    <li><i class="ri-checkbox-circle-line"></i> Anticipamos dudas con información clara y visible.</li>
                    <li><i class="ri-checkbox-circle-line"></i> Mostramos estados de carga en lugar de pantallas vacías.</li>
                    <li><i class="ri-checkbox-circle-line"></i> Cuidamos los vacíos de contenido con mensajes útiles.</li>
                    <li><i class="ri-checkbox-circle-line"></i> Facilitamos volver atrás sin perder contexto.</li>
                </ul>
            </div>
            <aside class="about-aside">
                <div class="about-aside__card">
                    <p class="about-aside__label">Diseñado con UX real</p>
                    <p class="about-aside__text">Tomamos patrones modernos de producto digital (e-commerce, SaaS y apps) para que la experiencia de compra se sienta familiar desde el primer click.</p>
                </div>
                <div class="about-aside__meta">
                    <div class="about-aside__meta-item">
                        <span class="about-aside__meta-label">Tiempo promedio de compra</span>
                        <span class="about-aside__meta-value">&lt; 5 min</span>
                    </div>
                    <div class="about-aside__meta-item">
                        <span class="about-aside__meta-label">Clientes recurrentes</span>
                        <span class="about-aside__meta-value">+65%</span>
                    </div>
                </div>
            </aside>
        </div>
    </section>

    <section class="about-section about-section--cta">
        <div class="site-container about-cta">
            <div class="about-cta__content">
                <h2>¿Te gustaría trabajar con nosotros?</h2>
                <p>Si tienes una marca, un catálogo o una idea que quieras llevar al siguiente nivel, conversemos. Podemos ayudarte a diseñar una experiencia de compra a la altura de tu producto.</p>
            </div>
            <a href="{{ route('contact.index') }}" class="about-cta__button">
                <i class="ri-send-plane-line"></i>
                Escríbenos
            </a>
        </div>
    </section>
</x-app-layout>
