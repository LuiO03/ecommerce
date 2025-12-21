<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-warning"><i class="ri-edit-circle-line"></i></div>
        Editar Post
    </x-slot>

    <x-slot name="action">
        <a href="{{ route('admin.posts.index') }}" class="boton boton-secondary">
            <span class="boton-icon"><i class="ri-arrow-go-back-line"></i></span>
            <span class="boton-text">Volver</span>
        </a>
    </x-slot>

    @php
        $mainImagePath = $post->main_image_path;
        $hasExistingImage = $mainImagePath && file_exists(public_path('storage/' . $mainImagePath));
    @endphp

    <form action="{{ route('admin.posts.update', $post->slug) }}" method="POST" enctype="multipart/form-data"
        class="form-container" autocomplete="off" id="postForm">
        @csrf
        @method('PUT')

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

        <x-alert type="info" title="Información:" :dismissible="true" :items="['Los campos con asterisco (<i class=\'ri-asterisk text-accent\'></i>) son obligatorios.']" />


        <div class="form-row-fit">
            <!-- ================= IMÁGENES ================= -->
            <div class="image-upload-section">
                <label class="label-form">Imágenes del post</label>

                <div class="custom-dropzone" id="customDropzone">
                    <i class="ri-multi-image-line"></i>
                    <p>Arrastra imágenes aquí o haz clic</p>
                    <input type="file" id="imageInput" name="images[]" accept="image/*" multiple hidden>
                </div>

                <div id="previewContainer" class="preview-container">
                    @foreach ($post->images as $img)
                        @php
                            $fullPath = public_path('storage/' . $img->path);
                            $exists = file_exists($fullPath);
                        @endphp

                        <div class="preview-item existing-image" data-type="existing" data-id="{{ $img->id }}"
                            data-key="existing-{{ $img->id }}" data-main="{{ $img->is_main ? 'true' : 'false' }}">
                            <button type="button" class="drag-handle" title="Reordenar imagen">
                                <i class="ri-draggable"></i>
                            </button>
                            @if ($exists)
                                {{-- Imagen encontrada --}}
                                <img src="{{ asset('storage/' . $img->path) }}" alt="Imagen adicional">
                            @else
                                {{-- Imagen no encontrada --}}
                                <i class="ri-file-close-line"></i>
                                <p>Imagen no encontrada</p>
                            @endif
                            <div class="overlay">
                                <span class="file-size">{{ $exists ? 'Existente' : 'No encontrada' }}</span>
                                <div class="overlay-actions">
                                    <button type="button" class="mark-main-btn" title="Marcar como portada del post">
                                        <i class="ri-gallery-line"></i>
                                        <span>Portada</span>
                                    </button>
                                    <button type="button" class="delete-btn delete-existing-image" title="Eliminar imagen"
                                        data-id="{{ $img->id }}">
                                        <span class="boton-icon"><i class="ri-delete-bin-6-fill"></i></span>
                                        <span class="boton-text">Eliminar</span>
                                    </button>
                                </div>
                            </div>
                            <span class="primary-badge"
                                style="{{ $img->is_main ? 'display:flex;' : 'display:none;' }}">
                                <i class="ri-gallery-fill"></i>
                                Portada
                            </span>
                        </div>
                    @endforeach
                </div>

                <input type="hidden" name="deletedImages" id="deletedImages">
                <input type="hidden" name="primary_image" id="primaryImageInput" value="">
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const dropzone = document.getElementById('customDropzone');
                    const input = document.getElementById('imageInput');
                    const previewContainer = document.getElementById('previewContainer');
                    const deletedImagesInput = document.getElementById('deletedImages');
                    const primaryImageInput = document.getElementById('primaryImageInput');

                    let galleryFiles = [];
                    const deletedIds = new Set();
                    let primaryState = null;
                    let currentDragKey = null;
                    let isReordering = false;

                    const refreshInputFiles = () => {
                        const dataTransfer = new DataTransfer();
                        galleryFiles.forEach(entry => dataTransfer.items.add(entry.file));
                        input.files = dataTransfer.files;
                    };

                    const syncDeletedInput = () => {
                        if (!deletedImagesInput) return;
                        deletedImagesInput.value = JSON.stringify(Array.from(deletedIds));
                    };

                    const formatFileSize = (bytes) => {
                        let size = bytes / 1024;
                        return size > 1024 ? `${(size / 1024).toFixed(2)} MB` : `${size.toFixed(1)} KB`;
                    };

                    const getAllPreviewItems = () => Array.from(previewContainer.querySelectorAll('.preview-item'));

                    const movePrimaryToStart = () => {
                        if (!primaryState) return;

                        let primaryItem = null;

                        if (primaryState.type === 'existing') {
                            primaryItem = previewContainer.querySelector(`.preview-item[data-type="existing"][data-id="${primaryState.id}"]`);
                        } else if (primaryState.type === 'new') {
                            primaryItem = previewContainer.querySelector(`.preview-item[data-type="new"][data-key="${primaryState.key}"]`);
                        }

                        if (!primaryItem) return;

                        const siblings = [...previewContainer.children];
                        const positions = new Map(siblings.map(el => [el.dataset.key || `existing-${el.dataset.id}`, el.getBoundingClientRect()]));

                        previewContainer.insertBefore(primaryItem, previewContainer.firstChild);

                        if (primaryState.type === 'new') {
                            const index = galleryFiles.findIndex(entry => entry.key === primaryState.key);
                            if (index > 0) {
                                const [entry] = galleryFiles.splice(index, 1);
                                galleryFiles.unshift(entry);
                                refreshInputFiles();
                            }
                        }

                        siblings.forEach(el => {
                            const key = el.dataset.key || `existing-${el.dataset.id}`;
                            const oldRect = positions.get(key);
                            const newRect = el.getBoundingClientRect();

                            if (!oldRect || (oldRect.left === newRect.left && oldRect.top === newRect.top)) {
                                return;
                            }

                            const dx = oldRect.left - newRect.left;
                            const dy = oldRect.top - newRect.top;

                            el.style.transition = 'none';
                            el.style.transform = `translate(${dx}px, ${dy}px)`;

                            requestAnimationFrame(() => {
                                el.style.transition = 'transform 0.3s ease';
                                el.style.transform = '';
                            });
                        });
                    };

                    const updatePrimaryBadges = () => {
                        getAllPreviewItems().forEach(item => {
                            const badge = item.querySelector('.primary-badge');
                            const markBtn = item.querySelector('.mark-main-btn');
                            const dragHandle = item.querySelector('.drag-handle');
                            if (!badge || !markBtn || !dragHandle) return;

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
                            dragHandle.draggable = !isActive;

                            if (isActive) {
                                dragHandle.classList.add('drag-disabled');
                                item.classList.add('primary-main');
                            } else {
                                dragHandle.classList.remove('drag-disabled');
                                item.classList.remove('primary-main');
                            }
                        });
                    };

                    const setPrimaryState = (state) => {
                        primaryState = state;
                        movePrimaryToStart();
                        updatePrimaryBadges();
                    };

                    const ensurePrimarySelection = () => {
                        if (primaryState) {
                            if (primaryState.type === 'existing') {
                                const stillExists = getAllPreviewItems().some(item => item.dataset.type === 'existing' && Number(item.dataset.id) === primaryState.id);
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

                    const registerExistingEvents = () => {
                        getAllPreviewItems()
                            .filter(item => item.dataset.type === 'existing')
                            .forEach(item => {
                                const id = Number(item.dataset.id);
                                const key = item.dataset.key;
                                const markButton = item.querySelector('.mark-main-btn');
                                const deleteButton = item.querySelector('.delete-existing-image');
                                const dragHandle = item.querySelector('.drag-handle');

                                if (item.dataset.main === 'true' && !primaryState) {
                                    setPrimaryState({
                                        type: 'existing',
                                        id
                                    });
                                }

                                if (dragHandle) {
                                    dragHandle.draggable = true;
                                    dragHandle.addEventListener('click', (event) => {
                                        event.preventDefault();
                                        event.stopPropagation();
                                    });
                                    dragHandle.addEventListener('dragstart', (event) => {
                                        event.stopPropagation();
                                        currentDragKey = key;
                                        item.classList.add('dragging');
                                        event.dataTransfer.effectAllowed = 'move';
                                        if (event.dataTransfer.setDragImage) {
                                            const offsetX = item.clientWidth - 32;
                                            const offsetY = 32;
                                            event.dataTransfer.setDragImage(item, offsetX, offsetY);
                                        }
                                    });
                                    dragHandle.addEventListener('dragend', (event) => {
                                        event.stopPropagation();
                                        item.classList.remove('dragging');
                                        currentDragKey = null;
                                    });
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
                                    if (!deletedIds.has(id)) {
                                        deletedIds.add(id);
                                        syncDeletedInput();
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
                            <button type="button" class="drag-handle" title="Reordenar imagen">
                                <i class="ri-draggable"></i>
                            </button>
                            <img src="${dataUrl}" alt="${file.name}">
                            <div class="overlay">
                                <span class="file-size">${formatFileSize(file.size)}</span>
                                <div class="overlay-actions">
                                    <button type="button" class="mark-main-btn" title="Marcar como portada del post">
                                        <i class="ri-gallery-line"></i>
                                        <span>Portada</span>
                                    </button>
                                    <button type="button" class="delete-btn" title="Eliminar imagen">
                                        <span class="boton-icon"><i class="ri-delete-bin-6-fill"></i></span>
                                        <span class="boton-text">Eliminar</span>
                                    </button>
                                </div>
                            </div>
                            <span class="primary-badge">
                                <i class="ri-gallery-fill"></i>
                                Portada
                            </span>
                        `;

                        const dragHandle = item.querySelector('.drag-handle');
                        dragHandle.draggable = true;
                        dragHandle.addEventListener('click', (event) => {
                            event.preventDefault();
                            event.stopPropagation();
                        });
                        dragHandle.addEventListener('dragstart', (event) => {
                            event.stopPropagation();
                            currentDragKey = key;
                            item.classList.add('dragging');
                            event.dataTransfer.effectAllowed = 'move';
                            if (event.dataTransfer.setDragImage) {
                                const offsetX = item.clientWidth - 32;
                                const offsetY = 32;
                                event.dataTransfer.setDragImage(item, offsetX, offsetY);
                            }
                        });
                        dragHandle.addEventListener('dragend', (event) => {
                            event.stopPropagation();
                            item.classList.remove('dragging');
                            currentDragKey = null;
                        });

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
                            refreshInputFiles();
                        });

                        previewContainer.appendChild(item);
                    };

                    const renderNewFiles = (entries) => {
                        entries.forEach(({ file, key }) => {
                            const reader = new FileReader();
                            reader.onload = (event) => {
                                buildNewPreviewItem(event.target.result, file, key);
                            };
                            reader.readAsDataURL(file);
                        });
                    };

                    const handleFiles = (files) => {
                        const newEntries = [];
                        let duplicateCount = 0;

                        [...files].forEach(file => {
                            if (!file.type.startsWith('image/')) return;

                            const isDuplicate = galleryFiles.some(existing =>
                                existing.file.name === file.name &&
                                existing.file.size === file.size &&
                                existing.file.lastModified === file.lastModified
                            );

                            if (isDuplicate) {
                                duplicateCount++;
                                return;
                            }

                            const key = `new-${file.lastModified}-${Math.random().toString(36).slice(2, 8)}`;
                            const entry = { file, key };
                            galleryFiles.push(entry);
                            newEntries.push(entry);
                        });

                        if (duplicateCount > 0) {
                            alert(`${duplicateCount} imagen(es) ya existen en la galería y fueron omitidas.`);
                        }

                        refreshInputFiles();
                        renderNewFiles(newEntries);
                    };

                    // Soporte de pegado desde portapapeles
                    document.addEventListener('paste', (event) => {
                        const items = (event.clipboardData || event.originalEvent?.clipboardData)?.items || [];
                        const files = [];
                        for (let i = 0; i < items.length; i++) {
                            if (items[i].kind === 'file' && items[i].type.startsWith('image/')) {
                                files.push(items[i].getAsFile());
                            }
                        }
                        if (files.length > 0) {
                            event.preventDefault();
                            handleFiles(files);
                        }
                    });

                    dropzone.addEventListener('click', () => input.click());
                    dropzone.addEventListener('dragover', (event) => {
                        event.preventDefault();
                        dropzone.classList.add('dragover');
                    });
                    dropzone.addEventListener('dragleave', () => {
                        dropzone.classList.remove('dragover');
                    });
                    dropzone.addEventListener('drop', (event) => {
                        event.preventDefault();
                        dropzone.classList.remove('dragover');
                        handleFiles(event.dataTransfer.files);
                    });
                    input.addEventListener('change', (event) => handleFiles(event.target.files));

                    previewContainer.addEventListener('dragover', (event) => {
                        event.preventDefault();
                        if (!currentDragKey || isReordering) return;

                        const draggingItem = previewContainer.querySelector(`[data-key="${currentDragKey}"]`);
                        const targetItem = event.target.closest('.preview-item');

                        let isTargetPrimary = false;
                        if (targetItem && primaryState) {
                            if (primaryState.type === 'existing' && targetItem.dataset.type === 'existing') {
                                isTargetPrimary = Number(targetItem.dataset.id) === primaryState.id;
                            }
                            if (primaryState.type === 'new' && targetItem.dataset.type === 'new') {
                                isTargetPrimary = targetItem.dataset.key === primaryState.key;
                            }
                        }

                        if (targetItem && !isTargetPrimary && targetItem !== draggingItem && previewContainer.contains(targetItem)) {
                            isReordering = true;
                            const items = Array.from(previewContainer.children);
                            const currentIndex = items.indexOf(draggingItem);
                            const targetIndex = items.indexOf(targetItem);

                            if (currentIndex !== targetIndex) {
                                const siblings = [...previewContainer.children];
                                const positions = new Map(siblings.map(el => [el.dataset.key, el.getBoundingClientRect()]));

                                if (currentIndex < targetIndex) {
                                    previewContainer.insertBefore(draggingItem, targetItem.nextSibling);
                                } else {
                                    previewContainer.insertBefore(draggingItem, targetItem);
                                }

                                siblings.forEach(el => {
                                    if (el === draggingItem) return;

                                    const oldRect = positions.get(el.dataset.key);
                                    const newRect = el.getBoundingClientRect();

                                    if (oldRect && (oldRect.left !== newRect.left || oldRect.top !== newRect.top)) {
                                        const dx = oldRect.left - newRect.left;
                                        const dy = oldRect.top - newRect.top;

                                        el.style.transition = 'none';
                                        el.style.transform = `translate(${dx}px, ${dy}px)`;

                                        requestAnimationFrame(() => {
                                            el.style.transition = 'transform 0.3s ease';
                                            el.style.transform = '';
                                        });
                                    }
                                });
                            }

                            setTimeout(() => {
                                isReordering = false;
                            }, 250);
                        }
                    });

                    previewContainer.addEventListener('drop', (event) => {
                        event.preventDefault();
                        if (!currentDragKey) return;

                        const newOrderKeys = Array.from(previewContainer.querySelectorAll('.preview-item'))
                            .map(item => item.dataset.key);

                        const newGalleryFiles = [];
                        newOrderKeys.forEach(key => {
                            const fileItem = galleryFiles.find(entry => entry.key === key);
                            if (fileItem) newGalleryFiles.push(fileItem);
                        });

                        galleryFiles = newGalleryFiles;
                        refreshInputFiles();
                        ensurePrimarySelection();

                        currentDragKey = null;
                        const draggingItem = previewContainer.querySelector('.dragging');
                        if (draggingItem) draggingItem.classList.remove('dragging');
                        updatePrimaryBadges();
                    });

                    registerExistingEvents();
                    ensurePrimarySelection();
                    updatePrimaryBadges();

                    const removeImageBtn = document.getElementById('removeImageBtn');
                    if (removeImageBtn) {
                        removeImageBtn.addEventListener('click', () => {
                            const deleteInput = document.getElementById('deleteImage');
                            const imagePreview = document.getElementById('imagePreview');
                            if (deleteInput) deleteInput.value = 1;
                            if (imagePreview) imagePreview.style.display = 'none';
                        });
                    }

                    document.getElementById('postForm').addEventListener('submit', () => {
                        ensurePrimarySelection();

                        if (!primaryState) {
                            if (primaryImageInput) {
                                primaryImageInput.value = '';
                            }
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
        </div>
        <!-- ================= CAMPOS PRINCIPALES ================= -->
        <div class="form-row-fill">
            <!-- Título -->
            <div class="input-group">
                <label for="title" class="label-form">
                    Título <i class="ri-asterisk text-accent"></i>
                </label>
                <div class="input-icon-container">
                    <i class="ri-file-text-line input-icon"></i>
                    <input type="text" name="title" id="title" class="input-form"
                        value="{{ old('title', $post->title) }}" data-validate="required|min:3|max:255">
                </div>
            </div>

            <!-- Estado -->
            <div class="input-group">
                <label class="label-form">Estado <i class="ri-asterisk text-accent"></i></label>
                <div class="input-icon-container">
                    <i class="ri-focus-2-line input-icon"></i>
                    <select name="status" class="select-form" data-validate="required|selected">
                        <option value="" disabled>Seleccione un estado</option>
                        <option value="draft" {{ $post->status == 'draft' ? 'selected' : '' }}>Borrador</option>
                        <option value="pending" {{ $post->status == 'pending' ? 'selected' : '' }}>Pendiente</option>
                        <option value="published" {{ $post->status == 'published' ? 'selected' : '' }}>Publicado
                        </option>
                        <option value="rejected" {{ $post->status == 'rejected' ? 'selected' : '' }}>Rechazado</option>
                    </select>
                    <i class="ri-arrow-down-s-line select-arrow"></i>
                </div>
            </div>

            <!-- Visibilidad -->
            <div class="input-group">
                <label class="label-form">Visibilidad <i class="ri-asterisk text-accent"></i></label>
                <div class="input-icon-container">
                    <i class="ri-eye-line input-icon"></i>

                    <select name="visibility" class="select-form" data-validate="required|selected">
                        <option value="" disabled>Seleccione visibilidad</option>
                        <option value="public" {{ $post->visibility == 'public' ? 'selected' : '' }}>Público</option>
                        <option value="private" {{ $post->visibility == 'private' ? 'selected' : '' }}>Privado
                        </option>
                        <option value="registered" {{ $post->visibility == 'registered' ? 'selected' : '' }}>
                            Registrado
                        </option>
                    </select>

                    <i class="ri-arrow-down-s-line select-arrow"></i>
                </div>
            </div>

            <!-- Comentarios -->
            <div class="input-group">
                <label class="label-form">Permitir comentarios</label>
                <div class="binary-switch">
                    <input type="radio" name="allow_comments" id="allowYes" value="1"
                        class="switch-input switch-input-on" {{ $post->allow_comments == 1 ? 'checked' : '' }}>
                    <input type="radio" name="allow_comments" id="allowNo" value="0"
                        class="switch-input switch-input-off" {{ $post->allow_comments == 0 ? 'checked' : '' }}>

                    <div class="switch-slider"></div>
                    <label for="allowYes" class="switch-label switch-label-on"><i
                            class="ri-checkbox-circle-line"></i> Sí</label>
                    <label for="allowNo" class="switch-label switch-label-off"><i class="ri-close-circle-line"></i>
                        No</label>
                </div>
            </div>
        </div>

        <!-- ================= CONTENIDO ================= -->
        <div class="form-row-fit">
            <div class="input-group">
                <label class="label-form">Contenido <i class="ri-asterisk text-accent"></i></label>
                <textarea name="content" id="content" class="textarea-form-post" rows="8"
                    data-validate="requiredText|minText:10">{{ old('content', $post->content) }}</textarea>
            </div>
            <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
            <script>
                let editorInstance;
                document.addEventListener("DOMContentLoaded", () => {
                    ClassicEditor.create(document.querySelector('#content'), {
                            toolbar: [
                                'undo', 'redo',
                                'heading',
                                'bold', 'italic', 'underline', 'strikethrough',
                                'blockQuote',
                                'bulletedList', 'numberedList',
                                'link',
                                'insertTable',
                            ],
                            table: {
                                contentToolbar: [
                                    'tableColumn', 'tableRow', 'mergeTableCells'
                                ]
                            }
                        })
                        .then(editor => {
                            editorInstance = editor;
                            window.editorInstance = editor;
                            // Registrar instancia global por id para soporte multi-editor
                            window._ckEditors = window._ckEditors || {};
                            const ta = document.querySelector('#content');
                            if (ta) {
                                window._ckEditors[ta.id] = editor;
                            }
                        })
                        .catch(error => console.error(error));
                });
                // Sincronizar contenido antes de enviar
                document.getElementById('postForm').addEventListener('submit', function() {
                    if (editorInstance) {
                        document.querySelector('#content').value = editorInstance.getData();
                    }
                });
            </script>
        </div>

        <div class="form-row-fill">
            <!-- ================= TAGS ================= -->
            <div class="input-group">
                <label class="label-form">Tags</label>

                <div class="input-icon-container">
                    <i class="ri-price-tag-3-line input-icon"></i>

                    <select id="tagSelect" class="select-form">
                        <option value="">Selecciona un tag</option>

                        @foreach ($tags as $tag)
                            <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                        @endforeach
                    </select>

                    <i class="ri-arrow-down-s-line select-arrow"></i>
                </div>

                <div id="tagContainer" class="tag-container">
                    @foreach ($post->tags as $tag)
                        <div class="tag-pill" data-id="{{ $tag->id }}">
                            {{ $tag->name }}
                            <i class="ri-close-line remove-tag"></i>
                        </div>
                    @endforeach
                </div>

                <div id="tagHiddenInputs">
                    @foreach ($post->tags as $tag)
                        <input type="hidden" name="tags[]" value="{{ $tag->id }}"
                            id="tag-hidden-{{ $tag->id }}">
                    @endforeach
                </div>
            </div>
            <script>
                document.addEventListener("DOMContentLoaded", () => {
                    const select = document.getElementById("tagSelect");
                    const tagContainer = document.getElementById("tagContainer");
                    const hiddenInputs = document.getElementById("tagHiddenInputs");

                    const selectedTags = new Set(
                        [...document.querySelectorAll("#tagHiddenInputs input")].map(i => i.value)
                    );

                    select.addEventListener("change", () => {
                        const id = select.value;
                        const name = select.options[select.selectedIndex].text;

                        if (!id || selectedTags.has(id)) return;

                        selectedTags.add(id);

                        const pill = document.createElement("div");
                        pill.classList.add("tag-pill");
                        pill.dataset.id = id;
                        pill.innerHTML = `${name} <i class="ri-close-line remove-tag"></i>`;
                        tagContainer.appendChild(pill);

                        const input = document.createElement("input");
                        input.type = "hidden";
                        input.name = "tags[]";
                        input.value = id;
                        input.id = "tag-hidden-" + id;
                        hiddenInputs.appendChild(input);

                        pill.querySelector(".remove-tag").addEventListener("click", () => {
                            selectedTags.delete(id);
                            pill.remove();
                            document.getElementById("tag-hidden-" + id)?.remove();
                        });
                    });

                    document.querySelectorAll(".tag-pill .remove-tag").forEach(btn => {
                        btn.addEventListener("click", () => {
                            const pill = btn.parentElement;
                            const id = pill.dataset.id;
                            selectedTags.delete(id);
                            pill.remove();
                            document.getElementById("tag-hidden-" + id)?.remove();
                        });
                    });
                });
            </script>
        </div>

        <!-- ================= FOOTER ================= -->
        <div class="form-footer">
            <a href="{{ url()->previous() }}" class="boton-form boton-volver">
                <span class="boton-form-icon">
                    <i class="ri-arrow-left-circle-fill"></i>
                </span>
                <span class="boton-form-text">Cancelar</span>
            </a>

            <button type="submit" class="boton-form boton-success" id="submitBtn">
                <span class="boton-form-icon"><i class="ri-save-3-fill"></i></span>
                <span class="boton-form-text">Actualizar Post</span>
            </button>
        </div>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Inicializar manejador de imágenes
                const imageHandler = initImageUpload({
                    mode: 'edit',
                    hasExistingImage: {{ $mainImagePath && file_exists(public_path('storage/' . $mainImagePath)) ? 'true' : 'false' }},
                    existingImageFilename: '{{ $mainImagePath ? basename($mainImagePath) : '' }}'
                });

                // 1. Inicializar submit loader PRIMERO
                const submitLoader = initSubmitLoader({
                    formId: 'postForm',
                    buttonId: 'submitBtn',
                    loadingText: 'Actualizando...'
                });

                // 2. Inicializar validación de formulario DESPUÉS
                const formValidator = initFormValidator('#postForm', {
                    validateOnBlur: true,
                    validateOnInput: false,
                    scrollToFirstError: true
                });

                // Enlazar CKEditor: sincronizar en cambios y validar solo al perder foco
                if (window.editorInstance) {
                    const textarea = document.querySelector('#content');
                    const editor = window.editorInstance;
                    editor.model.document.on('change:data', () => {
                        textarea.value = editor.getData();
                    });
                    editor.editing.view.document.on('blur', () => {
                        // Sincronizar antes de validar para capturar vacío
                        textarea.value = editor.getData();
                        const isValid = formValidator.validateField(textarea);
                        const group = textarea.closest('.input-group');
                        const editable = group ? group.querySelector('.ck-editor__editable') : null;
                        if (editable) {
                            editable.classList.toggle('input-success', isValid);
                            editable.classList.toggle('input-error', !isValid);
                        }
                        // Asegurar mensaje de error inline si es inválido (vacío vs. insuficiente)
                        if (!isValid && group) {
                            let errorEl = group.querySelector('.input-error-message');
                            if (!errorEl) {
                                errorEl = document.createElement('div');
                                errorEl.className = 'input-error-message';
                                errorEl.innerHTML =
                                    '<i class="ri-error-warning-line"></i> <span class="error-text"></span>';
                                group.appendChild(errorEl);
                            }
                            const textEl = errorEl.querySelector('.error-text');
                            if (textEl) {
                                const div = document.createElement('div');
                                div.innerHTML = textarea.value || '';
                                const plain = (div.textContent || div.innerText || '').replace(/\u00A0|&nbsp;/g,
                                    ' ').trim();
                                textEl.textContent = plain.length === 0 ? 'Este campo es obligatorio' :
                                    'Debe tener al menos 10 caracteres';
                            }
                            errorEl.style.display = 'flex';
                        }
                    });
                }
            });
        </script>
    @endpush
</x-admin-layout>
