// Gestor de galerías de imágenes para productos y posts
// Cada inicializador asume que el DOM ya está cargado.

export function initPostGalleryCreate() {
    const dropzone = document.getElementById('customDropzone');
    const input = document.getElementById('imageInput');
    const previewContainer = document.getElementById('previewContainer');
    const primaryImageInput = document.getElementById('primaryImageInput');

    if (!dropzone || !input || !previewContainer || !primaryImageInput) return;

    let galleryFiles = [];
    let primaryState = null; // { key }
    let currentDragKey = null;
    let isReordering = false;

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
    };

    const refreshInputFiles = () => {
        const dataTransfer = new DataTransfer();

        galleryFiles.forEach(item => {
            dataTransfer.items.add(item.file);
        });

        input.files = dataTransfer.files;
    };

    const animateRemoval = (item, callback) => {
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
    };

    const formatFileSize = (bytes) => {
        let size = bytes / 1024;
        return size > 1024 ? `${(size / 1024).toFixed(2)} MB` : `${size.toFixed(1)} KB`;
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
            if (exists) {
                return;
            }
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

        item.querySelector('.delete-btn').addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();

            galleryFiles = galleryFiles.filter(current => current.key !== key);

            animateRemoval(item, () => {
                ensurePrimarySelection();
                refreshInputFiles();
            });
        });

        item.querySelector('.mark-main-btn').addEventListener('click', (event) => {
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
            alert(`${duplicateCount} imagen(es) ya existen en la galería y fueron omitidas.`);
        }

        ensurePrimarySelection();
        refreshInputFiles();
        newEntries.forEach(({ file, key }) => appendPreview(file, key));
    };

    // Eventos globales
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
        updatePrimaryBadges();
    });

    const form = document.getElementById('postForm');
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

