@php
    use App\Models\Option;
    use Illuminate\Support\Str;

    $optionInstance = $option ?? null;
    $requestedName = old('name', $optionInstance?->name);
    $isColorOption = $optionInstance?->isColor() ?? Str::slug($requestedName ?? '') === Option::COLOR_SLUG;

    $featuresDataset = old('features');

    if ($featuresDataset === null) {
        $featuresDataset = $optionInstance
            ? $optionInstance->features
                ->map(
                    fn($feature) => [
                        'id' => $feature->id,
                        'value' => $feature->value,
                        'description' => $feature->description,
                    ],
                )
                ->toArray()
            : [];
    }

@endphp

{{-- Banner de errores backend --}}
@if ($errors->any())
    <div class="form-error-banner">
        <i class="ri-error-warning-line form-error-icon"></i>
        <div>
            <h4 class="form-error-title">Se encontraron los siguientes errores:</h4>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<x-alert type="info" title="Consejo" :dismissible="true" :items="[
    'Los campos marcados con <i class=\'ri-asterisk text-accent\'></i> son obligatorios.',
    'Puedes añadir múltiples valores y reorganizarlos luego si lo necesitas.',
]" />

<div class="form-columns-row">
    <div class="form-body">
        <div class="input-group">
            <label for="name" class="label-form">
                Nombre de la opción
                <i class="ri-asterisk text-accent"></i>
            </label>
            <div class="input-icon-container">
                <i class="ri-donut-chart-line input-icon"></i>
                <input type="text" id="name" name="name" class="input-form" placeholder="Ej. Material"
                    value="{{ old('name', $optionInstance?->name) }}" required data-validate="required|min:2|max:120">
            </div>
        </div>

        <div class="input-group">
            <label for="description" class="label-form label-textarea">
                Descripción
            </label>
            <div class="input-icon-container">
                <textarea id="description" name="description" class="textarea-form" rows="4"
                    placeholder="Describe brevemente cómo se usa esta opción" data-validate="min:3|max:600">{{ old('description', $optionInstance?->description) }}</textarea>
                <i class="ri-file-text-line input-icon"></i>
            </div>
        </div>
    </div>

    <div class="form-body">
        <div class="option-features-panel">
            <header class="option-features-header">
                <div class="card-header">
                    <span class="card-title">Valores disponibles</span>
                    <p class="card-description">
                        Define los valores que estarán disponibles al configurar productos.
                    </p>
                </div>
                <button type="button" class="boton-form boton-primary" id="addFeatureBtn"
                    data-action="open-feature-modal">
                    <span class="boton-form-icon"><i class="ri-add-circle-fill"></i></span>
                    <span class="boton-form-text">Añadir valor</span>
                </button>
            </header>

            <div class="tabla-wrapper option-features-table">
                <table class="tabla-general w-full tabla-normal">
                    <thead>
                        <tr>
                            <th class="column-name-th">Valor</th>
                            <th class="column-description-th" id="optionFeaturesHeaderDescription">Descripcion</th>
                            <th class="column-color-th option-feature-color-column" data-role="feature-color-header">
                                Color</th>
                            <th class="column-actions-th column-not-order">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="optionFeaturesBody" data-is-color="{{ $isColorOption ? 'true' : 'false' }}"
                        data-color-slug="{{ Option::COLOR_SLUG }}"
                        data-color-locked="{{ $optionInstance?->slug === Option::COLOR_SLUG ? 'true' : 'false' }}"
                        data-features='@json($featuresDataset)'>
                        <tr id="optionFeaturesEmpty" class="variants-empty-row">
                            <td colspan="4" class="text-muted-td text-center">
                                <div class="tabla-no-data">
                                    <i class="ri-folder-warning-line"></i>
                                    <span>No hay valores registrados para esta opcion.</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="optionFeaturesInputs" class="option-features-inputs" hidden></div>
        </div>
    </div>

</div>
<div id="optionFeatureModal" class="variant-crud-modal" hidden aria-hidden="true" role="dialog" aria-modal="true"
    aria-labelledby="optionFeatureModalTitle">
    <div class="variant-crud-modal-backdrop" data-action="close-feature-modal"></div>

    <div class="variant-crud-modal-dialog">
        <div class="variant-crud-modal-header bg-success" id="optionFeatureModalHeader">
            <h6 id="optionFeatureModalTitle" class="variant-crud-modal-title">Agregar valor</h6>
            <button type="button" class="confirm-close ripple-btn" title="Cerrar" data-action="close-feature-modal">
                <i class="ri-close-line"></i>
            </button>
        </div>

        <div class="variant-crud-modal-body ripple-card">
            <div class="card-header">
                <span class="card-title">Detalles del valor</span>
                <p class="card-description">
                    Completa la informacion para agregar o editar un valor.
                </p>
            </div>

            <div class="form-row-fill">
                <div class="input-group">
                    <label for="optionFeatureValue" class="label-form">
                        Nombre del valor <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-align-left input-icon"></i>
                        <input type="text" id="optionFeatureValue" class="input-form" placeholder="Nombre del valor"
                            data-validate="required|min:2|max:120">
                    </div>
                </div>
                <div class="input-group" data-role="option-feature-description">
                    <label for="optionFeatureDescription" class="label-form label-textarea">Descripcion</label>
                    <div class="input-icon-container">
                        <i class="ri-file-text-line input-icon"></i>
                        <input type="text" id="optionFeatureDescription" class="input-form"
                            placeholder="Descripcion opcional" data-validate="max:255">
                    </div>
                </div>
                <div class="input-group" data-role="option-feature-color" hidden>
                    <label for="optionFeatureHex" class="label-form">Valor HEX <i
                            class="ri-asterisk text-accent"></i></label>
                    <div class="input-icon-container">
                        <i class="ri-palette-line input-icon"></i>
                        <input type="text" id="optionFeatureHex" class="input-form" placeholder="#RRGGBB"
                            data-validate="required|colorCss" data-coloris autocomplete="off">
                    </div>
                    <div class="option-feature-preview">
                        <span class="option-feature-swatch" id="optionFeatureSwatch"></span>
                        <span class="option-feature-hex" id="optionFeatureHexLabel">#000000</span>
                    </div>
                </div>
            </div>
            <p id="optionFeatureModalError" class="variant-modal-error" hidden></p>
        </div>

        <div class="variant-crud-modal-footer confirm-actions">
            <button type="button" class="boton boton-modal-close" data-action="close-feature-modal">
                <span class="boton-icon text-base"><i class="ri-close-line"></i></span>
                <span class="boton-text">Cancelar</span>
            </button>
            <button type="button" class="boton bg-success" id="saveOptionFeatureBtn">
                <span class="boton-icon"><i class="ri-check-double-line"></i></span>
                <span class="boton-text">Agregar valor</span>
            </button>
        </div>
    </div>
</div>
