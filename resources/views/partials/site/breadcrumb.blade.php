@php
    $items = $items ?? [];
    $homeUrl = $homeUrl ?? route('welcome.index');
    $homeLabel = $homeLabel ?? 'Inicio';
@endphp

<nav class="site-container" aria-label="Breadcrumb">
    <ol class="site-breadcrumb-list">
        <li class="site-breadcrumb-item">
            <a class="site-breadcrumb-link" href="{{ $homeUrl }}">
                <i class="ri-home-4-line"></i>
                <span>{{ $homeLabel }}</span>
            </a>
        </li>
        @foreach ($items as $item)
            <li class="site-breadcrumb-separator" aria-hidden="true">
                <i class="ri-arrow-right-s-line"></i>
            </li>
            @if (!empty($item['url']))
                <li class="site-breadcrumb-item">
                    <a class="site-breadcrumb-link" href="{{ $item['url'] }}">
                        @if (!empty($item['icon']))
                            <i class="{{ $item['icon'] }}"></i>
                        @endif
                        <span>{{ $item['label'] ?? '' }}</span>
                    </a>
                </li>
            @else
                <li class="site-breadcrumb-item site-breadcrumb-current">
                    @if (!empty($item['icon']))
                        <i class="{{ $item['icon'] }}"></i>
                    @endif
                    <span>{{ $item['label'] ?? '' }}</span>
                </li>
            @endif
        @endforeach
    </ol>
</nav>
