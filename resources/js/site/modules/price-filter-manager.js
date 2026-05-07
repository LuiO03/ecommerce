// price-filter-manager.js - Mantiene excluyentes el rango manual y los presets de precio
document.addEventListener('DOMContentLoaded', () => {
    const manualInputs = Array.from(document.querySelectorAll('[data-price-manual]'));
    const presetInputs = Array.from(document.querySelectorAll('[data-price-preset]'));

    if (!manualInputs.length || !presetInputs.length) {
        return;
    }

    const getPresetLabel = (input) => input.closest('label');

    const setPresetState = (activeInput) => {
        presetInputs.forEach((input) => {
            const label = getPresetLabel(input);
            const isActive = input === activeInput;
            input.checked = isActive;
            if (label) {
                label.classList.toggle('is-active', isActive);
            }
        });
    };

    const clearManualInputs = () => {
        manualInputs.forEach((input) => {
            input.value = '';
        });
    };

    const selectPreset = (input) => {
        clearManualInputs();
        setPresetState(input);
    };

    const activateManualMode = () => {
        presetInputs.forEach((input) => {
            const label = getPresetLabel(input);
            const isDefaultPreset = input.value === '';
            input.checked = isDefaultPreset;
            if (label) {
                label.classList.toggle('is-active', isDefaultPreset);
            }
        });
    };

    manualInputs.forEach((input) => {
        input.addEventListener('input', () => {
            activateManualMode();
        });
    });

    presetInputs.forEach((input) => {
        input.addEventListener('change', () => {
            selectPreset(input);
        });
    });
});
