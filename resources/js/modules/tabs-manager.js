export function initTabsManager({
    selector = '[data-tabs]',
    initialTab = null,
    storageKey = null,
} = {}) {
    const roots = typeof selector === 'string'
        ? Array.from(document.querySelectorAll(selector))
        : (selector instanceof Element ? [selector] : []);

    roots.forEach((root) => {
        if (!(root instanceof Element) || root.dataset.tabsInitialized === 'true') {
            return;
        }

        const buttons = Array.from(root.querySelectorAll('[data-tab-target]'));
        const panels = Array.from(root.querySelectorAll('[data-tab-panel]'));

        if (!buttons.length || !panels.length) {
            return;
        }

        const validTabs = buttons
            .map((button) => button.dataset.tabTarget)
            .filter(Boolean);

        if (!validTabs.length) {
            return;
        }

        const rootStorageKey = storageKey || root.dataset.tabsStorageKey || '';
        let resolvedInitialTab = initialTab || root.dataset.tabsInitial || validTabs[0];

        if (rootStorageKey) {
            const savedTab = localStorage.getItem(rootStorageKey);
            if (savedTab && validTabs.includes(savedTab)) {
                resolvedInitialTab = savedTab;
            }
        }

        if (!validTabs.includes(resolvedInitialTab)) {
            resolvedInitialTab = validTabs[0];
        }

        const showTab = (tabName) => {
            buttons.forEach((button) => {
                const isActive = button.dataset.tabTarget === tabName;
                button.classList.toggle('is-active', isActive);
                button.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            panels.forEach((panel) => {
                const isActive = panel.dataset.tabPanel === tabName;
                panel.classList.toggle('is-active', isActive);
                panel.hidden = !isActive;
            });
        };

        buttons.forEach((button) => {
            button.addEventListener('click', () => {
                const target = button.dataset.tabTarget;
                if (!target || !validTabs.includes(target)) {
                    return;
                }

                showTab(target);

                if (rootStorageKey) {
                    localStorage.setItem(rootStorageKey, target);
                }
            });
        });

        showTab(resolvedInitialTab);
        root.dataset.tabsInitialized = 'true';
    });
}
