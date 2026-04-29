<x-app-layout>
    @section('title', 'Blog - ' . $post->title)

    @include('partials.site.breadcrumb', [
        'items' => $breadcrumbItems ?? [
            ['label' => 'Blog', 'icon' => 'ri-newspaper-fill'],
            ['label' => $post->title],
        ],
    ])
    <section class="site-container blog-article-page">
        <div class="blog-article-layout">

            <article class="blog-article">
                <header class="blog-article-header">
                    <h1 class="blog-title">{{ $post->title }}</h1>

                    <div class="blog-card-meta">
                        @if ($post->published_at)
                            <div class="blog-card-data">
                                <i class="ri-calendar-line"></i>
                                <span>{{ $post->published_at->format('d M Y') }}</span>
                            </div>
                        @endif
                        <div class="blog-card-data">
                            <i class="ri-user-line"></i>
                            <span>
                                {{ $post->creator?->name ?? 'Autor desconocido' }}
                            </span>
                        </div>
                    </div>

                    @if ($post->tags && $post->tags->isNotEmpty())
                        <div class="blog-card-tags" style="margin-bottom: 1.2rem;">
                            @foreach ($post->tags as $tag)
                                <a href="{{ route('site.blog.index', ['tag' => $tag->slug]) }}"
                                    class="blog-tag">#{{ $tag->name }}</a>
                            @endforeach
                        </div>
                    @endif
                </header>

                {{-- ================= HERO CON SWIPER ================= --}}
                @php
                    $allImages = collect();

                    if ($post->mainImage) {
                        $allImages->push($post->mainImage);
                    }

                    foreach ($post->images as $img) {
                        if (!$post->mainImage || $img->id !== $post->mainImage->id) {
                            $allImages->push($img);
                        }
                    }
                @endphp

                <div class="blog-article-hero-image">
                    @if ($allImages->isNotEmpty())
                        <div class="swiper blog-hero-slider">
                            <div class="swiper-wrapper">
                                @foreach ($allImages as $img)
                                    <div class="swiper-slide">
                                        <div class="blog-hero-img">
                                            @if ($img->path)
                                                <img src="{{ asset('storage/' . $img->path) }}"
                                                    alt="{{ $img->alt ?? $post->title }}" loading="lazy"
                                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <div class="blog-card-media-fallback" style="display: none;">
                                                    <i class="ri-image-line"></i>
                                                    <span>Imagen no disponible</span>
                                                </div>
                                            @else
                                                <div class="blog-card-media-fallback">
                                                    <i class="ri-image-line"></i>
                                                    <span>Imagen no disponible</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Controles -->
                            <div class="swiper-button-prev"></div>
                            <div class="swiper-button-next"></div>
                            <div class="swiper-pagination"></div>

                        </div>
                        <!-- Thumbnails -->
                        <div class="swiper blog-hero-thumbs">
                            <div class="swiper-wrapper">
                                @foreach ($allImages as $img)
                                    <div class="swiper-slide">
                                        @if ($img->path)
                                            <img src="{{ asset('storage/' . $img->path) }}" loading="lazy"
                                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="blog-card-thumb-fallback" style="display: none;">
                                                <i class="ri-image-line"></i>
                                                <span>Imagen no disponible</span>
                                            </div>
                                        @else
                                            <div class="blog-card-thumb-fallback">
                                                <i class="ri-image-line"></i>
                                                <span>Imagen no disponible</span>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="blog-card-media-fallback">
                            <i class="ri-image-line"></i>
                            <span>Imagen no disponible</span>
                        </div>
                    @endif
                </div>

                {{-- ================= CONTENIDO ================= --}}
                <div class="blog-article-content">
                    {!! $post->content !!}
                </div>

            </article>

            {{-- ================= SIDEBAR ================= --}}
            <aside class="blog-sidebar">
                @if ($related->isNotEmpty())
                    <section class="blog-sidebar-section">
                        <span class="card-title">También te puede interesar</span>

                        <div class="blog-sidebar-list">
                            @foreach ($related as $item)
                                <a href="{{ route('site.blog.show', $item) }}" class="blog-sidebar-item">
                                    <div class="blog-sidebar-text">
                                        <span class="blog-sidebar-item-title">{{ $item->title }}</span>
                                        @if ($item->published_at)
                                            <span class="blog-sidebar-item-meta">
                                                {{ $item->published_at->format('d M Y') }}
                                            </span>
                                        @endif
                                    </div>
                                    <i class="ri-arrow-right-up-line"></i>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section class="blog-sidebar-section blog-sidebar-card">
                    <span class="card-title">¿Necesitas ayuda?</span>
                    <p class="blog-sidebar-text">
                        Si tienes dudas sobre productos, envíos o devoluciones, nuestro equipo está listo para ayudarte.
                    </p>

                    <a href="#" class="boton-form boton-accent w-full justify-center">
                        <span class="boton-form-icon"><i class="ri-customer-service-2-line"></i></span>
                        <span class="boton-form-text">Ir a contacto</span>
                    </a>
                </section>
            </aside>

        </div>
    </section>

    {{-- ================= JS SWIPER ================= --}}
    @push('js')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (!window.Swiper) return;

                const thumbs = new Swiper('.blog-hero-thumbs', {
                    modules: [window.SwiperModules.Thumbs],
                    spaceBetween: 8,
                    freeMode: true,
                    watchSlidesProgress: true,
                    slideToClickedSlide: true,

                    breakpoints: {
                        0: {
                            slidesPerView: 1,
                        },
                        480: {
                            slidesPerView: 3,
                        },
                        768: {
                            slidesPerView: 3,
                        },
                        1024: {
                            slidesPerView: 4,
                        },
                        1400: {
                            slidesPerView: 5,
                        }
                    }
                });

                const main = new Swiper('.blog-hero-slider', {
                    modules: [
                        window.SwiperModules.Navigation,
                        window.SwiperModules.Pagination,
                        window.SwiperModules.Autoplay,
                        window.SwiperModules.Thumbs
                    ],
                    slidesPerView: 1,
                    spaceBetween: 10,
                    autoplay: {
                        delay: 6000,
                        disableOnInteraction: true,
                        pauseOnMouseEnter: true,
                    },
                    thumbs: {
                        swiper: thumbs,
                    },

                    navigation: {
                        nextEl: '.blog-hero-slider .swiper-button-next',
                        prevEl: '.blog-hero-slider .swiper-button-prev',
                    },

                    pagination: {
                        el: '.blog-hero-slider .swiper-pagination',
                        clickable: true,
                    },

                });

                // 🔥 FORZAR sincronización manual (clave)
                thumbs.on('click', function() {
                    const index = thumbs.clickedIndex;
                    if (index !== undefined) {
                        main.slideTo(index);
                    }
                });
            });
        </script>
    @endpush

</x-app-layout>
