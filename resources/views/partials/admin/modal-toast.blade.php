<div id="toastContainer" class="toast-container"></div>

<script>
window.showToast = function(options) {
    const container = document.getElementById('toastContainer');
    const toastId = 'toast-' + Date.now();

    const type = options.type || 'success';
    const title = options.title || 'Operación exitosa';
    const message = options.message || '';
    const duration = options.duration || 4000;

    const iconMap = {
        success: 'ri-checkbox-circle-fill',
        info: 'ri-information-fill',
        warning: 'ri-alert-fill',
        danger: 'ri-error-warning-fill',
    };

    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-icon">
            <i class="${iconMap[type]}"></i>
        </div>
        <div class="toast-content">
            <div class="toast-title">${title}</div>
            ${message ? `<div class="toast-message">${message}</div>` : ''}
        </div>
        <button type="button" class="toast-close">
            <i class="ri-close-line"></i>
        </button>
        <div class="toast-progress"></div>
    `;

    container.appendChild(toast);

    // Animar entrada
    setTimeout(() => toast.classList.add('show'), 10);

    // Cerrar al hacer clic en el botón
    const closeBtn = toast.querySelector('.toast-close');
    closeBtn.addEventListener('click', () => removeToast(toastId));

    // Barra de progreso
    const progressBar = toast.querySelector('.toast-progress');
    progressBar.style.animation = `toastProgress ${duration}ms linear forwards`;

    // Auto-cerrar
    const autoCloseTimer = setTimeout(() => removeToast(toastId), duration);

    // Pausar/reanudar con hover
    let remainingTime = duration;
    let startTime = Date.now();
    let isPaused = false;

    toast.addEventListener('mouseenter', () => {
        if (!isPaused) {
            isPaused = true;
            remainingTime -= Date.now() - startTime;
            clearTimeout(autoCloseTimer);
            progressBar.style.animationPlayState = 'paused';
        }
    });

    toast.addEventListener('mouseleave', () => {
        if (isPaused && remainingTime > 0) {
            isPaused = false;
            startTime = Date.now();
            progressBar.style.animation = `toastProgress ${remainingTime}ms linear forwards`;
            setTimeout(() => removeToast(toastId), remainingTime);
        }
    });

    function removeToast(id) {
        const toastEl = document.getElementById(id);
        if (toastEl) {
            toastEl.classList.remove('show');
            toastEl.classList.add('hide');
            setTimeout(() => toastEl.remove(), 300);
        }
    }
};
</script>
