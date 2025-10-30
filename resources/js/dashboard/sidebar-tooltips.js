document.addEventListener("DOMContentLoaded", () => {
    const sidebar = document.querySelector(".sidebar-principal");
    let tooltipEl = null;
    let hideTimeout = null;

    // Incluimos también el #theme-toggle
    document
        .querySelectorAll(".sidebar-link, .sidebar-sublink, .submenu-btn, #theme-toggle")
        .forEach((link) => {
            link.addEventListener("mouseenter", (e) => {
                if (!sidebar.classList.contains("collapsed")) return;

                const text = link.getAttribute("data-tooltip");
                if (!text) return;

                clearTimeout(hideTimeout);

                // Crear tooltip si no existe
                if (!tooltipEl) {
                    tooltipEl = document.createElement("div");
                    tooltipEl.className = "sidebar-tooltip";
                    document.body.appendChild(tooltipEl);
                }

                // Reiniciar animaciones
                tooltipEl.classList.remove("fade-out");
                void tooltipEl.offsetWidth; // fuerza reflow
                tooltipEl.classList.add("fade-in");

                // Contenido dinámico
                if (link.classList.contains("submenu-btn")) {
                    tooltipEl.innerHTML = `${text} <i class="ri-arrow-drop-down-line tooltip-icon"></i>`;
                } else {
                    tooltipEl.textContent = text;
                }

                // Posicionamiento preciso
                const rect = link.getBoundingClientRect();
                const sidebarWidth = sidebar.offsetWidth;

                tooltipEl.style.top = `${rect.top + window.scrollY + rect.height / 2}px`;
                tooltipEl.style.left = `${sidebarWidth + 12}px`;
                tooltipEl.style.transform = "translateY(-50%)"; // centrado vertical exacto

                tooltipEl.classList.add("show");
            });

            link.addEventListener("mouseleave", () => {
                if (tooltipEl) {
                    tooltipEl.classList.remove("fade-in");
                    tooltipEl.classList.add("fade-out");

                    hideTimeout = setTimeout(() => {
                        tooltipEl.classList.remove("show");
                    }, 180);
                }
            });
        });

    // Ocultar tooltip al hacer scroll
    const sidebarContent = document.querySelector(".sidebar-contenido");
    if (sidebarContent) {
        sidebarContent.addEventListener("scroll", () => {
            if (tooltipEl) tooltipEl.classList.remove("show");
        });
    }
});
