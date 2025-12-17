@verbatim
<div class="option-feature-card" data-feature-index="__INDEX__">
    <input type="hidden" name="features[__INDEX__][id]" value="__ID__">
    <div class="option-feature-card-header">
        <span class="option-feature-chip">
            <i class="ri-shape-2-line"></i>
            Valor #<span data-role="feature-number">__NUMBER__</span>
        </span>
        <button type="button" class="option-feature-remove" data-action="remove-feature" title="Eliminar valor">
            <i class="ri-delete-bin-6-line"></i>
        </button>
    </div>
    <div class="option-feature-card-body">
        <div class="option-feature-value-row">
            <div class="input-icon-container option-feature-value">
                <i class="ri-price-tag-3-line input-icon"></i>
                <input type="text"
                       class="input-form"
                       name="features[__INDEX__][value]"
                       placeholder="Valor (obligatorio)"
                       value="__VALUE__"
                       data-role="feature-value"
                       required>
            </div>
            <div class="option-feature-color" data-role="color-wrapper">
                <input type="color" class="option-feature-color-picker" value="__COLOR__" data-role="color-input" aria-label="Seleccionar color">
                <span class="option-feature-color-hex" data-role="color-hex">__COLOR__</span>
            </div>
        </div>
        <div class="input-icon-container option-feature-description">
            <textarea class="textarea-form"
                      name="features[__INDEX__][description]"
                      rows="2"
                      placeholder="Descripción opcional"
                      data-role="feature-description">__DESCRIPTION__</textarea>
            <i class="ri-align-left input-icon"></i>
        </div>
    </div>
</div>
@endverbatim
