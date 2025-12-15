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
        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="delete-form" data-entity="producto" style="margin: 0;">
            @csrf
            @method('DELETE')
            <button class="boton boton-danger" type="submit">
                <span class="boton-icon"><i class="ri-delete-bin-6-fill"></i></span>
                <span class="boton-text">Eliminar</span>
            </button>
        </form>
    </x-slot>

    @php
        $mainImage = $product->images->firstWhere('is_main', true);
        $mainImageExists = $mainImage && file_exists(public_path('storage/' . $mainImage->path));
        $galleryImages = $product->images->filter(fn($image) => !$image->is_main);
    @endphp

    <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="form-container" autocomplete="off" id="productForm">
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

        <div class="form-row">
            <div class="image-upload-section">
                <label class="label-form">Imagen principal</label>
                <input type="file" name="main_image" id="mainImage" class="file-input" accept="image/*" data-validate="image|maxSize:2048">
                <input type="hidden" name="remove_main_image" id="removeMainImageFlag" value="0">

                <div class="image-preview-zone {{ $mainImageExists ? 'has-image' : '' }}" id="mainImagePreviewZone">
                    <div class="image-placeholder" id="mainImagePlaceholder" style="{{ $mainImageExists || $mainImage ? 'display: none;' : 'display: flex;' }}">
                        <i class="ri-image-add-line"></i>
                        <p>Arrastra una imagen aquí</p>
                        <span>o haz clic para seleccionar</span>
                    </div>
                    <div class="image-error" id="mainImageError" style="{{ $mainImage && !$mainImageExists ? 'display: flex;' : 'display: none;' }}">
                        <i class="ri-folder-close-line"></i>
                        <p>Imagen no encontrada</p>
                        <span>Haz clic para subir una nueva</span>
                    </div>
                    <img id="mainImagePreview" class="image-preview image-pulse" style="{{ $mainImageExists ? 'display: block;' : 'display: none;' }}" src="{{ $mainImageExists ? asset('storage/' . $mainImage->path) : '' }}" alt="{{ $mainImageExists ? ($mainImage->alt ?? $product->name) : 'Imagen principal' }}">
                    <img id="mainImagePreviewNew" class="image-preview image-pulse" style="display: none;" alt="Nueva imagen">
                    <div class="image-overlay" id="mainImageOverlay" style="{{ $mainImageExists ? 'display: flex;' : 'display: none;' }}">
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
                <div class="image-filename" id="mainImageFilename" style="{{ $mainImageExists ? 'display: flex;' : 'display: none;' }}">
                    <i class="ri-file-image-line"></i>
                    <span id="mainImageFilenameText">{{ $mainImageExists ? basename($mainImage->path) : '' }}</span>
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
                        <option value="" disabled>Seleccione una categoría</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ (int) old('category_id', $product->category_id) === $category->id ? 'selected' : '' }}>
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
                    <input type="text" name="sku" id="sku" class="input-form" required value="{{ old('sku', $product->sku) }}" placeholder="Ej. PROD-001" data-validate="required|min:3|max:100">
                </div>
            </div>
            <div class="input-group">
                <label for="name" class="label-form">
                    Nombre
                    <i class="ri-asterisk text-accent"></i>
                </label>
                <div class="input-icon-container">
                    <i class="ri-price-tag-3-line input-icon"></i>
                    <input type="text" name="name" id="name" class="input-form" required value="{{ old('name', $product->name) }}" placeholder="Nombre del producto" data-validate="required|min:3|max:255">
                </div>
            </div>
            <div class="input-group">
                <label class="label-form">
                    Estado
                    <i class="ri-asterisk text-accent"></i>
                </label>
                <div class="binary-switch">
                    <input type="radio" name="status" id="statusActive" value="1" class="switch-input switch-input-on" {{ old('status', (int) $product->status) == 1 ? 'checked' : '' }}>
                    <input type="radio" name="status" id="statusInactive" value="0" class="switch-input switch-input-off" {{ old('status', (int) $product->status) == 0 ? 'checked' : '' }}>
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
                    <input type="number" name="price" id="price" class="input-form" required min="0" step="0.01" value="{{ old('price', $product->price) }}" placeholder="0.00" data-validate="required|minValue:0">
                </div>
            </div>
            <div class="input-group">
                <label for="discount" class="label-form">Descuento (S/)</label>
                <div class="input-icon-container">
                    <i class="ri-discount-percent-line input-icon"></i>
                    <input type="number" name="discount" id="discount" class="input-form" min="0" step="0.01" value="{{ old('discount', $product->discount) }}" placeholder="Opcional" data-validate="minValue:0">
                </div>
            </div>
            <div class="input-group">
                <label for="description" class="label-form">Descripción</label>
                <div class="input-icon-container">
                    <textarea name="description" id="description" class="textarea-form" rows="4" placeholder="Describe el producto" data-validate="max:5000">{{ old('description', $product->description) }}</textarea>
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
                <div id="galleryPreviewContainer" class="preview-container">
                    @foreach ($galleryImages as $image)
                        @php
                            $exists = file_exists(public_path('storage/' . $image->path));
                        @endphp
                        @if ($exists)
                            <div class="preview-item existing-image" data-id="{{ $image->id }}">
                                <img src="{{ asset('storage/' . $image->path) }}" alt="{{ $image->alt ?? $product->name }}">
                                <div class="overlay">
                                    <span class="file-size">Orden #{{ $image->order }}</span>
                                    <button type="button" class="delete-btn delete-existing-gallery" data-id="{{ $image->id }}" title="Eliminar imagen">
                                        <span class="boton-icon"><i class="ri-delete-bin-6-fill"></i></span>
                                        <span class="boton-text">Eliminar</span>
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="image-not-found-block existing-image" data-id="{{ $image->id }}">
                                <i class="ri-file-close-line"></i>
                                <p>Imagen no encontrada</p>
                                <button type="button" class="boton boton-danger boton-sm delete-existing-gallery" data-id="{{ $image->id }}">
                                    <span class="boton-icon"><i class="ri-delete-bin-6-fill"></i></span>
                                    <span class="boton-text">Eliminar</span>
                                </button>
                            </div>
                        @endif
                    @endforeach
                </div>
                <div id="removedGalleryContainer"></div>
            </div>
        </div>

        <div class="form-footer">
            <a href="{{ route('admin.products.index') }}" class="boton-form boton-volver">
                <span class="boton-form-icon"><i class="ri-arrow-left-circle-fill"></i></span>
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
                initImageUpload({
                    mode: 'edit',
                    inputId: 'mainImage',
                    previewZoneId: 'mainImagePreviewZone',
                    placeholderId: 'mainImagePlaceholder',
                    previewId: 'mainImagePreview',
                    previewNewId: 'mainImagePreviewNew',
                    overlayId: 'mainImageOverlay',
                    changeBtnId: 'mainImageChangeBtn',
                    removeBtnId: 'mainImageRemoveBtn',
                    filenameContainerId: 'mainImageFilename',
                    filenameTextId: 'mainImageFilenameText',
                    errorContainerId: 'mainImageError',
                    removeFlagId: 'removeMainImageFlag',
                    hasExistingImage: {{ $mainImageExists ? 'true' : 'false' }},
                    existingImageFilename: '{{ $mainImageExists ? basename($mainImage->path) : '' }}'
                });

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

                const galleryDropzone = document.getElementById('galleryDropzone');
                const galleryInput = document.getElementById('galleryInput');
                const galleryPreviewContainer = document.getElementById('galleryPreviewContainer');
                const removedGalleryContainer = document.getElementById('removedGalleryContainer');
                const removedGalleryIds = new Set();
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

                document.querySelectorAll('.delete-existing-gallery').forEach(button => {
                    button.addEventListener('click', (event) => {
                        event.stopPropagation();
                        const imageId = button.dataset.id;
                        if (!imageId) {
                            return;
                        }
                        if (!removedGalleryIds.has(imageId)) {
                            removedGalleryIds.add(imageId);
                            const hidden = document.createElement('input');
                            hidden.type = 'hidden';
                            hidden.name = 'remove_gallery[]';
                            hidden.value = imageId;
                            hidden.id = `remove-gallery-${imageId}`;
                            removedGalleryContainer.appendChild(hidden);
                        }
                        const wrapper = button.closest('.preview-item') || button.closest('.existing-image');
                        wrapper?.remove();
                    });
                });

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
