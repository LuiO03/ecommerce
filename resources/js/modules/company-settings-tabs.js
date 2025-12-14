/**
 * Inicializa el carrusel de pestañas laterales para la pantalla de configuración de la empresa.
 * Retorna un pequeño manejador con método destroy para limpiar listeners.
 */
export function initCompanySettingsTabs() {
    const layout = document.getElementById('companySettingsTabs');
    if (!layout) {
        return { destroy: () => {} };
    }

    const sectionsWrapper = layout.querySelector('.settings-tabs-sections');
    const slider = layout.querySelector('.settings-tabs-slider');
    const tabButtons = layout.querySelectorAll('.settings-tab-button');
    const sections = slider ? slider.querySelectorAll('.settings-section') : [];

    if (!slider || sections.length === 0 || tabButtons.length === 0) {
        return { destroy: () => {} };
    }

    const storageKey = 'companySettings.activeTab';
    const allButtons = Array.from(tabButtons);

    const findIndexByTarget = (target) => {
        if (!target) {
            return -1;
        }
        return allButtons.findIndex((button) => button.dataset.target === target);
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
    let resizeListener;
    let sectionObserver = null;

    const updateSliderHeight = () => {
        const activeSection = sections[activeIndex];
        if (!activeSection) {
            return;
        }

        const nextHeight = activeSection.scrollHeight;
        slider.style.height = `${nextHeight}px`;
        if (sectionsWrapper) {
            sectionsWrapper.style.height = `${nextHeight}px`;
        }
    };

    const attachObserver = () => {
        if (!(window.ResizeObserver) || !sections[activeIndex]) {
            updateSliderHeight();
            return;
        }

        if (!sectionObserver) {
            sectionObserver = new ResizeObserver(() => {
                window.requestAnimationFrame(updateSliderHeight);
            });
        }

        sectionObserver.disconnect();
        sectionObserver.observe(sections[activeIndex]);
    };

    const persistActiveTab = () => {
        const activeButton = tabButtons[activeIndex];
        if (!activeButton) {
            return;
        }

        const target = activeButton.dataset.target;

        if (!target) {
            return;
        }

        try {
            window.localStorage.setItem(storageKey, target);
        } catch (error) {
            console.warn('No se pudo guardar la pestaña activa en localStorage:', error);
        }
    };

    const switchToIndex = (nextIndex) => {
        if (nextIndex === activeIndex || !sections[nextIndex]) {
            return;
        }

        const previousButton = tabButtons[activeIndex];
        const nextButton = tabButtons[nextIndex];

        previousButton.classList.remove('is-active');
        previousButton.setAttribute('aria-selected', 'false');
        previousButton.setAttribute('tabindex', '-1');

        nextButton.classList.add('is-active');
        nextButton.setAttribute('aria-selected', 'true');
        nextButton.setAttribute('tabindex', '0');

        slider.style.transform = `translateX(-${nextIndex * 100}%)`;

        requestAnimationFrame(() => {
            updateSliderHeight();
            attachObserver();
        });

        sections[activeIndex].setAttribute('aria-hidden', 'true');
        sections[activeIndex].setAttribute('tabindex', '-1');

        sections[nextIndex].setAttribute('aria-hidden', 'false');
        sections[nextIndex].setAttribute('tabindex', '0');

        activeIndex = nextIndex;
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

    tabButtons.forEach((button, index) => {
        const isActive = index === activeIndex;
        button.setAttribute('aria-selected', isActive ? 'true' : 'false');
        button.setAttribute('tabindex', isActive ? '0' : '-1');
        button.classList.toggle('is-active', isActive);

        button.addEventListener('click', () => switchToIndex(index));
        button.addEventListener('keydown', handleKeydown);
    });

    slider.style.transform = `translateX(-${activeIndex * 100}%)`;
    updateSliderHeight();
    attachObserver();

    sections.forEach((section, index) => {
        const isActive = index === activeIndex;
        section.setAttribute('aria-hidden', isActive ? 'false' : 'true');
        section.setAttribute('tabindex', isActive ? '0' : '-1');
    });

    resizeListener = () => updateSliderHeight();
    window.addEventListener('resize', resizeListener, { passive: true });

    persistActiveTab();

    return {
        destroy: () => {
            tabButtons.forEach((button) => {
                button.replaceWith(button.cloneNode(true));
            });

            if (resizeListener) {
                window.removeEventListener('resize', resizeListener);
            }

            if (sectionObserver) {
                sectionObserver.disconnect();
            }
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

