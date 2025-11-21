/**
 * ==========================================
 * COMPONENTE ALERT - Manejo de cierre
 * ==========================================
 * 
 * Funcionalidades:
 * - Cierre con animación suave
 * - Persistencia en localStorage (opcional)
 * - Auto-cierre después de X segundos (opcional)
 */

export function initAlerts() {
    const alerts = document.querySelectorAll('[data-alert]');
    
    alerts.forEach(alert => {
        const closeBtn = alert.querySelector('[data-alert-close]');
        const autoDismiss = alert.dataset.autoDismiss; // Tiempo en ms
        const persistKey = alert.dataset.persistKey; // Clave para localStorage

        // Si tiene clave de persistencia, verificar si ya fue cerrado
        if (persistKey && localStorage.getItem(`alert-dismissed-${persistKey}`)) {
            alert.style.display = 'none';
            return;
        }

        // Auto-cerrar si está configurado
        if (autoDismiss) {
            setTimeout(() => {
                closeAlert(alert, persistKey);
            }, parseInt(autoDismiss));
        }

        // Evento de cierre manual
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                closeAlert(alert, persistKey);
            });
        }
    });
}

/**
 * Cierra un alert con animación
 */
function closeAlert(alert, persistKey = null) {
    // Agregar clase de cierre para animación
    alert.classList.add('alert-closing');
    
    // Guardar estado en localStorage si tiene clave
    if (persistKey) {
        localStorage.setItem(`alert-dismissed-${persistKey}`, 'true');
    }

    // Remover del DOM después de la animación
    setTimeout(() => {
        alert.remove();
    }, 300); // Debe coincidir con la duración de la transición CSS
}

// Inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAlerts);
} else {
    initAlerts();
}
