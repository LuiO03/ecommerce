<!-- Seccion de Categorias -->
@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const categoriesSliderEl = document.querySelector('.categories-slider');
            if (!categoriesSliderEl) return;
            const swiper = new Swiper(categoriesSliderEl, {
                modules: [
                    window.SwiperModules.Navigation,
                    window.SwiperModules.Pagination,
                    window.SwiperModules.Autoplay,
                ],
                speed: 400,
                centerInsufficientSlides: true,
                grabCursor: true,
                watchOverflow: true,
                centeredSlides: false,
                loop: false,
                autoplay: false,
                navigation: {
                    nextEl: '.categories-slider .swiper-button-next',
                    prevEl: '.categories-slider .swiper-button-prev',
                },
                pagination: {
                    el: '.categories-slider .swiper-pagination',
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
                        categoriesSliderEl.classList.remove('is-loading');
                    }
                }
            });

            swiper.init();

        });
    </script>
@endpush
<div class="section-container pb-0 {{ $customClass }}">
    <div class="section-header-conteiner">
        <div class="section-header">
            <h2 class="section-title">{{ $sectionTitle }}</h2>
            <p class="section-subtitle">
                {{ $sectionSubtitle }}
            </p>
        </div>
    </div>
    <div class="categories-slider swiper is-loading">
        <!-- Skeleton -->
        <div class="categories-skeleton">
            @for ($i = 0; $i < 8; $i++)
                <div class="category-skeleton-item shimmer"></div>
            @endfor
        </div>
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
                                <div class="product-card-image-fallback" style="display: none;">
                                    <i class="ri-image-fill"></i>
                                    <span>Imagen no disponible</span>
                                </div>
                            @endif
                            <div class="category-name">
                                {{ $category->name }}
                            </div>
                        </div>
                        @can('categorias.edit')
                            <a href="{{ route('admin.categories.edit', $category) }}" title="Editar categoría"
                                class="admin-btn" target="_blank">
                                <i class="ri-pencil-fill"></i>
                            </a>
                        @endcan
                    </a>
                </div>
            @endforeach
        </div>

        <div class="swiper-pagination"></div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>
    </div>
</div>
