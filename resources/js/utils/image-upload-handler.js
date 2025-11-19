/**
 * ============================================================================
 * IMAGE UPLOAD HANDLER - Módulo Global
 * ============================================================================
 * Sistema reutilizable para manejo de carga y previsualización de imágenes
 * en formularios de creación y edición.
 * 
 * @author GECKОМERCE
 * @version 1.0.0
 */

export class ImageUploadHandler {
    /**
     * @param {Object} config - Configuración del handler
     * @param {string} config.inputId - ID del input file (default: 'image')
     * @param {string} config.previewZoneId - ID de la zona de previsualización (default: 'imagePreviewZone')
     * @param {string} config.mode - 'create' o 'edit' (default: 'create')
     * @param {boolean} config.hasExistingImage - Si hay imagen existente en modo edit (default: false)
     * @param {string} config.existingImageFilename - Nombre del archivo existente
     */
    constructor(config = {}) {
        // Configuración con valores por defecto
        this.config = {
            inputId: 'image',
            previewZoneId: 'imagePreviewZone',
            placeholderId: 'imagePlaceholder',
            previewId: 'imagePreview',
            previewNewId: 'imagePreviewNew',
            overlayId: 'imageOverlay',
            changeBtnId: 'changeImageBtn',
            removeBtnId: 'removeImageBtn',
            filenameContainerId: 'imageFilename',
            filenameTextId: 'filenameText',
            errorContainerId: 'imageError',
            removeFlagId: 'removeImageFlag',
            mode: 'create',
            hasExistingImage: false,
            existingImageFilename: '',
            ...config
        };

        // Obtener elementos del DOM
        this.elements = this.getElements();

        // Validar que existen los elementos requeridos
        if (!this.elements.input || !this.elements.previewZone) {
            console.error('ImageUploadHandler: Elementos requeridos no encontrados');
            return;
        }

        // Inicializar
        this.init();
    }

    /**
     * Obtiene referencias a todos los elementos del DOM
     */
    getElements() {
        return {
            input: document.getElementById(this.config.inputId),
            previewZone: document.getElementById(this.config.previewZoneId),
            placeholder: document.getElementById(this.config.placeholderId),
            preview: document.getElementById(this.config.previewId),
            previewNew: document.getElementById(this.config.previewNewId),
            overlay: document.getElementById(this.config.overlayId),
            changeBtn: document.getElementById(this.config.changeBtnId),
            removeBtn: document.getElementById(this.config.removeBtnId),
            filenameContainer: document.getElementById(this.config.filenameContainerId),
            filenameText: document.getElementById(this.config.filenameTextId),
            errorContainer: document.getElementById(this.config.errorContainerId),
            removeFlag: document.getElementById(this.config.removeFlagId)
        };
    }

