const SUBMENU_STORAGE_KEY = 'admin.sidebar.openSubmenu';

function closeSubmenu(container) {
    if (!container) {
        return;
    }
    container.classList.remove('open');
    const trigger = container.previousElementSibling;
    if (trigger && trigger.classList.contains('submenu-btn')) {
        trigger.classList.remove('rounded-top');
        const arrow = trigger.querySelector('.submenu-arrow');
        if (arrow) {
            arrow.classList.remove('rotate-180');
        }
    }
}

function openSubmenu(container) {
    if (!container) {
        return;
    }
    container.classList.add('open');
    const trigger = container.previousElementSibling;
    if (trigger && trigger.classList.contains('submenu-btn')) {
        trigger.classList.add('rounded-top');
        const arrow = trigger.querySelector('.submenu-arrow');
        if (arrow) {
            arrow.classList.add('rotate-180');
        }
    }
}

function rememberOpenSubmenu(id) {
    try {
        if (id) {
            localStorage.setItem(SUBMENU_STORAGE_KEY, id);
        } else {
            localStorage.removeItem(SUBMENU_STORAGE_KEY);
        }
    } catch (error) {
        console.warn('No se pudo guardar el submenú en localStorage:', error);
    }
}

const submenuButtons = document.querySelectorAll('.submenu-btn');

submenuButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
        const submenu = btn.nextElementSibling;
        if (!submenu || !submenu.classList.contains('sidebar-submenu')) {
            return;
        }

        const isOpening = !submenu.classList.contains('open');

        // Cierra submenús abiertos previamente
        document.querySelectorAll('.sidebar-submenu.open').forEach((otherMenu) => {
            if (otherMenu !== submenu) {
                closeSubmenu(otherMenu);
            }
        });

        if (isOpening) {
            openSubmenu(submenu);
            rememberOpenSubmenu(submenu.id || '');
            // Scroll suave hasta el botón
            btn.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'nearest' });
        } else {
            closeSubmenu(submenu);
            rememberOpenSubmenu(null);
        }
    });
});

// Restaurar submenú abierto y desplazar hacia el elemento activo al cargar
document.addEventListener('DOMContentLoaded', () => {
    let isCollapsed = false;
    try {
        isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    } catch (error) {
        isCollapsed = document.body.classList.contains('sidebar-collapsed');
    }
    let targetToFocus = null;

    try {
        const savedSubmenuId = localStorage.getItem(SUBMENU_STORAGE_KEY);
        if (savedSubmenuId) {
            const savedSubmenu = document.getElementById(savedSubmenuId);
            if (savedSubmenu) {
                // Cierra para evitar animaciones conflictivas
                document.querySelectorAll('.sidebar-submenu.open').forEach(closeSubmenu);
                openSubmenu(savedSubmenu);
            }
        }
    } catch (error) {
        console.warn('No se pudo restaurar el submenú desde localStorage:', error);
    }

    // Encontrar elemento activo más prioritario (subenlace > enlace principal)
    const activeSubLink = document.querySelector('.sidebar-sublink.active');
    const activeLink = document.querySelector('.sidebar-link.active');

    if (activeSubLink) {
        targetToFocus = activeSubLink;
        const parentSubmenu = activeSubLink.closest('.sidebar-submenu');
        if (parentSubmenu && !parentSubmenu.classList.contains('open')) {
            openSubmenu(parentSubmenu);
        }
    } else if (activeLink) {
        targetToFocus = activeLink;
    }

    if (targetToFocus) {
        setTimeout(() => {
            const options = { block: 'center', inline: 'nearest' };
            if (isCollapsed) {
                targetToFocus.scrollIntoView(options);
            } else {
                targetToFocus.scrollIntoView({ behavior: 'smooth', ...options });
            }
        }, 150);
    }
});
