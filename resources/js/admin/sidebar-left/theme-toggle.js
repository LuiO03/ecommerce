// Función para aplicar el tema (siempre se ejecuta)
function applyTheme(isDark) {
    if (isDark) {
        document.documentElement.classList.add("dark");
        localStorage.setItem("color-theme", "dark");
    } else {
        document.documentElement.classList.remove("dark");
        localStorage.setItem("color-theme", "light");
    }
}

// Determinar y aplicar el tema guardado o preferencia del sistema
const isDark =
    localStorage.getItem("color-theme") === "dark" ||
    (!("color-theme" in localStorage) &&
        window.matchMedia("(prefers-color-scheme: dark)").matches);
applyTheme(isDark);

// Lógica del toggle (solo si existe en el dashboard)
const toggle = document.getElementById("theme-toggle");
const switchHandle = document.getElementById("switch-handle");
const darkIcon = document.getElementById("theme-toggle-dark-icon");
const lightIcon = document.getElementById("theme-toggle-light-icon");

if (toggle && switchHandle && darkIcon && lightIcon) {
    function setThemeUI(isDark) {
        if (isDark) {
            lightIcon.classList.remove("hidden");
            darkIcon.classList.add("hidden");
            switchHandle.style.transform = "translateX(1.25rem)";
        } else {
            darkIcon.classList.remove("hidden");
            lightIcon.classList.add("hidden");
            switchHandle.style.transform = "translateX(0)";
        }
    }

    // Aplicar UI del estado inicial
    setThemeUI(isDark);

    // Alternar al hacer clic
    toggle.addEventListener("click", () => {
        const currentTheme = localStorage.getItem("color-theme");
        const newIsDark = currentTheme !== "dark";
        applyTheme(newIsDark);
        setThemeUI(newIsDark);
    });
}