    /**
     * Inicializa event listeners
     */
    init() {
        const { input, previewZone, changeBtn, removeBtn, overlay } = this.elements;

        // Mostrar overlay si hay imagen existente en modo edit
        if (this.config.mode === 'edit' && this.config.hasExistingImage && overlay) {
            overlay.style.display = 'flex';
        }

        // Event: Cambio de archivo
        input.addEventListener('change', (e) => {
            if (e.target.files && e.target.files[0]) {
                this.showImagePreview(e.target.files[0]);
            }
        });

        // Event: Click en botón cambiar
        if (changeBtn) {
            changeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                input.click();
            });
        }

        // Event: Click en botón eliminar
        if (removeBtn) {
            removeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                if (this.config.mode === 'edit') {
                    this.removeImage();
                } else {
                    this.clearImagePreview();
                }
            });
        }

        // Event: Drag over
        previewZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            previewZone.classList.add('drag-over');
        });

        // Event: Drag leave
        previewZone.addEventListener('dragleave', () => {
            previewZone.classList.remove('drag-over');
        });

        // Event: Drop
        previewZone.addEventListener('drop', (e) => {
            e.preventDefault();
            previewZone.classList.remove('drag-over');

            const files = e.dataTransfer.files;
            if (files.length > 0 && files[0].type.startsWith('image/')) {
                input.files = files;
                this.showImagePreview(files[0]);
            }
        });

        // Event: Click en zona vacía o placeholder
        previewZone.addEventListener('click', (e) => {
            // Evitar que el click en botones del overlay dispare el input
            if (e.target.closest('.overlay-btn')) {
                return;
            }

            // Solo permitir click si no hay imagen o es placeholder/error
            const hasImage = previewZone.classList.contains('has-image');
            const isPlaceholder = this.elements.placeholder && this.elements.placeholder.style.display !== 'none';
            const isError = this.elements.errorContainer && this.elements.errorContainer.style.display !== 'none';

            if (!hasImage || isPlaceholder || isError) {
                input.click();
            }
        });

        // Event: Click directo en placeholder (fallback)
        if (this.elements.placeholder) {
            this.elements.placeholder.addEventListener('click', (e) => {
                e.stopPropagation();
                input.click();
            });
        }

        // Event: Click directo en error container (fallback)
        if (this.elements.errorContainer) {
            this.elements.errorContainer.addEventListener('click', (e) => {
                e.stopPropagation();
                input.click();
            });
        }
    }

    /**
     * Muestra previsualización de imagen seleccionada
     * @param {File} file - Archivo de imagen
     */
    showImagePreview(file) {
        if (!file || !file.type.startsWith('image/')) {
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            const {
                previewZone, placeholder, preview, previewNew, overlay,
                filenameContainer, filenameText, removeFlag, errorContainer
            } = this.elements;

            // Ocultar elementos anteriores
            if (placeholder) placeholder.style.display = 'none';
            if (errorContainer) errorContainer.style.display = 'none';

            // Determinar qué elemento de preview usar
            const previewElement = this.config.mode === 'edit' && previewNew ? previewNew : preview;

            if (previewElement) {
                // Ocultar preview antiguo en modo edit
                if (this.config.mode === 'edit' && preview) {
                    preview.style.display = 'none';
                }

                // Mostrar nueva imagen
                previewElement.src = e.target.result;
                previewElement.style.display = 'block';
            }

            // Mostrar overlay
            if (overlay) overlay.style.display = 'flex';

            // Agregar clase has-image
            previewZone.classList.add('has-image');

            // Restaurar flag de eliminación en modo edit
            if (removeFlag) removeFlag.value = '0';

            // Mostrar nombre de archivo
            if (filenameText) filenameText.textContent = file.name;
            if (filenameContainer) filenameContainer.style.display = 'flex';
        };

        reader.readAsDataURL(file);
    }

    /**
     * Limpia previsualización (modo CREATE)
     */
    clearImagePreview() {
        const {
            input, previewZone, placeholder, preview, overlay,
            filenameContainer, filenameText
        } = this.elements;

        // Limpiar input y preview
        input.value = '';
        if (preview) {
            preview.src = '';
            preview.style.display = 'none';
        }

        // Ocultar overlay
        if (overlay) overlay.style.display = 'none';

        // Remover clase has-image
        previewZone.classList.remove('has-image');

        // Ocultar filename
        if (filenameContainer) filenameContainer.style.display = 'none';
        if (filenameText) filenameText.textContent = '';

        // Mostrar placeholder
        if (placeholder) placeholder.style.display = 'flex';
    }

    /**
     * Elimina imagen (modo EDIT - restaura estado original)
     */
    removeImage() {
        const {
            input, previewZone, placeholder, preview, previewNew, overlay,
            filenameContainer, filenameText, removeFlag, errorContainer
        } = this.elements;

        // Limpiar input y nueva preview
        input.value = '';
        if (previewNew) {
            previewNew.src = '';
            previewNew.style.display = 'none';
        }

        // Ocultar preview existente
        if (preview) preview.style.display = 'none';

        // Activar flag de eliminación si hay imagen existente
        if (this.config.hasExistingImage && removeFlag) {
            removeFlag.value = '1';
        }

        // Ocultar overlay
        if (overlay) overlay.style.display = 'none';

        // Remover clase has-image
        previewZone.classList.remove('has-image');

        // Ocultar filename
        if (filenameContainer) filenameContainer.style.display = 'none';

        // Mostrar placeholder o error según corresponda
        if (errorContainer) {
            errorContainer.style.display = 'flex';
        } else if (placeholder) {
            placeholder.style.display = 'flex';
        }
    }

    /**
     * Restaura estado original en modo EDIT
     */
    restoreOriginalState() {
        const {
            input, previewZone, preview, previewNew, overlay,
            filenameContainer, filenameText, removeFlag, placeholder, errorContainer
        } = this.elements;

        // Limpiar input y nueva preview
        input.value = '';
        if (previewNew) {
            previewNew.src = '';
            previewNew.style.display = 'none';
        }

        // Restaurar flag
        if (removeFlag) removeFlag.value = '0';

        // Si hay imagen existente, restaurarla
        if (this.config.hasExistingImage && preview) {
            preview.style.display = 'block';
            if (overlay) overlay.style.display = 'flex';
            previewZone.classList.add('has-image');

            if (filenameText) filenameText.textContent = this.config.existingImageFilename;
            if (filenameContainer) filenameContainer.style.display = 'flex';
        } else if (errorContainer) {
            errorContainer.style.display = 'flex';
            if (overlay) overlay.style.display = 'none';
            previewZone.classList.remove('has-image');
            if (filenameContainer) filenameContainer.style.display = 'none';
        } else if (placeholder) {
            placeholder.style.display = 'flex';
            if (overlay) overlay.style.display = 'none';
            previewZone.classList.remove('has-image');
            if (filenameContainer) filenameContainer.style.display = 'none';
        }
    }

    /**
     * Destruye el handler y remueve event listeners
     */
    destroy() {
        // Los event listeners se removerán automáticamente al reasignar el DOM
        this.elements = null;
        this.config = null;
    }
}

/**
 * Factory function para inicialización rápida
 * @param {Object} config - Configuración del handler
 * @returns {ImageUploadHandler}
 */
export function initImageUpload(config) {
    return new ImageUploadHandler(config);
}
