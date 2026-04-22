<x-app-layout>
    @section('title', 'Blog')

    @include('partials.site.breadcrumb', [
        'items' => $breadcrumbItems ?? [['label' => 'Blog', 'icon' => 'ri-newspaper-fill']],
    ])

    <section class="site-container blog-page">
        <div class="blog-main-layout">
            <div class="blog-main-content">
                <header class="blog-hero">
                    <div class="blog-hero-content">
                        <p class="blog-kicker">Blog</p>
                        <h1 class="blog-title">Ideas, guías y tendencias para comprar mejor</h1>
                        <p class="blog-subtitle">Descubre recomendaciones, novedades de productos y consejos para sacarle
                            el
                            máximo provecho a tu experiencia de compra.</p>
                    </div>
                </header>

                @if ($featured->isNotEmpty())
                    <section class="blog-featured">
                        <div class="section-header">
                            <h2 class="section-title">Destacados</h2>
                            <p class="section-subtitle">
                                Explora nuestros artículos más populares y recomendados para ti.
                            </p>
                        </div>
                        <div class="blog-featured-grid">
                            @foreach ($featured as $post)
                                <article class="blog-card-featured">
                                    <a href="{{ route('site.blog.show', $post) }}" class="blog-card-media">
                                        @if ($post->mainImage)
                                            <img src="{{ asset('storage/' . $post->mainImage->path) }}"
                                                alt="{{ $post->title }}"
                                                loading="lazy"onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
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
                                    </a>
                                    <div class="blog-card-body">
                                        @if ($post->tags && $post->tags->isNotEmpty())
                                            <div class="blog-card-tags">
                                                @foreach ($post->tags as $tag)
                                                    <a href="{{ route('site.blog.index', ['tag' => $tag->slug]) }}"
                                                        class="blog-tag">#{{ $tag->name }}</a>
                                                @endforeach
                                            </div>

                                            <a href="{{ route('site.blog.show', $post) }}" class="blog-card-title">
                                                {{ $post->title }}
                                            </a>
                                        @endif
                                        <p class="blog-card-excerpt">
                                            {{ Str::limit(strip_tags($post->content), 300) }}
                                        </p>
                                        <div class="blog-card-meta">
                                            @if ($post->views)
                                                <div class="blog-card-data">
                                                    <i class="ri-eye-line"></i>
                                                    <span> {{ $post->views }}</span>
                                                </div>
                                            @endif
                                            @if ($post->published_at)
                                                <div class="blog-card-data">
                                                    <i class="ri-calendar-line"></i>
                                                    <span>{{ $post->published_at->format('d M Y') }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="blog-card-link-container">
                                            <a href="{{ route('site.blog.show', $post) }}" class="blog-card-link">
                                                Leer artículo
                                                <i class="ri-arrow-right-up-line"></i>
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section class="blog-list">
                    <div class="section-header">
                        <h2 class="section-title">Últimos artículos</h2>
                        <p class="section-subtitle">
                            Descubre las últimas noticias y actualizaciones en nuestro blog.
                        </p>
                    </div>

                    @if ($posts->isEmpty())
                        <div class="blog-empty">
                            <div class="card-empty-icon card-warning">
                                <i class="ri-article-line"></i>
                            </div>
                            <h2 class="card-title">No hay artículos publicados todavía</h2>
                            <p>Pronto compartiremos novedades, guías y contenido útil para ti.</p>
                        </div>
                    @else
                        <div class="blog-grid">
                            @foreach ($posts as $post)
                                <article class="blog-card">
                                    <a href="{{ route('site.blog.show', $post) }}" class="blog-card-media">
                                        @if ($post->mainImage)
                                            <img src="{{ asset('storage/' . $post->mainImage->path) }}"
                                                alt="{{ $post->title }}"
                                                loading="lazy"onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
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
                                    </a>
                                    <div class="blog-card-body">
                                        @if ($post->tags && $post->tags->isNotEmpty())
                                            <div class="blog-card-tags">
                                                @foreach ($post->tags as $tag)
                                                    <a href="{{ route('site.blog.index', ['tag' => $tag->slug]) }}"
                                                        class="blog-tag">#{{ $tag->name }}</a>
                                                @endforeach
                                            </div>

                                            <a href="{{ route('site.blog.show', $post) }}" class="blog-card-title">
                                                {{ $post->title }}
                                            </a>
                                        @endif
                                        <p class="blog-card-excerpt">
                                            {{ Str::limit(strip_tags($post->content), 100) }}
                                        </p>
                                        <div class="blog-card-meta">
                                            @if ($post->views)
                                                <div class="blog-card-data">
                                                    <i class="ri-eye-line"></i>
                                                    <span> {{ $post->views }}</span>
                                                </div>
                                            @endif
                                            @if ($post->published_at)
                                                <div class="blog-card-data">
                                                    <i class="ri-calendar-line"></i>
                                                    <span>{{ $post->published_at->format('d M Y') }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="blog-card-link-container">
                                            <a href="{{ route('site.blog.show', $post) }}" class="blog-card-link">
                                                Leer artículo
                                                <i class="ri-arrow-right-up-line"></i>
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        <div class="blog-pagination">
                            {{ $posts->links() }}
                        </div>
                    @endif
                </section>
            </div>

            <!-- SIDEBAR -->
            <aside class="blog-sidebar">
                <section class="blog-sidebar-section blog-sidebar-search">
                    <form action="{{ route('site.blog.index') }}" method="get" class="blog-search-form">
                        <article class="tabla-buscador">
                            <i class="ri-search-eye-line buscador-icon"></i>
                            <input type="text" id="customSearch" placeholder="Buscar artículos..." name="search" value="{{ request('search') }}"
                                autocomplete="off" />
                            <button type="button" id="clearSearch" class="buscador-clear" title="Limpiar búsqueda">
                                <i class="ri-close-circle-fill"></i>
                            </button>
                        </article>
                        <button type="submit" class="blog-search-btn" aria-label="Buscar"><i
                                class="ri-search-line"></i></button>
                    </form>


                </section>

                <section class="blog-sidebar-section blog-sidebar-tags">
                    <span class="card-title">Tags populares</span>
                    <div class="blog-sidebar-tags-list">
                        @php
                            $allTags = \App\Models\Tag::has('posts')
                                ->orderByDesc('posts_count')
                                ->limit(12)
                                ->withCount('posts')
                                ->get();
                        @endphp
                        @foreach ($allTags as $tag)
                            <a href="{{ route('site.blog.index', ['tag' => $tag->slug]) }}"
                                class="blog-tag">#{{ $tag->name }}</a>
                        @endforeach
                    </div>
                </section>
                <section class="blog-sidebar-section blog-sidebar-latest">
                    <span class="card-title">Últimos artículos</span>
                    <ul class="blog-sidebar-latest-list">
                        @php
                            $latestPosts = \App\Models\Post::published()
                                ->visibleTo('public')
                                ->latest('published_at')
                                ->limit(5)
                                ->get();
                        @endphp
                        @foreach ($latestPosts as $item)
                            <li>
                                <a href="{{ route('site.blog.show', $item) }}">{{ $item->title }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            </aside>
        </div>
    </section>

</x-app-layout>
