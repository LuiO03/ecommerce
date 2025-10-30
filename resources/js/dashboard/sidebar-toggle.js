document.addEventListener("DOMContentLoaded", () => {
    const sidebar = document.getElementById("logo-sidebar");
    const toggleBtn = document.getElementById("toggleSidebarWidth");
    const icon = toggleBtn.querySelector("i");

    if (!sidebar || !toggleBtn || !icon) return;

    // Función para actualizar el icono con animación
    const updateIcon = (collapsed) => {
        icon.style.transition = "transform 0.3s ease";
        icon.style.transform = "rotate(180deg)"; // animación de giro

        setTimeout(() => {
            icon.className = collapsed
                ? "ri-arrow-right-double-fill" // cuando sidebar está colapsado
                : "ri-arrow-left-double-fill"; // cuando sidebar está expandido
            icon.style.transform = "rotate(0deg)";
        }, 150);
    };

    // Estado inicial desde localStorage
    const isCollapsed = localStorage.getItem("sidebarCollapsed") === "true";
    if (isCollapsed) {
        sidebar.classList.add("collapsed");
        document.body.classList.add("sidebar-collapsed");
        updateIcon(true);
    } else {
        updateIcon(false);
    }

    // Evento click del toggle
    toggleBtn.addEventListener("click", () => {
        const collapsed = !sidebar.classList.contains("collapsed");

        sidebar.classList.toggle("collapsed");
        document.body.classList.toggle("sidebar-collapsed");
        localStorage.setItem("sidebarCollapsed", collapsed);

        updateIcon(collapsed);
    });
});
