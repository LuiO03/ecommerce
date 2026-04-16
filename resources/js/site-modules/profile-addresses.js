// Manejo de modal y formulario de direcciones en perfil de usuario

import { initFormValidator } from '../utils/form-validator';

// Evitar registrar listeners y validador múltiples veces
let profileAddressValidator = null;
let profileAddressModalStaticBound = false;
let profileAddressFormSubmitBound = false;
let profileAddressMode = 'create';
let profileAddressUpdateUrl = null;
let profileAddressIsSubmitting = false;

function setupProfileAddresses() {
    const section = document.getElementById('profileAddressesSection');
    const modal = document.getElementById('profileAddressModal');
    const form = document.getElementById('profileAddressForm');
    const titleEl = modal ? modal.querySelector('[data-profile-address-title]') : null;
    const methodInput = form ? form.querySelector('[data-profile-address-method]') : null;
    const submitBtn = document.getElementById('profileAddressSubmitBtn');

    if (!section || !modal || !form || !titleEl || !methodInput || !submitBtn) {
        return;
    }

    const storeUrl = section.getAttribute('data-store-url');
    const backdrop = modal.querySelector('.profile-address-backdrop');
    const closeButtons = modal.querySelectorAll('[data-profile-address-close]');

    if (!profileAddressValidator) {
        profileAddressValidator = initFormValidator('#profileAddressForm', {
            validateOnBlur: true,
            validateOnInput: false,
            scrollToFirstError: true,
            showSuccessIndicators: true,
        });
    }

    function resetForm() {
        form.reset();
        if (profileAddressValidator && typeof profileAddressValidator.reset === 'function') {
            profileAddressValidator.reset();
        }
    }

    function openModal() {
        modal.classList.add('is-visible');
        modal.setAttribute('aria-hidden', 'false');
        document.documentElement.classList.add('profile-address-modal-open');
        document.body.classList.add('profile-address-modal-open');
    }

    function closeModal() {
        modal.classList.remove('is-visible');
        modal.setAttribute('aria-hidden', 'true');
        document.documentElement.classList.remove('profile-address-modal-open');
        document.body.classList.remove('profile-address-modal-open');
        profileAddressUpdateUrl = null;
        profileAddressIsSubmitting = false;
        profileAddressMode = 'create';
    }

    function fillFormFromDataset(dataset) {
        const type = dataset.addressType || 'home';
        const addressLine = dataset.addressLine || '';
        const district = dataset.addressDistrict || '';
        const reference = dataset.addressReference || '';
        const receiverName = dataset.addressReceiverName || '';
        const receiverLastName = dataset.addressReceiverLastName || '';
        const receiverPhone = dataset.addressReceiverPhone || '';

        form.querySelector('#pa_type').value = type;
        form.querySelector('#pa_address_line').value = addressLine;
        form.querySelector('#pa_district').value = district;
        form.querySelector('#pa_reference').value = reference;
        form.querySelector('#pa_receiver_name').value = receiverName;
        form.querySelector('#pa_receiver_last_name').value = receiverLastName;
        form.querySelector('#pa_receiver_phone').value = receiverPhone;
    }

    function openCreateModal() {
        profileAddressMode = 'create';
        profileAddressUpdateUrl = null;
        titleEl.textContent = 'Agregar dirección';
        submitBtn.textContent = 'Guardar dirección';
        methodInput.value = 'POST';
        resetForm();
        openModal();
    }

    function openEditModal(button) {
        profileAddressMode = 'edit';
        profileAddressUpdateUrl = button.getAttribute('data-update-url');
        titleEl.textContent = 'Editar dirección';
        submitBtn.textContent = 'Actualizar dirección';
        methodInput.value = 'PUT';
        resetForm();
        fillFormFromDataset(button.dataset);
        openModal();
    }

    function bindOpenButtons() {
        const createButtons = section.querySelectorAll('[data-address-modal-open="create"]');
        const editButtons = section.querySelectorAll('[data-address-modal-open="edit"]');

        createButtons.forEach((btn) => {
            btn.addEventListener('click', () => openCreateModal());
        });

        editButtons.forEach((btn) => {
            btn.addEventListener('click', () => openEditModal(btn));
        });
    }

    function bindCloseButtons() {
        if (profileAddressModalStaticBound) {
            return;
        }
        profileAddressModalStaticBound = true;

        if (backdrop) {
            backdrop.addEventListener('click', () => closeModal());
        }
        closeButtons.forEach((btn) => {
            btn.addEventListener('click', () => closeModal());
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('is-visible')) {
                closeModal();
            }
        });
    }

    async function sendAddressRequest(url, options = {}) {
        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : null;

        if (!csrfToken) {
            console.error('CSRF token no encontrado en meta[name="csrf-token"]');
            return;
        }

        const method = options.method || 'POST';
        const formData = new FormData();
        formData.append('_token', csrfToken);

        if (method !== 'POST') {
            formData.append('_method', method);
        }

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            if (!response.ok) {
                const data = await response.json().catch(() => null);
                const errorMessage = data && data.message
                    ? data.message
                    : 'No se pudo actualizar tus direcciones.';
                console.error('Error en la petición de direcciones:', data || response.statusText);
                if (window.showToast) {
                    window.showToast({
                        type: 'danger',
                        title: 'Error',
                        message: errorMessage,
                    });
                } else {
                    alert(errorMessage);
                }
                return;
            }

            const data = await response.json();
            if (data && data.status === 'success' && typeof data.html === 'string') {
                const wrapper = document.getElementById('profileAddressesSection');
                if (wrapper) {
                    wrapper.outerHTML = data.html;
                }

                if (window.showToast && data.toast) {
                    window.showToast(data.toast);
                }

                // Reinicializar listeners sobre la nueva sección
                setTimeout(setupProfileAddresses, 0);
            }
        } catch (error) {
            console.error('Error de red en la petición de direcciones:', error);
            if (window.showToast) {
                window.showToast({
                    type: 'danger',
                    title: 'Error de red',
                    message: 'Ocurrió un problema al comunicarse con el servidor.',
                });
            }
        }
    }

    function bindDeleteAndDefaultButtons() {
        const deleteButtons = section.querySelectorAll('.address-delete-btn');

        deleteButtons.forEach((btn) => {
            btn.addEventListener('click', (event) => {
                event.preventDefault();

                const url = btn.getAttribute('data-address-delete-url');
                if (!url) return;

                const proceed = async () => {
                    btn.disabled = true;
                    await sendAddressRequest(url, {
                        method: 'DELETE',
                    });
                    btn.disabled = false;
                };

                if (typeof window.showConfirm === 'function') {
                    window.showConfirm({
                        type: 'danger',
                        header: 'Eliminar dirección',
                        title: '¿Deseas eliminar esta dirección?',
                        message: 'Esta acción no se puede deshacer.',
                        confirmText: 'Sí, eliminar',
                        cancelText: 'No, cancelar',
                        onConfirm: proceed,
                    });
                } else if (window.confirm('¿Estás seguro de que deseas eliminar esta dirección?')) {
                    proceed();
                }
            });
        });

    }

    async function handleSubmit(event) {
        event.preventDefault();
        if (profileAddressIsSubmitting) return;

        const isValid = profileAddressValidator ? profileAddressValidator.validateAll() : true;
        if (!isValid) return;

        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : null;

        if (!csrfToken) {
            console.error('CSRF token no encontrado en meta[name="csrf-token"]');
            return;
        }

        const formData = new FormData(form);
        let url = storeUrl;
        let method = 'POST';

        if (profileAddressMode === 'edit' && profileAddressUpdateUrl) {
            url = profileAddressUpdateUrl;
            formData.set('_method', 'PUT');
        } else {
            formData.set('_method', 'POST');
        }

        profileAddressIsSubmitting = true;
        submitBtn.disabled = true;

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            if (!response.ok) {
                const data = await response.json().catch(() => null);
                const firstError = data && data.errors
                    ? Object.values(data.errors)[0][0]
                    : 'No se pudo guardar la dirección.';
                console.error('Error al guardar dirección:', data || response.statusText);
                if (window.showToast) {
                    window.showToast({
                        type: 'danger',
                        title: 'Error',
                        message: firstError,
                    });
                } else {
                    alert(firstError);
                }
                return;
            }

            const data = await response.json();
            if (data && data.status === 'success' && typeof data.html === 'string') {
                const wrapper = document.getElementById('profileAddressesSection');
                if (wrapper) {
                    // Reemplazar todo el contenido de la sección de direcciones
                    wrapper.outerHTML = data.html;
                }
                closeModal();
                if (window.showToast && data.toast) {
                    window.showToast(data.toast);
                }
                // Reinicializar listeners sobre la nueva sección
                setTimeout(setupProfileAddresses, 0);
            }
        } catch (error) {
            console.error('Error de red al guardar dirección:', error);
            if (window.showToast) {
                window.showToast({
                    type: 'danger',
                    title: 'Error de red',
                    message: 'Ocurrió un problema al comunicarse con el servidor.',
                });
            }
        } finally {
            profileAddressIsSubmitting = false;
            submitBtn.disabled = false;
        }
    }

    bindOpenButtons();
    bindDeleteAndDefaultButtons();
    bindCloseButtons();

    if (!profileAddressFormSubmitBound) {
        form.addEventListener('submit', handleSubmit);
        profileAddressFormSubmitBound = true;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    setupProfileAddresses();
});
