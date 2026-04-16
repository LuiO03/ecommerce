// mobile-actions-menu.js
// Mobile floating actions menu for .column-actions-td rows (max-width: 768px)

export function initMobileActionsMenu() {
    const mobileQuery = window.matchMedia('(max-width: 768px)');
    const SELECTOR_BUTTON = '.column-actions-td .boton-show-actions';
    const SELECTOR_MENU = '.column-actions-td .tabla-botones';

    let activeMenu = null;

    const isMobile = () => mobileQuery.matches;

    const getMenuFromButton = (button) => {
        const container = button.closest('.column-actions-td');
        if (!container) return null;
        return container.querySelector('.tabla-botones');
    };

    const getContainerFromButton = (button) => {
        return button ? button.closest('.column-actions-td') : null;
    };

    const clearInlinePosition = (menu) => {
        if (!menu) return;
        menu.style.left = '';
        menu.style.top = '';
    };

    const closeActiveMenu = () => {
        if (!activeMenu) return;

        activeMenu.menu.classList.remove('is-open', 'is-measuring');
        activeMenu.button.classList.remove('is-active');
        activeMenu.container?.classList.remove('is-menu-open');
        activeMenu = null;
    };

    const positionMenu = (button, menu) => {
        if (!button || !menu) return;

        // Measure menu before opening animation.
        menu.classList.add('is-measuring');
        menu.classList.remove('is-open');

        const buttonRect = button.getBoundingClientRect();
        const menuRect = menu.getBoundingClientRect();

        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        const margin = 10;
        const gap = 8;

        let left = buttonRect.right - menuRect.width;
        left = Math.max(margin, Math.min(left, viewportWidth - menuRect.width - margin));

        const spaceBelow = viewportHeight - buttonRect.bottom - gap - margin;
        const spaceAbove = buttonRect.top - gap - margin;

        let top = buttonRect.bottom + gap;
        if (spaceBelow < menuRect.height && spaceAbove > spaceBelow) {
            top = buttonRect.top - menuRect.height - gap;
        }
        top = Math.max(margin, Math.min(top, viewportHeight - menuRect.height - margin));

        menu.style.left = `${Math.round(left)}px`;
        menu.style.top = `${Math.round(top)}px`;
        menu.classList.remove('is-measuring');
    };

    const openMenuForButton = (button) => {
        const menu = getMenuFromButton(button);
        if (!menu) return;

        // Keep only one row menu open at a time.
        closeActiveMenu();

        positionMenu(button, menu);
        button.classList.add('is-active');
        const container = getContainerFromButton(button);
        container?.classList.add('is-menu-open');

        requestAnimationFrame(() => {
            menu.classList.add('is-open');
        });

        activeMenu = { button, menu, container };
    };

    const handleDocumentClick = (event) => {
        if (!isMobile()) {
            closeActiveMenu();
            return;
        }

        const button = event.target.closest(SELECTOR_BUTTON);

        if (button) {
            event.preventDefault();
            event.stopPropagation();

            if (activeMenu && activeMenu.button === button) {
                closeActiveMenu();
            } else {
                openMenuForButton(button);
            }
            return;
        }

        if (!activeMenu) return;

        // Allow clicks inside the open menu (links, buttons, forms).
        if (activeMenu.menu.contains(event.target)) {
            return;
        }

        closeActiveMenu();
    };

    const closeOnEscape = (event) => {
        if (event.key === 'Escape') {
            closeActiveMenu();
        }
    };

    const closeOnLayoutChange = () => {
        if (!activeMenu) return;
        if (!isMobile()) {
            closeActiveMenu();
            return;
        }
        positionMenu(activeMenu.button, activeMenu.menu);
    };

    const closeOnScroll = () => {
        closeActiveMenu();
    };

    const resetAllMenus = () => {
        document.querySelectorAll(SELECTOR_MENU).forEach((menu) => {
            menu.classList.remove('is-open', 'is-measuring');
            clearInlinePosition(menu);
        });

        document.querySelectorAll(SELECTOR_BUTTON).forEach((button) => {
            button.classList.remove('is-active');
        });

        document.querySelectorAll('.column-actions-td.is-menu-open').forEach((container) => {
            container.classList.remove('is-menu-open');
        });

        activeMenu = null;
    };

    document.addEventListener('click', handleDocumentClick);
    document.addEventListener('keydown', closeOnEscape);
    window.addEventListener('resize', closeOnLayoutChange);
    // Capture scroll from containers too (main, table wrapper, etc.)
    document.addEventListener('scroll', closeOnScroll, true);

    mobileQuery.addEventListener('change', () => {
        resetAllMenus();
    });
}
