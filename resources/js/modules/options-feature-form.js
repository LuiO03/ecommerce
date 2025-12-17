function normalizeColorValue(value) {
    const raw = String(value || '').trim().replace(/^#/, '').toUpperCase();

    if (!raw) {
        return null;
    }

    if (!/^([0-9A-F]{3}|[0-9A-F]{6})$/.test(raw)) {
        return null;
    }

    const expanded = raw.length === 3
        ? raw.split('').map((char) => char + char).join('')
        : raw;

    return `#${expanded}`;
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

export function initOptionFeatureForm({
    containerId,
    addButtonId,
    typeSelectId,
    templateId,
} = {}) {
    const container = document.getElementById(containerId);
    if (!container) {
        return null;
    }

    const templateNode = document.getElementById(templateId);
    const templateContent = templateNode ? templateNode.textContent.trim() : '';
    const addButton = document.getElementById(addButtonId);
    const typeSelect = document.getElementById(typeSelectId);

    const colorType = Number(container.dataset.colorType || 0);
    const sizeType = Number(container.dataset.sizeType || 0);

    const state = {
        template: templateContent,
        colorType,
        sizeType,
    };

    if (!state.template) {
        console.warn('[options-feature-form] Plantilla no encontrada.');
    }

    function renderTemplate(data, index) {
        if (!state.template) {
            return null;
        }

        const color = data.color || '#000000';

        let html = state.template
            .replace(/__INDEX__/g, index)
            .replace(/__ID__/g, escapeHtml(data.id ?? ''))
            .replace(/__NUMBER__/g, index + 1)
            .replace(/__VALUE__/g, escapeHtml(data.value ?? ''))
            .replace(/__DESCRIPTION__/g, escapeHtml(data.description ?? ''))
            .replace(/__COLOR__/g, escapeHtml(color));

        const wrapper = document.createElement('div');
        wrapper.innerHTML = html.trim();
        return wrapper.firstElementChild;
    }

    function getFeatureCards() {
        return Array.from(container.querySelectorAll('.option-feature-card'));
    }

    function reindexFeatures() {
        const cards = getFeatureCards();
        cards.forEach((card, index) => {
            card.dataset.featureIndex = index;

            const idInput = card.querySelector('input[type="hidden"]');
            if (idInput) {
                idInput.name = `features[${index}][id]`;
            }

            const valueInput = card.querySelector('[data-role="feature-value"]');
            if (valueInput) {
                valueInput.name = `features[${index}][value]`;
            }

            const description = card.querySelector('[data-role="feature-description"]');
            if (description) {
                description.name = `features[${index}][description]`;
            }

            const number = card.querySelector('[data-role="feature-number"]');
            if (number) {
                number.textContent = index + 1;
            }
        });

        const disableRemove = cards.length <= 1;
        container.querySelectorAll('.option-feature-remove').forEach((button) => {
            button.disabled = disableRemove;
            button.classList.toggle('is-disabled', disableRemove);
        });
    }

    function buildFeature(data = {}) {
        const cards = getFeatureCards();
        const index = cards.length;
        const isColor = Number(typeSelect?.value) === state.colorType;

        const normalizedColor = normalizeColorValue(data.value ?? '');
        const prepared = {
            id: data.id ?? '',
            value: data.value ?? '',
            description: data.description ?? '',
            color: isColor ? (normalizedColor ?? '#000000') : '#000000',
        };

        const element = renderTemplate(prepared, index);
        if (!element) {
            return null;
        }

        container.appendChild(element);
        reindexFeatures();
        syncColorControls();
        return element;
    }

    function removeFeature(card) {
        if (!card || getFeatureCards().length <= 1) {
            return;
        }
        card.remove();
        reindexFeatures();
        syncColorControls();
    }

    function syncColorControls() {
        const isColor = Number(typeSelect?.value) === state.colorType;
        getFeatureCards().forEach((card) => {
            const wrapper = card.querySelector('[data-role="color-wrapper"]');
            if (!wrapper) {
                return;
            }

            if (!isColor) {
                wrapper.classList.add('is-hidden');
                wrapper.classList.remove('has-error');
                return;
            }

            const valueInput = card.querySelector('[data-role="feature-value"]');
            const colorInput = wrapper.querySelector('[data-role="color-input"]');
            const hexLabel = wrapper.querySelector('[data-role="color-hex"]');

            const normalized = normalizeColorValue(valueInput?.value ?? '');
            const safeColor = normalized ?? '#000000';

            wrapper.classList.remove('is-hidden');
            wrapper.classList.toggle('has-error', !normalized && (valueInput?.value?.trim() ?? '') !== '');

            if (colorInput) {
                colorInput.value = safeColor;
            }
            if (hexLabel) {
                hexLabel.textContent = safeColor;
            }
            if (valueInput && normalized) {
                valueInput.value = normalized;
            }
        });
    }

    function handleAddFeature() {
        buildFeature();
    }

    function handleContainerClick(event) {
        const button = event.target.closest('[data-action="remove-feature"]');
        if (!button) {
            return;
        }

        const card = button.closest('.option-feature-card');
        removeFeature(card);
    }

    function handleColorInput(event) {
        const target = event.target;
        if (!target.matches('[data-role="color-input"]')) {
            return;
        }

        const card = target.closest('.option-feature-card');
        const valueInput = card?.querySelector('[data-role="feature-value"]');
        const hexLabel = card?.querySelector('[data-role="color-hex"]');

        const normalized = normalizeColorValue(target.value);
        const safeColor = normalized ?? '#000000';

        if (valueInput) {
            valueInput.value = normalized ?? valueInput.value;
        }
        if (hexLabel) {
            hexLabel.textContent = safeColor;
        }
    }

    function handleValueInput(event) {
        const input = event.target;
        if (!input.matches('[data-role="feature-value"]')) {
            return;
        }

        const currentType = Number(typeSelect?.value) || 0;
        if (currentType === state.sizeType) {
            input.value = input.value.toUpperCase();
        }

        if (currentType === state.colorType) {
            const card = input.closest('.option-feature-card');
            const wrapper = card?.querySelector('[data-role="color-wrapper"]');
            const hexLabel = wrapper?.querySelector('[data-role="color-hex"]');
            const colorInput = wrapper?.querySelector('[data-role="color-input"]');
            const normalized = normalizeColorValue(input.value);

            if (colorInput) {
                colorInput.value = normalized ?? '#000000';
            }
            if (hexLabel) {
                hexLabel.textContent = normalized ?? '#000000';
            }

            wrapper?.classList.toggle('has-error', !normalized && input.value.trim() !== '');
        }
    }

    function handleValueBlur(event) {
        const input = event.target;
        if (!input.matches('[data-role="feature-value"]')) {
            return;
        }

        const currentType = Number(typeSelect?.value) || 0;
        if (currentType === state.colorType) {
            const normalized = normalizeColorValue(input.value);
            input.value = normalized ?? '#000000';
            syncColorControls();
        } else if (currentType === state.sizeType) {
            input.value = input.value.toUpperCase();
        } else {
            input.value = input.value.trim();
        }
    }

    if (addButton) {
        addButton.addEventListener('click', handleAddFeature);
    }

    container.addEventListener('click', handleContainerClick);
    container.addEventListener('input', handleValueInput);
    container.addEventListener('change', handleColorInput);
    container.addEventListener('blur', handleValueBlur, true);

    if (typeSelect) {
        typeSelect.addEventListener('change', () => {
            syncColorControls();
        });
    }

    if (!getFeatureCards().length) {
        buildFeature();
    } else {
        reindexFeatures();
        syncColorControls();
    }

    return {
        add: buildFeature,
        reindex: reindexFeatures,
        syncColorControls,
    };
}
