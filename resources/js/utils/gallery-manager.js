// Gestor de galerías de imágenes para productos y posts
// Cada inicializador asume que el DOM ya está cargado.

// Registro global opcional para que otros módulos (p.ej. validador de formularios)
// puedan conocer el índice visual (#N) de una imagen dada (File) dentro de la galería.
function registerGalleryRegistry(input, previewContainer, getKeyForFile) {
    if (!input || !previewContainer || !getKeyForFile || !input.id) return;

    window._galleryRegistries = window._galleryRegistries || {};
    window._galleryRegistries[input.id] = {
        getIndexForFile(file) {
            if (!file) return null;
            let key;
            try {
                key = getKeyForFile(file);
            } catch (e) {
                key = null;
            }
            if (!key) return null;

            const items = Array.from(previewContainer.querySelectorAll('.preview-item'));
            const target = items.find(el => el.dataset.key === key);
            if (!target) return null;

            const index = items.indexOf(target);
            return index >= 0 ? index + 1 : null; // 1-based para coincidir con el badge #N
        }
    };
}

export function initPostGalleryCreate() {
    return initGalleryCreateWithConfig({
        dropzoneId: 'customDropzone',
        inputId: 'imageInput',
        previewContainerId: 'previewContainer',
        primaryInputId: 'primaryImageInput',
        formId: 'postForm',
        labels: {
            markTitle: 'Marcar como portada del post',
            markText: 'Portada',
            markIconClass: 'ri-star-smile-fill',
            badgeIconClass: 'ri-gallery-fill',
            badgeText: 'Portada',
            deleteTitle: 'Eliminar imagen',
            deleteIconClass: 'ri-delete-bin-6-fill',
            deleteText: ''
        }
    });
}

export function initPostGalleryEdit() {
    return initGalleryEditWithConfig({
        dropzoneId: 'customDropzone',
        inputId: 'imageInput',
        previewContainerId: 'previewContainer',
        primaryInputId: 'primaryImageInput',
        formId: 'postForm',
        deletionMode: 'json-input',
        deletedInputId: 'deletedImages',
        existingDeleteSelector: '.delete-existing-image',
        labelsNew: {
            markTitle: 'Marcar como portada del post',
            markText: 'Portada',
            markIconClass: 'ri-star-smile-fill',
            badgeIconClass: 'ri-gallery-fill',
            badgeText: 'Portada',
            deleteTitle: 'Eliminar imagen',
            deleteIconClass: 'ri-delete-bin-6-fill',
            deleteText: 'Eliminar'
        }
    });
}

// ======================================================
// Inicializadores parametrizables para futuros modulos
// ======================================================

const GALLERY_DUPLICATE_MESSAGE = 'imagen(es) ya existen en la galería y fueron omitidas.';

function galleryFormatFileSize(bytes) {
    let size = bytes / 1024;
    return size > 1024 ? `${(size / 1024).toFixed(2)} MB` : `${size.toFixed(1)} KB`;
}

function galleryUpdateIndexBadges(container) {
    if (!container) return;

    const items = Array.from(container.querySelectorAll('.preview-item'));
    items.forEach((item, index) => {
        let badge = item.querySelector('.index-badge');
        if (!badge) {
            badge = document.createElement('span');
            badge.classList.add('index-badge');
            item.appendChild(badge);
        }
        badge.textContent = `#${index + 1}`;
    });
}

function galleryAnimateRemoval(item, callback) {
    if (!item || item.classList.contains('fade-out')) return;

    item.classList.add('fade-out');

    const handleAnimationEnd = () => {
        item.removeEventListener('animationend', handleAnimationEnd);
        item.remove();
        if (typeof callback === 'function') {
            callback();
        }
    };

    item.addEventListener('animationend', handleAnimationEnd);
}

function galleryGetImageFilesFromClipboard(event) {
    const items = (event.clipboardData || event.originalEvent?.clipboardData)?.items || [];
    const files = [];

    for (let i = 0; i < items.length; i++) {
        const item = items[i];
        if (item.kind === 'file' && item.type.startsWith('image/')) {
            const file = item.getAsFile();
            if (file) files.push(file);
        }
    }

    return files;
}

