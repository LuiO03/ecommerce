<!-- Sección de Categorías -->
<section class="section-container bg-section pb-0">
    <div class="section-header">
        <h2 class="section-title">Categorías populares</h2>
        <p class="section-subtitle">
            Explora nuestras categorías más populares y encuentra lo que buscas
        </p>
    </div>
    <div class="categories-slider swiper">
        <div class="swiper-wrapper">
            @foreach ($categories as $category)
                <div class="swiper-slide categories-slide">
                    <!-- colocalr imagen css url a la etiqueta "a" y eliminar la etiqueta "img" -->
                    <a href="{{ route('categories.show', $category) }}" class="category-card">
                        <div class="category-image">
                            @if ($category->image)
                                <img src="{{ $category->image ? asset('storage/' . $category->image) : asset('images/default-category.png') }}"
                                    alt="{{ $category->name }} imagen"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="product-image-fallback" style="display: none;">
                                    <i class="ri-image-fill"></i>
                                    <span>Imagen no disponible</span>
                                </div>
                            @endif
                        </div>
                        <div class="category-name">{{ $category->name }}</div>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>
        <div class="swiper-pagination"></div>
    </div>
</section>

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const categoriesSliderEl = document.querySelector('.categories-slider');

            if (categoriesSliderEl) {
                new Swiper('.categories-slider', {
                    modules: [
                        window.SwiperModules.Navigation,
                        window.SwiperModules.Pagination,
                        window.SwiperModules.Autoplay,
                    ],
                    loop: true,
                    autoplay: {
                        delay: 5500,
                        disableOnInteraction: true,
                        pauseOnMouseEnter: true,
                    },
                    speed: 400,
                    navigation: {
                        nextEl: '.categories-slider .swiper-button-next',
                        prevEl: '.categories-slider .swiper-button-prev',
                    },
                    pagination: {
                        el: '.categories-slider .swiper-pagination',
                        clickable: true,
                        dynamicBullets: false,
                        dynamicMainBullets: 3,
                    },
                    breakpoints: {
                        320: {
                            slidesPerView: 3,
                            spaceBetween: 3,
                        },
                        640: {
                            slidesPerView: 4,
                            spaceBetween: 5,
                        },
                        800: {
                            slidesPerView: 5,
                            spaceBetween: 8,
                        },
                        1024: {
                            slidesPerView: 6,
                            spaceBetween: 16,
                        },
                        1280: {
                            slidesPerView: 8,
                            spaceBetween: 16,
                        },
                    },
                    keyboard: {
                        enabled: true,
                    },
                    a11y: {
                        prevSlideMessage: 'Categoría anterior',
                        nextSlideMessage: 'Siguiente categoría',
                    },
                });
            }
        });
    </script>
@endpush
