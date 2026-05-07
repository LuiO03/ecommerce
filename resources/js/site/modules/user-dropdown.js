// user-dropdown.js - Gestión del dropdown del usuario logueado
document.addEventListener('DOMContentLoaded', () => {
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userMenu = document.getElementById('userMenu');

    if (!userMenuBtn || !userMenu) return;

    // Alternar dropdown al hacer click
    userMenuBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        const isOpen = userMenu.classList.contains('active');

        if (isOpen) {
            userMenu.classList.remove('active');
            userMenuBtn.setAttribute('aria-expanded', 'false');
        } else {
            userMenu.classList.add('active');
            userMenuBtn.setAttribute('aria-expanded', 'true');
        }
    });

    // Cerrar dropdown al hacer click fuera
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.nav-user-dropdown')) {
            userMenu.classList.remove('active');
            userMenuBtn.setAttribute('aria-expanded', 'false');
        }
    });

    // Cerrar dropdown con tecla Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && userMenu.classList.contains('active')) {
            userMenu.classList.remove('active');
            userMenuBtn.setAttribute('aria-expanded', 'false');
        }
    });

    // Cerrar dropdown al hacer click en un item del menú
    const menuItems = userMenu.querySelectorAll('.nav-user-menu-item');
    menuItems.forEach(item => {
        item.addEventListener('click', () => {
            userMenu.classList.remove('active');
            userMenuBtn.setAttribute('aria-expanded', 'false');
        });
    });
});
