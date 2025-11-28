<!-- resources/views/components/partials/modal-confirm.blade.php -->
<div id="confirmModal" class="confirm-modal hidden">
    <div class="confirm-dialog">
        <div class="confirm-header" id="confirmHeader">
            <h6 id="confirmHeaderText">Confirma la acción</h6>
            <button type="button" id="closeModalBtn" class="confirm-close ripple-btn">
                <i class="ri-close-line"></i>
            </button>
        </div>
        <div class="flex flex-col w-full ripple-card">
            <div class="confirm-body">
                <div class="confirm-text">
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
</div>

<script>
window.showConfirm = function(options) {
    const modal = document.getElementById('confirmModal');
    const dialog = modal.querySelector('.confirm-dialog');
    const header = document.getElementById('confirmHeader');
    const icon = document.getElementById('confirmIcon');
    const title = document.getElementById('confirmTitle');
    const message = document.getElementById('confirmMessage');
    const headerText = document.getElementById('confirmHeaderText');

    // Limpiar listeners previos
    const confirmBtnOld = document.getElementById('confirmButton');
    const cancelBtnOld = document.getElementById('cancelButton');
    const closeBtnOld = document.getElementById('closeModalBtn');
    const confirmBtn = confirmBtnOld.cloneNode(true);
    const cancelBtn = cancelBtnOld.cloneNode(true);
    const closeBtn = closeBtnOld.cloneNode(true);
    confirmBtnOld.replaceWith(confirmBtn);
    cancelBtnOld.replaceWith(cancelBtn);
    closeBtnOld.replaceWith(closeBtn);

    // Colores y tipo
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
    message.innerHTML = options.message || 'Esta acción no se puede deshacer.';
    confirmBtn.querySelector('.boton-text').textContent = options.confirmText || 'Confirmar';
    cancelBtn.querySelector('.boton-text').textContent = options.cancelText || 'Cancelar';

    // Mover modal al final del body para garantizar z-index máximo
    if (modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }
    
    // Reducir z-index del sidebar temporalmente
    const sidebar = document.getElementById('logo-sidebar');
    if (sidebar) {
        sidebar.style.zIndex = '1';
    }
    
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
            
            // Restaurar z-index del sidebar
            const sidebar = document.getElementById('logo-sidebar');
            if (sidebar) {
                sidebar.style.zIndex = '';
            }
        }, 250);
    }

    function onConfirm() {
        closeModal();
        if (typeof options.onConfirm === 'function') options.onConfirm();
    }

    confirmBtn.addEventListener('click', onConfirm);
    cancelBtn.addEventListener('click', closeModal);
    closeBtn.addEventListener('click', closeModal);

    // 🖱️ Cerrar al hacer clic fuera SOLO la confirmación
    function clickOutsideConfirmListener(e) {
        if (e.target === modal) {
            closeModalAndCleanup();
            e.stopPropagation();
        }
    }
    modal.addEventListener('click', clickOutsideConfirmListener);

    // ⌨️ Cerrar con ESC SOLO la confirmación
    function escConfirmListener(e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModalAndCleanup();
    }
    document.addEventListener('keydown', escConfirmListener);

    // Limpiar los listeners al cerrar la confirmación
    function closeModalAndCleanup() {
        closeModal();
        document.removeEventListener('keydown', escConfirmListener);
        modal.removeEventListener('click', clickOutsideConfirmListener);
    }
    cancelBtn.addEventListener('click', closeModalAndCleanup);
    closeBtn.addEventListener('click', closeModalAndCleanup);
    confirmBtn.addEventListener('click', function() {
        closeModalAndCleanup();
        if (typeof options.onConfirm === 'function') options.onConfirm();
    });
};
</script>

