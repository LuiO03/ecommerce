@php
    $optionsPayload = $options
        ->map(function ($option) {
            return [
                'id' => $option->id,
                'name' => $option->name,
                'is_color' => $option->isColor(),
                'features' => $option->features
                    ->map(
                        fn($feature) => [
                            'id' => $feature->id,
                            'value' => $feature->value, // nombre legible del color
                            'description' => $feature->description, // dato adicional del color
                        ],
                    )
                    ->values(),
            ];
        })
        ->values();

    $productModel = $product ?? null;

    $initialVariants = collect(optional($productModel)->variants ?? [])
        ->map(function ($variant) {
            return [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'price' => $variant->price,
                'stock' => $variant->stock,
                'status' => (bool) $variant->status,
                'features' => $variant->features
                    ->map(
                        fn($feature) => [
                            'id' => $feature->id,
                            'option_id' => $feature->option_id,
                            'value' => $feature->value,
                            'description' => $feature->description,
                        ],
                    )
                    ->values(),
            ];
        })
        ->values();
@endphp

<div class="form-body">
    <div class="card-header flex items-center justify-between gap-4">
        <div>
            <span class="card-title">Variantes</span>
            <p class="card-description">
                Administra las variantes de este producto.
            </p>
        </div>

        <button type="button" class="boton-form boton-accent" id="addVariantBtn" data-action="open-variant-modal">
            <span class="boton-form-icon"><i class="ri-add-box-fill"></i></span>
            <span class="boton-form-text">Agregar variante</span>
        </button>
    </div>
    <div class="form-row-fit">
        <div id="variantsContainer" class="variants-container" data-options='@json($optionsPayload)'
            data-initial-variants='@json($initialVariants)'>
            <div class="tabla-wrapper">
                <table class="tabla-general w-full tabla-normal" id="table">
                    <thead>
                        <tr>
                            <th class="column-variant-options-th">Variante</th>
                            <th class="column-variant-sku-th">SKU variante</th>
                            <th class="column-variant-price-th">Precio (S/)</th>
                            <th class="column-variant-stock-th">Stock</th>
                            <th class="column-variant-status-th">Estado</th>
                            <th class="column-variant-actions-th">Acciones</th>
                        </tr>
                    </thead>
                    <tbody data-role="variants-body">
                        <tr id="variantsEmpty" class="variants-empty-row">
                            <td colspan="6" class="text-muted-td text-center">
                                <div class="tabla-no-data">
                                    <i class="ri-folder-warning-line"></i>
                                    <span>
                                        No hay variantes registradas para este producto.
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="variantCrudModal" class="variant-crud-modal" hidden aria-hidden="true" role="dialog" aria-modal="true"
    aria-labelledby="variantModalTitle">
    <div class="variant-crud-modal-backdrop" data-action="close-variant-modal"></div>

    <div class="variant-crud-modal-dialog">
        <div class="variant-crud-modal-header" id="variantModalHeader">
            <h6 id="variantModalTitle" class="variant-crud-modal-title">Agregar variante</h6>
            <button type="button" class="confirm-close ripple-btn" title="Cerrar" data-action="close-variant-modal">
                <i class="ri-close-line"></i>
            </button>
        </div>


        <div class="variant-crud-modal-body ripple-card">
            <div class="card-header">
                <span class="card-title">Detalles de la variante</span>
                <p class="card-description">
                    Completa los campos para agregar una nueva variante o editar una existente.
                </p>
            </div>

            <div id="variantModalOptions" class="form-row-fill"></div>
            <div class="form-row-fill">
                <div class="input-group">
                    <label for="variantModalPrice" class="label-form">Precio (S/)</label>
                    <div class="input-icon-container">
                        <i class="ri-price-tag-3-line input-icon"></i>
                        <input type="number" id="variantModalPrice" class="input-form" min="0" step="0.01"
                            placeholder="Precio opcional" data-validate="minValue:0">
                    </div>
                </div>
                <div class="input-group">
                    <label for="variantModalStock" class="label-form">Stock</label>
                    <div class="input-icon-container">
                        <i class="ri-stack-line input-icon"></i>
                        <input type="number" id="variantModalStock" class="input-form" min="0" step="1"
                            placeholder="0" data-validate="minValue:0">
                    </div>
                </div>
            </div>
            <div class="form-row-fill">


                <div class="input-group">
                    <label class="label-form">Estado</label>
                    <div class="binary-switch binary-switch-inline">
                        <input type="radio" name="variantModalStatus" id="variantModalStatusActive" value="1"
                            class="switch-input switch-input-on" checked>
                        <input type="radio" name="variantModalStatus" id="variantModalStatusInactive"
                            value="0" class="switch-input switch-input-off">
                        <div class="switch-slider"></div>
                        <label for="variantModalStatusActive" class="switch-label switch-label-on">
                            <i class="ri-checkbox-circle-line"></i> Activo
                        </label>
                        <label for="variantModalStatusInactive" class="switch-label switch-label-off">
                            <i class="ri-close-circle-line"></i> Inactivo
                        </label>
                    </div>
                </div>
            </div>
            <p id="variantModalError" class="variant-modal-error" hidden></p>
        </div>

        <div class="variant-crud-modal-footer confirm-actions">
            <button type="button" class="boton boton-modal-close" data-action="close-variant-modal">
                <span class="boton-icon text-base"><i class="ri-close-line"></i></span>
                <span class="boton-text">Cancelar</span>
            </button>
            <button type="button" class="boton bg-success" id="saveVariantBtn">
                <span class="boton-icon"><i class="ri-check-double-line"></i></span>
                <span class="boton-text">Guardar variante</span>
            </button>
        </div>
    </div>
</div>
