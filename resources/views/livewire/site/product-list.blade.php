@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const productsSliders = document.querySelectorAll('.products-slider');

            if (!productsSliders.length) return;

            productsSliders.forEach((sliderEl) => {

                const swiper = new Swiper(sliderEl, {

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

                    speed: 400,

                    navigation: {
                        nextEl: sliderEl.querySelector('.swiper-button-next'),
                        prevEl: sliderEl.querySelector('.swiper-button-prev'),
                    },

                    pagination: {
                        el: sliderEl.querySelector('.swiper-pagination'),
                        clickable: true,
                        dynamicBullets: false,
                        dynamicMainBullets: 3,
                    },

                    breakpoints: {
                        320: {
                            slidesPerView: 2,
                            slidesPerGroup: 2,
                            spaceBetween: 3,
                        },

                        640: {
                            slidesPerView: 3,
                            slidesPerGroup: 3,
                            spaceBetween: 5,
                        },

                        800: {
                            slidesPerView: 4,
                            slidesPerGroup: 4,
                            spaceBetween: 8,
                        },

                        1024: {
                            slidesPerView: 5,
                            slidesPerGroup: 5,
                            spaceBetween: 16,
                        },

                        1280: {
                            slidesPerView: 6,
                            slidesPerGroup: 6,
                            spaceBetween: 16,
                        },
                    },

                    keyboard: {
                        enabled: true,
                    },

                    a11y: {
                        prevSlideMessage: 'Producto anterior',
                        nextSlideMessage: 'Siguiente producto',
                    },

                    on: {
                        init() {
                            sliderEl.classList.remove('is-loading');
                        }
                    }
                });

                swiper.init();

            });

        });
    </script>
@endpush

<section class="section-container pb-0">
    <div class="section-header-conteiner">
        <div class="section-header">
            <h2 class="section-title">{{ $title }}</h2>

            <p class="section-subtitle">
                {{ $subtitle }}
            </p>
        </div>
        <a href="{{ route('site.shop.index') }}" class="site-btn site-btn-outline">
            <i class="ri-arrow-right-line site-btn-icon"></i>
            Ver todo
        </a>
    </div>
    @if ($products->isNotEmpty())
        <div class="swiper products-slider is-loading">
            <!-- Skeleton -->
            <div class="products-skeleton">
                @for ($i = 0; $i < 6; $i++)
                    <div class="product-skeleton-item ">
                        <div class="product-skeleton-image"></div>
                        <div class="product-skeleton-content">
                            <div class="flex justify-between">
                                <div class="product-skeleton-line sm"></div>
                                <div class="product-skeleton-line xs"></div>
                            </div>
                            <div class="flex gap-2">
                                <div class="product-skeleton-line circle"></div>
                                <div class="product-skeleton-line circle"></div>
                                <div class="product-skeleton-line circle"></div>
                            </div>

                            <div class="product-skeleton-line"></div>
                            <div class="flex justify-between">
                                <div class="product-skeleton-line price"></div>
                                <div class="product-skeleton-line sm"></div>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>
            <div class="swiper-wrapper">
                @foreach ($products as $product)
                    <div class="swiper-slide products-slide">
                        @include('partials.site.product-card', ['product' => $product])
                    </div>
                @endforeach
            </div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
            <div class="swiper-pagination"></div>
        </div>
    @else
        <div class="no-products">
            <i class="ri-box-3-line"></i>
            <p>No hay productos disponibles en este momento</p>
        </div>
    @endif
</section>
