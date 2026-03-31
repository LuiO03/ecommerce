@php
    $isColor = $isColorOption ?? false;

    if ($isColor) {
        // Para color: value = nombre, description = HEX para la previsualización.
        $displayValue = $feature['value'] ?? '';
        $hexCandidate = $feature['description'] ?? '';
    } else {
        $displayValue = $feature['value'] ?? '';
        $hexCandidate = null;
    }

    $colorPreview = $isColor && $hexCandidate && preg_match('/^#([0-9A-F]{3}|[0-9A-F]{6})$/i', $hexCandidate)
        ? strtoupper($hexCandidate)
        : null;
@endphp
<div class="option-feature-pill {{ $colorPreview ? 'is-color' : '' }}"
    data-feature-id="{{ $feature['id'] ?? '' }}"
    data-delete-url="{{ $feature['delete_url'] ?? '' }}">
    @if ($colorPreview)
        <span class="pill-color" style="--pill-color: {{ $colorPreview }}"></span>
    @endif
    <span class="pill-value">{{ $displayValue }}</span>
    @if ($isColor && $colorPreview)
        <span class="pill-description">{{ $colorPreview }}</span>
    @elseif (!empty($feature['description']))
        <span class="pill-description">{{ $feature['description'] }}</span>
    @endif
    <button type="button" class="pill-remove" data-action="feature-remove" aria-label="Eliminar valor">
        <i class="ri-close-line"></i>
    </button>
</div>
