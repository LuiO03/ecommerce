// mobile-filters-panel.js
// Control del panel de filtros (.tabla-filtros) en vistas de tabla para móviles

export function initMobileFiltersPanel() {
    const toggleBtn = document.getElementById('toggleFiltersBtn');
    const filtersPanel = document.querySelector('.tabla-filtros');
    const clearBtn = document.getElementById('clearFiltersBtn');
    const applyBtn = document.getElementById('applyFiltersBtn');

    if (!toggleBtn || !filtersPanel) {
        return;
    }

    const mobileQuery = window.matchMedia('(max-width: 639px)');

    const isMobile = () => mobileQuery.matches;

    const openPanel = () => {
        if (!isMobile()) return;
        filtersPanel.classList.add('is-open');
        filtersPanel.style.transform = '';
    };

    const closePanel = () => {
        if (!isMobile()) return;
        filtersPanel.classList.remove('is-open');
        filtersPanel.style.transform = '';
        isDragging = false;
        currentDeltaY = 0;
    };

    // Toggle con el botón principal
    toggleBtn.addEventListener('click', (event) => {
        // En escritorio no intervenimos; el aside se muestra como bloque normal
        if (!isMobile()) return;
        event.preventDefault();
        const isOpen = filtersPanel.classList.contains('is-open');
        if (isOpen) {
            closePanel();
        } else {
            openPanel();
        }
    });

    // Cerrar al aplicar filtros (botón aplicar)
    if (applyBtn) {
        applyBtn.addEventListener('click', () => {
            if (!isMobile()) return;
            // Los filtros ya se aplican con los eventos de DataTableManager;
            // aquí solo cerramos el panel y el overlay.
            closePanel();
        });
    }

    // Cerrar al tocar fuera del panel (usando el overlay CSS en body::before)
    document.addEventListener('click', (event) => {
        if (!isMobile()) return;
        if (!filtersPanel.classList.contains('is-open')) return;

        const target = event.target;

        // Ignorar clicks dentro del panel o en los botones relacionados
        if (filtersPanel.contains(target)) return;
        if (toggleBtn.contains(target)) return;
        if (clearBtn && clearBtn.contains(target)) return;
        if (applyBtn && applyBtn.contains(target)) return;

        closePanel();
    });

    // Gesto de arrastre hacia abajo para cerrar (tipo bottom sheet)
    let startY = 0;
    let currentDeltaY = 0;
    let isDragging = false;

    const dragThreshold = 80; // píxeles necesarios para cerrar

    const getClientY = (event) => {
        if (event.touches && event.touches.length > 0) {
            return event.touches[0].clientY;
        }
        if (event.changedTouches && event.changedTouches.length > 0) {
            return event.changedTouches[0].clientY;
        }
        return event.clientY;
    };

    const onDragStart = (event) => {
        if (!isMobile()) return;
        if (!filtersPanel.classList.contains('is-open')) return;

        isDragging = true;
        startY = getClientY(event);
        currentDeltaY = 0;
        // Pausar transiciones CSS mientras se arrastra
        filtersPanel.style.transition = 'none';
    };

    const onDragMove = (event) => {
        if (!isDragging) return;

        const currentY = getClientY(event);
        if (currentY == null) return;

        currentDeltaY = currentY - startY;
        if (currentDeltaY < 0) {
            currentDeltaY = 0; // No permitir arrastrar hacia arriba
        }

        filtersPanel.style.transform = `translateY(${currentDeltaY}px)`;
    };

    const onDragEnd = () => {
        if (!isDragging) return;

        // Restaurar transición CSS
        filtersPanel.style.transition = '';

        if (currentDeltaY > dragThreshold) {
            // Cerrar si se arrastró lo suficiente hacia abajo
            closePanel();
        } else {
            // Volver a la posición original
            filtersPanel.style.transform = '';
        }

        isDragging = false;
        currentDeltaY = 0;
    };

    // Escuchamos arrastre en todo el panel para que sea más cómodo
    filtersPanel.addEventListener('mousedown', onDragStart);
    window.addEventListener('mousemove', onDragMove);
    window.addEventListener('mouseup', onDragEnd);

    filtersPanel.addEventListener('touchstart', onDragStart, { passive: true });
    window.addEventListener('touchmove', onDragMove, { passive: true });
    window.addEventListener('touchend', onDragEnd);

    // Si cambia el tamaño de pantalla y dejamos de estar en móvil, aseguramos estado consistente
    mobileQuery.addEventListener('change', () => {
        filtersPanel.classList.remove('is-open');
        filtersPanel.style.transform = '';
        isDragging = false;
        currentDeltaY = 0;
    });
}
