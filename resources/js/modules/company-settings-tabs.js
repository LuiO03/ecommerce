/**
 * Inicializa el carrusel de pestañas laterales para la pantalla de configuración de la empresa.
 * Retorna un pequeño manejador con método destroy para limpiar listeners.
 */
export function initCompanySettingsTabs() {
    const layout = document.getElementById('companySettingsTabs');
    if (!layout) {
        return { destroy: () => {} };
    }

    const tabButtons = Array.from(layout.querySelectorAll('.settings-tab-button'));
    const sections = Array.from(layout.querySelectorAll('.settings-section'));

    if (!tabButtons.length || !sections.length) {
        return { destroy: () => {} };
    }

    const storageKey = 'companySettings.activeTab';

    const findIndexByTarget = (target) => {
        if (!target) {
            return -1;
        }

        return tabButtons.findIndex((button) => button.dataset.target === target);
    };

    const readStoredIndex = () => {
        try {
            const storedTarget = window.localStorage.getItem(storageKey);
            const storedIndex = findIndexByTarget(storedTarget);
            return storedIndex >= 0 ? storedIndex : 0;
        } catch (error) {
            console.warn('No se pudo leer la pestaña activa de localStorage:', error);
            return 0;
        }
    };

    let activeIndex = readStoredIndex();
    if (activeIndex < 0 || activeIndex >= tabButtons.length) {
        activeIndex = 0;
    }

    const persistActiveTab = () => {
        const activeButton = tabButtons[activeIndex];
        const target = activeButton && activeButton.dataset ? activeButton.dataset.target : null;

        if (!target) {
            return;
        }

        try {
            window.localStorage.setItem(storageKey, target);
        } catch (error) {
            console.warn('No se pudo guardar la pestaña activa en localStorage:', error);
        }
    };

    const updateButtons = () => {
        tabButtons.forEach((button, index) => {
            const isActive = index === activeIndex;
            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-selected', isActive ? 'true' : 'false');
            button.setAttribute('tabindex', isActive ? '0' : '-1');
        });
    };

    const updateSections = () => {
        sections.forEach((section, index) => {
            const isActive = index === activeIndex;

            section.classList.toggle('is-active', isActive);
            section.setAttribute('aria-hidden', isActive ? 'false' : 'true');
            section.setAttribute('tabindex', isActive ? '0' : '-1');

            if (isActive) {
                section.classList.remove('fade-in');
                void section.offsetWidth;
                section.classList.add('fade-in');
            } else {
                section.classList.remove('fade-in');
            }
        });
    };

    const switchToIndex = (nextIndex) => {
        if (nextIndex === activeIndex || nextIndex < 0 || nextIndex >= sections.length) {
            return;
        }

        activeIndex = nextIndex;
        updateButtons();
        updateSections();
        persistActiveTab();
    };

    const handleKeydown = (event) => {
        if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
            event.preventDefault();
            const nextIndex = (activeIndex + 1) % tabButtons.length;
            tabButtons[nextIndex].focus();
            switchToIndex(nextIndex);
        } else if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
            event.preventDefault();
            const previousIndex = (activeIndex - 1 + tabButtons.length) % tabButtons.length;
            tabButtons[previousIndex].focus();
            switchToIndex(previousIndex);
        }
    };

    const buttonListeners = new Map();

    tabButtons.forEach((button, index) => {
        const clickHandler = () => switchToIndex(index);
        const keydownHandler = (event) => handleKeydown(event);

        button.addEventListener('click', clickHandler);
        button.addEventListener('keydown', keydownHandler);

        buttonListeners.set(button, { clickHandler, keydownHandler });
    });

    updateButtons();
    updateSections();
    persistActiveTab();

    return {
        destroy: () => {
            buttonListeners.forEach((handlers, button) => {
                button.removeEventListener('click', handlers.clickHandler);
                button.removeEventListener('keydown', handlers.keydownHandler);
            });
        }
    };
}

export function initCompanySettingsEditors(options = {}) {
    const EditorConstructor = window.ClassicEditor;

    if (!EditorConstructor || typeof EditorConstructor.create !== 'function') {
        console.warn('ClassicEditor no está disponible. Asegúrate de cargar el script de CKEditor antes de llamar a initCompanySettingsEditors.');
        return {
            ready: Promise.resolve([]),
            destroy: () => Promise.resolve()
        };
    }

    const fieldIds = [options.termsId, options.privacyId, options.claimsId].filter(Boolean);

    if (fieldIds.length === 0) {
        return {
            ready: Promise.resolve([]),
            destroy: () => Promise.resolve()
        };
    }

    const editors = new Map();
    const formListeners = new Map();
    const editorConfig = {
        toolbar: [
            'undo',
            'redo',
            'heading',
            'bold',
            'italic',
            'underline',
            'strikethrough',
            'blockQuote',
            'bulletedList',
            'numberedList',
            'link',
            'insertTable'
        ],
        table: {
            contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells']
        }
    };

    const editorPromises = fieldIds.map((fieldId) => {
        const textarea = document.getElementById(fieldId);

        if (!textarea) {
            console.warn(`No se encontró el textarea con id "${fieldId}" para inicializar CKEditor.`);
            return Promise.resolve(null);
        }

        return EditorConstructor.create(textarea, editorConfig)
            .then((editor) => {
                editors.set(fieldId, editor);

                window._ckEditors = window._ckEditors || {};
                window._ckEditors[fieldId] = editor;

                const form = textarea.form;
                if (form && !formListeners.has(form)) {
                    const submitHandler = () => {
                        editors.forEach((instance) => {
                            if (instance.updateSourceElement) {
                                instance.updateSourceElement();
                            }
                        });
                    };

                    form.addEventListener('submit', submitHandler, { passive: true });
                    formListeners.set(form, submitHandler);
                }

                return editor;
            })
            .catch((error) => {
                console.error(`Error al inicializar CKEditor para ${fieldId}:`, error);
                return null;
            });
    });

    const destroy = () => {
        const destroyPromises = [];

        editors.forEach((editor, fieldId) => {
            if (window._ckEditors) {
                delete window._ckEditors[fieldId];
            }

            if (editor && typeof editor.destroy === 'function') {
                destroyPromises.push(editor.destroy());
            }
        });

        formListeners.forEach((listener, form) => {
            form.removeEventListener('submit', listener);
        });

        editors.clear();
        formListeners.clear();

        return Promise.allSettled(destroyPromises);
    };

    return {
        ready: Promise.all(editorPromises),
        destroy
    };
}

