@php
    $routeName = Route::currentRouteName();
    $segments = explode('.', $routeName);
@endphp

<nav class="breadcrumb-nav">
    <ol class="breadcrumb-list">
        {{-- Inicio --}}
        <li class="breadcrumb">
            <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">
                <i class="ri-home-heart-fill breadcrumb-icon"></i>
                <span>Dashboard</span>
            </a>
        </li>

        {{-- Segmentos dinámicos (solo si NO estamos en dashboard) --}}
        @if ($routeName !== 'admin.dashboard')
            @foreach ($segments as $index => $segment)
                @php
                    $label = ucfirst(str_replace(['admin', 'index'], '', $segment));
                @endphp

                @if ($label)
                    <li class="breadcrumb">
                        <i class="ri-arrow-right-s-line breadcrumb-separator"></i>
                        @if ($index === count($segments) - 1)
                            <span class="breadcrumb-current">{{ $label }}</span>
                        @else
                            <a href="#" class="breadcrumb-link">{{ $label }}</a>
                        @endif
                    </li>
                @endif
            @endforeach
        @endif
    </ol>
</nav>

