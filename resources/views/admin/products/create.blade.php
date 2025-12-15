<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-success"><i class="ri-add-circle-line"></i></div>
        Nuevo Producto
    </x-slot>

    <x-slot name="action">
        <a href="{{ route('admin.products.index') }}" class="boton boton-secondary">
            <span class="boton-icon"><i class="ri-arrow-go-back-line"></i></span>
            <span class="boton-text">Volver</span>
        </a>
    </x-slot>

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="form-container" autocomplete="off" id="productForm">
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

        <div class="form-row">
            <div class="image-upload-section">
                <label class="label-form">Imagen principal</label>
                <input type="file" name="main_image" id="mainImage" class="file-input" accept="image/*" data-validate="image|maxSize:2048">

                <div class="image-preview-zone" id="mainImagePreviewZone">
                    <div class="image-placeholder" id="mainImagePlaceholder">
                        <i class="ri-image-add-line"></i>
                        <p>Arrastra una imagen aquí</p>
                        <span>o haz clic para seleccionar</span>
                    </div>
                    <img id="mainImagePreview" class="image-preview image-pulse" style="display: none;" alt="Vista previa">
                    <div class="image-overlay" id="mainImageOverlay" style="display: none;">
                        <button type="button" class="overlay-btn" id="mainImageChangeBtn" title="Cambiar imagen">
                            <i class="ri-upload-2-line"></i>
                            <span>Cambiar</span>
                        </button>
                        <button type="button" class="overlay-btn overlay-btn-danger" id="mainImageRemoveBtn" title="Eliminar imagen">
                            <i class="ri-delete-bin-line"></i>
                            <span>Eliminar</span>
                        </button>
                    </div>
                </div>
                <div class="image-filename" id="mainImageFilename" style="display: none;">
                    <i class="ri-file-image-line"></i>
                    <span id="mainImageFilenameText"></span>
                </div>
            </div>
        </div>

        <div class="form-row">
            <div class="input-group">
                <label for="category_id" class="label-form">
                    Categoría
                    <i class="ri-asterisk text-accent"></i>
                </label>
                <div class="input-icon-container">
                    <i class="ri-archive-stack-line input-icon"></i>
                    <select name="category_id" id="category_id" class="select-form" required data-validate="required|selected">
                        <option value="" disabled {{ old('category_id') ? '' : 'selected' }}>Seleccione una categoría</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ (int) old('category_id') === $category->id ? 'selected' : '' }}>
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
                    <input type="text" name="sku" id="sku" class="input-form" required value="{{ old('sku') }}" placeholder="Ej. PROD-001" data-validate="required|min:3|max:100">
                </div>
            </div>
            <div class="input-group">
                <label for="name" class="label-form">
                    Nombre
                    <i class="ri-asterisk text-accent"></i>
                </label>
                <div class="input-icon-container">
                    <i class="ri-price-tag-3-line input-icon"></i>
                    <input type="text" name="name" id="name" class="input-form" required value="{{ old('name') }}" placeholder="Nombre del producto" data-validate="required|min:3|max:255">
                </div>
            </div>
            <div class="input-group">
                <label class="label-form">
                    Estado
                    <i class="ri-asterisk text-accent"></i>
                </label>
                <div class="binary-switch">
                    <input type="radio" name="status" id="statusActive" value="1" class="switch-input switch-input-on" {{ old('status', 1) == 1 ? 'checked' : '' }}>
                    <input type="radio" name="status" id="statusInactive" value="0" class="switch-input switch-input-off" {{ old('status') == 0 ? 'checked' : '' }}>
                    <div class="switch-slider"></div>
                    <label for="statusActive" class="switch-label switch-label-on"><i class="ri-checkbox-circle-line"></i> Activo</label>
                    <label for="statusInactive" class="switch-label switch-label-off"><i class="ri-close-circle-line"></i> Inactivo</label>
                </div>
            </div>
        </div>

        <div class="form-row">
            <div class="input-group">
                <label for="price" class="label-form">
                    Precio (S/)
                    <i class="ri-asterisk text-accent"></i>
                </label>
                <div class="input-icon-container">
                    <i class="ri-currency-line input-icon"></i>
                    <input type="number" name="price" id="price" class="input-form" required min="0" step="0.01" value="{{ old('price') }}" placeholder="0.00" data-validate="required|minValue:0">
                </div>
            </div>
            <div class="input-group">
                <label for="discount" class="label-form">Descuento (S/)</label>
                <div class="input-icon-container">
                    <i class="ri-discount-percent-line input-icon"></i>
                    <input type="number" name="discount" id="discount" class="input-form" min="0" step="0.01" value="{{ old('discount') }}" placeholder="Opcional" data-validate="minValue:0">
                </div>
            </div>
            <div class="input-group">
                <label for="description" class="label-form">Descripción</label>
                <div class="input-icon-container">
                    <textarea name="description" id="description" class="textarea-form" rows="4" placeholder="Describe el producto" data-validate="max:5000">{{ old('description') }}</textarea>
                    <i class="ri-file-text-line input-icon textarea-icon"></i>
                </div>
            </div>
        </div>

        <div class="form-row">
            <div class="image-upload-section w-full">
                <label class="label-form">Galería de imágenes</label>
                <div class="custom-dropzone" id="galleryDropzone">
                    <i class="ri-multi-image-line"></i>
                    <p>Arrastra imágenes aquí o haz clic</p>
                    <input type="file" name="gallery[]" id="galleryInput" accept="image/*" multiple hidden data-validate="image|maxSize:2048">
                </div>
                <div id="galleryPreviewContainer" class="preview-container"></div>
            </div>
        </div>

        <div class="form-footer">
            <a href="{{ route('admin.products.index') }}" class="boton-form boton-volver">
                <span class="boton-form-icon"><i class="ri-arrow-left-circle-fill"></i></span>
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
                initImageUpload({
                    mode: 'create',
                    inputId: 'mainImage',
                    previewZoneId: 'mainImagePreviewZone',
                    placeholderId: 'mainImagePlaceholder',
                    previewId: 'mainImagePreview',
                    overlayId: 'mainImageOverlay',
                    changeBtnId: 'mainImageChangeBtn',
                    removeBtnId: 'mainImageRemoveBtn',
                    filenameContainerId: 'mainImageFilename',
                    filenameTextId: 'mainImageFilenameText'
                });

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

                const galleryDropzone = document.getElementById('galleryDropzone');
                const galleryInput = document.getElementById('galleryInput');
                const galleryPreviewContainer = document.getElementById('galleryPreviewContainer');
                let galleryFiles = [];

                const refreshGalleryInput = () => {
                    const dataTransfer = new DataTransfer();
                    galleryFiles.forEach(file => dataTransfer.items.add(file));
                    galleryInput.files = dataTransfer.files;
                };

                const formatFileSize = (bytes) => {
                    let size = bytes / 1024;
                    return size > 1024 ? `${(size / 1024).toFixed(2)} MB` : `${size.toFixed(1)} KB`;
                };

                const addGalleryPreview = (file) => {
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        const item = document.createElement('div');
                        item.classList.add('preview-item');
                        item.innerHTML = `
                            <img src="${event.target.result}" alt="${file.name}">
                            <div class="overlay">
                                <span class="file-size">${formatFileSize(file.size)}</span>
                                <button type="button" class="delete-btn" title="Eliminar imagen">
                                    <span class="boton-icon"><i class="ri-delete-bin-6-fill"></i></span>
                                    <span class="boton-text">Eliminar</span>
                                </button>
                            </div>
                        `;
                        item.querySelector('.delete-btn').addEventListener('click', (e) => {
                            e.stopPropagation();
                            galleryFiles = galleryFiles.filter(current => current !== file);
                            item.remove();
                            refreshGalleryInput();
                        });
                        galleryPreviewContainer.appendChild(item);
                    };
                    reader.readAsDataURL(file);
                };

                const handleGalleryFiles = (files) => {
                    [...files].forEach(file => {
                        if (!file.type.startsWith('image/')) {
                            return;
                        }
                        galleryFiles.push(file);
                        addGalleryPreview(file);
                    });
                    refreshGalleryInput();
                };

                galleryDropzone.addEventListener('click', () => galleryInput.click());
                galleryDropzone.addEventListener('dragover', (event) => {
                    event.preventDefault();
                    galleryDropzone.classList.add('dragover');
                });
                galleryDropzone.addEventListener('dragleave', () => {
                    galleryDropzone.classList.remove('dragover');
                });
                galleryDropzone.addEventListener('drop', (event) => {
                    event.preventDefault();
                    galleryDropzone.classList.remove('dragover');
                    handleGalleryFiles(event.dataTransfer.files);
                });
                galleryInput.addEventListener('change', (event) => handleGalleryFiles(event.target.files));
            });
        </script>
    @endpush
</x-admin-layout>
