const toggle = document.getElementById("theme-toggle");
const switchHandle = document.getElementById("switch-handle");
const darkIcon = document.getElementById("theme-toggle-dark-icon");
const lightIcon = document.getElementById("theme-toggle-light-icon");

// Verificar si estamos en una página con el toggle (admin dashboard)
if (!toggle || !switchHandle || !darkIcon || !lightIcon) {
    console.log('Theme toggle elements not found - skipping initialization');
} else {
    function setTheme(isDark) {
        if (isDark) {
            document.documentElement.classList.add("dark");
            localStorage.setItem("color-theme", "dark");
            lightIcon.classList.remove("hidden");
            darkIcon.classList.add("hidden");
            switchHandle.style.transform = "translateX(1.25rem)";
        } else {
            document.documentElement.classList.remove("dark");
            localStorage.setItem("color-theme", "light");
            darkIcon.classList.remove("hidden");
            lightIcon.classList.add("hidden");
            switchHandle.style.transform = "translateX(0)";
        }
    }

    // Inicializar estado
    const isDark =
        localStorage.getItem("color-theme") === "dark" ||
        (!("color-theme" in localStorage) &&
            window.matchMedia("(prefers-color-scheme: dark)").matches);
    setTheme(isDark);

    // Alternar al hacer clic
    toggle.addEventListener("click", () => {
        const currentTheme = localStorage.getItem("color-theme");
        setTheme(currentTheme !== "dark");
    });
}