export function initPostGalleryEdit() {
    const dropzone = document.getElementById('customDropzone');
    const input = document.getElementById('imageInput');
    const previewContainer = document.getElementById('previewContainer');
    const deletedImagesInput = document.getElementById('deletedImages');
    const primaryImageInput = document.getElementById('primaryImageInput');

    if (!dropzone || !input || !previewContainer || !primaryImageInput) return;

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

    const animateRemoval = (item, callback) => {
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

                    animateRemoval(item, () => {
                        ensurePrimarySelection();
                    });
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

            animateRemoval(item, () => {
                ensurePrimarySelection();
                refreshInputFiles();
            });
        });

        previewContainer.appendChild(item);
    };

    const renderNewFiles = (entries) => {
        entries.forEach(({ file, key }) => {
            const reader = new FileReader();
            reader.onload = (event) => {
                buildNewPreviewItem(event.target.result, file, key);
                updatePrimaryBadges();
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
        ensurePrimarySelection();
    };

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
            const fileItem = galleryFiles.find(item => item.key === key);
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

    const form = document.getElementById('postForm');
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

// ======================================================
// Inicializadores parametrizables para futuros módulos
// ======================================================

const GALLERY_DUPLICATE_MESSAGE = 'imagen(es) ya existen en la galería y fueron omitidas.';

function galleryFormatFileSize(bytes) {
    let size = bytes / 1024;
    return size > 1024 ? `${(size / 1024).toFixed(2)} MB` : `${size.toFixed(1)} KB`;
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
                    <button type="button" class="mark-main-btn" title="${labels.markTitle}">
                        <i class="${labels.markIconClass}"></i>
                        <span>${labels.markText}</span>
                    </button>
                    <button type="button" class="delete-btn" title="${labels.deleteTitle}">
                        <span class="boton-icon"><i class="${labels.deleteIconClass}"></i></span>
                        <span class="boton-text">${labels.deleteText}</span>
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
        updatePrimaryBadges();
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
                    });
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
                <span class="file-size">${galleryFormatFileSize(file.size)}</span>
                <div class="overlay-actions">
                    <button type="button" class="mark-main-btn" title="${labelsNew.markTitle}">
                        <i class="${labelsNew.markIconClass}"></i>
                        <span>${labelsNew.markText}</span>
                    </button>
                    <button type="button" class="delete-btn" title="${labelsNew.deleteTitle}">
                        <span class="boton-icon"><i class="${labelsNew.deleteIconClass}"></i></span>
                        <span class="boton-text">${labelsNew.deleteText}</span>
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
            });
        });

        previewContainer.appendChild(item);
    };

    const renderNewFiles = (entries) => {
        entries.forEach(({ file, key }) => {
            const reader = new FileReader();
            reader.onload = (event) => {
                buildNewPreviewItem(event.target.result, file, key);
                updatePrimaryBadges();
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
        updatePrimaryBadges();
    });

    registerExistingEvents();
    ensurePrimarySelection();
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
    const galleryDropzone = document.getElementById('galleryDropzone');
    const galleryInput = document.getElementById('galleryInput');
    const galleryPreviewContainer = document.getElementById('galleryPreviewContainer');
    const primaryImageInput = document.getElementById('primaryImageInput');
    const galleryAltContainer = document.getElementById('galleryAltContainer');

    if (!galleryDropzone || !galleryInput || !galleryPreviewContainer || !primaryImageInput) return;

    let galleryFiles = [];
    let primaryState = null; // { key }
    let currentDragKey = null;
    let isReordering = false;

    const movePrimaryToStart = () => {
        if (!primaryState) return;

        const primaryKey = primaryState.key;
        if (!primaryKey) return;

        const primaryItem = galleryPreviewContainer.querySelector(`.preview-item[data-key="${primaryKey}"]`);
        if (!primaryItem) return;

        const siblings = [...galleryPreviewContainer.children];
        const positions = new Map(siblings.map(el => [el.dataset.key, el.getBoundingClientRect()]));

        galleryPreviewContainer.insertBefore(primaryItem, galleryPreviewContainer.firstChild);

        const index = galleryFiles.findIndex(item => item.key === primaryKey);
        if (index > 0) {
            const [entry] = galleryFiles.splice(index, 1);
            galleryFiles.unshift(entry);
            refreshGalleryInput();
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
    };

    const refreshGalleryInput = () => {
        const dataTransfer = new DataTransfer();
        if (galleryAltContainer) {
            galleryAltContainer.innerHTML = '';
        }

        galleryFiles.forEach((item) => {
            dataTransfer.items.add(item.file);

            if (galleryAltContainer) {
                const altInput = document.createElement('input');
                altInput.type = 'hidden';
                altInput.name = 'gallery_alt[]';
                altInput.value = item.alt || '';
                galleryAltContainer.appendChild(altInput);
            }
        });

        galleryInput.files = dataTransfer.files;
    };

    const animateRemoval = (item, callback) => {
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
    };

    const formatFileSize = (bytes) => {
        let size = bytes / 1024;
        return size > 1024 ? `${(size / 1024).toFixed(2)} MB` : `${size.toFixed(1)} KB`;
    };

    const updatePrimaryBadges = () => {
        galleryPreviewContainer.querySelectorAll('.preview-item').forEach(item => {
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

    const buildPreviewItem = (dataUrl, file, key) => {
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
                    <button type="button" class="mark-main-btn" title="Marcar como principal">
                        <i class="ri-star-line"></i>
                        <span>Principal</span>
                    </button>
                    <button type="button" class="delete-btn" title="Eliminar imagen">
                        <span class="boton-icon"><i class="ri-delete-bin-6-line"></i></span>
                        <span class="boton-text">Eliminar</span>
                    </button>
                </div>
            </div>
            <span class="primary-badge">
                <i class="ri-star-fill"></i>
                Principal
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

        item.querySelector('.delete-btn').addEventListener('click', (event) => {
            event.stopPropagation();

            galleryFiles = galleryFiles.filter(current => current.key !== key);

            animateRemoval(item, () => {
                ensurePrimarySelection();
                refreshGalleryInput();
            });
        });

        item.querySelector('.mark-main-btn').addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            setPrimaryState(key);
        });

        return item;
    };

    const appendGalleryPreview = (file, key) => {
        const reader = new FileReader();
        reader.onload = (event) => {
            const item = buildPreviewItem(event.target.result, file, key);
            galleryPreviewContainer.appendChild(item);
            updatePrimaryBadges();
        };
        reader.readAsDataURL(file);
    };

    const handleGalleryFiles = (files) => {
        const newEntries = [];
        let duplicateCount = 0;

        [...files].forEach(file => {
            if (!file.type.startsWith('image/')) {
                return;
            }

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

        ensurePrimarySelection();
        refreshGalleryInput();
        newEntries.forEach(({ file, key }) => appendGalleryPreview(file, key));
    };

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
            handleGalleryFiles(files);
        }
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

    galleryPreviewContainer.addEventListener('dragover', (event) => {
        event.preventDefault();
        if (!currentDragKey || isReordering) return;

        const draggingItem = galleryPreviewContainer.querySelector(`[data-key="${currentDragKey}"]`);
        const targetItem = event.target.closest('.preview-item');
        const isTargetPrimary = targetItem && primaryState && targetItem.dataset.key === primaryState.key;

        if (targetItem && !isTargetPrimary && targetItem !== draggingItem && galleryPreviewContainer.contains(targetItem)) {
            isReordering = true;
            const items = Array.from(galleryPreviewContainer.children);
            const currentIndex = items.indexOf(draggingItem);
            const targetIndex = items.indexOf(targetItem);

            if (currentIndex !== targetIndex) {
                const siblings = [...galleryPreviewContainer.children];
                const positions = new Map(siblings.map(el => [el.dataset.key, el.getBoundingClientRect()]));

                if (currentIndex < targetIndex) {
                    galleryPreviewContainer.insertBefore(draggingItem, targetItem.nextSibling);
                } else {
                    galleryPreviewContainer.insertBefore(draggingItem, targetItem);
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

    galleryPreviewContainer.addEventListener('drop', (event) => {
        event.preventDefault();
        if (!currentDragKey) return;

        const newOrderKeys = Array.from(galleryPreviewContainer.querySelectorAll('.preview-item'))
            .map(item => item.dataset.key);

        const newGalleryFiles = [];
        newOrderKeys.forEach(key => {
            const fileItem = galleryFiles.find(item => item.key === key);
            if (fileItem) newGalleryFiles.push(fileItem);
        });

        galleryFiles = newGalleryFiles;
        refreshGalleryInput();
        ensurePrimarySelection();

        currentDragKey = null;
        const draggingItem = galleryPreviewContainer.querySelector('.dragging');
        if (draggingItem) draggingItem.classList.remove('dragging');
        updatePrimaryBadges();
    });

    const form = document.getElementById('productForm');
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

export function initProductGalleryEdit() {
    const galleryDropzone = document.getElementById('galleryDropzone');
    const galleryInput = document.getElementById('galleryInput');
    const galleryPreviewContainer = document.getElementById('galleryPreviewContainer');
    const removedGalleryContainer = document.getElementById('removedGalleryContainer');
    const primaryImageInput = document.getElementById('primaryImageInput');

    if (!galleryDropzone || !galleryInput || !galleryPreviewContainer || !primaryImageInput) return;

    let galleryFiles = [];
    const removedGalleryIds = new Set();
    let primaryState = null;
    let currentDragKey = null;
    let isReordering = false;

    const refreshGalleryInput = () => {
        const dataTransfer = new DataTransfer();
        galleryFiles.forEach(entry => dataTransfer.items.add(entry.file));
        galleryInput.files = dataTransfer.files;
    };

    const animateRemoval = (item, callback) => {
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
    };

    const formatFileSize = (bytes) => {
        let size = bytes / 1024;
        return size > 1024 ? `${(size / 1024).toFixed(2)} MB` : `${size.toFixed(1)} KB`;
    };

    const getAllPreviewItems = () => Array.from(galleryPreviewContainer.querySelectorAll('.preview-item'));

    const movePrimaryToStart = () => {
        if (!primaryState) return;

        let primaryItem = null;

        if (primaryState.type === 'existing') {
            primaryItem = galleryPreviewContainer.querySelector(`.preview-item[data-type="existing"][data-id="${primaryState.id}"]`);
        } else if (primaryState.type === 'new') {
            primaryItem = galleryPreviewContainer.querySelector(`.preview-item[data-type="new"][data-key="${primaryState.key}"]`);
        }

        if (!primaryItem) return;

        const siblings = [...galleryPreviewContainer.children];
        const positions = new Map(siblings.map(el => [el.dataset.key || `existing-${el.dataset.id}`, el.getBoundingClientRect()]));

        galleryPreviewContainer.insertBefore(primaryItem, galleryPreviewContainer.firstChild);

        if (primaryState.type === 'new') {
            const index = galleryFiles.findIndex(entry => entry.key === primaryState.key);
            if (index > 0) {
                const [entry] = galleryFiles.splice(index, 1);
                galleryFiles.unshift(entry);
                refreshGalleryInput();
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
                const key = item.dataset.key;
                const markButton = item.querySelector('.mark-main-btn');
                const deleteButton = item.querySelector('.delete-existing-gallery');
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

                    if (!removedGalleryIds.has(id)) {
                        removedGalleryIds.add(id);
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'remove_gallery[]';
                        input.value = id;
                        removedGalleryContainer.appendChild(input);
                    }

                    animateRemoval(item, () => {
                        ensurePrimarySelection();
                    });
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

            animateRemoval(item, () => {
                ensurePrimarySelection();
                refreshGalleryInput();
            });
        });

        galleryPreviewContainer.appendChild(item);
    };

    const renderNewFiles = (entries) => {
        entries.forEach(({ file, key }) => {
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
        let duplicateCount = 0;

        [...files].forEach(file => {
            if (!file.type.startsWith('image/')) {
                return;
            }

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

        refreshGalleryInput();
        renderNewFiles(newEntries);
        ensurePrimarySelection();
    };

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
            handleGalleryFiles(files);
        }
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

    galleryPreviewContainer.addEventListener('dragover', (event) => {
        event.preventDefault();
        if (!currentDragKey || isReordering) return;

        const draggingItem = galleryPreviewContainer.querySelector(`[data-key="${currentDragKey}"]`);
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

        if (targetItem && !isTargetPrimary && targetItem !== draggingItem && galleryPreviewContainer.contains(targetItem)) {
            isReordering = true;
            const items = Array.from(galleryPreviewContainer.children);
            const currentIndex = items.indexOf(draggingItem);
            const targetIndex = items.indexOf(targetItem);

            if (currentIndex !== targetIndex) {
                const siblings = [...galleryPreviewContainer.children];
                const positions = new Map(siblings.map(el => [el.dataset.key, el.getBoundingClientRect()]));

                if (currentIndex < targetIndex) {
                    galleryPreviewContainer.insertBefore(draggingItem, targetItem.nextSibling);
                } else {
                    galleryPreviewContainer.insertBefore(draggingItem, targetItem);
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

    galleryPreviewContainer.addEventListener('drop', (event) => {
        event.preventDefault();
        if (!currentDragKey) return;

        const newOrderKeys = Array.from(galleryPreviewContainer.querySelectorAll('.preview-item'))
            .map(item => item.dataset.key);

        const newGalleryFiles = [];
        newOrderKeys.forEach(key => {
            const fileItem = galleryFiles.find(item => item.key === key);
            if (fileItem) newGalleryFiles.push(fileItem);
        });

        galleryFiles = newGalleryFiles;
        refreshGalleryInput();
        ensurePrimarySelection();

        currentDragKey = null;
        const draggingItem = galleryPreviewContainer.querySelector('.dragging');
        if (draggingItem) draggingItem.classList.remove('dragging');
        updatePrimaryBadges();
    });

    registerExistingPreviewEvents();
    ensurePrimarySelection();
    updatePrimaryBadges();

    const form = document.getElementById('productForm');
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
