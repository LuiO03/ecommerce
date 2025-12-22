<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-warning"><i class="ri-edit-circle-line"></i></div>
        <div class="page-edit-title">
            <span class="page-subtitle">Editar Producto</span>
            {{ $product->name }}
        </div>
    </x-slot>

    <x-slot name="action">
        <a href="{{ route('admin.products.index') }}" class="boton boton-secondary">
            <span class="boton-icon"><i class="ri-arrow-go-back-line"></i></span>
            <span class="boton-text">Volver</span>
        </a>
        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="delete-form"
            data-entity="producto" style="margin: 0;">
            @csrf
            @method('DELETE')
            <button class="boton boton-danger" type="submit">
                <span class="boton-icon"><i class="ri-delete-bin-6-fill"></i></span>
                <span class="boton-text">Eliminar</span>
            </button>
        </form>
    </x-slot>

    @php
        $sortedImages = $product->images->sortBy('order');
    @endphp

    <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data"
        class="form-container" autocomplete="off" id="productForm">
        @csrf
        @method('PUT')

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

        <x-alert type="info" title="Información:" :dismissible="true" :items="['Los campos con asterisco (<i class=\'ri-asterisk text-accent\'></i>) son obligatorios.']" />

        <div class="form-body">
            <div class="form-row-fill">
                <div class="input-group">
                    <label for="category_id" class="label-form">
                        Categoría
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-archive-stack-line input-icon"></i>
                        <select name="category_id" id="category_id" class="select-form" required
                            data-validate="required|selected">
                            <option value="" disabled>Seleccione una categoría</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ (int) old('category_id', $product->category_id) === $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <i class="ri-arrow-down-s-line select-arrow"></i>
                    </div>
                </div>
                <div class="input-group">
                    <label for="sku" class="label-form">
                        SKU
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-hashtag input-icon"></i>
                        <input type="text" name="sku" id="sku" class="input-form" required
                            value="{{ old('sku', $product->sku) }}" placeholder="Ej. PROD-001"
                            data-validate="required|min:3|max:100">
                    </div>
                </div>
                <div class="input-group">
                    <label for="name" class="label-form">
                        Nombre
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-price-tag-3-line input-icon"></i>
                        <input type="text" name="name" id="name" class="input-form" required
                            value="{{ old('name', $product->name) }}" placeholder="Nombre del producto"
                            data-validate="required|min:3|max:255">
                    </div>
                </div>
                <div class="input-group">
                    <label class="label-form">
                        Estado
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="binary-switch">
                        <input type="radio" name="status" id="statusActive" value="1"
                            class="switch-input switch-input-on"
                            {{ old('status', (int) $product->status) == 1 ? 'checked' : '' }}>
                        <input type="radio" name="status" id="statusInactive" value="0"
                            class="switch-input switch-input-off"
                            {{ old('status', (int) $product->status) == 0 ? 'checked' : '' }}>
                        <div class="switch-slider"></div>
                        <label for="statusActive" class="switch-label switch-label-on"><i
                                class="ri-checkbox-circle-line"></i> Activo</label>
                        <label for="statusInactive" class="switch-label switch-label-off"><i
                                class="ri-close-circle-line"></i> Inactivo</label>
                    </div>
                </div>
            </div>

            <div class="form-row-fit">
                <div class="input-group">
                    <label for="price" class="label-form">
                        Precio (S/)
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-currency-line input-icon"></i>
                        <input type="number" name="price" id="price" class="input-form" required min="0"
                            step="0.01" value="{{ old('price', $product->price) }}" placeholder="0.00"
                            data-validate="required|minValue:0">
                    </div>
                </div>
                <div class="input-group">
                    <label for="discount" class="label-form">Descuento (S/)</label>
                    <div class="input-icon-container">
                        <i class="ri-discount-percent-line input-icon"></i>
                        <input type="number" name="discount" id="discount" class="input-form" min="0"
                            step="0.01" value="{{ old('discount', $product->discount) }}" placeholder="Opcional"
                            data-validate="minValue:0">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-columns-row">
            <div class="form-column">
                <div class="input-group">
                    <label for="description" class="label-form">Descripción</label>
                    <div class="input-icon-container">
                        <textarea name="description" id="description" class="textarea-form" rows="4"
                            placeholder="Describe el producto" data-validate="max:5000">{{ old('description', $product->description) }}</textarea>
                        <i class="ri-file-text-line input-icon textarea-icon"></i>
                    </div>
                </div>
                <div class="image-upload-section w-full">
                    <label class="label-form">Galería de imágenes</label>
                    <div class="custom-dropzone" id="galleryDropzone">
                        <i class="ri-multi-image-line"></i>
                        <p>Arrastra imágenes aquí o haz clic</p>
                        <input type="file" name="gallery[]" id="galleryInput" accept="image/*" multiple hidden
                            data-validate="fileRequired|image|maxSize:2048">
                    </div>
                    <div id="galleryPreviewContainer" class="preview-container">
                        @foreach ($sortedImages as $image)
                            @php
                                $fullPath = $image->path ? public_path('storage/' . $image->path) : null;
                                $exists = $fullPath && file_exists($fullPath);
                                $imageUrl = $exists ? asset('storage/' . $image->path) : asset('storage/default.png');
                                $altText = $image->alt ?? $product->name;
                            @endphp
                            <div class="preview-item existing-image" data-type="existing"
                                data-id="{{ $image->id }}" data-key="existing-{{ $image->id }}" data-main="{{ $image->is_main ? 'true' : 'false' }}">
                                <button type="button" class="drag-handle" title="Reordenar imagen">
                                    <i class="ri-draggable"></i>
                                </button>
                                @if ($exists)
                                    <img src="{{ $imageUrl }}" alt="{{ $altText }}">
                                @else
                                    <div class="image-not-found-block">
                                        <i class="ri-file-close-line"></i>
                                        <p>Imagen no encontrada</p>
                                    </div>
                                @endif
                                <div class="overlay">
                                    <span class="file-size"></span>
                                    <div class="overlay-actions">
                                        <button type="button" class="mark-main-btn" title="Marcar como principal">
                                            <i class="ri-star-smile-fill"></i>
                                            <span>Principal</span>
                                        </button>
                                        <button type="button" class="delete-btn delete-existing-gallery"
                                            data-id="{{ $image->id }}" title="Eliminar imagen">
                                            <span class="boton-icon"><i class="ri-delete-bin-6-fill"></i></span>
                                            <span class="boton-text">Eliminar</span>
                                        </button>
                                    </div>
                                </div>
                                <span class="primary-badge"
                                    style="{{ $image->is_main ? 'display:flex;' : 'display:none;' }}">
                                    <i class="ri-star-fill"></i>
                                    Principal
                                </span>
                            </div>
                        @endforeach
                    </div>
                    <div id="removedGalleryContainer"></div>
                    <input type="hidden" name="primary_image" id="primaryImageInput" value="">
                </div>
            </div>
        </div>

        <section class="product-variants-section">
            <div class="product-variants-section-inner">
                <div class="variants-header">
                    <div class="variants-header-main">
                        <div class="variants-header-icon">
                            <i class="ri-shape-2-line"></i>
                        </div>
                        <div class="variants-header-text">
                            <h3>Variantes del producto</h3>
                            <p>
                                Ajusta combinaciones como <span>talla</span>, <span>color</span> o presentación, con su
                                propio SKU, precio y stock.
                            </p>
                        </div>
                    </div>
                    <div class="variants-header-actions">
                        <span class="variants-summary-chip" data-role="variants-summary">
                            <i class="ri-shape-line"></i>
                            <span data-role="variants-count">0</span>
                            variantes
                        </span>
                        <button type="button" class="boton boton-primary" id="addVariantBtn">
                            <span class="boton-icon"><i class="ri-add-circle-line"></i></span>
                            <span class="boton-text">Agregar variante</span>
                        </button>
                    </div>
                </div>

                <div class="variants-body" id="variantsContainer">
                    <p class="variants-empty" id="variantsEmpty">
                        <i class="ri-lightbulb-flash-line"></i>
                        Aún no has agregado variantes para este producto. Puedes crearlas para controlar stock y precios por
                        combinación.
                    </p>

                    @php
                        $variants = $product->variants ?? collect();
                    @endphp

                    @foreach ($variants as $variant)
                        @php
                            $rowKey = 'e' . $variant->id;
                            $selectedFeatureIds = collect(old("variants.$rowKey.features", $variant->features->pluck('id')->all()))
                                ->map(fn ($id) => (int) $id)
                                ->all();
                        @endphp
                        <div class="variant-row" data-role="variant-row">
                            <div class="variant-row-header">
                                <div class="variant-row-title">
                                    <span class="variant-chip">
                                        <i class="ri-shape-2-line"></i>
                                        <span data-role="variant-index">#{{ $loop->iteration }}</span>
                                    </span>
                                    <span class="variant-subtitle">SKU específico, precio opcional y stock dedicado.</span>
                                </div>
                                <button type="button" class="variant-remove-btn" data-action="remove-variant"
                                    title="Quitar variante">
                                    <i class="ri-delete-bin-6-line"></i>
                                </button>
                            </div>

                            <div class="variant-row-fields">
                                <div class="input-group">
                                    <label for="variant_sku_{{ $rowKey }}" class="label-form">SKU variante</label>
                                    <div class="input-icon-container">
                                        <i class="ri-hashtag input-icon"></i>
                                        <input type="text" id="variant_sku_{{ $rowKey }}"
                                            name="variants[{{ $rowKey }}][sku]" class="input-form"
                                            value="{{ old("variants.$rowKey.sku", $variant->sku) }}"
                                            placeholder="Ej. PROD-001-RED-M" data-validate="max:100">
                                    </div>
                                </div>
                                <div class="input-group">
                                    <label for="variant_price_{{ $rowKey }}" class="label-form">Precio (S/)</label>
                                    <div class="input-icon-container">
                                        <i class="ri-currency-line input-icon"></i>
                                        <input type="number" id="variant_price_{{ $rowKey }}"
                                            name="variants[{{ $rowKey }}][price]" class="input-form" min="0"
                                            step="0.01"
                                            value="{{ old("variants.$rowKey.price", $variant->price) }}"
                                            placeholder="Usar precio base" data-validate="minValue:0">
                                    </div>
                                </div>
                                <div class="input-group">
                                    <label for="variant_stock_{{ $rowKey }}" class="label-form">Stock</label>
                                    <div class="input-icon-container">
                                        <i class="ri-stack-line input-icon"></i>
                                        <input type="number" id="variant_stock_{{ $rowKey }}"
                                            name="variants[{{ $rowKey }}][stock]" class="input-form" min="0"
                                            step="1"
                                            value="{{ old("variants.$rowKey.stock", $variant->stock) }}"
                                            placeholder="0" data-validate="minValue:0">
                                    </div>
                                </div>
                                <div class="input-group variant-status-group">
                                    <label class="label-form">Estado</label>
                                    <div class="binary-switch">
                                        @php
                                            $statusOld = old("variants.$rowKey.status", $variant->status ? 1 : 0);
                                        @endphp
                                        <input type="radio" name="variants[{{ $rowKey }}][status]"
                                            id="variant_status_on_{{ $rowKey }}" value="1"
                                            class="switch-input switch-input-on"
                                            {{ (int) $statusOld === 1 ? 'checked' : '' }}>
                                        <input type="radio" name="variants[{{ $rowKey }}][status]"
                                            id="variant_status_off_{{ $rowKey }}" value="0"
                                            class="switch-input switch-input-off"
                                            {{ (int) $statusOld === 0 ? 'checked' : '' }}>
                                        <div class="switch-slider"></div>
                                        <label for="variant_status_on_{{ $rowKey }}"
                                            class="switch-label switch-label-on">
                                            <i class="ri-checkbox-circle-line"></i> Activa
                                        </label>
                                        <label for="variant_status_off_{{ $rowKey }}"
                                            class="switch-label switch-label-off">
                                            <i class="ri-close-circle-line"></i> Inactiva
                                        </label>
                                    </div>
                                </div>
                            </div>

                            @if (isset($options) && $options->isNotEmpty())
                                <div class="variant-options">
                                    @foreach ($options as $option)
                                        @php
                                            $isColorOption = $option->isColor();
                                        @endphp
                                        <div class="variant-option-group" data-option-id="{{ $option->id }}">
                                            <div class="variant-option-heading">
                                                <span class="variant-option-name">
                                                    <i class="{{ $isColorOption ? 'ri-palette-line' : 'ri-price-tag-3-line' }}"></i>
                                                    {{ $option->name }}
                                                </span>
                                            </div>
                                            <div class="variant-option-chips">
                                                @foreach ($option->features as $feature)
                                                    @php
                                                        $isChecked = in_array($feature->id, $selectedFeatureIds, true);
                                                    @endphp
                                                    <label class="variant-feature-chip{{ $isChecked ? ' is-selected' : '' }}">
                                                        <input type="checkbox" class="variant-feature-input" value="{{ $feature->id }}"
                                                            name="variants[{{ $rowKey }}][features][]"
                                                            {{ $isChecked ? 'checked' : '' }}>
                                                        <span class="chip-label">
                                                            {{ $feature->value }}
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <input type="hidden" name="variants[{{ $rowKey }}][id]" value="{{ $variant->id }}">
                        </div>
                    @endforeach
                </div>

                <template id="variantRowTemplate">
                    <div class="variant-row" data-role="variant-row">
                        <div class="variant-row-header">
                            <div class="variant-row-title">
                                <span class="variant-chip">
                                    <i class="ri-shape-2-line"></i>
                                    <span data-role="variant-index">#1</span>
                                </span>
                                <span class="variant-subtitle">SKU específico, precio opcional y stock dedicado.</span>
                            </div>
                            <button type="button" class="variant-remove-btn" data-action="remove-variant"
                                title="Quitar variante">
                                <i class="ri-delete-bin-6-line"></i>
                            </button>
                        </div>

                        <div class="variant-row-fields">
                            <div class="input-group">
                                <label for="variant_sku___KEY__" class="label-form">SKU variante</label>
                                <div class="input-icon-container">
                                    <i class="ri-hashtag input-icon"></i>
                                    <input type="text" id="variant_sku___KEY__" name="variants[__KEY__][sku]"
                                        class="input-form" placeholder="Ej. PROD-001-RED-M" data-validate="max:100">
                                </div>
                            </div>
                            <div class="input-group">
                                <label for="variant_price___KEY__" class="label-form">Precio (S/)</label>
                                <div class="input-icon-container">
                                    <i class="ri-currency-line input-icon"></i>
                                    <input type="number" id="variant_price___KEY__" name="variants[__KEY__][price]"
                                        class="input-form" min="0" step="0.01" placeholder="Usar precio base"
                                        data-validate="minValue:0">
                                </div>
                            </div>
                            <div class="input-group">
                                <label for="variant_stock___KEY__" class="label-form">Stock</label>
                                <div class="input-icon-container">
                                    <i class="ri-stack-line input-icon"></i>
                                    <input type="number" id="variant_stock___KEY__" name="variants[__KEY__][stock]"
                                        class="input-form" min="0" step="1" placeholder="0"
                                        data-validate="minValue:0">
                                </div>
                            </div>
                            <div class="input-group variant-status-group">
                                <label class="label-form">Estado</label>
                                <div class="binary-switch">
                                    <input type="radio" name="variants[__KEY__][status]"
                                        id="variant_status_on___KEY__" value="1"
                                        class="switch-input switch-input-on" checked>
                                    <input type="radio" name="variants[__KEY__][status]"
                                        id="variant_status_off___KEY__" value="0"
                                        class="switch-input switch-input-off">
                                    <div class="switch-slider"></div>
                                    <label for="variant_status_on___KEY__" class="switch-label switch-label-on">
                                        <i class="ri-checkbox-circle-line"></i> Activa
                                    </label>
                                    <label for="variant_status_off___KEY__" class="switch-label switch-label-off">
                                        <i class="ri-close-circle-line"></i> Inactiva
                                    </label>
                                </div>
                            </div>
                        </div>

                        @if (isset($options) && $options->isNotEmpty())
                            <div class="variant-options">
                                @foreach ($options as $option)
                                    @php
                                        $isColorOption = $option->isColor();
                                    @endphp
                                    <div class="variant-option-group" data-option-id="{{ $option->id }}">
                                        <div class="variant-option-heading">
                                            <span class="variant-option-name">
                                                <i class="{{ $isColorOption ? 'ri-palette-line' : 'ri-price-tag-3-line' }}"></i>
                                                {{ $option->name }}
                                            </span>
                                        </div>
                                        <div class="variant-option-chips">
                                            @foreach ($option->features as $feature)
                                                <label class="variant-feature-chip">
                                                    <input type="checkbox" class="variant-feature-input" value="{{ $feature->id }}"
                                                        name="variants[__KEY__][features][]">
                                                    <span class="chip-label">
                                                        {{ $feature->value }}
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <input type="hidden" name="variants[__KEY__][id]" value="">
                    </div>
                </template>
            </div>
        </section>

        <div class="form-footer">
            <a href="{{ url()->previous() }}" class="boton-form boton-volver">
                <span class="boton-form-icon">
                    <i class="ri-arrow-left-circle-fill"></i>
                </span>
                <span class="boton-form-text">Cancelar</span>
            </a>
            <button type="submit" class="boton-form boton-accent" id="submitBtn">
                <span class="boton-form-icon"><i class="ri-loop-left-line"></i></span>
                <span class="boton-form-text">Actualizar Producto</span>
            </button>
        </div>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                initSubmitLoader({
                    formId: 'productForm',
                    buttonId: 'submitBtn',
                    loadingText: 'Actualizando...'
                });

                initFormValidator('#productForm', {
                    validateOnBlur: true,
                    validateOnInput: false,
                    scrollToFirstError: true
                });

                if (window.initGalleryEditWithConfig) {
                    window.initGalleryEditWithConfig({
                        dropzoneId: 'galleryDropzone',
                        inputId: 'galleryInput',
                        previewContainerId: 'galleryPreviewContainer',
                        primaryInputId: 'primaryImageInput',
                        formId: 'productForm',
                        deletionMode: 'hidden-inputs',
                        removedContainerId: 'removedGalleryContainer',
                        removedFieldName: 'remove_gallery[]',
                        existingDeleteSelector: '.delete-existing-gallery',
                        labelsNew: {
                            markTitle: 'Marcar como imagen principal',
                            markText: 'Principal',
                            markIconClass: 'ri-star-smile-fill',
                            badgeIconClass: 'ri-star-fill',
                            badgeText: 'Principal',
                            deleteTitle: 'Eliminar imagen',
                            deleteIconClass: 'ri-delete-bin-6-fill',
                            deleteText: 'Eliminar'
                        }
                    });
                }

                if (window.initProductVariantsManager) {
                    window.initProductVariantsManager({
                        containerId: 'variantsContainer',
                        emptyStateId: 'variantsEmpty',
                        addButtonId: 'addVariantBtn',
                        templateId: 'variantRowTemplate',
                    });
                }
            });
        </script>
    @endpush
</x-admin-layout>
