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
            <div class="form-row-fit">
                <div class="input-group">
                    <label for="description" class="label-form">Descripción</label>
                    <div class="input-icon-container">
                        <textarea name="description" id="description" class="textarea-form" rows="4"
                            placeholder="Describe el producto" data-validate="max:5000">{{ old('description', $product->description) }}</textarea>
                        <i class="ri-file-text-line input-icon textarea-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-body">
            <div class="form-row-fit">
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

        @include('admin.products.partials.variants-manager', [
            'product' => $product,
            'options' => $options,
        ])

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
                        optionsContainerId: 'productOptionsContainer',
                        generateButtonId: 'generateVariantsBtn',
                        baseSkuInputId: 'sku',
                    });
                }
            });
        </script>
    @endpush
</x-admin-layout>
