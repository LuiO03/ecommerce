@php
    use App\Models\Option;
    use Illuminate\Support\Str;

    $optionInstance = $option ?? null;
    $requestedName = old('name', $optionInstance?->name);
    $isColorOption = $optionInstance?->isColor() ?? (Str::slug($requestedName ?? '') === Option::COLOR_SLUG);

    $featuresDataset = old('features');

    if ($featuresDataset === null) {
        $featuresDataset = $optionInstance
            ? $optionInstance->features->map(fn ($feature) => [
                'id' => $feature->id,
                'value' => $feature->value,
                'description' => $feature->description,
            ])->toArray()
            : [];
    }

    if (empty($featuresDataset)) {
        $featuresDataset = [
            ['id' => null, 'value' => '', 'description' => ''],
        ];
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
    <div class="form-column">
        <div class="input-group">
            <label for="name" class="label-form">
                Nombre de la opción
                <i class="ri-asterisk text-accent"></i>
            </label>
            <div class="input-icon-container">
                <i class="ri-donut-chart-line input-icon"></i>
                <input type="text"
                       id="name"
                       name="name"
                       class="input-form"
                       placeholder="Ej. Material"
                       value="{{ old('name', $optionInstance?->name) }}"
                       required
                       data-validate="required|min:2|max:120">
            </div>
        </div>

        <div class="input-group">
            <label for="description" class="label-form label-textarea">
                Descripción
            </label>
            <div class="input-icon-container">
                <textarea id="description"
                          name="description"
                          class="textarea-form"
                          rows="4"
                          placeholder="Describe brevemente cómo se usa esta opción"
                          data-validate="min:3|max:600">{{ old('description', $optionInstance?->description) }}</textarea>
                <i class="ri-file-text-line input-icon"></i>
            </div>
        </div>
    </div>

    <div class="form-column">
        <div class="option-meta-card">
            <div class="option-meta-icon">
                <i class="ri-settings-4-line"></i>
            </div>
            <div class="option-meta-copy">
                <h4>Buenas prácticas</h4>
                <ul>
                    <li>Utiliza nombres cortos y fáciles de recordar.</li>
                    <li>Para colores, usa códigos HEX (#RRGGBB).</li>
                    <li>Las tallas se recomiendan en mayúsculas.</li>
                </ul>
            </div>
        </div>

        <div class="option-meta-card">
            <div class="option-meta-icon option-meta-icon-secondary">
                <i class="ri-lightbulb-flash-line"></i>
            </div>
            <div class="option-meta-copy">
                <h4>Ayuda rápida</h4>
                <p>Mantén al menos un valor registrado. Puedes eliminar o añadir más valores en cualquier momento.</p>
            </div>
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
            <button type="button" class="boton-form boton-primary" id="addFeatureBtn">
                <span class="boton-form-icon"><i class="ri-add-circle-fill"></i></span>
                <span class="boton-form-text">Añadir valor</span>
            </button>
        </header>

        <div class="option-features-list"
                id="featureList"
                data-is-color="{{ $isColorOption ? 'true' : 'false' }}"
                data-color-slug="{{ Option::COLOR_SLUG }}"
                data-color-locked="{{ ($optionInstance?->slug === Option::COLOR_SLUG) ? 'true' : 'false' }}">
            @foreach ($featuresDataset as $index => $feature)
                @include('admin.options.partials.feature-item', [
                    'index' => $index,
                    'feature' => $feature,
                    'isColorOption' => $isColorOption,
                ])
            @endforeach
        </div>
    </div>
</div>


<script type="text/template" id="featureRowTemplate">
    @include('admin.options.partials.feature-template', ['isColorTemplate' => $isColorOption])
</script>
