<x-app-layout>
    @if ($covers->isNotEmpty())
        <section class="covers-section">
            <div class="swiper covers-slider">
                <div class="swiper-wrapper">
                    @foreach ($covers as $cover)
                        <div class="swiper-slide">
                            <div class="cover-item"
                                style="background-image: url('{{ asset('storage/' . $cover->image_path) }}'); background-attachment: fixed;">
                                <!-- Overlay oscuro si está habilitado -->
                                @if ($cover->overlay_bg_enabled)
                                    <div class="cover-overlay"
                                        style="background: rgba(0, 0, 0, {{ $cover->overlay_bg_opacity }})"></div>
                                @endif

                                <!-- Container con límite de ancho -->
                                <div class="cover-container">
                                    <!-- Contenido de texto -->
                                    <div class="cover-content position-{{ $cover->text_position }}"
                                        style="color: {{ $cover->text_color }}">
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

    <!-- Sección de Últimos Productos -->
    <section class="latest-products-section">
        <div class="latest-products-container">
            <div class="latest-products-header">
                <h2 class="latest-products-title">Últimos Productos</h2>
                <p class="latest-products-subtitle">Descubre nuestras incorporaciones más recientes</p>
            </div>

            @if ($lastProducts->isNotEmpty())
                <div class="swiper products-slider">
                    <div class="swiper-wrapper">
                        @foreach ($lastProducts as $product)
                            <div class="swiper-slide products-slide">
                                <div class="product-card">
                                    <!-- Imagen Principal -->
                                    <div class="product-image">
                                        @if ($product->mainImage)
                                            <img src="{{ asset('storage/' . $product->mainImage->path) }}"
                                                alt="{{ $product->mainImage->alt ?? $product->name }}"
                                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="product-image-fallback" style="display: none;">
                                                <i class="ri-image-line"></i>
                                                <span>Imagen no disponible</span>
                                            </div>
                                        @elseif ($product->image_path)
                                            <img src="{{ asset('storage/' . $product->image_path) }}"
                                                alt="{{ $product->name }}"
                                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="product-image-fallback" style="display: none;">
                                                <i class="ri-image-line"></i>
                                                <span>Imagen no disponible</span>
                                            </div>
                                        @else
                                            <div class="product-image-fallback">
                                                <i class="ri-image-line"></i>
                                                <span>Imagen no disponible</span>
                                            </div>
                                        @endif

                                        @if ($product->discount)
                                            <span class="product-badge">-{{ $product->discount }}%</span>
                                        @endif
                                    </div>

                                    <!-- Contenido -->
                                    <div class="product-content">
                                        <p class="product-brand">{{ $product->category?->name ?? 'Sin categoría' }}</p>
                                        <h3 class="product-name">{{ $product->name }}</h3>

                                        <div class="flex w-full justify-between">
                                            <!-- Precio -->
                                            <div>
                                                <span
                                                    class="product-price">S/.{{ number_format($product->price, 2) }}</span>
                                                @if ($product->original_price)
                                                    <span
                                                        class="product-price-original">S/.{{ number_format($product->original_price, 2) }}</span>
                                                @endif
                                            </div>

                                            <!-- Rating -->
                                            <p class="product-rating">
                                                <i class="ri-star-fill"></i>
                                                <span>4.5 (128 reseñas)</span>
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Botones -->
                                    <div class="product-footer">
                                        <button class="product-btn" aria-label="Agregar a favoritos"
                                            title="Agregar a favoritos">
                                            <i class="ri-heart-line"></i>
                                        </button>
                                        <a href="#" class="product-btn product-btn-primary"
                                            aria-label="Ver detalles del producto">
                                            <i class="ri-eye-line"></i>
                                            <span>Ver</span>
                                        </a>
                                        <button class="product-btn" aria-label="Agregar al carrito"
                                            title="Agregar al carrito">
                                            <i class="ri-shopping-cart-2-line"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Navegación -->
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-button-next"></div>

                    <!-- Paginación -->
                    <div class="swiper-pagination"></div>
                </div>
            @else
                <div class="no-products">
                    <i class="ri-box-3-line"></i>
                    <p>No hay productos disponibles en este momento</p>
                </div>
            @endif
        </div>
    </section>

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
                }

                // Slider de productos
                const productsSliderEl = document.querySelector('.products-slider');
                if (productsSliderEl) {
                    new Swiper('.products-slider', {
                        modules: [
                            window.SwiperModules.Navigation,
                            window.SwiperModules.Pagination,
                            window.SwiperModules.Autoplay,
                        ],
                        loop: true,
                        autoplay: {
                            delay: 6000,
                            disableOnInteraction: true,
                            pauseOnMouseEnter: true,
                        },
                        speed: 600,
                        navigation: {
                            nextEl: '.products-slider .swiper-button-next',
                            prevEl: '.products-slider .swiper-button-prev',
                        },
                        pagination: {
                            el: '.products-slider .swiper-pagination',
                            clickable: true,
                            dynamicBullets: true,
                        },
                        breakpoints: {
                            320: {
                                slidesPerView: 1,
                                spaceBetween: 15,
                            },
                            640: {
                                slidesPerView: 2,
                                spaceBetween: 15,
                            },
                            1024: {
                                slidesPerView: 3,
                                spaceBetween: 20,
                            },
                            1280: {
                                slidesPerView: 4,
                                spaceBetween: 20,
                            },
                        },
                        keyboard: {
                            enabled: true,
                        },
                        a11y: {
                            prevSlideMessage: 'Producto anterior',
                            nextSlideMessage: 'Siguiente producto',
                        },
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>
