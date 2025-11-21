<div class="alert alert-{{ $type }} {{ $dismissible ? 'alert-dismissible' : '' }}" data-alert>
    <i class="{{ $icon }} alert-icon"></i>
    
    <div class="alert-content">
        @if($title)
            <h4 class="alert-title">{{ $title }}</h4>
        @endif

        @if($items && count($items) > 0)
            <ul class="alert-list">
                @foreach($items as $item)
                    <li>{!! $item !!}</li>
                @endforeach
            </ul>
        @else
            <div class="alert-message">
                {{ $slot }}
            </div>
        @endif
    </div>

    @if($dismissible)
        <button type="button" class="alert-close" data-alert-close aria-label="Cerrar">
            <i class="ri-close-line"></i>
        </button>
    @endif
</div>
