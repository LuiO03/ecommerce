@php
    $featureId = $feature['id'] ?? null;
    $value = old("features.$index.value", $feature['value'] ?? '') ?? '';
    $description = old("features.$index.description", $feature['description'] ?? '') ?? '';
    $isColor = $isColorOption ?? false;
    $colorCandidate = $value;
    $colorHex =
        $isColor && preg_match('/^#([0-9A-F]{3}|[0-9A-F]{6})$/i', $colorCandidate ?? '')
            ? strtoupper($colorCandidate)
            : '#000000';
@endphp

<div class="option-feature-card" data-feature-index="{{ $index }}">
    <input type="hidden" name="features[{{ $index }}][id]" value="{{ $featureId }}">

    <div class="option-feature-card-header">
        <span class="option-feature-chip">
            <i class="ri-shape-2-line"></i>
            Valor #<span data-role="feature-number">{{ $index + 1 }}</span>
        </span>
        <button type="button" class="boton-sm boton-danger option-feature-remove" data-action="remove-feature"
            title="Eliminar valor">
            <span class="boton-sm-icon"><i class="ri-delete-bin-2-fill"></i></span>
        </button>
    </div>
    <div class="option-feature-card-body">
        @if ($isColor)
            <div class="input-group">
                <label for="value" class="label-form">Valor del color</label>
                <div class="input-icon-container">
                    <i class="ri-palette-line input-icon"></i>
                    <input type="text" id="features-{{ $index }}-value" data-role="feature-value"
                        name="features[{{ $index }}][value]" placeholder="#RRGGBB" style="cursor: pointer"
                        autocomplete="off" data-validate="required|colorCss" value="{{ $value }}" data-coloris>
                </div>
            </div>
            <div class="input-group">
                <label for="description" class="label-form label-textarea">Nombre del color</label>
                <div class="input-icon-container option-feature-description">
                    <i class="ri-align-left input-icon"></i>
                    <input type="text" class="input-form" placeholder="Nombre del color"
                        name="features[{{ $index }}][description]" data-role="feature-description"
                        value="{{ $description }}" data-validate="required|max:50|min:3" value="{{ $description }}">
                </div>
            </div>
        @else
            <div class="input-group">
                <label for="value" class="label-form">Nombre del Valor</label>
                <div class="input-icon-container option-feature-value">
                    <i class="ri-artboard-2-line input-icon"></i>
                    <input type="text" class="input-form" name="features[{{ $index }}][value]"
                        data-validate="required|max:25|min:3" placeholder="Valor (obligatorio)" value="{{ $value }}"
                        data-role="feature-value" required>
                </div>
            </div>
            <div class="input-group">
                <label for="description" class="label-form label-textarea">Descripción</label>
                <div class="input-icon-container option-feature-description">
                    <input type="text" class="input-form" placeholder="Descripción opcional"
                        name="features[{{ $index }}][description]" data-role="feature-description"
                        value="{{ $description }}" data-validate="max:50|min:3" value="{{ $description }}">
                    <i class="ri-align-left input-icon"></i>
                </div>
            </div>
        @endif
    </div>
</div>
<script>
    Coloris({
        theme: 'pill',
        themeMode: 'dark',
        swatches: [
            'DarkSlateGray',
            '#2a9d8f',
            '#e9c46a',
            'coral',
            'rgb(231, 111, 81)',
            'Crimson',
            '#023e8a',
            '#0077b6',
            'hsl(194, 100%, 39%)',
            '#00b4d8',
            '#48cae4'
        ],
        onChange: (color, inputEl) => {
            console.log(`The new color is ${color}`);
        }
    });
</script>
