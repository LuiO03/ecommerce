export function normalizeColorValue(value) {
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

export function initOptionFeatureForm({
    containerId,
    addButtonId,
    templateId,
    nameInputId,
    isColor,
} = {}) {
    const container = document.getElementById(containerId);
    if (!container) {
        return null;
    }

    // Usar templates explícitos para color y no color
    let templateContentColor = '';
    let templateContentNoColor = '';
    const templateNodeColor = document.getElementById('featureRowTemplateColor');
    if (templateNodeColor) {
        templateContentColor = templateNodeColor.textContent.trim();
    }
    const templateNodeNoColor = document.getElementById('featureRowTemplateNoColor');
    if (templateNodeNoColor) {
        templateContentNoColor = templateNodeNoColor.textContent.trim();
    }
    const addButton = document.getElementById(addButtonId);
    const nameInput = nameInputId ? document.getElementById(nameInputId) : null;

    const state = {
        isColor: typeof isColor === 'boolean' ? isColor : container.dataset.isColor === 'true',
        colorSlug: container.dataset.colorSlug || 'color',
        colorLocked: container.dataset.colorLocked === 'true',
    };

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

    function syncColorControls() {
        getFeatureCards().forEach((card) => {
            const wrapper = card.querySelector('[data-role="color-wrapper"]');
            const valueInput = card.querySelector('[data-role="feature-value"]');
            const colorInput = wrapper?.querySelector('[data-role="color-input"]');
            const hexLabel = wrapper?.querySelector('[data-role="color-hex"]');

            if (!wrapper || !valueInput) {
                return;
            }

            if (!state.isColor) {
                wrapper.classList.add('is-hidden');
                wrapper.classList.remove('has-error');
                if (colorInput) {
                    colorInput.disabled = true;
                }
                if (hexLabel) {
                    hexLabel.textContent = '';
                }
                return;
            }

            const normalized = normalizeColorValue(valueInput.value);
            const safeColor = normalized ?? '#000000';

            wrapper.classList.remove('is-hidden');
            wrapper.classList.toggle('has-error', !normalized && valueInput.value.trim() !== '');

            if (colorInput) {
                colorInput.disabled = false;
                colorInput.value = safeColor;
            }
            if (hexLabel) {
                hexLabel.textContent = safeColor;
            }
            if (normalized) {
                valueInput.value = normalized;
            }
        });
    }

    function setColorMode(next) {
        const target = Boolean(next);
        if (state.isColor === target) {
            return;
        }
        state.isColor = target;
        container.dataset.isColor = target ? 'true' : 'false';
        if (!state.isColor) {
            getFeatureCards().forEach((card) => {
                const valueInput = card.querySelector('[data-role="feature-value"]');
                if (valueInput) {
                    valueInput.value = normaliseTextValue(valueInput.value);
                }
            });
        }
        syncColorControls();
    }

    function buildFeature(data = {}) {
        const cards = getFeatureCards();
        const index = cards.length;
        const normalizedColor = normalizeColorValue(data.value ?? '');
        const prepared = {
            id: data.id ?? '',
            value: data.value ?? '',
            description: data.description ?? '',
            color: state.isColor ? (normalizedColor ?? '#000000') : '#000000',
        };

        // Selecciona plantilla según tipo
        let element;
        if (state.isColor && templateContentColor) {
            const wrapper = document.createElement('div');
            let html = templateContentColor
                .replace(/__INDEX__/g, index)
                .replace(/__ID__/g, escapeHtml(prepared.id ?? ''))
                .replace(/__NUMBER__/g, index + 1)
                .replace(/__VALUE__/g, escapeHtml(prepared.value ?? ''))
                .replace(/__DESCRIPTION__/g, escapeHtml(prepared.description ?? ''));
            wrapper.innerHTML = html.trim();
            element = wrapper.firstElementChild;
        } else if (!state.isColor && templateContentNoColor) {
            const wrapper = document.createElement('div');
            let html = templateContentNoColor
                .replace(/__INDEX__/g, index)
                .replace(/__ID__/g, escapeHtml(prepared.id ?? ''))
                .replace(/__NUMBER__/g, index + 1)
                .replace(/__VALUE__/g, escapeHtml(prepared.value ?? ''))
                .replace(/__DESCRIPTION__/g, escapeHtml(prepared.description ?? ''));
            wrapper.innerHTML = html.trim();
            element = wrapper.firstElementChild;
        } else {
            element = null;
        }

        if (!element) {
            console.warn('[options-feature-form] No se encontró plantilla para el tipo actual.');
            return null;
        }

        container.appendChild(element);
        reindexFeatures();
        syncColorControls();
        // Inicializar Coloris para inputs nuevos y forzar previsualización
        if (window.Coloris) {
            window.Coloris({ el: '[data-coloris]' });
        }
        // Re-inicializar validación para nuevos campos
        const form = container.closest('form');
        if (form && typeof window.initFormValidator === 'function') {
            // Elimina referencia previa para forzar nuevo escaneo
            form.__validator = undefined;
            window.initFormValidator('#' + form.id, {
                validateOnBlur: true,
                validateOnInput: false,
                scrollToFirstError: true
            });
        }
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
        if (!state.isColor) {
            return;
        }

        const target = event.target;
        if (!target.matches('[data-role="color-input"]')) {
            return;
        }

        const card = target.closest('.option-feature-card');
        const valueInput = card?.querySelector('[data-role="feature-value"]');
        const hexLabel = card?.querySelector('[data-role="color-hex"]');

        const normalized = normalizeColorValue(target.value);
        const safeColor = normalized ?? '#000000';

        if (valueInput && normalized) {
            valueInput.value = normalized;
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

        if (!state.isColor) {
            return;
        }

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

    function handleValueBlur(event) {
        const input = event.target;
        if (!input.matches('[data-role="feature-value"]')) {
            return;
        }

        if (state.isColor) {
            const normalized = normalizeColorValue(input.value);
            input.value = normalized ?? '#000000';
            syncColorControls();
        } else {
            input.value = normaliseTextValue(input.value);
        }
    }

    if (addButton) {
        addButton.addEventListener('click', handleAddFeature);
    }

    container.addEventListener('click', handleContainerClick);
    container.addEventListener('input', handleValueInput);
    container.addEventListener('change', handleColorInput);
    container.addEventListener('blur', handleValueBlur, true);

    if (nameInput && !state.colorLocked) {
        const updateColorMode = () => {
            const slug = slugify(nameInput.value);
            setColorMode(slug === state.colorSlug);
        };
        nameInput.addEventListener('input', updateColorMode);
        updateColorMode();
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
        setColorMode,
    };
}
