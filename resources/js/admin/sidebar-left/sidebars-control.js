document.addEventListener("DOMContentLoaded", () => {
    const overlay = document.getElementById("overlay");
    const leftSidebar = document.getElementById("logo-sidebar");
    const userSidebar = document.getElementById("userSidebar");
    const hamburgerBtn = document.getElementById("userSidebarToggle");
    const notificationBtn = document.getElementById("notificationSidebarToggle");
    const openLeftSidebarBtn = document.getElementById("openLeftSidebarBtn");

    // Funciones overlay y scroll
    function showOverlay(zIndex = 50) {
        overlay.style.zIndex = String(zIndex);
        overlay.classList.remove("hidden");
        requestAnimationFrame(() => {
            overlay.classList.remove("opacity-0");
            overlay.classList.add("opacity-100");
        });
        document.body.style.overflow = "hidden"; // bloquea scroll
    }

    function hideOverlay() {
        overlay.classList.remove("opacity-100");
        overlay.classList.add("opacity-0");
        setTimeout(() => {
            overlay.classList.add("hidden");
            overlay.style.zIndex = "";
            document.body.style.overflow = ""; // desbloquea scroll
            hamburgerBtn.style.zIndex = "";
        }, 300); // igual al duration-300
    }

    // Abrir/Cerrar Left Sidebar
    function openLeftSidebar() {
        leftSidebar.classList.remove("-translate-x-full");
        // Cierra el sidebar derecho si está abierto
        if (!userSidebar.classList.contains("translate-x-full"))
            closeUserSidebar();
        // Bajar z-index del botón derecho para que overlay lo cubra
        hamburgerBtn.style.zIndex = "10";
        showOverlay(20);
    }

    function closeLeftSidebar() {
        leftSidebar.classList.add("-translate-x-full");
        hideOverlay();
    }

    // Tabs del sidebar derecho
    const sidebarTabs = userSidebar?.querySelectorAll("[data-sidebar-tab]");
    const sidebarSections = userSidebar?.querySelectorAll("[data-sidebar-section]");

    function activateSidebarTab(target = "profile") {
        if (!sidebarTabs || !sidebarSections) return;

        sidebarTabs.forEach((tab) => {
            const tabTarget = tab.getAttribute("data-sidebar-tab");
            tab.classList.toggle("active", tabTarget === target);
        });

        sidebarSections.forEach((section) => {
            const sectionTarget = section.getAttribute("data-sidebar-section");
            section.classList.toggle("active", sectionTarget === target);
        });
    }

    activateSidebarTab("profile");

    // Abrir/Cerrar User Sidebar (right)
    function openUserSidebar() {
        userSidebar.classList.remove("translate-x-full");
        hamburgerBtn?.classList.add("active");
        // Cierra el sidebar izquierdo si está abierto
        if (!leftSidebar.classList.contains("-translate-x-full"))
            closeLeftSidebar();
        showOverlay(60);
    }

    function closeUserSidebar() {
        userSidebar.classList.add("translate-x-full");
        hamburgerBtn?.classList.remove("active");
        hideOverlay();
    }

    // Botones
    hamburgerBtn?.addEventListener("click", () => {
        const isOpen = !userSidebar.classList.contains("translate-x-full");
        if (isOpen) closeUserSidebar();
        else {
            activateSidebarTab("profile");
            openUserSidebar();
        }
    });

    notificationBtn?.addEventListener("click", () => {
        const isOpen = !userSidebar.classList.contains("translate-x-full");
        activateSidebarTab("notifications");
        if (!isOpen) openUserSidebar();
    });

    openLeftSidebarBtn?.addEventListener("click", () => {
        const isOpen = !leftSidebar.classList.contains("-translate-x-full");
        if (isOpen) closeLeftSidebar();
        else openLeftSidebar();
    });

    // Cierra ambos sidebars al click en overlay
    overlay?.addEventListener("click", () => {
        closeUserSidebar();
        closeLeftSidebar();
    });
});
