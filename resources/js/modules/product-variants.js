export function initProductVariantsManager(config = {}) {
    const {
        containerId = 'variantsContainer',
        emptyStateId = 'variantsEmpty',
        addButtonId = 'addVariantBtn',
        templateId = 'variantRowTemplate',
    } = config;

    const container = document.getElementById(containerId);
    const emptyState = document.getElementById(emptyStateId);
    const addButton = document.getElementById(addButtonId);
    const template = document.getElementById(templateId);

    if (!container || !addButton || !template) {
        return;
    }

    let newCounter = 0;

    const updateSummary = () => {
        const rows = container.querySelectorAll('[data-role="variant-row"]');
        const summaryChip = document.querySelector('[data-role="variants-summary"]');

        if (emptyState) {
            emptyState.style.display = rows.length ? 'none' : '';
        }

        rows.forEach((row, idx) => {
            const label = row.querySelector('[data-role="variant-index"]');
            if (label) {
                label.textContent = `#${idx + 1}`;
            }
        });

        if (summaryChip) {
            const countSpan = summaryChip.querySelector('[data-role="variants-count"]');
            if (countSpan) {
                countSpan.textContent = rows.length.toString();
            }
        }
    };

    const attachRowEvents = (row) => {
        const removeBtn = row.querySelector('[data-action="remove-variant"]');
        if (removeBtn) {
            removeBtn.addEventListener('click', () => {
                row.classList.add('opacity-0', 'translate-y-1');
                setTimeout(() => {
                    row.remove();
                    updateSummary();
                }, 160);
            });
        }
    };

    const createKey = () => {
        newCounter += 1;
        return `n_${Date.now()}_${newCounter}`;
    };

    const addRow = () => {
        const key = createKey();
        const html = template.innerHTML.replace(/__KEY__/g, key);
        const wrapper = document.createElement('div');
        wrapper.innerHTML = html.trim();
        const row = wrapper.firstElementChild;
        if (!row) return;

        container.appendChild(row);
        attachRowEvents(row);
        updateSummary();
    };

    // Inicializar filas existentes
    const existingRows = container.querySelectorAll('[data-role="variant-row"]');
    existingRows.forEach((row) => attachRowEvents(row));
    updateSummary();

    addButton.addEventListener('click', (e) => {
        e.preventDefault();
        addRow();
    });
}
