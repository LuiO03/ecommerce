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

    // Event listeners
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const isOpen = sidebar.classList.contains('active');
            isOpen ? closeSidebar() : openSidebar();
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', closeSidebar);
    }

    // Cerrar al hacer click en el overlay
    overlay.addEventListener('click', closeSidebar);

    // Cerrar al hacer click en items del menú
    const menuItems = sidebar.querySelectorAll('.site-nav-menu-item');
    menuItems.forEach(item => {
        item.addEventListener('click', closeSidebar);
    });

    // Cerrar al hacer click en el header (excepto el botón de toggle)
    header.addEventListener('click', (e) => {
        // Si no es el botón toggle, cerrar
        if (!e.target.closest('#siteMenuToggle')) {
            // closeSidebar(); // Comentado para permitir interacciones en el header
        }
    });

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

    // Exportar funciones globales
    window.openSiteSidebar = openSidebar;
    window.closeSiteSidebar = closeSidebar;

    // ===== Manejo de Familias y Categorías Expandibles =====
    const familyLinks = sidebar.querySelectorAll('.site-nav-family-link');
    const categoryLinks = sidebar.querySelectorAll('.site-nav-category-link');

    // Expandir/contraer familias
    familyLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const family = link.closest('.site-nav-family');
            family.classList.toggle('active');
        });
    });

    // Expandir/contraer categorías
    categoryLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const categoryItem = link.closest('.site-nav-category-item');
            categoryItem.classList.toggle('active');
        });
    });
});
