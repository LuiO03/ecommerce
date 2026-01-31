// site-sidebar-manager.js - Gestión del sidebar del sitio público
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('siteSidebar');
    const overlay = document.getElementById('siteOverlay');
    const toggleBtn = document.getElementById('siteMenuToggle');
    const closeBtn = document.getElementById('siteMenuClose');
    const header = document.getElementById('siteHeader');

    if (!sidebar || !overlay) return;

    // Función para abrir sidebar
    function openSidebar() {
        sidebar.classList.add('active');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    // Función para cerrar sidebar
    function closeSidebar() {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
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

    // ===== Panel derecho por familia (hover) =====
    const familyLinks = sidebar.querySelectorAll('.site-nav-family-link');
    const flyoutContainer = sidebar.querySelector('.site-sidebar-flyout');
    const flyoutPanels = sidebar.querySelectorAll('.site-flyout-panel');
    let closeTimer = null;

    const clearActiveFamily = () => {
        familyLinks.forEach(link => link.classList.remove('active'));
        flyoutPanels.forEach(panel => panel.classList.remove('active'));
        flyoutContainer?.classList.remove('active');
    };

    const activateFamily = (familyId) => {
        if (!familyId) return;
        clearActiveFamily();
        const activeLink = sidebar.querySelector(`.site-nav-family-link[data-family-id="${familyId}"]`);
        const activePanel = sidebar.querySelector(`.site-flyout-panel[data-family-panel="${familyId}"]`);
        activeLink?.classList.add('active');
        activePanel?.classList.add('active');
        flyoutContainer?.classList.add('active');
    };

    familyLinks.forEach(link => {
        const familyId = link.getAttribute('data-family-id');

        link.addEventListener('mouseenter', () => {
            clearTimeout(closeTimer);
            activateFamily(familyId);
        });

        link.addEventListener('focus', () => {
            clearTimeout(closeTimer);
            activateFamily(familyId);
        });

        link.addEventListener('click', (e) => {
            e.preventDefault();
            const isActive = link.classList.contains('active');
            if (isActive) {
                clearActiveFamily();
            } else {
                activateFamily(familyId);
            }
        });
    });

    sidebar.addEventListener('mouseenter', () => {
        clearTimeout(closeTimer);
    });

    sidebar.addEventListener('mouseleave', () => {
        closeTimer = setTimeout(clearActiveFamily, 150);
    });

    flyoutContainer?.addEventListener('mouseenter', () => {
        clearTimeout(closeTimer);
    });

    flyoutContainer?.addEventListener('mouseleave', () => {
        closeTimer = setTimeout(clearActiveFamily, 150);
    });

    // Cerrar flyout al cerrar sidebar
    const originalCloseSidebar = closeSidebar;
    function closeSidebarWithFlyout() {
        originalCloseSidebar();
        clearActiveFamily();
    }
    // Handlers con cierre completo
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const isOpen = sidebar.classList.contains('active');
            isOpen ? closeSidebarWithFlyout() : openSidebar();
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', closeSidebarWithFlyout);
    }

    overlay.addEventListener('click', closeSidebarWithFlyout);

    // Cerrar al hacer click en items del menú
    const menuItems = sidebar.querySelectorAll('.site-nav-menu-item');
    menuItems.forEach(item => {
        item.addEventListener('click', closeSidebarWithFlyout);
    });

    // Cerrar con tecla Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && sidebar.classList.contains('active')) {
            closeSidebarWithFlyout();
        }
    });

    // Cerrar al hacer resize a desktop
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768 && sidebar.classList.contains('active')) {
            closeSidebarWithFlyout();
        }
    });
});