export function initCompanySettingsColorInputs() {
    const pickers = document.querySelectorAll('[data-color-picker]');

    if (!pickers.length) {
        return { destroy: () => {} };
    }

    const listeners = [];
    const normalizeHex = (value, fallback) => {
        if (!value) {
            return fallback;
        }

        let hex = value.trim();
        if (!hex) {
            return fallback;
        }

        if (!hex.startsWith('#')) {
            hex = `#${hex}`;
        }

        const shortPattern = /^#[0-9a-fA-F]{3}$/;
        const longPattern = /^#[0-9a-fA-F]{6}$/;

        if (shortPattern.test(hex)) {
            const chars = hex.slice(1);
            hex = `#${chars[0]}${chars[0]}${chars[1]}${chars[1]}${chars[2]}${chars[2]}`;
        }

        if (longPattern.test(hex)) {
            return hex.toUpperCase();
        }

        return fallback;
    };

    const detach = () => {
        listeners.forEach(({ target, type, handler }) => {
            target.removeEventListener(type, handler);
        });
        listeners.length = 0;
    };

    pickers.forEach((picker) => {
        const textInput = picker.querySelector('[data-color-input="text"]');
        const colorInput = picker.querySelector('[data-color-input="picker"]');
        const preview = picker.querySelector('[data-color-preview]');
        const copyButton = picker.querySelector('[data-color-copy]');

        if (!textInput || !colorInput || !preview) {
            return;
        }

        const defaultColor = normalizeHex(picker.dataset.defaultColor, '#000000');
        let currentColor = normalizeHex(textInput.value || colorInput.value || defaultColor, defaultColor);

        const applyColor = (value, options = { updateText: true, updatePicker: true }) => {
            const nextColor = normalizeHex(value, currentColor);
            currentColor = nextColor;

            if (options.updateText) {
                textInput.value = nextColor;
            }

            if (options.updatePicker) {
                colorInput.value = nextColor;
            }

            preview.style.backgroundColor = nextColor;
        };

        applyColor(currentColor);

        const handleTextBlur = () => applyColor(textInput.value);
        const handleTextInput = () => {
            const maybeColor = normalizeHex(textInput.value, null);
            if (maybeColor) {
                preview.style.backgroundColor = maybeColor;
            }
        };
        const handleColorChange = () => applyColor(colorInput.value, { updateText: true, updatePicker: true });

        textInput.addEventListener('blur', handleTextBlur);
        textInput.addEventListener('input', handleTextInput);
        colorInput.addEventListener('input', handleColorChange);

        listeners.push({ target: textInput, type: 'blur', handler: handleTextBlur });
        listeners.push({ target: textInput, type: 'input', handler: handleTextInput });
        listeners.push({ target: colorInput, type: 'input', handler: handleColorChange });

        if (copyButton) {
            const defaultLabel = copyButton.dataset.copyLabel || 'Copiar';
            const copiedLabel = copyButton.dataset.copiedLabel || 'Copiado';
            const labelSpan = copyButton.querySelector('span');
            let copyTimeout;

            const setCopyState = (isCopied) => {
                if (!labelSpan) {
                    return;
                }

                if (isCopied) {
                    copyButton.classList.add('is-copied');
                    labelSpan.textContent = copiedLabel;
                } else {
                    copyButton.classList.remove('is-copied');
                    labelSpan.textContent = defaultLabel;
                }
            };

            const handleCopyClick = () => {
                const value = textInput.value;

                const notify = (type, title, message) => {
                    if (typeof window.showToast === 'function') {
                        window.showToast({ type, title, message });
                    }
                };

                const copyFromElement = () => {
                    const tempInput = document.createElement('input');
                    tempInput.value = value;
                    tempInput.setAttribute('readonly', '');
                    tempInput.style.position = 'absolute';
                    tempInput.style.opacity = '0';
                    document.body.appendChild(tempInput);
                    tempInput.select();
                    const succeeded = document.execCommand('copy');
                    document.body.removeChild(tempInput);
                    return succeeded;
                };

                const onSuccess = () => {
                    clearTimeout(copyTimeout);
                    setCopyState(true);
                    notify('success', 'Color copiado', `Valor: ${value}`);
                    copyTimeout = window.setTimeout(() => setCopyState(false), 1600);
                };

                const onFail = () => {
                    setCopyState(false);
                    notify('warning', 'No se pudo copiar el color', 'Intenta copiarlo manualmente.');
                };

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(value).then(onSuccess).catch(() => {
                        if (copyFromElement()) {
                            onSuccess();
                        } else {
                            onFail();
                        }
                    });
                    return;
                }

                if (copyFromElement()) {
                    onSuccess();
                } else {
                    onFail();
                }
            };

            copyButton.addEventListener('click', handleCopyClick);
            listeners.push({ target: copyButton, type: 'click', handler: handleCopyClick });
        }
    });

    return {
        destroy: detach
    };
}

