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
    @if ($isColor)
        <div class="input-group">
            <label for="value" class="label-form">Valor del color</label>
            <div class="input-icon-container">
                <input type="color" id="features-{{ $index }}-value" data-role="feature-color"
                    name="features[{{ $index }}][color]" placeholder="Seleccionar color" value="{{ $colorHex }}">
                <input type="text" id="features-{{ $index }}-value" data-role="feature-value"
                    name="features[{{ $index }}][value]" maxlength="7" minlength="7" placeholder="#RRGGBB" autocomplete="off"
                    data-validate="required|max:7|min:7" value="{{ $colorHex }}">
            </div>
        </div>
        <div class="input-group">
            <label for="description" class="label-form label-textarea">Nombre del color</label>
            <div class="input-icon-container option-feature-description">
                <textarea class="textarea-form" name="features[{{ $index }}][description]" rows="2"
                    placeholder="Nombre del color" data-role="feature-description" data-validate="required|max:50|min:3">{{ $description }}</textarea>
                <i class="ri-align-left input-icon"></i>
            </div>
        </div>
    @else
        <div class="input-group">
            <label for="value" class="label-form">Nombre del Valor</label>
            <div class="input-icon-container option-feature-value">
                <i class="ri-artboard-2-line input-icon"></i>
                <input type="text" class="input-form" name="features[{{ $index }}][value]" data-validate="required|max:25|min:3"
                    placeholder="Valor (obligatorio)" value="{{ $value }}" data-role="feature-value" required>
            </div>
        </div>
        <div class="input-group">
            <label for="description" class="label-form label-textarea">Descripción</label>
            <div class="input-icon-container option-feature-description">
                <textarea class="textarea-form" name="features[{{ $index }}][description]" rows="2"
                    placeholder="Descripción opcional" data-role="feature-description" data-validate="max:50|min:3">{{ $description }}</textarea>
                <i class="ri-align-left input-icon"></i>
            </div>
        </div>
    @endif
</div>
