<!-- resources/views/components/partials/confirm-modal.blade.php -->
<div id="confirmModal" class="confirm-modal hidden">
    <div class="confirm-dialog">
        <div class="confirm-header" id="confirmHeader">
            <h6 id="confirmHeaderText">Confirma la acción</h6>
            <button type="button" id="closeModalBtn" class="confirm-close ripple-btn">
                <i class="ri-close-line"></i>
            </button>
        </div>

        <div class="confirm-body ripple-card">
            <div class="flex flex-col gap-1">
                <h6 class="confirm-title" id="confirmTitle">¿Estás seguro?</h6>
                <p class="confirm-message" id="confirmMessage">Esta acción no se puede deshacer.</p>
            </div>
            <i id="confirmIcon" class="ri-error-warning-line confirm-icon"></i>
        </div>

        <div class="confirm-actions">
            <button type="button" class="boton boton-modal-close" id="cancelButton">
                <span class="boton-icon text-base"><i class="ri-close-line"></i></span>
                <span class="boton-text">No, Cancelar</span>
            </button>
            <button id="confirmButton" class="boton">
                <span class="boton-icon"><i class="ri-check-double-line"></i></span>
                <span class="boton-text">Sí, confirmar</span>
            </button>
        </div>
    </div>
</div>

<script>
window.showConfirm = function(options) {
    const modal = document.getElementById('confirmModal');
    const dialog = modal.querySelector('.confirm-dialog');
    const header = document.getElementById('confirmHeader');
    const icon = document.getElementById('confirmIcon');
    const title = document.getElementById('confirmTitle');
    const message = document.getElementById('confirmMessage');
    const confirmBtn = document.getElementById('confirmButton');
    const cancelBtn = document.getElementById('cancelButton');
    const closeBtn = document.getElementById('closeModalBtn');
    const headerText = document.getElementById('confirmHeaderText');

    const colorClasses = ['danger', 'success', 'warning', 'info', 'dark'];
    colorClasses.forEach(c => {
        header.classList.remove(`bg-${c}`);
        confirmBtn.classList.remove(`bg-${c}`);
        icon.classList.remove(`text-${c}`);
    });

    const type = options.type || 'danger';
    header.classList.add(`bg-${type}`);
    confirmBtn.classList.add(`bg-${type}`);
    icon.classList.add(`text-${type}`);

    const iconMap = {
        danger: 'ri-error-warning-fill',
        success: 'ri-checkbox-circle-fill',
        warning: 'ri-alert-fill',
        info: 'ri-information-fill',
        dark: 'ri-shut-down-fill',
    };
    icon.className = `${iconMap[type]} confirm-icon text-${type}`;

    headerText.textContent = options.header || 'Confirma la acción';
    title.textContent = options.title || '¿Estás seguro?';
    message.textContent = options.message || 'Esta acción no se puede deshacer.';
    confirmBtn.querySelector('.boton-text').textContent = options.confirmText || 'Confirmar';
    cancelBtn.querySelector('.boton-text').textContent = options.cancelText || 'Cancelar';

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    dialog.classList.remove('animate-out');
    dialog.classList.add('animate-in');

    function closeModal() {
        dialog.classList.remove('animate-in');
        dialog.classList.add('animate-out');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 250);
        confirmBtn.removeEventListener('click', onConfirm);
        cancelBtn.removeEventListener('click', closeModal);
        closeBtn.removeEventListener('click', closeModal);
    }

    function onConfirm() {
        closeModal();
        if (typeof options.onConfirm === 'function') options.onConfirm();
    }

    confirmBtn.addEventListener('click', onConfirm);
    cancelBtn.addEventListener('click', closeModal);
    closeBtn.addEventListener('click', closeModal);
};
</script>
