@php
    $optionsPayload = $options
        ->map(function ($option) {
            return [
                'id' => $option->id,
                'name' => $option->name,
                'features' => $option->features
                    ->map(
                        fn($feature) => [
                            'id' => $feature->id,
                            'value' => $feature->value,
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
                        ],
                    )
                    ->values(),
            ];
        })
        ->values();
@endphp

<div class="form-body">
    <div class="form-row-fit">
        <div class="input-group w-full">
            <label class="label-form">
                Opciones y variantes
            </label>
            <p class="help-text mb-2">
                Selecciona qué opciones (por ejemplo, Color, Talla) usará este producto y genera automáticamente las
                variantes a partir de sus combinaciones.
            </p>

            <div id="productOptionsContainer" class="product-options-container"></div>

            <div class="variants-toolbar mt-3 flex gap-2 flex-wrap">
                <button type="button" class="boton boton-secondary" id="generateVariantsBtn">
                    <span class="boton-icon"><i class="ri-shape-2-line"></i></span>
                    <span class="boton-text">Generar variantes automáticamente</span>
                </button>
                <button type="button" class="boton boton-primary" id="addVariantBtn">
                    <span class="boton-icon"><i class="ri-add-box-fill"></i></span>
                    <span class="boton-text">Agregar variante manual</span>
                </button>
            </div>

            <div id="variantsEmpty" class="variants-empty mt-3 text-muted-td">
                No hay variantes configuradas. Usa "Generar variantes" o "Agregar variante manual".
            </div>

            <div id="variantsContainer" class="variants-container mt-3" data-options='@json($optionsPayload)'
                data-initial-variants='@json($initialVariants)'>
            </div>
        </div>
    </div>
</div>

<script type="text/template" id="variantRowTemplate">
    <div class="variant-row" data-index="__INDEX__">
        <div class="variants-table-wrapper mt-3">
                <table class="tabla tabla-variants">
                    <thead>
                        <tr>
                            <th class="column-variant-options">Variante</th>
                            <th class="column-variant-sku">SKU variante</th>
                            <th class="column-variant-price">Precio (S/)</th>
                            <th class="column-variant-stock">Stock</th>
                            <th class="column-variant-status">Estado</th>
                            <th class="column-variant-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="variantsContainer">
                        <tr>
                            <input type="hidden" name="variants[__INDEX__][id]" value="__ID__">
                            <td class="column-variant-options">__OPTIONS_LABEL__</td>
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
                                <button type="button" class="boton-sm boton-danger" data-action="remove-variant">
                                    <span class="boton-sm-icon"><i class="ri-delete-bin-6-fill"></i></span>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        <div class="variant-hidden-features" data-role="features-container"></div>
    </div>
</script>
