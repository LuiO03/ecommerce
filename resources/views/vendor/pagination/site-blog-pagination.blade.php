@if ($paginator->hasPages())
    <nav class="site-pagination">

        @if ($paginator->onFirstPage())
            <span class="page-btn disabled">Anterior</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}#blog-list" class="page-btn">Anterior</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="page-dots">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="page-number active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}#blog-list" class="page-number">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}#blog-list" class="page-btn">Siguiente</a>
        @else
            <span class="page-btn disabled">Siguiente</span>
        @endif

    </nav>
    <style>
        .site-pagination {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            margin-top: 1rem;
        }

        .page-btn,
        .page-number {
            min-width: 42px;
            height: 42px;
            padding: 0 14px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-weight: 600;
            border: 1px solid var(--color-border);
            background: var(--color-card);
            color: var(--color-text);
            transition: .2s ease;
        }

        .page-btn:hover,
        .page-number:hover {
            background: var(--color-site-nav);
            color: #fff;
            border-color: var(--color-site-nav);
        }

        .page-number.active {
            background: var(--color-site-nav);
            color: #fff;
            border-color: var(--color-site-nav);
        }

        .page-btn.disabled {
            opacity: .45;
            pointer-events: none;
        }

        .page-dots {
            padding: 0 .5rem;
            opacity: .6;
        }
    </style>
@endif
