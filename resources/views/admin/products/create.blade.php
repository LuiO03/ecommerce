@section('title', 'Nuevo producto')

<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-success"><i class="ri-add-large-line"></i></div>
        Nuevo Producto
    </x-slot>

    <x-slot name="action">
        <a href="{{ route('admin.products.index') }}" class="boton-form boton-accent">
            <span class="boton-form-icon"><i class="ri-arrow-go-back-line"></i></span>
            <span class="boton-form-text">Volver</span>
        </a>
    </x-slot>

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data"
        class="form-container" autocomplete="off" id="productForm">
        @csrf

        {{-- Banner de errores --}}
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

        <x-note-alert type="info" :dismissible="true">
            Los campos con asterisco (<i class="ri-asterisk text-accent"></i>) son obligatorios.
        </x-note-alert>

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
                            <option value="" disabled {{ old('category_id') ? '' : 'selected' }}>
                                Seleccione una
                                categoría</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ (int) old('category_id') === $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <i class="ri-arrow-down-s-line select-arrow"></i>
                    </div>
                </div>

                <div class="input-group">
                    <label for="brand_id" class="label-form">
                        Marca
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-bookmark-3-line input-icon"></i>
                        <select name="brand_id" id="brand_id" class="select-form" required
                            data-validate="required|selected">
                            <option value="" disabled {{ old('brand_id') ? '' : 'selected' }}>
                                Seleccione una marca</option>
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->id }}"
                                    {{ (int) old('brand_id') === $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                        <i class="ri-arrow-down-s-line select-arrow"></i>
                    </div>
                </div>

                <div class="input-group">
                    <label for="name" class="label-form">
                        Nombre
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-price-tag-2-line input-icon"></i>
                        <input type="text" name="name" id="name" class="input-form" required
                            value="{{ old('name') }}" placeholder="Nombre del producto"
                            data-validate="required|min:3|max:255">
                    </div>
                </div>

                <div class="input-group">
                    <label class="label-form">
                        Estado del producto
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="binary-switch">
                        <!-- Checkbox real -->
                        <input type="hidden" name="status" value="0">

                        <input type="checkbox" name="status" id="status" class="switch-input" value="1"
                            {{ old('status', 1) == 1 ? 'checked' : '' }} data-validate="required">

                        <!-- Labels visuales -->
                        <label for="status" class="switch-label switch-label-on">
                            <i class="ri-checkbox-circle-line"></i> Activo
                        </label>

                        <label for="status" class="switch-label switch-label-off">
                            <i class="ri-close-circle-line"></i> Inactivo
                        </label>

                        <div class="switch-slider"></div>
                    </div>
                </div>

                <div class="input-group">
                    <label for="price" class="label-form">
                        Precio (S/)
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-price-tag-3-line input-icon"></i>
                        <input type="number" name="price" id="price" class="input-form" required
                            min="0" step="0.01" value="{{ old('price') }}" placeholder="0.00"
                            data-validate="required|minValue:0">
                    </div>
                </div>

                <div class="input-group">
                    <label for="discount" class="label-form">Descuento (%)</label>
                    <div class="input-icon-container">
                        <i class="ri-discount-percent-line input-icon"></i>
                        <input type="number" name="discount" id="discount" class="input-form" min="0"
                            step="1" value="{{ old('discount') }}" placeholder="Opcional"
                            data-validate="minValue:0">
                    </div>
                </div>

                <div class="input-group">
                    <label for="min_stock" class="label-form">
                        Stock mínimo
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-stack-line input-icon"></i>
                        <input type="number" name="min_stock" id="min_stock" class="input-form" min="0"
                            step="1" value="{{ old('min_stock') }}" placeholder="Ej. 10"
                            data-validate="minValue:0">
                    </div>
                </div>
            </div>
            <div class="form-row-fit">
                <div class="input-group">
                    <label for="description" class="label-form">Descripción</label>
                    <div class="input-icon-container">
                        <textarea name="description" id="description" class="textarea-form" rows="4"
                            placeholder="Describe el producto" data-validate="max:500|min:3">{{ old('description') }}</textarea>
                        <i class="ri-file-text-line input-icon textarea-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-body">
            <div class="form-row-fit">
                <div class="image-upload-section w-full">
                    <label class="label-form">Galería de imágenes <i class="ri-asterisk text-accent"></i></label>
                    <div class="gallery-media-layout">
                        <div class="custom-dropzone" id="galleryDropzone">
                            <i class="ri-multi-image-line"></i>
                            <p>Arrastra una imagen aquí</p>
                            <span>o haz clic para seleccionar</span>
                            <span>Formatos: PNG, JPG, JPEG (máx. 3 MB)</span>
                            <input type="file" name="gallery[]" id="galleryInput" accept="image/*" multiple
                                hidden
                                data-validate="fileRequired|image|maxSizeMB:3|fileTypes:jpg,png,gif,webp|maxFiles:10">
                        </div>
                        <div id="galleryPreviewContainer" class="preview-container"></div>
                    </div>
                    <input type="hidden" name="primary_image" id="primaryImageInput">
                    <div id="galleryAltContainer"></div>
                </div>
            </div>
        </div>

        @include('admin.products.partials.variants-manager')

        <div class="form-footer">
            <a href="{{ url()->previous() }}" class="boton-form boton-volver">
                <span class="boton-form-icon">
                    <i class="ri-arrow-left-circle-fill"></i>
                </span>
                <span class="boton-form-text">Cancelar</span>
            </a>
            <button type="reset" class="boton-form boton-warning">
                <span class="boton-form-icon"><i class="ri-paint-brush-fill"></i></span>
                <span class="boton-form-text">Limpiar</span>
            </button>
            <button type="submit" class="boton-form boton-success" id="submitBtn">
                <span class="boton-form-icon"><i class="ri-save-3-fill"></i></span>
                <span class="boton-form-text">Crear Producto</span>
            </button>
        </div>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                initSubmitLoader({
                    formId: 'productForm',
                    buttonId: 'submitBtn',
                    loadingText: 'Guardando...'
                });

                initFormValidator('#productForm', {
                    validateOnBlur: true,
                    validateOnInput: false,
                    scrollToFirstError: true
                });

                if (window.initTabsManager) {
                    window.initTabsManager({
                        selector: '#productFormTabs'
                    });
                }

                if (window.initGalleryCreateWithConfig) {
                    window.initGalleryCreateWithConfig({
                        dropzoneId: 'galleryDropzone',
                        inputId: 'galleryInput',
                        previewContainerId: 'galleryPreviewContainer',
                        primaryInputId: 'primaryImageInput',
                        formId: 'productForm',
                        altContainerId: 'galleryAltContainer',
                        labels: {
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
                        baseSkuInputId: 'sku',
                    });
                }
            });
        </script>
    @endpush
</x-admin-layout>
