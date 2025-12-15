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

        <div class="form-row">
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

        <div class="form-row">
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

        <div class="form-columns-row">

            <div class="form-column">
                <div class="image-upload-section w-full">
                    <label class="label-form">Galería de imágenes</label>
                    <div class="custom-dropzone" id="galleryDropzone">
                        <i class="ri-multi-image-line"></i>
                        <p>Arrastra imágenes aquí o haz clic</p>
                        <input type="file" name="gallery[]" id="galleryInput" accept="image/*" multiple hidden
                            data-validate="image|maxSize:2048">
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
                                data-id="{{ $image->id }}" data-main="{{ $image->is_main ? 'true' : 'false' }}">
                                @if ($exists)
                                    <img src="{{ $imageUrl }}" alt="{{ $altText }}">
                                @else
                                    <div class="image-not-found-block">
                                        <i class="ri-file-close-line"></i>
                                        <p>Imagen no encontrada</p>
                                    </div>
                                @endif
                                <div class="overlay">
                                    <span class="file-size">{{ $exists ? 'Existente' : 'No encontrada' }}</span>
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
            <div class="form-column">
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
                const primaryImageInput = document.getElementById('primaryImageInput');

                let galleryFiles = [];
                const removedGalleryIds = new Set();
                let primaryState = null;

                const refreshGalleryInput = () => {
                    const dataTransfer = new DataTransfer();
                    galleryFiles.forEach(entry => dataTransfer.items.add(entry.file));
                    galleryInput.files = dataTransfer.files;
                };

                const formatFileSize = (bytes) => {
                    let size = bytes / 1024;
                    return size > 1024 ? `${(size / 1024).toFixed(2)} MB` : `${size.toFixed(1)} KB`;
                };

                const getAllPreviewItems = () => Array.from(galleryPreviewContainer.querySelectorAll('.preview-item'));

                const updatePrimaryBadges = () => {
                    getAllPreviewItems().forEach(item => {
                        const badge = item.querySelector('.primary-badge');
                        const markBtn = item.querySelector('.mark-main-btn');
                        if (!badge || !markBtn) return;

                        let isActive = false;
                        if (primaryState) {
                            if (primaryState.type === 'existing' && item.dataset.type === 'existing') {
                                isActive = Number(item.dataset.id) === primaryState.id;
                            }
                            if (primaryState.type === 'new' && item.dataset.type === 'new') {
                                isActive = item.dataset.key === primaryState.key;
                            }
                        }

                        badge.style.display = isActive ? 'flex' : 'none';
                        markBtn.disabled = isActive;
                    });
                };

                const setPrimaryState = (state) => {
                    primaryState = state;
                    updatePrimaryBadges();
                };

                const ensurePrimarySelection = () => {
                    if (primaryState) {
                        if (primaryState.type === 'existing') {
                            const stillExists = getAllPreviewItems().some(item => item.dataset.type ===
                                'existing' && Number(item.dataset.id) === primaryState.id);
                            if (stillExists) {
                                updatePrimaryBadges();
                                return;
                            }
                        } else if (primaryState.type === 'new') {
                            const stillExists = galleryFiles.some(entry => entry.key === primaryState.key);
                            if (stillExists) {
                                updatePrimaryBadges();
                                return;
                            }
                        }
                    }

                    const allItems = getAllPreviewItems();
                    if (allItems.length === 0) {
                        primaryState = null;
                        updatePrimaryBadges();
                        return;
                    }

                    const firstExisting = allItems.find(item => item.dataset.type === 'existing');
                    if (firstExisting) {
                        setPrimaryState({
                            type: 'existing',
                            id: Number(firstExisting.dataset.id)
                        });
                        return;
                    }

                    if (galleryFiles.length > 0) {
                        setPrimaryState({
                            type: 'new',
                            key: galleryFiles[0].key
                        });
                        return;
                    }
                };

                const registerExistingPreviewEvents = () => {
                    getAllPreviewItems()
                        .filter(item => item.dataset.type === 'existing')
                        .forEach(item => {
                            const id = Number(item.dataset.id);
                            const markButton = item.querySelector('.mark-main-btn');
                            const deleteButton = item.querySelector('.delete-existing-gallery');

                            if (item.dataset.main === 'true' && !primaryState) {
                                primaryState = {
                                    type: 'existing',
                                    id
                                };
                            }

                            markButton?.addEventListener('click', (event) => {
                                event.preventDefault();
                                event.stopPropagation();
                                setPrimaryState({
                                    type: 'existing',
                                    id
                                });
                            });

                            deleteButton?.addEventListener('click', (event) => {
                                event.preventDefault();
                                event.stopPropagation();

                                if (!removedGalleryIds.has(id)) {
                                    removedGalleryIds.add(id);
                                    const input = document.createElement('input');
                                    input.type = 'hidden';
                                    input.name = 'remove_gallery[]';
                                    input.value = id;
                                    removedGalleryContainer.appendChild(input);
                                }

                                item.remove();
                                ensurePrimarySelection();
                            });
                        });
                };

                const buildNewPreviewItem = (dataUrl, file, key) => {
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

                    item.querySelector('.mark-main-btn').addEventListener('click', (event) => {
                        event.preventDefault();
                        event.stopPropagation();
                        setPrimaryState({
                            type: 'new',
                            key
                        });
                    });

                    item.querySelector('.delete-btn').addEventListener('click', (event) => {
                        event.preventDefault();
                        event.stopPropagation();
                        galleryFiles = galleryFiles.filter(entry => entry.key !== key);
                        item.remove();
                        ensurePrimarySelection();
                        refreshGalleryInput();
                    });

                    galleryPreviewContainer.appendChild(item);
                };

                const renderNewFiles = (entries) => {
                    entries.forEach(({
                        file,
                        key
                    }) => {
                        const reader = new FileReader();
                        reader.onload = (event) => {
                            buildNewPreviewItem(event.target.result, file, key);
                            updatePrimaryBadges();
                        };
                        reader.readAsDataURL(file);
                    });
                };

                const handleGalleryFiles = (files) => {
                    const newEntries = [];
                    [...files].forEach(file => {
                        if (!file.type.startsWith('image/')) {
                            return;
                        }
                        const key = `new-${file.lastModified}-${Math.random().toString(36).slice(2, 8)}`;
                        const entry = {
                            file,
                            key
                        };
                        galleryFiles.push(entry);
                        newEntries.push(entry);
                    });

                    refreshGalleryInput();
                    renderNewFiles(newEntries);
                    ensurePrimarySelection();
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

                registerExistingPreviewEvents();
                ensurePrimarySelection();
                updatePrimaryBadges();

                document.getElementById('productForm').addEventListener('submit', () => {
                    ensurePrimarySelection();

                    if (!primaryState) {
                        primaryImageInput.value = '';
                        return;
                    }

                    if (primaryState.type === 'existing') {
                        primaryImageInput.value = `existing:${primaryState.id}`;
                        return;
                    }

                    const index = galleryFiles.findIndex(entry => entry.key === primaryState.key);
                    primaryImageInput.value = index >= 0 ? `new:${index}` : '';
                });
            });
        </script>
    @endpush
</x-admin-layout>
