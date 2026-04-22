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

function openSubmenu(container, animate = true) {
    if (!container) {
        return;
    }

    const trigger = container.previousElementSibling;
    const arrow = trigger && trigger.classList.contains('submenu-btn')
        ? trigger.querySelector('.submenu-arrow')
        : null;

    if (!animate) {
        const prevTransition = container.style.transition;
        container.style.transition = 'none';

        container.classList.add('open');
        if (trigger && trigger.classList.contains('submenu-btn')) {
            trigger.classList.add('rounded-top');
            if (arrow) {
                arrow.classList.add('rotate-180');
            }
        }

        // Forzar reflow y restaurar transición
        void container.offsetHeight;
        container.style.transition = prevTransition;
        return;
    }

    container.classList.add('open');
    if (trigger && trigger.classList.contains('submenu-btn')) {
        trigger.classList.add('rounded-top');
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
                // Abrir sin animación al restaurar desde localStorage
                openSubmenu(savedSubmenu, false);
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
            // Abrir sin animación cuando se basa en el enlace activo al navegar
            openSubmenu(parentSubmenu, false);
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

// --- SUBMENÚS FLOTANTES EN SIDEBAR COLAPSADO ---
/*
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('.sidebar-principal');
    const submenuButtons = document.querySelectorAll('.submenu-btn');
    let floatingMenu = null;
    let floatingMenuTimeout = null;

    submenuButtons.forEach((btn) => {
        btn.addEventListener('mouseenter', (e) => {
            if (!sidebar.classList.contains('collapsed')) return;
            // Cerrar otros flotantes
            removeFloatingMenu();
            const submenu = btn.nextElementSibling;
            if (!submenu || !submenu.classList.contains('sidebar-submenu')) return;

            // Clonar el submenú
            floatingMenu = submenu.cloneNode(true);
            floatingMenu.classList.add('floating-submenu');
            floatingMenu.classList.add('open');
            floatingMenu.style.position = 'fixed';
            floatingMenu.style.zIndex = 9999;
            floatingMenu.style.minWidth = '180px';
            floatingMenu.style.boxShadow = '0 8px 24px rgba(0,0,0,0.18)';
            floatingMenu.style.background = 'var(--color-sidebar-bg, #fff)';
            floatingMenu.style.borderRadius = '0.5rem';
            floatingMenu.style.padding = '8px 0';
            floatingMenu.style.left = `${sidebar.offsetWidth + 4}px`;

            // Posicionar verticalmente alineado al botón
            const btnRect = btn.getBoundingClientRect();
            floatingMenu.style.top = `${btnRect.top}px`;

            document.body.appendChild(floatingMenu);

            // Ocultar al salir del área
            floatingMenu.addEventListener('mouseleave', removeFloatingMenu);
            floatingMenu.addEventListener('mouseenter', () => {
                clearTimeout(floatingMenuTimeout);
            });
        });
        btn.addEventListener('mouseleave', () => {
            if (!sidebar.classList.contains('collapsed')) return;
            // Espera breve para permitir pasar al menú flotante
            floatingMenuTimeout = setTimeout(removeFloatingMenu, 180);
        });
    });

    function removeFloatingMenu() {
        if (floatingMenu) {
            floatingMenu.remove();
            floatingMenu = null;
        }
    }

    // Cerrar flotante al hacer scroll o clic fuera
    document.addEventListener('scroll', removeFloatingMenu, true);
    document.addEventListener('click', (e) => {
        if (floatingMenu && !floatingMenu.contains(e.target)) {
            removeFloatingMenu();
        }
    });
});
*/
// --- FIN SUBMENÚS FLOTANTES ---
