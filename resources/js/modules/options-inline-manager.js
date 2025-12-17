import { normalizeColorValue } from './options-feature-form.js';
import FormValidator from './form-validator.js';
import { SubmitButtonLoader } from '../utils/submit-button-loader.js';

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

function pluralize(count, singular, plural) {
    const safeSingular = singular || 'valor';
    const safePlural = plural || 'valores';
    return `${count} ${count === 1 ? safeSingular : safePlural}`;
}

function buildEmptyState() {
    const placeholder = document.createElement('p');
    placeholder.className = 'option-feature-empty';
    placeholder.dataset.role = 'feature-empty';
    placeholder.textContent = 'Sin valores registrados.';
    return placeholder;
}

function buildFeaturePill(feature) {
    const pill = document.createElement('div');
    pill.className = 'option-feature-pill';
    pill.dataset.featureId = feature.id;
    pill.dataset.deleteUrl = feature.delete_url;

    if (feature.is_color) {
        pill.classList.add('is-color');
    }

    if (feature.is_color && feature.value) {
        const colorPreview = document.createElement('span');
        colorPreview.className = 'pill-color';
        colorPreview.style.setProperty('--pill-color', feature.value);
        pill.appendChild(colorPreview);
    }

    const valueNode = document.createElement('span');
    valueNode.className = 'pill-value';
    valueNode.textContent = feature.value;
    pill.appendChild(valueNode);

    if (feature.description) {
        const description = document.createElement('span');
        description.className = 'pill-description';
        description.textContent = feature.description;
        pill.appendChild(description);
    }

    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'pill-remove';
    removeBtn.dataset.action = 'feature-remove';
    removeBtn.setAttribute('aria-label', 'Eliminar valor');
    removeBtn.innerHTML = '<i class="ri-close-line"></i>';
    pill.appendChild(removeBtn);

    pill.classList.add('is-new');
    setTimeout(() => pill.classList.remove('is-new'), 800);

    return pill;
}

function updateCountDisplay(node, count) {
    if (!node) {
        return;
    }
    const singular = node.dataset.labelSingular;
    const plural = node.dataset.labelPlural;
    node.dataset.count = String(count);
    node.textContent = pluralize(count, singular, plural);
}

