<x-app-layout>
    @if($covers->isNotEmpty())
    <section class="covers-section">
        <div class="swiper covers-slider">
            <div class="swiper-wrapper">
                @foreach($covers as $cover)
                <div class="swiper-slide">
                    <div class="cover-item" style="background-image: url('{{ asset('storage/' . $cover->image_path) }}')">
                        <!-- Overlay oscuro si está habilitado -->
                        @if($cover->overlay_bg_enabled)
                        <div class="cover-overlay" style="background: rgba(0, 0, 0, {{ $cover->overlay_bg_opacity }})"></div>
                        @endif

                        <!-- Container con límite de ancho -->
                        <div class="cover-container">
                            <!-- Contenido de texto -->
                            <div class="cover-content position-{{ $cover->text_position }}" style="color: {{ $cover->text_color }}">
                            @if($cover->overlay_text)
                            <h1 class="cover-title">{{ $cover->overlay_text }}</h1>
                            @endif

                            @if($cover->overlay_subtext)
                            <p class="cover-subtitle">{{ $cover->overlay_subtext }}</p>
                            @endif

                            @if($cover->button_text && $cover->button_link)
                            <a href="{{ $cover->button_link }}" class="cover-btn btn-{{ $cover->button_style }}">
                                {{ $cover->button_text }}
                            </a>
                            @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Navegación -->
            <div class="swiper-button-prev">
            </div>
            <div class="swiper-button-next">
            </div>

            <!-- Paginación -->
            <div class="swiper-pagination"></div>
        </div>
    </section>
    @else
    <section class="no-covers">
        <div class="no-covers-content">
            <i class="ri-image-line"></i>
            <h2>No hay portadas disponibles</h2>
            <p>Pronto habrá contenido destacado aquí</p>
        </div>
    </section>
    @endif

    <!-- Sección de contenido adicional -->
    <section class="main-content">
        <div class="container">
            <h2>Bienvenido a GECKOMERCE</h2>
            <p>Tu tienda virtual inteligente en Laravel</p>
        </div>
    </section>

    @push('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const swiperEl = document.querySelector('.covers-slider');
            if (!swiperEl) return;

            new Swiper('.covers-slider', {
                modules: [
                    window.SwiperModules.Navigation,
                    window.SwiperModules.Pagination,
                    window.SwiperModules.Autoplay,
                    window.SwiperModules.EffectFade
                ],
                effect: 'fade',
                fadeEffect: {
                    crossFade: true
                },
                loop: true,
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                },
                speed: 800,
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                    dynamicBullets: true,
                },
                keyboard: {
                    enabled: true,
                },
                a11y: {
                    prevSlideMessage: 'Portada anterior',
                    nextSlideMessage: 'Siguiente portada',
                    firstSlideMessage: 'Esta es la primera portada',
                    lastSlideMessage: 'Esta es la última portada',
                },
            });
        });
    </script>
    @endpush
</x-app-layout>
