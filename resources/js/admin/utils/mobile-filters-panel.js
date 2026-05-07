// mobile-filters-panel.js
// Control del panel de filtros (.tabla-filtros) en vistas de tabla para móviles

export function initMobileFiltersPanel() {
    const toggleBtn = document.getElementById('toggleFiltersBtn');
    const filtersPanel = document.querySelector('.tabla-filtros');
    const clearBtn = document.getElementById('clearFiltersBtn');
    const applyBtn = document.getElementById('applyFiltersBtn');
    // Overlay dedicado solo para el panel de filtros móvil
    const filtersOverlay = document.createElement('div');
    filtersOverlay.className = 'tabla-filtros-overlay';
    document.body.appendChild(filtersOverlay);

    if (!toggleBtn || !filtersPanel) {
        return;
    }



    const mobileQuery = window.matchMedia('(max-width: 639px)');

    const isMobile = () => mobileQuery.matches;

    // Guardar posición original del panel para poder restaurarla en escritorio
    const originalParent = filtersPanel.parentElement;
    const originalNextSibling = filtersPanel.nextElementSibling;

    const movePanelToBody = () => {
        if (filtersPanel.parentElement !== document.body) {
            document.body.appendChild(filtersPanel);
        }
    };

    const restorePanelToOriginal = () => {
        if (!originalParent) return;
        if (filtersPanel.parentElement === originalParent) return;

        if (originalNextSibling && originalNextSibling.parentElement === originalParent) {
            originalParent.insertBefore(filtersPanel, originalNextSibling);
        } else {
            originalParent.appendChild(filtersPanel);
        }
    };

    // Sólo mover al body cuando estamos en móvil (max-width: 639px)
    if (isMobile()) {
        movePanelToBody();
    }

    const openPanel = () => {
        if (!isMobile()) return;
        // Asegurar que en móvil el panel cuelga del body para respetar z-index
        movePanelToBody();
        filtersPanel.classList.add('is-open');
        filtersPanel.style.transform = '';
        filtersOverlay.classList.add('is-visible');
        document.body.style.overflow = 'hidden';
    };

    const closePanel = () => {
        if (!isMobile()) return;
        filtersPanel.classList.remove('is-open');
        isDragging = false;
        currentDeltaY = 0;
        filtersOverlay.classList.remove('is-visible');
        document.body.style.overflow = '';
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

    // Cerrar al hacer click fuera del panel (en cualquier parte de la página)
    document.addEventListener('click', (event) => {
        if (!isMobile()) return;
        if (!filtersPanel.classList.contains('is-open')) return;

        const target = event.target;

        // Si el click fue dentro del panel, no cerramos
        if (filtersPanel.contains(target)) return;

        // Si el click fue en el botón de toggle, dejamos que su handler maneje el estado
        if (toggleBtn && toggleBtn.contains(target)) return;

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
        filtersOverlay.classList.remove('is-visible');
        document.body.style.overflow = '';

        // Sincronizar ubicación del panel según el breakpoint actual
        if (isMobile()) {
            movePanelToBody();
        } else {
            restorePanelToOriginal();
        }
    });
}
