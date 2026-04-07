<x-app-layout>
    <section class="site-container blog-page">
        <header class="blog-hero">
            <div class="blog-hero-content">
                <p class="blog-kicker">Blog</p>
                <h1 class="blog-title">Ideas, guías y tendencias para comprar mejor</h1>
                <p class="blog-subtitle">Descubre recomendaciones, novedades de productos y consejos para sacarle el máximo provecho a tu experiencia de compra.</p>
            </div>
        </header>

        @if ($featured->isNotEmpty())
            <section class="blog-featured">
                <h2 class="blog-section-title">Destacados</h2>
                <div class="blog-featured-grid">
                    @foreach ($featured as $post)
                        <article class="blog-card blog-card-featured">
                            <a href="{{ route('site.blog.show', $post) }}" class="blog-card-media">
                                @if ($post->mainImage)
                                    <img src="{{ asset('storage/' . $post->mainImage->path) }}" alt="{{ $post->title }}" loading="lazy">
                                @else
                                    <div class="blog-card-media-fallback">
                                        <i class="ri-image-line"></i>
                                    </div>
                                @endif
                            </a>
                            <div class="blog-card-body">
                                <div class="blog-card-meta">
                                    @if ($post->published_at)
                                        <span>{{ $post->published_at->format('d M Y') }}</span>
                                    @endif
                                    @if ($post->views)
                                        <span><i class="ri-eye-line"></i> {{ $post->views }}</span>
                                    @endif
                                </div>
                                <a href="{{ route('site.blog.show', $post) }}" class="blog-card-title">
                                    {{ $post->title }}
                                </a>
                                <p class="blog-card-excerpt">
                                    {{ Str::limit(strip_tags($post->content), 130) }}
                                </p>
                                <a href="{{ route('site.blog.show', $post) }}" class="blog-card-link">
                                    Leer artículo
                                    <i class="ri-arrow-right-up-line"></i>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="blog-list">
            <div class="blog-list-header">
                <h2 class="blog-section-title">Últimos artículos</h2>
            </div>

            @if ($posts->isEmpty())
                <div class="blog-empty">
                    <i class="ri-article-line"></i>
                    <h3>No hay artículos publicados todavía</h3>
                    <p>Pronto compartiremos novedades, guías y contenido útil para ti.</p>
                </div>
            @else
                <div class="blog-grid">
                    @foreach ($posts as $post)
                        <article class="blog-card">
                            <a href="{{ route('site.blog.show', $post) }}" class="blog-card-media">
                                @if ($post->mainImage)
                                    <img src="{{ asset('storage/' . $post->mainImage->path) }}" alt="{{ $post->title }}" loading="lazy">
                                @else
                                    <div class="blog-card-media-fallback">
                                        <i class="ri-image-line"></i>
                                    </div>
                                @endif
                            </a>
                            <div class="blog-card-body">
                                <div class="blog-card-meta">
                                    @if ($post->published_at)
                                        <span>{{ $post->published_at->format('d M Y') }}</span>
                                    @endif
                                    @if ($post->views)
                                        <span><i class="ri-eye-line"></i> {{ $post->views }}</span>
                                    @endif
                                </div>
                                <a href="{{ route('site.blog.show', $post) }}" class="blog-card-title">
                                    {{ $post->title }}
                                </a>
                                <p class="blog-card-excerpt">
                                    {{ Str::limit(strip_tags($post->content), 100) }}
                                </p>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="blog-pagination">
                    {{ $posts->links() }}
                </div>
            @endif
        </section>
    </section>
</x-app-layout>
