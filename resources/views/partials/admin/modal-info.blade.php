<div id="infoModal" class="info-modal hidden">
    <div class="info-dialog">
        <div class="info-header" id="infoHeader">
            <h6 id="infoHeaderText">Información</h6>
            <button type="button" id="closeInfoModalBtn" class="info-close ripple-btn">
                <i class="ri-close-line"></i>
            </button>
        </div>

        <div class="info-body ripple-card">
            <i id="infoIcon" class="ri-information-fill info-icon"></i>
            <div class="flex flex-col gap-1 w-full">
                <h6 class="info-title" id="infoTitle">Acción completada</h6>
                <p class="info-message" id="infoMessage">Tu operación se realizó correctamente.</p>
            </div>
            <!-- ✅ Nueva sección de acciones -->
            <div class="info-actions">
                <button type="button" class="boton boton-modal-close" id="cancelButtonInfo">
                    <span class="boton-icon text-base"><i class="ri-close-line"></i></span>
                    <span class="boton-text">Cerrar</span>
                </button>
            </div>
        </div>
        
        <!-- ✅ Barra de progreso para auto-cierre -->
        <div class="progress-container" id="progressContainer">
            <div class="progress-bar" id="progressBar"></div>
            <div class="progress-text" id="progressText">Se cerrará automáticamente</div>
        </div>
    </div>
</div>

<script>
window.showInfoModal = function (options) {
    const modal = document.getElementById('infoModal');
    const dialog = modal.querySelector('.info-dialog');
    const header = document.getElementById('infoHeader');
    const icon = document.getElementById('infoIcon');
    const title = document.getElementById('infoTitle');
    const message = document.getElementById('infoMessage');
    const closeBtn = document.getElementById('closeInfoModalBtn');
    const cancelButtonInfo = document.getElementById('cancelButtonInfo');
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
        success: 'ri-checkbox-circle-line',
        info: 'ri-information-line',
        warning: 'ri-alert-line',
        danger: 'ri-error-warning-line',
        dark: 'ri-shut-down-line',
    };
    icon.className = `${iconMap[type]} info-icon text-${type}`;

    // Asignar textos
    headerText.textContent = options.header || 'Información';
    title.innerHTML = options.title || 'Acción completada';
    message.innerHTML = options.message || 'Tu operación se realizó correctamente.';

    // Mover modal al final del body para garantizar z-index máximo
    if (modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }
    
    
    // Reducir z-index del sidebar temporalmente
    const sidebar = document.getElementById('logo-sidebar');
    if (sidebar) {
        sidebar.style.zIndex = '1';
    }
    
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
            
            // Restaurar z-index del sidebar
            const sidebar = document.getElementById('logo-sidebar');
            if (sidebar) {
                sidebar.style.zIndex = '';
            }
        }, 250);
        removeListeners();
    }

    // --- 🔹 Eliminar listeners al cerrar
    function removeListeners() {
        closeBtn.removeEventListener('click', closeModal);
        cancelButtonInfo.removeEventListener('click', closeModal);
        modal.removeEventListener('click', handleOutsideClick);
        dialog.removeEventListener('mouseenter', pauseAutoClose);
        dialog.removeEventListener('mouseleave', resumeAutoClose);
        
        // Limpiar timers de la barra de progreso
        clearTimeout(autoCloseTimer);
        cancelAnimationFrame(progressTimer);
    }

    // --- 🔹 Cerrar al hacer clic fuera del cuadro
    function handleOutsideClick(e) {
        if (e.target === modal) closeModal();
    }

    // --- 🔹 Listeners para botones
    closeBtn.addEventListener('click', closeModal);
    cancelButtonInfo.addEventListener('click', closeModal);
    modal.addEventListener('click', handleOutsideClick);

    // --- 🔹 Autocierre (pausable con hover) con barra de progreso
    const timeout = options.timeout ?? 5000; // 5 segundos
    const progressContainer = document.getElementById('progressContainer');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    let autoCloseTimer;
    let progressTimer;
    let startTime;
    let pausedTime = 0;
    let isPaused = false;

    function updateProgressBar() {
        if (!isPaused && timeout > 0) {
            const elapsed = Date.now() - startTime + pausedTime;
            const progress = Math.min((elapsed / timeout) * 100, 100);
            const remaining = Math.max(0, timeout - elapsed);
            const remainingSeconds = Math.ceil(remaining / 1000);
            
            progressBar.style.width = progress + '%';
            
            if (remainingSeconds > 0) {
                progressText.textContent = `Se cerrará en ${remainingSeconds}s`;
            } else {
                progressText.textContent = 'Cerrando...';
            }
            
            if (progress < 100) {
                progressTimer = requestAnimationFrame(updateProgressBar);
            }
        }
    }

    function startAutoClose() {
        if (timeout > 0) {
            startTime = Date.now();
            isPaused = false;
            progressContainer.style.display = 'block';
            
            // Resetear barra de progreso
            progressBar.style.width = '0%';
            
            // Iniciar timer de cierre
            autoCloseTimer = setTimeout(closeModal, timeout);
            
            // Iniciar animación de progreso
            updateProgressBar();
        } else {
            // Si no hay timeout, ocultar barra de progreso
            progressContainer.style.display = 'none';
        }
    }
    
    function pauseAutoClose() {
        clearTimeout(autoCloseTimer);
        cancelAnimationFrame(progressTimer);
        isPaused = true;
        pausedTime += Date.now() - startTime;
        progressContainer.style.opacity = '0.5';
        progressText.textContent = 'Pausado - Aleja el cursor para continuar';
    }
    
    function resumeAutoClose() {
        if (timeout > 0 && isPaused) {
            const remainingTime = timeout - pausedTime;
            if (remainingTime > 0) {
                startTime = Date.now();
                isPaused = false;
                progressContainer.style.opacity = '1';
                autoCloseTimer = setTimeout(closeModal, remainingTime);
                updateProgressBar();
            } else {
                closeModal();
            }
        }
    }

    // Pausar/reanudar con hover
    dialog.addEventListener('mouseenter', pauseAutoClose);
    dialog.addEventListener('mouseleave', resumeAutoClose);

    // Iniciar auto-cierre
    startAutoClose();
};
</script>
