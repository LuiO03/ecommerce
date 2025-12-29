@php
    $isColor = $isColorOption ?? false;
    $displayValue = $feature['value'] ?? '';
    $colorPreview = $isColor && preg_match('/^#([0-9A-F]{3}|[0-9A-F]{6})$/i', $displayValue)
        ? strtoupper($displayValue)
        : null;
@endphp
<div class="option-feature-pill {{ $colorPreview ? 'is-color' : '' }}"
    data-feature-id="{{ $feature['id'] ?? '' }}"
    data-delete-url="{{ $feature['delete_url'] ?? '' }}">
    @if ($colorPreview)
        <span class="pill-color" style="--pill-color: {{ $colorPreview }}"></span>
    @endif
    <span class="pill-value">{{ $displayValue }}</span>
    @if (!empty($feature['description']))
        <span class="pill-description">{{ $feature['description'] }}</span>
    @endif
    <button type="button" class="pill-remove" data-action="feature-remove" aria-label="Eliminar valor">
        <i class="ri-close-line"></i>
    </button>
</div>
