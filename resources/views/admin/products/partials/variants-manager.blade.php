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
                            'value' => $feature->value, // normalmente el HEX
                            'description' => $feature->description, // nombre legible del color
                        ],
                    )
                    ->values(),
            ];
        })
        ->values();

    $initialVariants = collect(optional($product)->variants ?? [])
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
    <div class="form-row-fit">
        <div class="input-group">

            <div class="card-header">
                <span class="card-title">Opciones</span>
                <p class="card-description">
                    Define las opciones y características que tendrán las variantes de este producto.
                </p>
            </div>

            <div id="productOptionsContainer" class="product-options-container"></div>

            <div class="variants-toolbar">
                <button type="button" class="boton boton-secondary" id="generateVariantsBtn">
                    <span class="boton-icon"><i class="ri-shape-2-line"></i></span>
                    <span class="boton-text">Generar variantes automáticamente</span>
                </button>
                <button type="button" class="boton boton-primary" id="addVariantBtn">
                    <span class="boton-icon"><i class="ri-add-box-fill"></i></span>
                    <span class="boton-text">Agregar variante manual</span>
                </button>
            </div>

        </div>
    </div>
</div>

<div class="form-body">
    <div class="card-header">
        <span class="card-title">Variantes</span>
        <p class="card-description">
            Administra las variantes de este producto. Puedes editar el SKU, precio, stock y estado de cada variante.
        </p>
    </div>
    <div class="form-row-fit">
        <div id="variantsContainer" class="variants-container" data-options='@json($optionsPayload)' data-initial-variants='@json($initialVariants)'>
            <div class="variants-table-wrapper">
                <table class="tabla tabla-variants">
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
                                <i class="ri-information-line"></i>
                                No hay variantes configuradas. Usa "Generar variantes" o "Agregar variante manual".
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<script type="text/template" id="variantRowTemplate">
    <tr class="variant-row" data-index="__INDEX__">
        <td class="column-variant-options">
            <input type="hidden" name="variants[__INDEX__][id]" value="__ID__">
            __OPTIONS_LABEL__
            <div class="variant-hidden-features" data-role="features-container"></div>
        </td>
        <td class="column-variant-sku">
            <div class="input-group">
                <div class="input-icon-container">
                    <i class="ri-hashtag input-icon"></i>
                    <input type="text" class="input-form" name="variants[__INDEX__][sku]" value="__SKU__"
                        placeholder="Ej. PROD-001-RED-M" data-role="variant-sku">
                </div>
            </div>
        </td>
        <td class="column-variant-price">
            <div class="input-group">
                <div class="input-icon-container">
                    <i class="ri-currency-line input-icon"></i>
                    <input type="number" class="input-form" name="variants[__INDEX__][price]" min="0"
                        step="0.01" value="__PRICE__" placeholder="Opcional" data-role="variant-price">
                </div>
            </div>
        </td>
        <td class="column-variant-stock">
            <div class="input-group">
                <div class="input-icon-container">
                    <i class="ri-stack-line input-icon"></i>
                    <input type="number" class="input-form" name="variants[__INDEX__][stock]" min="0"
                        step="1" value="__STOCK__" placeholder="0" data-role="variant-stock">
                </div>
            </div>
        </td>
        <td class="column-variant-status">
            <div class="input-group">
                <div class="switch-tabla-wrapper">
                    <input type="hidden" name="variants[__INDEX__][status]" value="0">
                    <label class="switch-tabla" title="Activar o desactivar variante">
                        <input type="checkbox" name="variants[__INDEX__][status]" value="1"
                            __STATUS_CHECKED__ data-role="variant-status">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
        </td>
        <td class="column-variant-actions">
            <div class="input-group">
            <button type="button" class="boton boton-danger" data-action="remove-variant" title="Eliminar variante">
                <span class="boton-text">Eliminar</span>
                <span class="boton-icon"><i class="ri-delete-bin-6-fill"></i></span>
            </button>
            </div>
        </td>
    </tr>
</script>
