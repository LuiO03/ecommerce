<div class="{{ $products->isEmpty() ? 'swiper-products-empty' : 'swiper-products-section' }}">

    <section class="section-container pb-0">
        @push('js')
            <script>
                document.addEventListener('DOMContentLoaded', () => {
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
                            speed: 400,
                            navigation: {
                                nextEl: '.products-slider .swiper-button-next',
                                prevEl: '.products-slider .swiper-button-prev',
                            },
                            pagination: {
                                el: '.products-slider .swiper-pagination',
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
                        });
                    }
                });
            </script>
        @endpush

        <div class="section-header">
            <h2 class="section-title">{{ $title }}</h2>
            <p class="section-subtitle">
                {{ $subtitle }}
            </p>
        </div>

        @if ($products->isNotEmpty())
            <div class="swiper products-slider">
                <div class="swiper-wrapper">
                    @foreach ($products as $product)
                        <div class="swiper-slide products-slide">
                            @include('partials.components.product-card', ['product' => $product])
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
</div>
