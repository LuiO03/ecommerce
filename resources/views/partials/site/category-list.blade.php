<!-- Sección de Categorías -->
@php $categoriesListId = 'categories-' . uniqid(); @endphp
<section class="categories-section is-loading" data-categories-list data-categories-list-id="{{ $categoriesListId }}">
    @push('js')
        <script>
            (() => {
                const categoriesListId = @js($categoriesListId);

                const initCategoriesSlider = () => {
                    const categoriesListRoot = document.querySelector(
                        `[data-categories-list-id="${categoriesListId}"]`);

                    if (!categoriesListRoot || categoriesListRoot.dataset.categoriesInitialized === 'true') {
                        return;
                    }

                    categoriesListRoot.dataset.categoriesInitialized = 'true';

                    const sliderEl = categoriesListRoot.querySelector('.categories-slider');
                    const loadingStartedAt = Date.now();
                    const minimumLoadingTime = 600;

                    const markReady = () => {
                        const elapsed = Date.now() - loadingStartedAt;
                        const remaining = Math.max(minimumLoadingTime - elapsed, 0);

                        window.setTimeout(() => {
                            categoriesListRoot.classList.remove('is-loading');
                            categoriesListRoot.classList.add('is-ready');
                        }, remaining);
                    };

                    if (sliderEl) {
                        new Swiper(sliderEl, {
                            modules: [
                                window.SwiperModules.Navigation,
                                window.SwiperModules.Pagination,
                                window.SwiperModules.Autoplay,
                            ],
                            centerInsufficientSlides: true,
                            loop: false,
                            speed: 1400,
                            navigation: {
                                nextEl: categoriesListRoot.querySelector('.swiper-button-next'),
                                prevEl: categoriesListRoot.querySelector('.swiper-button-prev'),
                            },
                            pagination: {
                                el: categoriesListRoot.querySelector('.swiper-pagination'),
                                clickable: true,
                            },
                            breakpoints: {
                                320: {
                                    slidesPerView: 3,
                                    spaceBetween: 3
                                },
                                640: {
                                    slidesPerView: 4,
                                    spaceBetween: 5
                                },
                                800: {
                                    slidesPerView: 5,
                                    spaceBetween: 8
                                },
                                1024: {
                                    slidesPerView: 6,
                                    spaceBetween: 16
                                },
                                1280: {
                                    slidesPerView: 8,
                                    spaceBetween: 16
                                },
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
                    document.addEventListener('DOMContentLoaded', initCategoriesSlider, {
                        once: true
                    });
                } else {
                    initCategoriesSlider();
                }
            })();
        </script>
    @endpush

    <div class="section-header">
        <h2 class="section-title">Categorías populares</h2>
        <p class="section-subtitle">
            Explora nuestras categorías más populares y encuentra lo que buscas
        </p>
    </div>

    <div class="categories-slider-wrapper">

        <div class="categories-slider-skeleton" aria-hidden="true" data-skeleton-container>
            @for ($i = 0; $i < 8; $i++)
                <div class="category-card category-card-skeleton">
                    <div class="category-image skeleton-block shimmer"></div>
                </div>
            @endfor
        </div>
        <div class="categories-slider swiper">
            <div class="swiper-wrapper">
                @foreach ($categories as $category)
                    <div class="swiper-slide categories-slide">
                        <a href="{{ route('categories.show', $category) }}" class="category-card">
                            <div class="category-image">
                                @if ($category->image)
                                    <img src="{{ $category->image ? asset('storage/' . $category->image) : asset('images/default-category.png') }}"
                                        alt="{{ $category->name }} imagen"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="product-card-image-fallback" style="display: none;">
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
    </div>
</section>
