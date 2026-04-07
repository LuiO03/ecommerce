<x-app-layout>
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
                </header>

                @if ($post->mainImage)
                    <div class="blog-article-hero-image">
                        <img src="{{ asset('storage/' . $post->mainImage->path) }}" alt="{{ $post->title }}" loading="lazy">
                    </div>
                @endif

                <div class="blog-article-content">
                    {!! $post->content !!}
                </div>
            </article>

            <aside class="blog-sidebar">
                @if ($related->isNotEmpty())
                    <section class="blog-sidebar-section">
                        <h2 class="blog-sidebar-title">También te puede interesar</h2>
                        <div class="blog-sidebar-list">
                            @foreach ($related as $item)
                                <a href="{{ route('site.blog.show', $item) }}" class="blog-sidebar-item">
                                    <div class="blog-sidebar-text">
                                        <span class="blog-sidebar-item-title">{{ $item->title }}</span>
                                        @if ($item->published_at)
                                            <span class="blog-sidebar-item-meta">{{ $item->published_at->format('d M Y') }}</span>
                                        @endif
                                    </div>
                                    <i class="ri-arrow-right-up-line"></i>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section class="blog-sidebar-section blog-sidebar-card">
                    <h2 class="blog-sidebar-title">¿Necesitas ayuda?</h2>
                    <p class="blog-sidebar-text">Si tienes dudas sobre productos, envíos o devoluciones, nuestro equipo está listo para ayudarte.</p>
                    <a href="#" class="boton-form boton-accent w-full justify-center">
                        <span class="boton-form-icon"><i class="ri-customer-service-2-line"></i></span>
                        <span class="boton-form-text">Ir a contacto</span>
                    </a>
                </section>
            </aside>
        </div>
    </section>
</x-app-layout>
