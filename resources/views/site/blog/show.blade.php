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
                    <p class="blog-kicker">Blog</p>
                    <h1 class="blog-title">{{ $post->title }}</h1>
                    <div class="blog-card-meta">
                        @if ($post->published_at)
                            <span>{{ $post->published_at->format('d M Y') }}</span>
                        @endif
                        @if ($post->views)
                            <span><i class="ri-eye-line"></i> {{ $post->views }} lecturas</span>
                        @endif
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

                <div class="blog-article-hero-image">
                    @if ($post->mainImage)
                        <img src="{{ asset('storage/' . $post->mainImage->path) }}" alt="{{ $post->title }}"
                            loading="lazy"
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

                <div class="blog-article-content">
                    {!! $post->content !!}
                </div>
            </article>

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
                                            <span
                                                class="blog-sidebar-item-meta">{{ $item->published_at->format('d M Y') }}</span>
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
                    <p class="blog-sidebar-text">Si tienes dudas sobre productos, envíos o devoluciones, nuestro equipo
                        está listo para ayudarte.</p>
                    <a href="#" class="boton-form boton-accent w-full justify-center">
                        <span class="boton-form-icon"><i class="ri-customer-service-2-line"></i></span>
                        <span class="boton-form-text">Ir a contacto</span>
                    </a>
                </section>
            </aside>
        </div>
    </section>
</x-app-layout>
