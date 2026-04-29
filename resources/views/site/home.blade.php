<x-app-layout>
    @section('title', 'Inicio')
    @if ($covers->isNotEmpty())
        <section class="covers-section">
            <div class="swiper covers-slider">
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
                <i class="ri-gallery-line"></i>
                <h2>No hay portadas disponibles</h2>
                <p>Pronto habrá contenido destacado aquí</p>
            </div>
        </section>
    @endif
    @push('js')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // Slider de portadas
                const coversSliderEl = document.querySelector('.covers-slider');
                if (coversSliderEl) {
                    new Swiper('.covers-slider', {
                        modules: [
                            window.SwiperModules.Navigation,
                            window.SwiperModules.Pagination,
                            window.SwiperModules.Autoplay,
                        ],
                        effect: 'slide',
                        loop: true,
                        autoplay: {
                            delay: 5000,
                            disableOnInteraction: false,
                            pauseOnMouseEnter: true,
                        },
                        speed: 400,
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
                        a11y: {
                            prevSlideMessage: 'Portada anterior',
                            nextSlideMessage: 'Siguiente portada',
                            firstSlideMessage: 'Esta es la primera portada',
                            lastSlideMessage: 'Esta es la última portada',
                        },
                    });
                }
            });
        </script>
    @endpush

    <!-- Sección de Categorías -->
    <section class="section-container bg-section">
        <div class="section-header">
            <h2 class="section-title">Categorías populares</h2>
            <p class="section-subtitle">
                Explora nuestras categorías más populares y encuentra lo que buscas
            </p>
        </div>
        <div class="categories-list">
            @foreach ($categories as $category)
                <!-- colocalr imagen css url a la etiqueta "a" y eliminar la etiqueta "img" -->
                <a href="{{ route('categories.show', $category) }}" class="category-card">
                    <div class="category-image">
                        @if ($category->image)
                            <img src="{{ $category->image ? asset('storage/' . $category->image) : asset('images/default-category.png') }}"
                                alt="{{ $category->name }} imagen"onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="product-image-fallback" style="display: none;">
                                <i class="ri-image-fill"></i>
                                <span>Imagen no disponible</span>
                            </div>
                        @endif
                    </div>
                    <div class="category-name">{{ $category->name }}</div>
                </a>
            @endforeach
        </div>
    </section>

    <!-- Sección de Últimos Productos con Livewire -->
    <livewire:site.product-list :limit="8" title="Últimos Productos"
        subtitle="Descubre nuestras incorporaciones más recientes" />
    <!-- Sección de Últimos productos de la categoria vestidos -->
    <livewire:site.product-list :limit="8" title="Últimos Vestidos"
        subtitle="Explora los vestidos más recientes en nuestra colección" scope="strict_category" :categoryId="3" />
    <!-- Sección de Ofertas -->
    <livewire:site.product-list title="Ofertas" subtitle="Aprovecha descuentos" :limit="8" :onSale="true"
        :strict="false" />

    <!-- Sección de Productos más baratos -->
    <livewire:site.product-list :limit="8" order-by="cheap" title="¡Los más baratos!"
        subtitle="Aprovecha los mejores precios" />

    @include('partials.site.why-us')

</x-app-layout>
