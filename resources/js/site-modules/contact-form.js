function createFieldErrorElement(message) {
    const error = document.createElement('small');
    error.className = 'input-error-message contact-field-error';
    error.textContent = message;

    return error;
}

function clearFieldErrors(form) {
    form.querySelectorAll('.contact-field-error').forEach((el) => el.remove());
    form.querySelectorAll('.input-form.input-error').forEach((el) => el.classList.remove('input-error'));
}

function showFieldErrors(form, errors = {}) {
    Object.entries(errors).forEach(([field, message]) => {
        if (!field || !message || field === 'form') {
            return;
        }

        const input = form.querySelector(`[name="${field}"]`);
        if (!input) {
            return;
        }

        input.classList.add('input-error');

        const container = input.closest('.input-group') || input.parentElement;
        if (!container) {
            return;
        }

        container.appendChild(createFieldErrorElement(String(message)));
    });
}

function setAlert(container, type, message) {
    if (!container) {
        return;
    }

    const iconClass = type === 'success' ? 'ri-checkbox-circle-line' : 'ri-error-warning-line';
    const alertType = type === 'success' ? 'success' : 'danger';

    container.innerHTML = `
        <div class="note-alert note-alert-${alertType} note-alert-dismissible" data-alert role="alert">
            <i class="note-alert-icon ${iconClass}"></i>
            <span>${message}</span>
            <button type="button" class="note-alert-close" data-alert-close aria-label="Cerrar">
                <i class="ri-close-line"></i>
            </button>
        </div>
    `;
}

async function resolveRecaptchaToken(form) {
    const siteKey = form.dataset.recaptchaSiteKey || '';
    const skipCaptcha = form.dataset.skipCaptcha === '1';

    if (skipCaptcha || !siteKey) {
        return '';
    }

    if (!window.grecaptcha || typeof window.grecaptcha.execute !== 'function') {
        throw new Error('No se pudo cargar reCAPTCHA.');
    }

    await new Promise((resolve) => {
        window.grecaptcha.ready(resolve);
    });

    return window.grecaptcha.execute(siteKey, { action: 'contact_submit' });
}

export function initContactForm() {
    const form = document.getElementById('contact-form');

    if (!form) {
        return;
    }

    const submitButton = form.querySelector('[data-submit-loader]');
    const loadingText = submitButton?.dataset.loadingText || 'Enviando...';
    const originalText = submitButton?.dataset.defaultText || 'Enviar mensaje';
    const statusContainer = document.getElementById('contact-form-status');

    let isSubmitting = false;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (isSubmitting) {
            return;
        }

        clearFieldErrors(form);
        if (statusContainer) {
            statusContainer.innerHTML = '';
        }

        isSubmitting = true;

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.setAttribute('aria-busy', 'true');
            submitButton.innerHTML = `<i class="ri-loader-4-line"></i> ${loadingText}`;
        }

        try {
            const recaptchaToken = await resolveRecaptchaToken(form);
            const formData = new FormData(form);

            if (recaptchaToken) {
                formData.set('recaptcha_token', recaptchaToken);
            }

            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: formData,
                credentials: 'same-origin',
            });

            const payload = await response.json().catch(() => ({}));

            if (payload.success) {
                setAlert(statusContainer, 'success', payload.message || 'Tu consulta fue enviada correctamente.');
                form.reset();

                const idempotencyInput = form.querySelector('[name="idempotency_key"]');
                if (idempotencyInput && window.crypto?.randomUUID) {
                    idempotencyInput.value = window.crypto.randomUUID();
                }

                clearFieldErrors(form);
                return;
            }

            const errors = payload.errors || { form: 'Ocurrio un error al procesar tu solicitud.' };
            showFieldErrors(form, errors);
            setAlert(statusContainer, 'error', errors.form || 'Revisa los campos marcados e intenta nuevamente.');
        } catch (error) {
            setAlert(statusContainer, 'error', 'No se pudo enviar en este momento. Intenta nuevamente en unos segundos.');
        } finally {
            isSubmitting = false;

            if (submitButton) {
                submitButton.disabled = false;
                submitButton.removeAttribute('aria-busy');
                submitButton.innerHTML = `<i class="ri-send-plane-line"></i> ${originalText}`;
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    if (typeof window.initFormValidator === 'function') {
        window.initFormValidator('#contact-form', {
            validateOnBlur: true,
            scrollToFirstError: true,
        });
    }

    if (typeof window.initTextareaAutosize === 'function') {
        window.initTextareaAutosize();
    }

    initContactForm();
});
