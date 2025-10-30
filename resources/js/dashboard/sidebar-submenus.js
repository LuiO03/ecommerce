

const submenuButtons = document.querySelectorAll(".submenu-btn");
submenuButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
        const submenu = btn.nextElementSibling;
        const arrow = btn.querySelector(".submenu-arrow");

        // Cierra los demás submenús
        document.querySelectorAll(".sidebar-submenu").forEach((otherMenu) => {
            if (otherMenu !== submenu && otherMenu.classList.contains("open")) {
                otherMenu.classList.remove("open");
                const otherArrow =
                    otherMenu.previousElementSibling.querySelector(
                        ".submenu-arrow"
                    );
                otherArrow.classList.remove("rotate-180");

                // Quitar border-radius al botón cerrado
                otherArrow
                    .closest(".submenu-btn")
                    .classList.remove("rounded-top");
            }
        });

        // Alternar estado actual
        submenu.classList.toggle("open");
        arrow.classList.toggle("rotate-180");

        // Añadir o quitar clase de border-radius al botón
        btn.classList.toggle("rounded-top");
    });
});
