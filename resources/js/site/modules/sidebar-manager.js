// site-sidebar-manager.js - Gestión del sidebar del sitio público
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('siteSidebar');
    const overlay = document.getElementById('siteOverlay');
    const toggleBtn = document.getElementById('siteMenuToggle');
    const closeBtn = document.getElementById('siteMenuClose');
    const header = document.getElementById('siteHeader');

    if (!sidebar || !overlay) return;

    // ===== Navegación por páginas dentro del sidebar =====
    const pagesContainer = sidebar.querySelector('[data-sidebar-pages]');
    const pages = pagesContainer
        ? Array.from(pagesContainer.querySelectorAll('[data-sidebar-page]'))
        : [];
    const pageById = new Map(pages.map(page => [page.getAttribute('data-sidebar-page'), page]));

    const TRANSITION_MS = 260;

    let activePageId = 'root';
    let historyStack = [];
    let isAnimating = false;

    const setActivePageInstant = (pageId) => {
        if (!pageById.has(pageId)) return;

        pages.forEach(page => {
            const isActive = page.getAttribute('data-sidebar-page') === pageId;
            page.classList.toggle('active', isActive);
            page.classList.remove(
                'sidebar-enter-from-right',
                'sidebar-enter-from-left',
                'sidebar-leave-to-left',
                'sidebar-leave-to-right'
            );
            page.style.transform = '';
            page.style.transition = '';
            page.style.zIndex = '';
            page.setAttribute('aria-hidden', isActive ? 'false' : 'true');
        });

        activePageId = pageId;
    };

    const transitionToPage = (toPageId, direction = 'forward') => {
        if (isAnimating) return;
        if (!toPageId || toPageId === activePageId) return;
        if (!pageById.has(toPageId)) return;

        const fromPageId = activePageId;
        const fromEl = pageById.get(fromPageId);
        const toEl = pageById.get(toPageId);

        if (!fromEl || !toEl) {
            setActivePageInstant(toPageId);
            return;
        }

        isAnimating = true;

        // Capas: que la página entrante quede encima para que el movimiento se note
        toEl.style.zIndex = '2';
        fromEl.style.zIndex = '1';

        // Asegurar visible
        toEl.classList.add('active');
        toEl.setAttribute('aria-hidden', 'false');

        // Estado inicial (sin transición)
        const startX = direction === 'back' ? '-100%' : '100%';
        const endFromX = direction === 'back' ? '100%' : '-100%';

        toEl.style.transition = 'none';
        fromEl.style.transition = 'none';
        toEl.style.transform = `translateX(${startX})`;
        fromEl.style.transform = 'translateX(0)';

        // Forzar reflow
        void toEl.offsetWidth;
        void fromEl.offsetWidth;

        // Activar transición
        toEl.style.transition = `transform ${TRANSITION_MS}ms ease`;
        fromEl.style.transition = `transform ${TRANSITION_MS}ms ease`;

        requestAnimationFrame(() => {
            toEl.style.transform = 'translateX(0)';
            fromEl.style.transform = `translateX(${endFromX})`;
        });

        const cleanup = () => {
            fromEl.classList.remove('active');
            fromEl.setAttribute('aria-hidden', 'true');

            // Reset inline styles
            fromEl.style.transform = '';
            fromEl.style.transition = '';
            fromEl.style.zIndex = '';
            toEl.style.transform = '';
            toEl.style.transition = '';
            toEl.style.zIndex = '';

            isAnimating = false;
        };

        const onEnd = (event) => {
            if (event.target !== toEl) return;
            toEl.removeEventListener('transitionend', onEnd);
            cleanup();
        };

        toEl.addEventListener('transitionend', onEnd);

        // Fallback por si el transitionend no dispara
        setTimeout(() => {
            if (!isAnimating) return;
            toEl.removeEventListener('transitionend', onEnd);
            cleanup();
        }, TRANSITION_MS + 60);

        activePageId = toPageId;
    };

    const resetPages = () => {
        historyStack = [];
        setActivePageInstant('root');
    };

    const resetPagesAnimated = () => {
        historyStack = [];
        if (activePageId === 'root') return;
        transitionToPage('root', 'back');
    };

    const navigateTo = (pageId) => {
        if (!pageId || pageId === activePageId) return;
        if (!pageById.has(pageId)) return;
        historyStack.push(activePageId);
        transitionToPage(pageId, 'forward');
    };

    const navigateBack = () => {
        const previous = historyStack.pop();
        transitionToPage(previous || 'root', 'back');
    };

    // Función para abrir sidebar
    function openSidebar() {
        resetPages();
        sidebar.classList.add('active');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    // Función para cerrar sidebar
    function closeSidebar() {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
        resetPages();
    }

    // Cerrar al hacer click en el header (excepto el botón de toggle)
    header.addEventListener('click', (e) => {
        if (!e.target.closest('#siteMenuToggle')) {
            // closeSidebar(); // Comentado para permitir interacciones en el header
        }
    });

    // Exportar funciones globales
    window.openSiteSidebar = openSidebar;
    window.closeSiteSidebar = closeSidebar;

    // ===== Interacciones dentro del sidebar (delegación) =====
    sidebar.addEventListener('click', (e) => {
        const target = e.target;

        const navTo = target.closest?.('[data-sidebar-nav-to]');
        if (navTo) {
            e.preventDefault();
            navigateTo(navTo.getAttribute('data-sidebar-nav-to'));
            return;
        }

        const backBtn = target.closest?.('[data-sidebar-back]');
        if (backBtn) {
            e.preventDefault();
            navigateBack();
            return;
        }

        const homeBtn = target.closest?.('[data-sidebar-home]');
        if (homeBtn) {
            e.preventDefault();
            resetPagesAnimated();
            return;
        }

        // Cerrar al hacer click en links que deberían cerrar el sidebar
        const closeLink = target.closest?.('[data-sidebar-close]');
        if (closeLink && closeLink.tagName === 'A') {
            closeSidebar();
        }
    });

    // Handlers abrir/cerrar
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const isOpen = sidebar.classList.contains('active');
            isOpen ? closeSidebar() : openSidebar();
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', closeSidebar);
    }

    overlay.addEventListener('click', closeSidebar);

    // Cerrar con tecla Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && sidebar.classList.contains('active')) {
            closeSidebar();
        }
    });

    // Cerrar al hacer resize a desktop
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768 && sidebar.classList.contains('active')) {
            closeSidebar();
        }
    });
});
