document
    .querySelectorAll(
        ".sidebar-link .sidebar-icon, .sidebar-sublink .sidebar-icon, .menu-item .sidebar-icon"
    )
    .forEach((icon) => {
        const original = [...icon.classList].find((c) => c.includes("-line"));
        if (!original) return;
        const filled = original.replace("-line", "-fill");

        const parent = icon.closest("a, button");

        parent?.addEventListener("mouseenter", () => {
            if (icon.classList.contains(original)) {
                icon.classList.replace(original, filled);
            }
        });

        parent?.addEventListener("mouseleave", () => {
            if (icon.classList.contains(filled)) {
                icon.classList.replace(filled, original);
            }
        });
    });
