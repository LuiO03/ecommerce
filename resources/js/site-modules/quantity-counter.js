// Manejador global para contadores de cantidad (quantity-counter)
// Aplica a todos los [data-quantity-root] que no estén dentro de [data-variant-root]

document.addEventListener('DOMContentLoaded', () => {
    const roots = Array.from(document.querySelectorAll('[data-quantity-root]')).filter((root) => {
        return !root.closest('[data-variant-root]');
    });

    roots.forEach((root) => {
        const valueEl = root.querySelector('[data-quantity-value]');
        const decrementBtn = root.querySelector('[data-quantity-decrement]');
        const incrementBtn = root.querySelector('[data-quantity-increment]');

        if (!valueEl || !decrementBtn || !incrementBtn) {
            return;
        }

        let currentQuantity = Number.parseInt(valueEl.textContent || '1', 10) || 1;
        const maxAttr = root.dataset.maxQuantity;
        const maxQuantity = maxAttr ? Number.parseInt(maxAttr, 10) || 99 : 99;

        const updateUI = () => {
            if (currentQuantity < 1) {
                currentQuantity = 1;
            }
            if (currentQuantity > maxQuantity) {
                currentQuantity = maxQuantity;
            }

            valueEl.textContent = String(currentQuantity);

            const canDecrement = currentQuantity > 1;
            const canIncrement = currentQuantity < maxQuantity;

            decrementBtn.disabled = !canDecrement;
            decrementBtn.classList.toggle('is-disabled', !canDecrement);

            incrementBtn.disabled = !canIncrement;
            incrementBtn.classList.toggle('is-disabled', !canIncrement);

            valueEl.classList.remove('is-changing');
            // forzar reflow para la animación
            // eslint-disable-next-line no-unused-expressions
            valueEl.offsetWidth;
            valueEl.classList.add('is-changing');

            window.setTimeout(() => {
                valueEl.classList.remove('is-changing');
            }, 200);
        };

        decrementBtn.addEventListener('click', () => {
            if (currentQuantity <= 1) {
                return;
            }
            currentQuantity -= 1;
            updateUI();
        });

        incrementBtn.addEventListener('click', () => {
            if (currentQuantity >= maxQuantity) {
                return;
            }
            currentQuantity += 1;
            updateUI();
        });

        updateUI();
    });
});
