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

        <div class="form-columns-row">
            <div class="form-column">
                <div class="input-group">
                    <label for="description" class="label-form">Descripción</label>
                    <div class="input-icon-container">
                        <textarea name="description" id="description" class="textarea-form" rows="4"
                            placeholder="Describe el producto" data-validate="max:5000">{{ old('description') }}</textarea>
                        <i class="ri-file-text-line input-icon textarea-icon"></i>
                    </div>
                </div>
                <div class="image-upload-section w-full">
                    <label class="label-form">Galería de imágenes</label>
                    <div class="custom-dropzone" id="galleryDropzone">
                        <i class="ri-multi-image-line"></i>
                        <p>Arrastra imágenes aquí o haz clic</p>
                        <input type="file" name="gallery[]" id="galleryInput" accept="image/*" multiple hidden
                            data-validate="image|maxSize:2048">
                    </div>
                    <div id="galleryPreviewContainer" class="preview-container"></div>
                    <input type="hidden" name="primary_image" id="primaryImageInput">
                </div>
            </div>
        </div>

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

                const galleryDropzone = document.getElementById('galleryDropzone');
                const galleryInput = document.getElementById('galleryInput');
                const galleryPreviewContainer = document.getElementById('galleryPreviewContainer');
                const primaryImageInput = document.getElementById('primaryImageInput');

                let galleryFiles = [];
                let primaryState = null; // { key }

                const refreshGalleryInput = () => {
                    const dataTransfer = new DataTransfer();
                    galleryFiles.forEach(item => dataTransfer.items.add(item.file));
                    galleryInput.files = dataTransfer.files;
                };

                const formatFileSize = (bytes) => {
                    let size = bytes / 1024;
                    return size > 1024 ? `${(size / 1024).toFixed(2)} MB` : `${size.toFixed(1)} KB`;
                };

                const setPrimaryState = (key) => {
                    primaryState = key ? {
                        key
                    } : null;
                    updatePrimaryBadges();
                };

                const ensurePrimarySelection = () => {
                    if (primaryState) {
                        const exists = galleryFiles.some(item => item.key === primaryState.key);
                        if (exists) {
                            return;
                        }
                    }
                    if (galleryFiles.length === 0) {
                        setPrimaryState(null);
                        return;
                    }
                    setPrimaryState(galleryFiles[0].key);
                };

                const updatePrimaryBadges = () => {
                    galleryPreviewContainer.querySelectorAll('.preview-item').forEach(item => {
                        const badge = item.querySelector('.primary-badge');
                        const markBtn = item.querySelector('.mark-main-btn');
                        if (!badge || !markBtn) return;
                        const key = item.dataset.key;
                        const isActive = primaryState && primaryState.key === key;
                        badge.style.display = isActive ? 'flex' : 'none';
                        markBtn.disabled = isActive;
                    });
                };

                const buildPreviewItem = (dataUrl, file, key) => {
                    const item = document.createElement('div');
                    item.classList.add('preview-item');
                    item.dataset.type = 'new';
                    item.dataset.key = key;
                    item.innerHTML = `
                        <img src="${dataUrl}" alt="${file.name}">
                        <div class="overlay">
                            <span class="file-size">${formatFileSize(file.size)}</span>
                            <div class="overlay-actions">
                                <button type="button" class="mark-main-btn" title="Marcar como principal">
                                    <i class="ri-star-smile-fill"></i>
                                    <span>Principal</span>
                                </button>
                                <button type="button" class="delete-btn" title="Eliminar imagen">
                                    <span class="boton-icon"><i class="ri-delete-bin-6-fill"></i></span>
                                    <span class="boton-text">Eliminar</span>
                                </button>
                            </div>
                        </div>
                        <span class="primary-badge">
                            <i class="ri-star-fill"></i>
                            Principal
                        </span>
                    `;

                    item.querySelector('.delete-btn').addEventListener('click', (event) => {
                        event.stopPropagation();
                        galleryFiles = galleryFiles.filter(current => current.key !== key);
                        item.remove();
                        ensurePrimarySelection();
                        refreshGalleryInput();
                    });

                    item.querySelector('.mark-main-btn').addEventListener('click', (event) => {
                        event.preventDefault();
                        event.stopPropagation();
                        setPrimaryState(key);
                    });

                    return item;
                };

                const renderGalleryPreviews = () => {
                    galleryPreviewContainer.innerHTML = '';
                    galleryFiles.forEach(({
                        file,
                        key
                    }) => {
                        const reader = new FileReader();
                        reader.onload = (event) => {
                            const item = buildPreviewItem(event.target.result, file, key);
                            galleryPreviewContainer.appendChild(item);
                            updatePrimaryBadges();
                        };
                        reader.readAsDataURL(file);
                    });
                };

                const handleGalleryFiles = (files) => {
                    [...files].forEach(file => {
                        if (!file.type.startsWith('image/')) {
                            return;
                        }
                        const key = `new-${file.lastModified}-${Math.random().toString(36).slice(2, 8)}`;
                        galleryFiles.push({
                            file,
                            key
                        });
                    });
                    ensurePrimarySelection();
                    refreshGalleryInput();
                    renderGalleryPreviews();
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

                document.getElementById('productForm').addEventListener('submit', () => {
                    ensurePrimarySelection();
                    if (!primaryState || galleryFiles.length === 0) {
                        primaryImageInput.value = '';
                        return;
                    }

                    const primaryIndex = galleryFiles.findIndex(item => item.key === primaryState.key);
                    primaryImageInput.value = primaryIndex >= 0 ? `new:${primaryIndex}` : '';
                });
            });
        </script>
    @endpush
</x-admin-layout>
