<div class="latest-products-container">
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
                        speed: 200,
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
                                spaceBetween: 15,
                            },
                            640: {
                                slidesPerView: 2,
                                spaceBetween: 15,
                            },
                            1024: {
                                slidesPerView: 4,
                                spaceBetween: 20,
                            },
                            1280: {
                                slidesPerView: 6,
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
    <div class="latest-products-header">
        <h1>Últimos Productos</h1>
        <p>Descubre nuestras incorporaciones más recientes</p>
    </div>

    @if ($products->isNotEmpty())
        <div class="swiper products-slider">
            <div class="swiper-wrapper">
                @foreach ($products as $product)
                    <div class="swiper-slide products-slide">
                        <div class="product-card">
                            <a href="{{ route('products.show', $product) }}" class="product-image">
                                @if ($product->mainImage)
                                    <img src="{{ asset('storage/' . $product->mainImage->path) }}"
                                        alt="{{ $product->mainImage->alt ?? $product->name }}"
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
                            </a>

                            <div class="product-details">
                                <div class="product-content">
                                    <div class="flex justify-between">
                                        <p class="product-brand">{{ $product->category?->name ?? 'Sin categoría' }}</p>
                                        <p class="product-rating">
                                            <i class="ri-star-fill"></i>
                                            <span>4.5 (128)</span>
                                        </p>
                                    </div>
                                    <h3 class="product-name">{{ $product->name }}</h3>
                                    <div class="flex w-full flex-col">
                                        <div class="product-pricing">
                                            @if (!is_null($product->discount) && (float) $product->discount > 0)
                                                @php
                                                    $discountPercent = min(max((float) $product->discount, 0), 100);
                                                    $discounted = max(
                                                        (float) $product->price * (1 - $discountPercent / 100),
                                                        0,
                                                    );
                                                @endphp
                                                <span
                                                    class="product-price">S/.{{ number_format($discounted, 2) }}</span>
                                                <span
                                                    class="product-price-original">S/.{{ number_format($product->price, 2) }}</span>
                                            @else
                                                <span
                                                    class="product-price">S/.{{ number_format($product->price, 2) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <livewire:site.add-to-wishlist-card :product-id="$product->id" :key="'wishlist-card-' . $product->id" />
                                <button class="product-btn" aria-label="Agregar al carrito" title="Agregar al carrito">
                                    <i class="ri-shopping-bag-line"></i>
                                    Agregar
                                </button>
                                @if ($product->discount)
                                    <span class="product-badge">-{{ number_format($product->discount, 0) }}% OFF</span>
                                @endif
                            </div>
                        </div>
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
</div>
