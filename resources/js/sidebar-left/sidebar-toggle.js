document.addEventListener("DOMContentLoaded", () => {
    const sidebar = document.getElementById("logo-sidebar");
    const toggleBtn = document.getElementById("toggleSidebarWidth");
    const icon = toggleBtn ? toggleBtn.querySelector("i") : null;

    if (!sidebar || !toggleBtn || !icon) return;

    const setIcon = (collapsed) => {
        icon.className = collapsed
            ? "ri-arrow-right-double-fill" // cuando sidebar está colapsado
            : "ri-arrow-left-double-fill"; // cuando sidebar está expandido
    };

    // Función para actualizar el icono con o sin animación
    const updateIcon = (collapsed, animate = true) => {
        if (!animate) {
            icon.style.transition = "none";
            icon.style.transform = "";
            setIcon(collapsed);
            return;
        }

        icon.style.transition = "transform 0.3s ease";
        icon.style.transform = "rotate(180deg)"; // animación de giro

        setTimeout(() => {
            setIcon(collapsed);
            icon.style.transform = "rotate(0deg)";
        }, 150);
    };

    // Estado inicial desde localStorage (sin animaciones)
    const isCollapsed = localStorage.getItem("sidebarCollapsed") === "true";
    if (isCollapsed) {
        // Ya hay un estilo inicial aplicado vía .sidebar-start-collapsed en <html>
        // Solo sincronizamos clases internas sin animaciones
        sidebar.classList.add("no-transition");
        sidebar.classList.add("collapsed");
        document.body.classList.add("sidebar-collapsed");
        updateIcon(true, false);

        requestAnimationFrame(() => {
            sidebar.classList.remove("no-transition");
            document.documentElement.classList.remove("sidebar-start-collapsed");
        });
    } else {
        document.documentElement.classList.remove("sidebar-start-collapsed");
        updateIcon(false, false);
    }

    // Evento click del toggle
    toggleBtn.addEventListener("click", () => {
        const collapsed = !sidebar.classList.contains("collapsed");

        sidebar.classList.toggle("collapsed");
        document.body.classList.toggle("sidebar-collapsed");
        localStorage.setItem("sidebarCollapsed", collapsed);

        // Aquí sí queremos animación al colapsar/expandir manualmente
        updateIcon(collapsed, true);
    });
});
