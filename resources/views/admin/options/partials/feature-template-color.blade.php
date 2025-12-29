@verbatim
<div class="option-feature-card" data-feature-index="__INDEX__">
    <input type="hidden" name="features[__INDEX__][id]" value="__ID__">
    <div class="option-feature-card-header">
        <span class="option-feature-chip">
            <i class="ri-shape-2-line"></i>
            Valor #<span data-role="feature-number">__NUMBER__</span>
        </span>
        <button type="button" class="boton-sm boton-danger option-feature-remove" data-action="remove-feature" title="Eliminar valor">
            <span class="boton-sm-icon"><i class="ri-delete-bin-2-fill"></i></span>
        </button>
    </div>
    <div class="option-feature-card-body">
        <div class="input-group">
            <label class="label-form">Valor del color <i class="ri-asterisk text-accent"></i></label>
            <div class="input-icon-container">
                <i class="ri-palette-line input-icon"></i>
                <input type="text" id="features-__INDEX__-value" data-role="feature-value"
                    name="features[__INDEX__][value]" placeholder="#RRGGBB" style="cursor: pointer"
                    autocomplete="off" data-validate="required|colorCss" value="__VALUE__" data-coloris>
            </div>
        </div>
        <div class="input-group">
            <label class="label-form label-textarea">Nombre del color <i class="ri-asterisk text-accent"></i></label>
            <div class="input-icon-container option-feature-description">
                <i class="ri-align-left input-icon"></i>
                <input type="text" class="input-form" placeholder="Nombre del color"
                    name="features[__INDEX__][description]" data-role="feature-description"
                    value="__DESCRIPTION__" data-validate="required|max:50|min:3">
            </div>
        </div>
    </div>
</div>
@endverbatim
