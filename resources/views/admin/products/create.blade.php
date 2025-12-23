<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-success"><i class="ri-add-large-line"></i></div>
        Nuevo Producto
    </x-slot>

    <x-slot name="action">
        <a href="{{ route('admin.products.index') }}" class="boton boton-secondary">
            <span class="boton-icon"><i class="ri-arrow-go-back-line"></i></span>
            <span class="boton-text">Volver</span>
        </a>
    </x-slot>

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data"
        class="form-container" autocomplete="off" id="productForm">
        @csrf

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
                            <option value="" disabled {{ old('category_id') ? '' : 'selected' }}>Seleccione una
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
                    <label for="sku" class="label-form">
                        SKU
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-hashtag input-icon"></i>
                        <input type="text" name="sku" id="sku" class="input-form" required
                            value="{{ old('sku') }}" placeholder="Ej. PROD-001" data-validate="required|min:3|max:100">
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
                            value="{{ old('name') }}" placeholder="Nombre del producto"
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
                            class="switch-input switch-input-on" {{ old('status', 1) == 1 ? 'checked' : '' }}>
                        <input type="radio" name="status" id="statusInactive" value="0"
                            class="switch-input switch-input-off" {{ old('status') == 0 ? 'checked' : '' }}>
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
                            step="0.01" value="{{ old('price') }}" placeholder="0.00"
                            data-validate="required|minValue:0">
                    </div>
                </div>
                <div class="input-group">
                    <label for="discount" class="label-form">Descuento (S/)</label>
                    <div class="input-icon-container">
                        <i class="ri-discount-percent-line input-icon"></i>
                        <input type="number" name="discount" id="discount" class="input-form" min="0"
                            step="0.01" value="{{ old('discount') }}" placeholder="Opcional"
                            data-validate="minValue:0">
                    </div>
                </div>
            </div>
            <div class="form-row-fit">
                <div class="input-group">
                    <label for="description" class="label-form">Descripción</label>
                    <div class="input-icon-container">
                        <textarea name="description" id="description" class="textarea-form" rows="4"
                            placeholder="Describe el producto" data-validate="max:5000">{{ old('description') }}</textarea>
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
                    <div id="galleryPreviewContainer" class="preview-container"></div>
                    <input type="hidden" name="primary_image" id="primaryImageInput">
                    <div id="galleryAltContainer"></div>
                </div>
            </div>
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
                        templateId: 'variantRowTemplate',
                    });
                }
            });
        </script>
    @endpush
</x-admin-layout>
