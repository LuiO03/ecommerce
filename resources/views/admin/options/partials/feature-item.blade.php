@php
    $featureId = $feature['id'] ?? null;
    $value = old("features.$index.value", $feature['value'] ?? '') ?? '';
    $description = old("features.$index.description", $feature['description'] ?? '') ?? '';
    $colorCandidate = $value;
    $colorHex = preg_match('/^#([0-9A-F]{3}|[0-9A-F]{6})$/i', $colorCandidate ?? '')
        ? strtoupper($colorCandidate)
        : '#1F2937';
@endphp

<div class="option-feature-card" data-feature-index="{{ $index }}">
    <input type="hidden" name="features[{{ $index }}][id]" value="{{ $featureId }}">

    <div class="option-feature-card-header">
        <span class="option-feature-chip">
            <i class="ri-shape-2-line"></i>
            Valor #<span data-role="feature-number">{{ $index + 1 }}</span>
        </span>
        <button type="button" class="boton-sm boton-danger" data-action="remove-feature" title="Eliminar valor">
            <span class="boton-sm-icon"><i class="ri-delete-bin-2-fill"></i></span>
        </button>
    </div>

    <div class="form-row-fit">
        <div class="option-feature-value-row">
            <div class="input-group">
                <label for="value" class="label-form">Nombre</label>
                <div class="input-icon-container option-feature-value">
                    <i class="ri-shape-2-line input-icon"></i>
                    <input type="text"
                           class="input-form"
                           name="features[{{ $index }}][value]"
                           placeholder="Valor (obligatorio)"
                           value="{{ $value }}"
                           data-role="feature-value"
                           required>
                </div>
            </div>
            <div class="option-feature-color" data-role="color-wrapper">
                <input type="color" class="option-feature-color-picker" value="{{ $colorHex }}" data-role="color-input"
                       aria-label="Seleccionar color">
                <span class="option-feature-color-hex" data-role="color-hex">{{ $colorHex }}</span>
            </div>
        </div>
        <div class="input-group">
            <label for="description" class="label-form label-textarea">Descripción</label>
            <div class="input-icon-container option-feature-description">
                <textarea class="textarea-form"
                          name="features[{{ $index }}][description]"
                          rows="2"
                          placeholder="Descripción opcional"
                          data-role="feature-description">{{ $description }}</textarea>
                <i class="ri-align-left input-icon"></i>
            </div>
        </div>
    </div>
</div>
