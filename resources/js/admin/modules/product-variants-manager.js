function safeParseJson(raw, fallback) {
    if (!raw) return fallback;
    try {
        return JSON.parse(raw);
    } catch (error) {
        console.warn('[product-variants-manager] JSON invalido', error);
        return fallback;
    }
}

function slugifySegment(value) {
    return String(value || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toUpperCase()
        .trim()
        .replace(/[^A-Z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}


function buildOptionsIndex(optionsData) {
    const options = [];
    const featureMap = new Map();

    optionsData.forEach((option, optionIndex) => {
        const normalizedOption = {
            id: Number(option.id),
            name: String(option.name || ''),
            isColor: Boolean(option.is_color),
            order: optionIndex,
            features: Array.isArray(option.features)
                ? option.features.map((feature) => ({
                    id: Number(feature.id),
                    value: String(feature.value ?? '').trim(),
                    description: String(feature.description ?? '').trim(),
                }))
                : [],
        };

        normalizedOption.features.forEach((feature) => {
            const label = normalizedOption.isColor
                ? (feature.value || feature.description)
                : feature.value;

            featureMap.set(feature.id, {
                featureId: feature.id,
                optionId: normalizedOption.id,
                optionName: normalizedOption.name,
                optionOrder: normalizedOption.order,
                value: feature.value,
                description: feature.description,
                label,
                isColor: normalizedOption.isColor,
            });
        });

        options.push(normalizedOption);
    });

    return { options, featureMap };
}

function buildCombinationKey(featureIds) {
    return featureIds
        .map((id) => Number(id))
        .filter((id) => Number.isFinite(id) && id > 0)
        .sort((a, b) => a - b)
        .join('-');
}

function sortByOptionOrder(featuresMeta) {
    return [...featuresMeta].sort((a, b) => {
        if (a.optionOrder === b.optionOrder) {
            return a.featureId - b.featureId;
        }
        return a.optionOrder - b.optionOrder;
    });
}

function createVariantLabel(featuresMeta) {
    if (!featuresMeta.length) {
        return 'Sin opciones';
    }

    const labels = sortByOptionOrder(featuresMeta)
        .map((meta) => meta.label || meta.value)
        .filter(Boolean);

    return labels.length ? labels.join(' / ') : 'Sin opciones';
}

function formatPrice(value) {
    const num = Number(value);
    if (!Number.isFinite(num)) {
        return '-';
    }

    return `S/. ${num.toFixed(2)}`;
}

function normalizeVariant(rawVariant, featureMap) {
    const rawFeatures = Array.isArray(rawVariant.features) ? rawVariant.features : [];
    const featuresMeta = rawFeatures
        .map((feature) => featureMap.get(Number(feature.id)))
        .filter(Boolean);

    const featureIds = sortByOptionOrder(featuresMeta).map((meta) => meta.featureId);

    return {
        id: rawVariant.id ?? null,
        price: rawVariant.price === null || rawVariant.price === '' ? '' : String(rawVariant.price),
        stock: rawVariant.stock === null || rawVariant.stock === '' ? '0' : String(rawVariant.stock),
        status: Boolean(rawVariant.status),
        featureIds,
    };
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

export function initProductVariantsManager({
    containerId,
    emptyStateId,
    addButtonId,
} = {}) {
    const container = containerId ? document.getElementById(containerId) : null;
    if (!container) return null;

    if (container.dataset.variantsManagerInitialized === '1') {
        return null;
    }
    container.dataset.variantsManagerInitialized = '1';

    const emptyState = emptyStateId ? document.getElementById(emptyStateId) : null;
    const addButton = addButtonId ? document.getElementById(addButtonId) : null;
    const form = container.closest('form');
    const validator = form && form.__validator ? form.__validator : null;

    const modal = document.getElementById('variantCrudModal');
    const modalOptions = document.getElementById('variantModalOptions');
    const modalTitle = document.getElementById('variantModalTitle');
    const modalError = document.getElementById('variantModalError');
    const saveVariantBtn = document.getElementById('saveVariantBtn');
    const priceInput = document.getElementById('variantModalPrice');
    const stockInput = document.getElementById('variantModalStock');
    const statusActiveInput = document.getElementById('variantModalStatusActive');
    const statusInactiveInput = document.getElementById('variantModalStatusInactive');

    if (!modal || !modalOptions || !modalTitle || !modalError || !saveVariantBtn || !priceInput || !stockInput || !statusActiveInput || !statusInactiveInput) {
        console.warn('[product-variants-manager] No se encontro la modal de variantes.');
        return null;
    }

    const optionsData = safeParseJson(container.dataset.options, []);
    const initialVariantsData = safeParseJson(container.dataset.initialVariants, []);
    const { options, featureMap } = buildOptionsIndex(optionsData);

    const tbody = container.querySelector('[data-role="variants-body"]');
    if (!tbody) return null;

    let variants = initialVariantsData.map((item) => normalizeVariant(item, featureMap));
    let editingIndex = null;
    let isModalClosing = false;

    if (modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }

    function getFeaturesMeta(featureIds) {
        return featureIds
            .map((featureId) => featureMap.get(Number(featureId)))
            .filter(Boolean);
    }

    function getModalValidationFields() {
        const optionFields = Array.from(modalOptions.querySelectorAll('[data-option-select]'));
        return [...optionFields, priceInput, stockInput];
    }

    function clearModalFieldFeedback() {
        if (!validator) return;

        getModalValidationFields().forEach((field) => {
            validator.clearError(field);
            validator.clearSuccess(field);
        });
    }

    function validateModalFields() {
        if (!validator) return true;

        let isValid = true;
        getModalValidationFields().forEach((field) => {
            const fieldValid = validator.validateField(field);
            if (!fieldValid) {
                isValid = false;
            }
        });

        return isValid;
    }

    function collectFormVariantDraft() {
        const selectedFeatureIds = [];
        const hasValidator = Boolean(validator);

        if (options.length) {
            for (const option of options) {
                const select = modalOptions.querySelector(`[data-option-select="${option.id}"]`);
                const selected = select ? Number(select.value) : 0;

                if (!selected && !hasValidator) {
                    return {
                        valid: false,
                        message: `Selecciona un valor para ${option.name}.`,
                    };
                }

                selectedFeatureIds.push(selected);
            }
        }

        const stockRaw = String(stockInput.value || '').trim();
        const stockNum = stockRaw === '' ? 0 : Number(stockRaw);
        if ((!Number.isFinite(stockNum) || stockNum < 0) && !hasValidator) {
            return {
                valid: false,
                message: 'El stock debe ser un numero valido mayor o igual a 0.',
            };
        }

        const priceRaw = String(priceInput.value || '').trim();
        if (priceRaw !== '') {
            const priceNum = Number(priceRaw);
            if ((!Number.isFinite(priceNum) || priceNum < 0) && !hasValidator) {
                return {
                    valid: false,
                    message: 'El precio debe ser un numero valido mayor o igual a 0.',
                };
            }
        }

        const normalizedFeatureIds = buildCombinationKey(selectedFeatureIds)
            .split('-')
            .filter(Boolean)
            .map((id) => Number(id));

        const key = buildCombinationKey(normalizedFeatureIds);
        const duplicated = variants.some((variant, index) => {
            if (editingIndex !== null && index === editingIndex) {
                return false;
            }
            return buildCombinationKey(variant.featureIds) === key;
        });

        if (duplicated) {
            if (validator) {
                const duplicateField = modalOptions.querySelector('[data-option-select]');
                validator.showError(duplicateField, 'Esta combinacion de opciones ya fue agregada. Selecciona otra.');
            }
            return {
                valid: false,
                message: 'Esta combinacion de opciones ya fue agregada. Selecciona otra.',
            };
        }

        return {
            valid: true,
            data: {
                id: editingIndex !== null ? variants[editingIndex].id : null,
                price: priceRaw,
                stock: String(Math.floor(stockNum)),
                status: statusActiveInput.checked,
                featureIds: normalizedFeatureIds,
            },
        };
    }

    function renderRows() {
        const rows = variants
            .map((variant, index) => {
                const featuresMeta = getFeaturesMeta(variant.featureIds);
                const label = createVariantLabel(featuresMeta);
                const statusText = variant.status ? 'Activo' : 'Inactivo';
                const statusClass = variant.status ? 'success' : 'danger';
                const statusicon = variant.status ? 'ri-checkbox-circle-fill' : 'ri-close-circle-fill';
                const hiddenId = `<input type="hidden" name="variants[${index}][id]" value="${escapeHtml(variant.id ?? '')}">`;
                const hiddenPrice = `<input type="hidden" name="variants[${index}][price]" value="${escapeHtml(variant.price)}">`;
                const hiddenStock = `<input type="hidden" name="variants[${index}][stock]" value="${escapeHtml(variant.stock)}">`;
                const hiddenStatus = `<input type="hidden" name="variants[${index}][status]" value="${variant.status ? '1' : '0'}">`;
                const hiddenFeatures = variant.featureIds
                    .map((featureId) => `<input type="hidden" name="variants[${index}][features][]" value="${featureId}">`)
                    .join('');

                return `
          <tr class="" data-index="${index}">
            <td class="column-name-td">
              <div class="variant-label">${escapeHtml(label)}</div>
              ${hiddenId}${hiddenFeatures}
            </td>
            <td class="column-variant-price">
              ${escapeHtml(formatPrice(variant.price))}
              ${hiddenPrice}
            </td>
            <td class="column-variant-stock">
              ${escapeHtml(variant.stock)}
              ${hiddenStock}
            </td>
            <td class="column-variant-status">
              <span class="badge badge-${statusClass}">
                <i class="${statusicon}"></i>
                ${statusText}
              </span>
              ${hiddenStatus}
            </td>
            <td class="column-actions-td">
                <button class="boton-show-actions">
                    <i class="ri-more-fill"></i>
                </button>
                <div class="tabla-botones">
                    <button type="button" class="boton-sm boton-warning" data-action="edit-variant" data-index="${index}" title="Editar variante">
                    <i class="ri-edit-circle-fill"></i>
                    <span class="boton-sm-text">Editar Variante</span>
                    </button>

                    <button type="button" class="boton-sm boton-danger" data-action="remove-variant" data-index="${index}" title="Eliminar variante">
                    <i class="ri-delete-bin-2-fill"></i>
                        <span class="boton-sm-text">Eliminar Variante</span>
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
    }

    function resetModalError() {
        modalError.hidden = true;
        modalError.textContent = '';
    }

    function setModalError(message) {
        modalError.hidden = false;
        modalError.textContent = message;
    }

    function buildModalOptions(selectedFeatureIds = []) {
        if (!options.length) {
            modalOptions.innerHTML = '<p class="variant-modal-no-options">No hay opciones disponibles. Crea opciones y valores para generar variantes.</p>';
            return;
        }

        const selectedSet = new Set(selectedFeatureIds.map((id) => Number(id)));

        modalOptions.innerHTML = options
            .map((option) => {
                const optionChoices = option.features
                    .map((feature) => {
                        const label = option.isColor
                            ? (feature.value || feature.description)
                            : feature.value;
                        const selected = selectedSet.has(feature.id) ? 'selected' : '';
                        return `<option value="${feature.id}" ${selected}>${escapeHtml(label)}</option>`;
                    })
                    .join('');

                return `
          <div class="input-group">
            <label class="label-form">${escapeHtml(option.name)}</label>
            <div class="input-icon-container">
              <i class="ri-shapes-line input-icon"></i>
                            <select class="select-form" data-option-select="${option.id}" data-validate="selected">
                <option value="">Seleccione un valor</option>
                ${optionChoices}
              </select>
              <i class="ri-arrow-down-s-line select-arrow"></i>
            </div>
          </div>`;
            })
            .join('');
    }

    function openModal(mode, index = null) {
        if (isModalClosing) {
            return;
        }

        editingIndex = mode === 'edit' ? index : null;
        resetModalError();
        const bgheader = modal.querySelector('#variantModalHeader');

        if (mode === 'edit' && editingIndex !== null && variants[editingIndex]) {
            bgheader.classList.add('bg-warning');
            const current = variants[editingIndex];
            modalTitle.textContent = 'Editar variante';
            buildModalOptions(current.featureIds);
            priceInput.value = current.price;
            stockInput.value = current.stock;
            statusActiveInput.checked = Boolean(current.status);
            statusInactiveInput.checked = !Boolean(current.status);
        } else {
            modalTitle.textContent = 'Agregar variante';
            bgheader.classList.remove('bg-warning');
            bgheader.classList.add('bg-success');
            buildModalOptions([]);
            const labels = [];
            priceInput.value = '';
            stockInput.value = '0';
            statusActiveInput.checked = true;
            statusInactiveInput.checked = false;
        }

        clearModalFieldFeedback();

        const sidebar = document.getElementById('logo-sidebar');
        if (sidebar) {
            sidebar.style.zIndex = '1';
        }

        modal.hidden = false;
        modal.classList.remove('is-open');
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');

        const dialog = modal.querySelector('.variant-crud-modal-dialog');
        if (dialog) {
            dialog.classList.remove('animate-out');
            dialog.classList.add('animate-in');
        }
    }

    function closeModal() {
        if (isModalClosing || modal.hidden) {
            return;
        }

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
            clearModalFieldFeedback();
            isModalClosing = false;
        }, 260);
    }

    function saveModalVariant() {
        if (!validateModalFields()) {
            return;
        }

        const result = collectFormVariantDraft();
        if (!result.valid) {
            if (!validator) {
                setModalError(result.message);
            }
            return;
        }

        if (editingIndex !== null && variants[editingIndex]) {
            variants[editingIndex] = result.data;
        } else {
            variants.push(result.data);
        }

        renderRows();
        closeModal();
    }

    function removeVariant(index) {
        if (!Number.isInteger(index) || index < 0 || index >= variants.length) {
            return;
        }

        const doDelete = () => {
            variants.splice(index, 1);
            renderRows();
        };

        if (typeof window.showConfirm === 'function') {
            window.showConfirm({
                type: 'danger',
                header: 'Eliminar variante',
                title: 'Deseas eliminar esta variante?',
                message: 'Esta accion no se puede deshacer.',
                confirmText: 'Si, eliminar',
                cancelText: 'Cancelar',
                onConfirm: doDelete,
            });
            return;
        }

        if (window.confirm('Deseas eliminar esta variante?')) {
            doDelete();
        }
    }

    if (!options.length) {
        showWarning(
            'Variantes sin opciones',
            'No hay opciones configuradas. Primero crea opciones y valores para poder registrar variantes.'
        );
    }

    if (addButton) {
        addButton.addEventListener('click', (event) => {
            event.preventDefault();
            openModal('create');
        });
    }

    saveVariantBtn.addEventListener('click', saveModalVariant);

    modal.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        if (target.closest('[data-action="close-variant-modal"]')) {
            closeModal();
            return;
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.hidden) {
            closeModal();
        }
    });

    container.addEventListener('click', (event) => {
        const button = event.target.closest('[data-action]');
        if (!button) return;

        const rowIndex = Number(button.dataset.index);
        if (!Number.isInteger(rowIndex)) return;

        if (button.dataset.action === 'edit-variant') {
            openModal('edit', rowIndex);
            return;
        }

        if (button.dataset.action === 'remove-variant') {
            removeVariant(rowIndex);
        }
    });

    renderRows();

    return {
        openCreateModal: () => openModal('create'),
        openEditModal: (index) => openModal('edit', index),
        getVariants: () => [...variants],
    };
}

// Inicializacion automatica de respaldo por si no se llama explicitamente.
document.addEventListener('DOMContentLoaded', () => {
    const autoContainer = document.getElementById('variantsContainer');
    if (!autoContainer || autoContainer.dataset.variantsManagerInitialized === '1') {
        return;
    }

    initProductVariantsManager({
        containerId: 'variantsContainer',
        emptyStateId: 'variantsEmpty',
        addButtonId: 'addVariantBtn',
    });
});
