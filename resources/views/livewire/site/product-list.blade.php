<section class="{{ $products->isEmpty() ? 'swiper-products-empty' : 'swiper-products-section is-loading' }}" data-products-list data-products-list-id="{{ $this->getId() }}">
    @push('js')
        <script>
            (() => {
                const productsListId = @js($this->getId());

                const initializeProductsList = () => {
                    const productsListRoot = document.querySelector(`[data-products-list-id="${productsListId}"]`);

                    if (!productsListRoot || productsListRoot.dataset.productsListInitialized === 'true') {
                        return;
                    }

                    productsListRoot.dataset.productsListInitialized = 'true';

                    const productsSliderEl = productsListRoot.querySelector('.products-slider');
                    const loadingStartedAt = Date.now();
                    const minimumLoadingTime = 800;

                    const markReady = () => {
                        const elapsed = Date.now() - loadingStartedAt;
                        const remaining = Math.max(minimumLoadingTime - elapsed, 0);

                        window.setTimeout(() => {
                            productsListRoot.classList.remove('is-loading');
                            productsListRoot.classList.add('is-ready');
                        }, remaining);
                    };

                    if (productsSliderEl) {
                        new Swiper(productsSliderEl, {
                            modules: [
                                window.SwiperModules.Navigation,
                                window.SwiperModules.Pagination,
                                window.SwiperModules.Autoplay,
                            ],
                            centerInsufficientSlides: true,
                            loop: true,
                            autoplay: {
                                delay: 6000,
                                disableOnInteraction: true,
                                pauseOnMouseEnter: true,
                            },
                            speed: 400,
                            navigation: {
                                nextEl: productsListRoot.querySelector('.swiper-button-next'),
                                prevEl: productsListRoot.querySelector('.swiper-button-prev'),
                            },
                            pagination: {
                                el: productsListRoot.querySelector('.swiper-pagination'),
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
                                    markReady();
                                },
                                imagesReady() {
                                    markReady();
                                },
                            },
                        });
                    } else {
                        markReady();
                    }
                };

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initializeProductsList, { once: true });
                } else {
                    initializeProductsList();
                }
            })();
        </script>
    @endpush

    <div class="section-conteiner">
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
        <div class="swiper products-slider">
            <div class="products-slider-skeleton" aria-hidden="true" data-skeleton-container>
                @for ($i = 0; $i < 6; $i++)
                    <article class="product-card product-card-skeleton">
                        <div class="product-image skeleton-block shimmer"></div>
                        <div class="product-card-details">
                            <div class="skeleton-row">
                                <span class="skeleton-chip shimmer"></span>
                                <span class="skeleton-chip shimmer"></span>
                            </div>
                            <div class="skeleton-line shimmer"></div>
                            <div class="skeleton-line skeleton-line-sm shimmer"></div>
                            <div class="product-card-pricing skeleton-pricing">
                                <span class="skeleton-price shimmer"></span>
                                <span class="skeleton-price skeleton-price-sm shimmer"></span>
                            </div>
                            <div class="skeleton-button shimmer"></div>
                        </div>
                    </article>
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