// Galería genérica para CREATE (sólo imágenes nuevas)
// config:
// - dropzoneId, inputId, previewContainerId, primaryInputId, formId
// - altContainerId? (para productos)
// - labels: { markTitle, markText, markIconClass, badgeIconClass, badgeText, deleteTitle, deleteIconClass, deleteText }
export function initGalleryCreateWithConfig(config) {
    const {
        dropzoneId,
        inputId,
        previewContainerId,
        primaryInputId,
        formId,
        altContainerId = null,
        labels
    } = config;

    const dropzone = document.getElementById(dropzoneId);
    const input = document.getElementById(inputId);
    const previewContainer = document.getElementById(previewContainerId);
    const primaryImageInput = document.getElementById(primaryInputId);
    const altContainer = altContainerId ? document.getElementById(altContainerId) : null;

    if (!dropzone || !input || !previewContainer || !primaryImageInput) return;

    let galleryFiles = [];
    let primaryState = null; // { key }
    let currentDragKey = null;
    let isReordering = false;

    const refreshInputFiles = () => {
        const dataTransfer = new DataTransfer();
        if (altContainer) {
            altContainer.innerHTML = '';
        }

        galleryFiles.forEach((item) => {
            dataTransfer.items.add(item.file);

            if (altContainer) {
                const altInput = document.createElement('input');
                altInput.type = 'hidden';
                altInput.name = 'gallery_alt[]';
                altInput.value = item.alt || '';
                altContainer.appendChild(altInput);
            }
        });

        input.files = dataTransfer.files;

        galleryUpdateIndexBadges(previewContainer);
    };

    const movePrimaryToStart = () => {
        if (!primaryState) return;

        const primaryKey = primaryState.key;
        if (!primaryKey) return;

        const primaryItem = previewContainer.querySelector(`.preview-item[data-key="${primaryKey}"]`);
        if (!primaryItem) return;

        const siblings = [...previewContainer.children];
        const positions = new Map(siblings.map(el => [el.dataset.key, el.getBoundingClientRect()]));

        previewContainer.insertBefore(primaryItem, previewContainer.firstChild);

        const index = galleryFiles.findIndex(item => item.key === primaryKey);
        if (index > 0) {
            const [entry] = galleryFiles.splice(index, 1);
            galleryFiles.unshift(entry);
            refreshInputFiles();
        }

        siblings.forEach(el => {
            const key = el.dataset.key;
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

        // Revalidar el input de archivos para que los índices (#N)
        // de los mensajes de error reflejen la nueva posición de la portada
        if (input && input.form && input.form.__validator) {
            input.form.__validator.validateField(input);
        }
    };

    const updatePrimaryBadges = () => {
        previewContainer.querySelectorAll('.preview-item').forEach(item => {
            const badge = item.querySelector('.primary-badge');
            const markBtn = item.querySelector('.mark-main-btn');
            const dragHandle = item.querySelector('.drag-handle');
            if (!badge || !markBtn || !dragHandle) return;

            const key = item.dataset.key;
            const isActive = primaryState && primaryState.key === key;

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

    const setPrimaryState = (key) => {
        primaryState = key ? { key } : null;
        movePrimaryToStart();
        updatePrimaryBadges();
    };

    const ensurePrimarySelection = () => {
        if (primaryState) {
            const exists = galleryFiles.some(item => item.key === primaryState.key);
            if (exists) return;
        }

        if (galleryFiles.length === 0) {
            primaryState = null;
            updatePrimaryBadges();
            return;
        }

        setPrimaryState(galleryFiles[0].key);
    };

    const buildPreviewItem = (dataUrl, file, key) => {
        const item = document.createElement('div');
        item.classList.add('preview-item');
        item.classList.add('has-image');
        item.dataset.type = 'new';
        item.dataset.key = key;
        item.innerHTML = `
            <button type="button" class="drag-handle" title="Reordenar imagen">
                <i class="ri-draggable"></i>
            </button>
            <img src="${dataUrl}" alt="${file.name}">
            <div class="overlay">
                <span class="file-size">${galleryFormatFileSize(file.size)}</span>
                <div class="overlay-actions">
                    <button type="button" class="mark-main-btn boton-form boton-success" title="${labels.markTitle}">
                        <span class="boton-form-icon"><i class="${labels.markIconClass}"></i></span>
                    </button>
                    <button type="button" class="delete-btn boton-form boton-danger" title="${labels.deleteTitle}">
                        <span class="boton-form-icon"><i class="${labels.deleteIconClass}"></i></span>
                    </button>
                </div>
            </div>
            <span class="primary-badge">
                <i class="${labels.badgeIconClass}"></i>
                ${labels.badgeText}
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

        const deleteBtn = item.querySelector('.delete-btn');
        deleteBtn.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();

            galleryFiles = galleryFiles.filter(current => current.key !== key);

            galleryAnimateRemoval(item, () => {
                ensurePrimarySelection();
                refreshInputFiles();

                // Revalidar input de archivos si el formulario tiene FormValidator
                if (input && input.form && input.form.__validator) {
                    input.form.__validator.validateField(input);
                }
            });
        });

        const markBtn = item.querySelector('.mark-main-btn');
        markBtn.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            setPrimaryState(key);
        });

        return item;
    };

    const appendPreview = (file, key) => {
        const reader = new FileReader();
        reader.onload = (event) => {
            const item = buildPreviewItem(event.target.result, file, key);
            previewContainer.appendChild(item);
            galleryUpdateIndexBadges(previewContainer);
            updatePrimaryBadges();
        };
        reader.readAsDataURL(file);
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
            alert(`${duplicateCount} ${GALLERY_DUPLICATE_MESSAGE}`);
        }

        ensurePrimarySelection();
        refreshInputFiles();
        // Validar después de actualizar la lista de archivos (soporta drop, paste, etc.)
        if (input && input.form && input.form.__validator) {
            input.form.__validator.validateField(input);
        }
        newEntries.forEach(({ file, key }) => appendPreview(file, key));
    };

    // Eventos de pegado desde el portapapeles
    document.addEventListener('paste', (event) => {
        const files = galleryGetImageFilesFromClipboard(event);
        if (files.length > 0) {
            event.preventDefault();
            handleFiles(files);
        }
    });

    // Eventos de dropzone
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

    // Reordenación con drag & drop
    previewContainer.addEventListener('dragover', (event) => {
        event.preventDefault();
        if (!currentDragKey || isReordering) return;

        const draggingItem = previewContainer.querySelector(`[data-key="${currentDragKey}"]`);
        const targetItem = event.target.closest('.preview-item');
        const isTargetPrimary = targetItem && primaryState && targetItem.dataset.key === primaryState.key;

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
            const fileItem = galleryFiles.find(item => item.key === key);
            if (fileItem) newGalleryFiles.push(fileItem);
        });

        galleryFiles = newGalleryFiles;
        refreshInputFiles();
        ensurePrimarySelection();

        currentDragKey = null;
        const draggingItem = previewContainer.querySelector('.dragging');
        if (draggingItem) draggingItem.classList.remove('dragging');
        galleryUpdateIndexBadges(previewContainer);
        updatePrimaryBadges();

        // Revalidar input de archivos para refrescar índices en mensajes de error
        if (input && input.form && input.form.__validator) {
            input.form.__validator.validateField(input);
        }
    });

    // Registrar esta galería genérica (create) para mapear Files -> índice visual
    registerGalleryRegistry(input, previewContainer, (file) => {
        const entry = galleryFiles.find(item =>
            item.file.name === file.name &&
            item.file.size === file.size &&
            item.file.lastModified === file.lastModified
        );
        return entry ? entry.key : null;
    });

    const form = formId ? document.getElementById(formId) : null;
    if (form) {
        form.addEventListener('submit', () => {
            ensurePrimarySelection();
            if (!primaryState || galleryFiles.length === 0) {
                primaryImageInput.value = '';
                return;
            }

            const primaryIndex = galleryFiles.findIndex(item => item.key === primaryState.key);
            primaryImageInput.value = primaryIndex >= 0 ? `new:${primaryIndex}` : '';
        });
    }
}

// Galería genérica para EDIT (imágenes existentes + nuevas)
// config:
// - dropzoneId, inputId, previewContainerId, primaryInputId, formId
// - deletionMode: 'json-input' | 'hidden-inputs' | null
//   - si 'json-input' => deletedInputId
//   - si 'hidden-inputs' => removedContainerId, removedFieldName
// - existingDeleteSelector: selector del botón de borrado de imágenes existentes
// - labelsNew: estructura de labels para nuevas imágenes
export function initGalleryEditWithConfig(config) {
    const {
        dropzoneId,
        inputId,
        previewContainerId,
        primaryInputId,
        formId,
        deletionMode = null,
        deletedInputId = null,
        removedContainerId = null,
        removedFieldName = 'remove_gallery[]',
        existingDeleteSelector,
        labelsNew
    } = config;

    const dropzone = document.getElementById(dropzoneId);
    const input = document.getElementById(inputId);
    const previewContainer = document.getElementById(previewContainerId);
    const primaryImageInput = document.getElementById(primaryInputId);
    const deletedImagesInput = deletedInputId ? document.getElementById(deletedInputId) : null;
    const removedGalleryContainer = removedContainerId ? document.getElementById(removedContainerId) : null;

    if (!dropzone || !input || !previewContainer || !primaryImageInput) return;

    let galleryFiles = [];
    const deletedIds = new Set();
    let primaryState = null; // { type: 'existing'|'new', id?, key? }
    let currentDragKey = null;
    let isReordering = false;

    const getAllPreviewItems = () => Array.from(previewContainer.querySelectorAll('.preview-item'));

    const refreshInputFiles = () => {
        const dataTransfer = new DataTransfer();
        galleryFiles.forEach(entry => dataTransfer.items.add(entry.file));
        input.files = dataTransfer.files;
        // Actualizar badges de índice en edición cuando cambian los archivos nuevos
        galleryUpdateIndexBadges(previewContainer);
    };

    const syncDeletedInput = () => {
        if (deletionMode === 'json-input' && deletedImagesInput) {
            deletedImagesInput.value = JSON.stringify(Array.from(deletedIds));
        }
    };

    const pushHiddenRemoved = (id) => {
        if (deletionMode === 'hidden-inputs' && removedGalleryContainer) {
            const inputHidden = document.createElement('input');
            inputHidden.type = 'hidden';
            inputHidden.name = removedFieldName;
            inputHidden.value = id;
            removedGalleryContainer.appendChild(inputHidden);
        }
    };

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

        // Recalcular numeración después de mover la portada al inicio
        galleryUpdateIndexBadges(previewContainer);

        // Revalidar el input de archivos para que los índices (#N)
        // de los mensajes de error reflejen la nueva posición de la portada
        if (input && input.form && input.form.__validator) {
            input.form.__validator.validateField(input);
        }
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
        }
    };

    const registerExistingEvents = () => {
        getAllPreviewItems()
            .filter(item => item.dataset.type === 'existing')
            .forEach(item => {
                const id = Number(item.dataset.id);
                const key = item.dataset.key;
                const markButton = item.querySelector('.mark-main-btn');
                const deleteButton = existingDeleteSelector ? item.querySelector(existingDeleteSelector) : null;
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
                        pushHiddenRemoved(id);
                    }

                    galleryAnimateRemoval(item, () => {
                        ensurePrimarySelection();
                        // Recalcular numeración cuando se elimina una imagen existente
                        galleryUpdateIndexBadges(previewContainer);

                        // Revalidar input de archivos para que maxFiles
                        // tenga en cuenta también la eliminación de imágenes existentes
                        if (input && input.form && input.form.__validator) {
                            input.form.__validator.validateField(input);
                        }
                    });
                });
            });
    };

    const buildNewPreviewItem = (dataUrl, file, key) => {
        const item = document.createElement('div');
        item.classList.add('preview-item');
        item.classList.add('has-image');
        item.dataset.type = 'new';
        item.dataset.key = key;
        item.innerHTML = `
            <button type="button" class="drag-handle" title="Reordenar imagen">
                <i class="ri-draggable"></i>
            </button>
            <img src="${dataUrl}" alt="${file.name}">
            <div class="overlay">
                <span class="file-size">${galleryFormatFileSize(file.size)}</span>
                <div class="overlay-actions">
                    <button type="button" class="mark-main-btn boton-form boton-success" title="${labelsNew.markTitle}">
                        <span class="boton-form-icon"><i class="${labelsNew.markIconClass}"></i></span>
                    </button>
                    <button type="button" class="delete-btn boton-form boton-danger" title="${labelsNew.deleteTitle}">
                        <span class="boton-form-icon"><i class="${labelsNew.deleteIconClass}"></i></span>
                    </button>
                </div>
            </div>
            <span class="primary-badge">
                <i class="${labelsNew.badgeIconClass}"></i>
                ${labelsNew.badgeText}
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

        const markButton = item.querySelector('.mark-main-btn');
        markButton.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            setPrimaryState({
                type: 'new',
                key
            });
        });

        const deleteButton = item.querySelector('.delete-btn');
        deleteButton.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();

            galleryFiles = galleryFiles.filter(entry => entry.key !== key);

            galleryAnimateRemoval(item, () => {
                ensurePrimarySelection();
                refreshInputFiles();

                // Revalidar input de archivos si el formulario tiene FormValidator
                if (input && input.form && input.form.__validator) {
                    input.form.__validator.validateField(input);
                }
            });
        });

        previewContainer.appendChild(item);
    };

    // Renderizar nuevas imágenes y, una vez que todas estén en el DOM,
    // disparar la validación del input de archivos para que los índices (#N)
    // y el conteo total (maxFiles) se calculen con la galería completa.
    const renderNewFiles = (entries) => {
        if (!entries || entries.length === 0) {
            if (input && input.form && input.form.__validator) {
                input.form.__validator.validateField(input);
            }
            return;
        }

        let pending = entries.length;

        entries.forEach(({ file, key }) => {
            const reader = new FileReader();
            reader.onload = (event) => {
                buildNewPreviewItem(event.target.result, file, key);
                // Numerar nuevamente al agregar nuevas imágenes en edición
                galleryUpdateIndexBadges(previewContainer);
                updatePrimaryBadges();

                pending -= 1;
                if (pending === 0 && input && input.form && input.form.__validator) {
                    input.form.__validator.validateField(input);
                }
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
            alert(`${duplicateCount} ${GALLERY_DUPLICATE_MESSAGE}`);
        }

        refreshInputFiles();
        renderNewFiles(newEntries);
        ensurePrimarySelection();
    };

    // Pegar desde portapapeles
    document.addEventListener('paste', (event) => {
        const files = galleryGetImageFilesFromClipboard(event);
        if (files.length > 0) {
            event.preventDefault();
            handleFiles(files);
        }
    });

    // Dropzone
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

    // Reordenación
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
            const fileItem = galleryFiles.find(item => item.key === key);
            if (fileItem) newGalleryFiles.push(fileItem);
        });

        galleryFiles = newGalleryFiles;
        refreshInputFiles();
        ensurePrimarySelection();

        currentDragKey = null;
        const draggingItem = previewContainer.querySelector('.dragging');
        if (draggingItem) draggingItem.classList.remove('dragging');
        // Actualizar numeración tras reordenar en edición
        galleryUpdateIndexBadges(previewContainer);
        updatePrimaryBadges();

        // Revalidar input de archivos para refrescar índices en mensajes de error
        if (input && input.form && input.form.__validator) {
            input.form.__validator.validateField(input);
        }
    });

    // Registrar esta galería genérica (edit) para mapear Files -> índice visual
    registerGalleryRegistry(input, previewContainer, (file) => {
        const entry = galleryFiles.find(item =>
            item.file.name === file.name &&
            item.file.size === file.size &&
            item.file.lastModified === file.lastModified
        );
        return entry ? entry.key : null;
    });

    registerExistingEvents();
    ensurePrimarySelection();
    // Numerar imágenes existentes al cargar la vista de edición
    galleryUpdateIndexBadges(previewContainer);
    updatePrimaryBadges();

    const form = formId ? document.getElementById(formId) : null;
    if (form) {
        form.addEventListener('submit', () => {
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
    }
}


export function initProductGalleryCreate() {
    return initGalleryCreateWithConfig({
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

export function initProductGalleryEdit() {
    return initGalleryEditWithConfig({
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

