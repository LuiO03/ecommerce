<div id="infoModal" class="confirm-modal hidden">
    <div class="confirm-dialog">
        <div class="confirm-header" id="infoHeader">
            <h6 id="infoHeaderText">Información</h6>
            <button type="button" id="closeInfoModalBtn" class="confirm-close ripple-btn">
                <i class="ri-close-line"></i>
            </button>
        </div>

        <div class="confirm-info ripple-card">
            <i id="infoIcon" class="ri-information-fill confirm-icon text-info"></i>
            <div class="flex flex-col gap-1">
                <h6 class="confirm-title" id="infoTitle">Acción completada</h6>
                <p class="confirm-message" id="infoMessage">Tu operación se realizó correctamente.</p>
            </div>
            <!-- ✅ Nueva sección de acciones -->
            <div class="confirm-actions-info">
                <button type="button" class="boton boton-modal-close" id="cancelButton">
                    <span class="boton-icon text-base"><i class="ri-close-line"></i></span>
                    <span class="boton-text">Cerrar</span>
                </button>
            </div>
        </div>

    </div>
</div>

<script>
window.showInfoModal = function (options) {
    const modal = document.getElementById('infoModal');
    const dialog = modal.querySelector('.confirm-dialog');
    const header = document.getElementById('infoHeader');
    const icon = document.getElementById('infoIcon');
    const title = document.getElementById('infoTitle');
    const message = document.getElementById('infoMessage');
    const closeBtn = document.getElementById('closeInfoModalBtn');
    const cancelButton = document.getElementById('cancelButton');
    const headerText = document.getElementById('infoHeaderText');

    // Limpiar clases previas
    const colorClasses = ['danger', 'success', 'warning', 'info', 'dark'];
    colorClasses.forEach(c => {
        header.classList.remove(`bg-${c}`);
        icon.classList.remove(`text-${c}`);
    });

    // Asignar tipo
    const type = options.type || 'info';
    header.classList.add(`bg-${type}`);
    icon.classList.add(`text-${type}`);

    const iconMap = {
        success: 'ri-checkbox-circle-fill',
        info: 'ri-information-fill',
        warning: 'ri-alert-fill',
        danger: 'ri-error-warning-fill',
        dark: 'ri-shut-down-fill',
    };
    icon.className = `${iconMap[type]} confirm-icon text-${type}`;

    // Asignar textos
    headerText.textContent = options.header || 'Información';
    title.textContent = options.title || 'Acción completada';
    message.innerHTML = options.message || 'Tu operación se realizó correctamente.';

    // Mostrar modal
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    dialog.classList.remove('animate-out');
    dialog.classList.add('animate-in');

    // --- 🔹 Función para cerrar modal
    function closeModal() {
        dialog.classList.remove('animate-in');
        dialog.classList.add('animate-out');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 250);
        removeListeners();
    }

    // --- 🔹 Eliminar listeners al cerrar
    function removeListeners() {
        closeBtn.removeEventListener('click', closeModal);
        cancelButton.removeEventListener('click', closeModal);
        modal.removeEventListener('click', handleOutsideClick);
        dialog.removeEventListener('mouseenter', pauseAutoClose);
        dialog.removeEventListener('mouseleave', resumeAutoClose);
    }

    // --- 🔹 Cerrar al hacer clic fuera del cuadro
    function handleOutsideClick(e) {
        if (e.target === modal) closeModal();
    }

    // --- 🔹 Listeners para botones
    closeBtn.addEventListener('click', closeModal);
    cancelButton.addEventListener('click', closeModal);
    modal.addEventListener('click', handleOutsideClick);

    // --- 🔹 Autocierre (pausable con hover)
    const timeout = options.timeout ?? 5000; // 5 segundos
    let autoCloseTimer;

    function startAutoClose() {
        if (timeout > 0) autoCloseTimer = setTimeout(closeModal, timeout);
    }
    function pauseAutoClose() {
        clearTimeout(autoCloseTimer);
    }
    function resumeAutoClose() {
        startAutoClose();
    }

    dialog.addEventListener('mouseenter', pauseAutoClose);
    dialog.addEventListener('mouseleave', resumeAutoClose);

    startAutoClose();
};
</script>
