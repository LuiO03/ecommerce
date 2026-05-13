import { normalizeColorValue } from './options-form-feature-manager.js';

function safeParseJson(raw, fallback) {
    if (!raw) return fallback;
    try {
        return JSON.parse(raw);
    } catch (error) {
        console.warn('[options-features-modal] JSON invalido', error);
        return fallback;
    }
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function slugify(value) {
    return String(value || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

function normaliseTextValue(value) {
    const compact = String(value || '')
        .trim()
        .replace(/\s+/g, ' ')
        .toLowerCase();
    return compact.replace(/\b\w/g, (match) => match.toUpperCase());
}

function showWarning(title, message) {
    if (typeof window.showInfoModal === 'function') {
        window.showInfoModal({
            type: 'warning',
            header: title,
            title,
            message,
        });
        return;
    }

    alert(message);
}



export function initOptionFeaturesModal({
    containerId,
    emptyStateId,
    addButtonId,
    modalId,
    inputsContainerId,
    nameInputId,
    descriptionHeaderId,
} = {}) {
    const tbody = containerId ? document.getElementById(containerId) : null;
    if (!tbody) return null;

    if (tbody.dataset.featuresManagerInitialized === '1') {
        return null;
    }
    tbody.dataset.featuresManagerInitialized = '1';

    const emptyState = emptyStateId ? document.getElementById(emptyStateId) : null;
    const addButton = addButtonId ? document.getElementById(addButtonId) : null;
    const modal = modalId ? document.getElementById(modalId) : null;
    const inputsContainer = inputsContainerId ? document.getElementById(inputsContainerId) : null;
    const nameInput = nameInputId ? document.getElementById(nameInputId) : null;
    const descHeader = descriptionHeaderId ? document.getElementById(descriptionHeaderId) : null;

    if (!modal || !inputsContainer) {
        console.warn('[options-features-modal] No se encontro la modal de valores.');
        return null;
    }

    const modalTitle = document.getElementById('optionFeatureModalTitle');
    const modalError = document.getElementById('optionFeatureModalError');
    const modalHeader = document.getElementById('optionFeatureModalHeader');
    const saveBtn = document.getElementById('saveOptionFeatureBtn');
    const valueInput = document.getElementById('optionFeatureValue');
    const descInput = document.getElementById('optionFeatureDescription');
    const hexInput = document.getElementById('optionFeatureHex');
    const hexLabel = document.getElementById('optionFeatureHexLabel');
    const swatch = document.getElementById('optionFeatureSwatch');
    const colorWrapper = modal.querySelector('[data-role="option-feature-color"]');
    const descWrapper = modal.querySelector('[data-role="option-feature-description"]');
    const colorHeader = tbody.closest('table')?.querySelector('[data-role="feature-color-header"]');

    if (!modalTitle || !modalError || !saveBtn || !valueInput || !descInput || !hexInput || !hexLabel || !swatch) {
        console.warn('[options-features-modal] No se encontro el formulario de la modal.');
        return null;
    }

    const initialFeatures = safeParseJson(tbody.dataset.features, []);
    const state = {
        isColor: tbody.dataset.isColor === 'true',
        colorSlug: tbody.dataset.colorSlug || 'color',
        colorLocked: tbody.dataset.colorLocked === 'true',
        features: Array.isArray(initialFeatures) ? initialFeatures.map((item) => ({
            id: item.id ?? null,
            value: String(item.value ?? ''),
            description: String(item.description ?? ''),
        })) : [],
    };

    let editingIndex = null;
    let isModalClosing = false;

    if (modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }

    function resetModalError() {
        modalError.hidden = true;
        modalError.textContent = '';
    }

    function setModalError(message) {
        modalError.hidden = false;
        modalError.textContent = message;
    }

    function updateColorPreview(value) {
        const normalized = normalizeColorValue(value);
        const safe = normalized ?? '#000000';
        hexLabel.textContent = safe;
        swatch.style.setProperty('--swatch-color', safe);
    }

    function setModalMode(mode) {
        const isEdit = mode === 'edit';
        if (modalHeader) {
            modalHeader.classList.toggle('bg-warning', isEdit);
            modalHeader.classList.toggle('bg-success', !isEdit);
        }
    }

    function setSaveButtonMode(isEdit) {
        saveBtn.classList.toggle('bg-success', !isEdit);
        saveBtn.classList.toggle('bg-warning', isEdit);

        const textEl = saveBtn.querySelector('.boton-text');
        if (textEl) {
            textEl.textContent = isEdit ? 'Actualizar valor' : 'Agregar valor';
        }
    }

    function openModal(mode, index = null) {
        if (isModalClosing) return;

        editingIndex = mode === 'edit' ? index : null;
        resetModalError();
        setModalMode(mode);

        const isEdit = editingIndex !== null && state.features[editingIndex];
        setSaveButtonMode(isEdit);
        modalTitle.textContent = isEdit ? 'Editar valor' : 'Agregar valor';

        const current = isEdit ? state.features[editingIndex] : null;
        valueInput.value = current?.value ?? '';

        if (state.isColor) {
            const normalized = normalizeColorValue(current?.description ?? '') ?? '#000000';
            hexInput.value = normalized;
            updateColorPreview(normalized);
        } else {
            descInput.value = current?.description ?? '';
        }

        if (colorWrapper && descWrapper) {
            colorWrapper.hidden = !state.isColor;
            descWrapper.hidden = state.isColor;
        }

        const sidebar = document.getElementById('logo-sidebar');
        if (sidebar) {
            sidebar.style.zIndex = '1';
        }

        modal.hidden = false;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');

        const dialog = modal.querySelector('.variant-crud-modal-dialog');
        if (dialog) {
            dialog.classList.remove('animate-out');
            dialog.classList.add('animate-in');
        }

        if (window.Coloris) {
            window.Coloris({ el: '#optionFeatureHex' });
        }
    }

    function closeModal() {
        if (isModalClosing || modal.hidden) return;

        isModalClosing = true;

        const dialog = modal.querySelector('.variant-crud-modal-dialog');
        if (dialog) {
            dialog.classList.remove('animate-in');
            dialog.classList.add('animate-out');
        }

        window.setTimeout(() => {
            const sidebar = document.getElementById('logo-sidebar');
            if (sidebar) {
                sidebar.style.zIndex = '';
            }

            modal.classList.remove('is-open');
            modal.hidden = true;
            modal.setAttribute('aria-hidden', 'true');
            if (dialog) {
                dialog.classList.remove('animate-out');
            }

            editingIndex = null;
            resetModalError();
            isModalClosing = false;
        }, 260);
    }

    function normalizeValueInput(raw) {
        return state.isColor ? normaliseTextValue(raw) : normaliseTextValue(raw);
    }

    function collectModalData() {
        const rawValue = String(valueInput.value || '').trim();
        if (!rawValue) {
            return { valid: false, message: 'Ingresa un nombre para el valor.' };
        }

        const normalizedValue = normalizeValueInput(rawValue);

        let description = '';
        if (state.isColor) {
            const rawHex = String(hexInput.value || '').trim();
            const normalizedHex = normalizeColorValue(rawHex);
            if (!normalizedHex) {
                return { valid: false, message: 'El HEX debe ser un color valido (#RRGGBB).' };
            }
            description = normalizedHex;
        } else {
            const rawDescription = String(descInput.value || '').trim();
            description = rawDescription;
        }

        const duplicate = state.features.some((feature, index) => {
            if (editingIndex !== null && index === editingIndex) {
                return false;
            }
            return normaliseTextValue(feature.value) === normalizedValue;
        });

        if (duplicate) {
            return { valid: false, message: 'Este valor ya existe. Ingresa uno diferente.' };
        }

        return {
            valid: true,
            data: {
                id: editingIndex !== null ? state.features[editingIndex].id : null,
                value: normalizedValue,
                description: description,
            },
        };
    }

    function renderInputs() {
        inputsContainer.innerHTML = state.features
            .map((feature, index) => {
                const idInput = `<input type="hidden" name="features[${index}][id]" value="${escapeHtml(feature.id ?? '')}">`;
                const valueInputHidden = `<input type="hidden" name="features[${index}][value]" value="${escapeHtml(feature.value ?? '')}">`;
                const descInputHidden = `<input type="hidden" name="features[${index}][description]" value="${escapeHtml(feature.description ?? '')}">`;
                return `${idInput}${valueInputHidden}${descInputHidden}`;
            })
            .join('');
    }

    function renderRows() {
        const rows = state.features
            .map((feature, index) => {
                const normalizedHex = normalizeColorValue(feature.description ?? '') ?? '#000000';
                const colorCell = state.isColor
                    ? `<span class="option-feature-swatch" style="--swatch-color:${escapeHtml(normalizedHex)}"></span>`
                    : '';

                const descriptionCell = state.isColor
                    ? escapeHtml(feature.description || '—')
                    : escapeHtml(feature.description || '—');

                const colorClass = state.isColor ? 'option-feature-color-column' : 'option-feature-color-column is-hidden';

                return `
            <tr data-index="${index}">
                <td class="column-name-td">${escapeHtml(feature.value || '—')}</td>
                <td class="column-description-td">${descriptionCell}</td>
                <td class="column-color-td ${colorClass}">${colorCell}</td>
                <td class="column-actions-td">
                    <button class="boton-show-actions" type="button">
                        <i class="ri-more-fill"></i>
                    </button>
                    <div class="tabla-botones">
                        <button type="button" class="boton-sm boton-warning" data-action="edit-feature" data-index="${index}">
                            <i class="ri-edit-circle-fill"></i>
                            <span class="boton-sm-text">Editar</span>
                        </button>
                        <button type="button" class="boton-sm boton-danger" data-action="remove-feature" data-index="${index}">
                            <i class="ri-delete-bin-2-fill"></i>
                            <span class="boton-sm-text">Eliminar</span>
                        </button>
                    </div>
                </td>
            </tr>`;
            })
            .join('');

        if (rows) {
            tbody.innerHTML = rows;
            if (emptyState) {
                emptyState.classList.add('is-hidden');
            }
        } else {
            tbody.innerHTML = emptyState ? emptyState.outerHTML : '';
        }

        renderInputs();
    }

    function saveFeature() {
        const result = collectModalData();
        if (!result.valid) {
            setModalError(result.message);
            return;
        }

        if (editingIndex !== null && state.features[editingIndex]) {
            state.features[editingIndex] = result.data;
        } else {
            state.features.push(result.data);
        }

        renderRows();
        closeModal();
    }

    function removeFeature(index) {
        if (!Number.isInteger(index) || index < 0 || index >= state.features.length) {
            return;
        }

        const doDelete = () => {
            state.features.splice(index, 1);
            renderRows();
        };

        if (typeof window.showConfirm === 'function') {
            window.showConfirm({
                type: 'danger',
                header: 'Eliminar valor',
                title: 'Deseas eliminar este valor?',
                message: 'Esta accion no se puede deshacer.',
                confirmText: 'Si, eliminar',
                cancelText: 'Cancelar',
                onConfirm: doDelete,
            });
            return;
        }

        if (window.confirm('Deseas eliminar este valor?')) {
            doDelete();
        }
    }

    function applyColorMode(isColor) {
        state.isColor = Boolean(isColor);
        tbody.dataset.isColor = state.isColor ? 'true' : 'false';
        if (descHeader) {
            descHeader.textContent = state.isColor ? 'HEX' : 'Descripcion';
        }

        if (colorHeader) {
            colorHeader.classList.toggle('is-hidden', !state.isColor);
        }

        if (state.isColor) {
            state.features = state.features.map((feature) => ({
                ...feature,
                description: normalizeColorValue(feature.description) ?? '#000000',
            }));
        }

        renderRows();
    }

    function updateColorModeFromName() {
        if (!nameInput || state.colorLocked) {
            return;
        }
        const next = slugify(nameInput.value) === state.colorSlug;
        applyColorMode(next);
    }

    if (!state.features.length) {
        showWarning('Valores vacios', 'Agrega valores para continuar.');
    }

    if (addButton) {
        addButton.addEventListener('click', (event) => {
            event.preventDefault();
            openModal('create');
        });
    }

    saveBtn.addEventListener('click', saveFeature);

    modal.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        if (target.closest('[data-action="close-feature-modal"]')) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.hidden) {
            closeModal();
        }
    });

    tbody.addEventListener('click', (event) => {
        const button = event.target.closest('[data-action]');
        if (!button) return;

        const rowIndex = Number(button.dataset.index);
        if (!Number.isInteger(rowIndex)) return;

        if (button.dataset.action === 'edit-feature') {
            openModal('edit', rowIndex);
        }

        if (button.dataset.action === 'remove-feature') {
            removeFeature(rowIndex);
        }
    });

    hexInput.addEventListener('input', () => updateColorPreview(hexInput.value));

    if (nameInput && !state.colorLocked) {
        nameInput.addEventListener('input', updateColorModeFromName);
        updateColorModeFromName();
    }

    if (!descHeader) {
        tbody.dataset.isColor = state.isColor ? 'true' : 'false';
    }

    if (colorHeader) {
        colorHeader.classList.toggle('is-hidden', !state.isColor);
    }

    renderRows();

    return {
        openCreateModal: () => openModal('create'),
        openEditModal: (index) => openModal('edit', index),
        getFeatures: () => [...state.features],
    };
}

// Inicializacion automatica de respaldo por si no se llama explicitamente.
document.addEventListener('DOMContentLoaded', () => {
    const autoContainer = document.getElementById('optionFeaturesBody');
    if (!autoContainer || autoContainer.dataset.featuresManagerInitialized === '1') {
        return;
    }

    initOptionFeaturesModal({
        containerId: 'optionFeaturesBody',
        emptyStateId: 'optionFeaturesEmpty',
        addButtonId: 'addFeatureBtn',
        modalId: 'optionFeatureModal',
        inputsContainerId: 'optionFeaturesInputs',
        nameInputId: 'name',
        descriptionHeaderId: 'optionFeaturesHeaderDescription',
    });
});