function updateUpdatedText(node, humanText) {
    if (!node) {
        return;
    }
    node.textContent = `Actualizado ${humanText}`;
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function handleColorSync(form, isColor) {
    const valueInput = form.querySelector('[data-role="feature-value"]');
    const colorInput = form.querySelector('[data-role="feature-color"]');
    const colorHex = form.querySelector('[data-role="feature-color-hex"]');

    if (colorInput) {
        colorInput.disabled = !isColor;
    }

    if (!isColor) {
        form.classList.remove('is-color');
        if (colorHex) {
            colorHex.textContent = '';
        }
        return;
    }

    form.classList.add('is-color');

    if (colorHex && !colorHex.textContent) {
        colorHex.textContent = '#000000';
    }

    const sync = (value) => {
        const normalized = normalizeColorValue(value) || '#000000';
        if (valueInput) {
            valueInput.value = normalized;
        }
        if (colorInput) {
            colorInput.value = normalized;
        }
        if (colorHex) {
            colorHex.textContent = normalized;
        }
    };

    if (valueInput) {
        valueInput.addEventListener('input', (event) => {
            const normalized = normalizeColorValue(event.target.value);
            if (colorInput && normalized) {
                colorInput.value = normalized;
            }
            if (colorHex) {
                colorHex.textContent = normalized || '#000000';
            }
        });

        valueInput.addEventListener('blur', () => {
            const normalized = normalizeColorValue(valueInput.value) || '#000000';
            sync(normalized);
        });
    }

    if (colorInput) {
        colorInput.addEventListener('input', (event) => {
            const normalized = normalizeColorValue(event.target.value) || '#000000';
            sync(normalized);
        });
    }

    sync(valueInput?.value || '#000000');
}

function normaliseValue(value, isColor) {
    const raw = String(value || '').trim();

    if (isColor) {
        return normalizeColorValue(raw) || '#000000';
    }

    const lowered = raw.toLocaleLowerCase();
    return lowered.replace(/(^|\s)(\p{L})/gu, (match, space, letter) => `${space}${letter.toUpperCase()}`);
}

export function initOptionInlineManager() {
    const cards = document.querySelectorAll('[data-option-inline]');
    if (!cards.length) {
        return;
    }

    const csrfToken = getCsrfToken();

    cards.forEach((card) => {
        if (card.dataset.inlineReady) {
            return;
        }
        card.dataset.inlineReady = 'true';

        const form = card.querySelector('[data-role="feature-form"]');
        const featureList = card.querySelector('[data-role="feature-list"]');
        const countNode = card.querySelector('[data-role="feature-count"]');
        const updatedText = card.querySelector('[data-role="updated-text"]');

        if (!form || !featureList) {
            return;
        }

        const isColorOption = (form.dataset.optionIsColor === 'true')
            || (card.dataset.optionIsColor === 'true');
        const valueInput = form.querySelector('[data-role="feature-value"]');
        const descriptionInput = form.querySelector('[data-role="feature-description"]');
        const submitBtn = form.querySelector('[data-role="feature-submit"]');
        const feedback = form.querySelector('[data-role="feature-feedback"]');
        const feedbackText = feedback ? feedback.querySelector('[data-role="feature-feedback-text"]') : null;
        const colorInput = form.querySelector('[data-role="feature-color"]');
        const colorHex = form.querySelector('[data-role="feature-color-hex"]');

        if (!form.id) {
            const slug = card.dataset.optionSlug || `option-${Math.random().toString(36).slice(2)}`;
            form.id = `featureForm-${slug}`;
        }

        if (submitBtn && !submitBtn.id) {
            submitBtn.id = `${form.id}-submit`;
        }

        let submitLoader = null;
        if (submitBtn && submitBtn.id) {
            submitLoader = new SubmitButtonLoader({
                formId: form.id,
                buttonId: submitBtn.id,
                loadingText: 'Agregando...',
            });
        }

        let validator = null;
        if (form.id) {
            validator = new FormValidator(`#${form.id}`, {
                validateOnBlur: true,
                validateOnInput: true,
                showErrorsInline: true,
                showSuccessIndicators: false,
                scrollToFirstError: false,
                preventSubmitOnError: false,
            });
        }

        handleColorSync(form, isColorOption);

        if (!isColorOption && valueInput) {
            valueInput.addEventListener('blur', () => {
                valueInput.value = normaliseValue(valueInput.value, isColorOption);
            });
        }

        const setBusy = (busy) => {
            form.classList.toggle('is-busy', busy);
            if (submitLoader) {
                if (busy) {
                    submitLoader.showLoading();
                } else {
                    submitLoader.resetButton();
                }
            } else if (submitBtn) {
                submitBtn.disabled = busy;
            }
        };

        const showFeedback = (message) => {
            if (!feedback) {
                return;
            }
            const content = message ? message.toString().trim() : '';
            if (feedbackText) {
                feedbackText.textContent = content;
            } else {
                feedback.textContent = content;
            }
            feedback.style.display = content ? 'flex' : 'none';
        };

        showFeedback('');

        const resetInputs = () => {
            if (isColorOption) {
                const resetColor = '#000000';
                if (valueInput) {
                    valueInput.value = resetColor;
                }
                if (colorInput) {
                    colorInput.value = resetColor;
                }
                if (colorHex) {
                    colorHex.textContent = resetColor;
                }
            } else if (valueInput) {
                valueInput.value = '';
            }

            if (descriptionInput) {
                descriptionInput.value = '';
            }

            valueInput?.focus();
            validator?.reset();
        };

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (!submitBtn) {
                return;
            }

            showFeedback('');

            if (validator && !validator.validateAll()) {
                return;
            }

            const payload = {
                value: valueInput ? valueInput.value : '',
                description: descriptionInput ? descriptionInput.value : '',
            };

            if (isColorOption && !payload.value && colorInput) {
                payload.value = colorInput.value;
            }

            setBusy(true);

            try {
                const response = await fetch(form.dataset.createUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                if (response.status === 422) {
                    const data = await response.json();
                    const message = data?.errors?.value?.[0] || data?.message || 'No se pudo agregar el valor.';
                    showFeedback(message);
                    return;
                }

                if (!response.ok) {
                    showFeedback('No se pudo agregar el valor. Inténtalo nuevamente.');
                    return;
                }

                const data = await response.json();
                const feature = data.feature;

                if (data.meta) {
                    updateCountDisplay(countNode, Number(data.meta.count || 0));
                    if (updatedText && data.meta.updated_human) {
                        updateUpdatedText(updatedText, data.meta.updated_human);
                    }
                }

                const emptyState = card.querySelector('[data-role="feature-empty"]');
                if (emptyState) {
                    emptyState.remove();
                }

                feature.is_color = Boolean(feature.is_color);
                const pill = buildFeaturePill(feature);
                featureList.appendChild(pill);

                resetInputs();

                if (typeof window.showToast === 'function' && data.message) {
                    window.showToast({ type: 'success', title: 'Valor agregado', message: data.message });
                }
            } catch (error) {
                console.error('[options-inline-manager] error al agregar valor', error);
                showFeedback('Ocurrió un problema inesperado.');
            } finally {
                setBusy(false);
            }
        });

        featureList.addEventListener('click', (event) => {
            const button = event.target.closest('[data-action="feature-remove"]');
            if (!button) {
                return;
            }

            const pill = button.closest('.option-feature-pill');
            const deleteUrl = pill?.dataset.deleteUrl;
            const valueLabel = pill?.querySelector('.pill-value')?.textContent || '';

            if (!deleteUrl || !pill) {
                return;
            }

            const proceed = async () => {
                showFeedback('');
                button.disabled = true;

                try {
                    const response = await fetch(deleteUrl, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                    });

                    if (!response.ok) {
                        const data = await response.json().catch(() => null);
                        const message = data?.message || 'No se pudo eliminar el valor.';
                        showFeedback(message);
                        return;
                    }

                    const data = await response.json();
                    pill.remove();

                    if (!featureList.querySelector('.option-feature-pill')) {
                        featureList.appendChild(buildEmptyState());
                    }

                    if (data.meta) {
                        updateCountDisplay(countNode, Number(data.meta.count || 0));
                        if (updatedText && data.meta.updated_human) {
                            updateUpdatedText(updatedText, data.meta.updated_human);
                        }
                    }

                    if (typeof window.showToast === 'function' && data.message) {
                        window.showToast({ type: 'info', title: 'Valor eliminado', message: data.message });
                    }
                } catch (error) {
                    console.error('[options-inline-manager] error al eliminar valor', error);
                    showFeedback('No se pudo eliminar el valor.');
                } finally {
                    button.disabled = false;
                }
            };

            if (typeof window.showConfirm === 'function') {
                window.showConfirm({
                    type: 'danger',
                    header: 'Eliminar valor',
                    title: '¿Deseas continuar?',
                    message: `Se eliminará el valor <strong>${escapeHtml(valueLabel)}</strong>.`,
                    confirmText: 'Sí, eliminar',
                    cancelText: 'Cancelar',
                    onConfirm: proceed,
                });
            } else if (window.confirm(`¿Eliminar el valor ${valueLabel}?`)) {
                proceed();
            }
        });
    });
}
