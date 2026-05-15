<x-app-layout>
    @section('title', 'Inicio')
    @if ($covers->isNotEmpty())
        <section class="covers-section">
            <div class="covers-slider swiper is-loading">

                <!-- Skeleton -->
                <div class="covers-skeleton">
                    @for ($i = 0; $i < 1; $i++)
                        <div class="cover-skeleton shimmer"></div>
                    @endfor
                </div>
                <div class="swiper-wrapper">
                    @foreach ($covers as $cover)
                        <div class="swiper-slide">
                            <div class="cover-item"
                                style="background-image: url('{{ asset('storage/' . $cover->image_path) }}');">
                                <!-- Overlay transparente degradado según posición -->
                                @if ($cover->overlay_bg_enabled)
                                    <div class="cover-bg-overlay position-{{ $cover->text_position }}"
                                        style="--overlay-bg-opacity: {{ $cover->overlay_bg_opacity ?? 0.35 }};">
                                    </div>
                                @endif

                                <!-- Container con límite de ancho -->
                                <div class="cover-container">
                                    <!-- Contenido de texto -->
                                    <div class="cover-content position-{{ $cover->text_position }}"
                                        style="color: {{ $cover->text_color }};">
                                        @if ($cover->overlay_text)
                                            <h1 class="cover-title">{{ $cover->overlay_text }}</h1>
                                        @endif

                                        @if ($cover->overlay_subtext)
                                            <p class="cover-subtitle">{{ $cover->overlay_subtext }}</p>
                                        @endif

                                        @if ($cover->button_text && $cover->button_link)
                                            <a href="{{ $cover->button_link }}"
                                                class="cover-btn btn-{{ $cover->button_style }}">
                                                {{ $cover->button_text }}
                                            </a>
                                        @endif
                                        @can('portadas.edit')
                                            <a href="{{ route('admin.covers.edit', $cover) }}"
                                                class="site-select-trigger filter-toggle-btn" target="_blank">
                                                <i class="ri-pencil-fill"></i>Editar Portada
                                            </a>
                                        @endcan
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
                <i class="ri-gallery-fill no-covers-icon"></i>
                <h2>No hay portadas disponibles</h2>
                <p>Pronto habrá contenido destacado aquí</p>
                @can('portadas.create')
                    <a href="{{ route('admin.covers.create') }}" class="site-select-trigger filter-toggle-btn">
                        <i class="ri-add-line"></i>Crear Portada
                    </a>
                @endcan
            </div>
        </section>
    @endif
    @push('js')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // Slider de portadas
                const coversSliderEl = document.querySelector('.covers-slider');
                if (!coversSliderEl) return;
                const swiper = new Swiper('.covers-slider', {
                    modules: [
                        window.SwiperModules.Navigation,
                        window.SwiperModules.Pagination,
                        window.SwiperModules.Autoplay,
                        window.SwiperModules.EffectFade,
                    ],

                    effect: 'fade',

                    fadeEffect: {
                        crossFade: true,
                    },

                    loop: true,

                    speed: 350,

                    preventInteractionOnTransition: false,

                    grabCursor: true,

                    simulateTouch: true,
                    followFinger: true,

                    autoplay: {
                        delay: 5000,
                        disableOnInteraction: false,
                        pauseOnMouseEnter: true,
                    },

                    navigation: {
                        nextEl: '.covers-slider .swiper-button-next',
                        prevEl: '.covers-slider .swiper-button-prev',
                    },

                    pagination: {
                        el: '.covers-slider .swiper-pagination',
                        clickable: true,
                        dynamicBullets: true,
                    },

                    keyboard: {
                        enabled: true,
                    },

                    on: {
                        init() {
                            coversSliderEl.classList.remove('is-loading');
                        }
                    }
                });
            });
        </script>
    @endpush

    <livewire:site.category-list custom-class="bg-section" section-title="Categorias populares"
        section-subtitle="Explora nuestras categorias mas populares y encuentra lo que buscas" :limit="12" />

    <!-- Sección de Últimos Productos con Livewire -->
    <livewire:site.product-list :limit="12" title="Últimos Productos" subtitle="Descubre nuestras novedades" />
    <!-- Sección de Ofertas -->
    <livewire:site.product-list custom-class="bg-section" title="Ofertas" subtitle="Aprovecha descuentos"
        :limit="12" :onSale="true" :strict="false" />

    <livewire:site.product-list title="Los más vendidos" subtitle="Los productos más comprados" :limit="12"
        orderBy="best_selling" />

    <livewire:site.product-list custom-class="bg-section" title="Productos destacados"
        subtitle="Nuestros productos recomendados" :limit="12" orderBy="featured" />

    <!-- Sección de Productos más baratos -->
    <livewire:site.product-list :limit="12" order-by="cheap" title="¡Los más baratos!"
        subtitle="Aprovecha los mejores precios" />

    @include('partials.site.why-us')

</x-app-layout>
