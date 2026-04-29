document.addEventListener("DOMContentLoaded", () => {
    const leftSidebar = document.getElementById("logo-sidebar");
    const rightSidebar = document.getElementById("userSidebar");
    const overlay = document.getElementById("overlay");
    const hamburgerBtn = document.getElementById("userSidebarToggle");

    if (!leftSidebar || !rightSidebar || !overlay) return;

    const EDGE_SIZE = 32;        // zona sensible del borde
    const TRIGGER = 72;         // distancia mínima para abrir/cerrar
    const VERTICAL_RATIO = 1.15; // prioridad al scroll vertical

    let startX = 0;
    let startY = 0;
    let currentX = 0;

    let touching = false;
    let active = null; // left | right | null
    let dragging = false;

    /* =============================
       HELPERS
    ============================== */

    const isLeftClosed = () =>
        leftSidebar.classList.contains("-translate-x-full");

    const isRightClosed = () =>
        rightSidebar.classList.contains("translate-x-full");

    const widthOf = (el) => el.getBoundingClientRect().width;

    function disableTransitions() {
        leftSidebar.style.transition = "none";
        rightSidebar.style.transition = "none";
    }

    function enableTransitions() {
        leftSidebar.style.transition = "";
        rightSidebar.style.transition = "";
    }

    function showOverlay(opacity = 0.45, z = 50) {
        overlay.classList.remove("hidden");
        overlay.style.opacity = opacity;
        overlay.style.zIndex = String(z);
        document.body.style.overflow = "hidden";
    }

    function hideOverlay() {
        if (!isLeftClosed() || !isRightClosed()) return;

        overlay.style.opacity = "0";

        setTimeout(() => {
            overlay.classList.add("hidden");
            overlay.style.opacity = "";
            overlay.style.zIndex = "";
            document.body.style.overflow = "";
        }, 250);
    }

    function closeLeft() {
        leftSidebar.classList.add("-translate-x-full");
        leftSidebar.style.transform = "";
        hideOverlay();
    }

    function openLeft() {
        closeRight();
        leftSidebar.classList.remove("-translate-x-full");
        leftSidebar.style.transform = "";
        showOverlay(0.45, 20);
    }

    function closeRight() {
        rightSidebar.classList.add("translate-x-full");
        rightSidebar.style.transform = "";
        hamburgerBtn?.classList.remove("active");
        hideOverlay();
    }

    function openRight() {
        closeLeft();
        rightSidebar.classList.remove("translate-x-full");
        rightSidebar.style.transform = "";
        hamburgerBtn?.classList.add("active");
        showOverlay(0.45, 60);
    }

    function resetDrag() {
        touching = false;
        dragging = false;
        active = null;
        enableTransitions();
    }

    /* =============================
       TOUCH START
    ============================== */

    document.addEventListener("touchstart", (e) => {
        if (e.touches.length !== 1) return;

        const touch = e.touches[0];

        startX = touch.clientX;
        startY = touch.clientY;
        currentX = startX;

        touching = true;
        dragging = false;
        active = null;

        const screenW = window.innerWidth;

        // abrir desde bordes
        if (startX <= EDGE_SIZE && isLeftClosed()) {
            active = "left";
        } else if (startX >= screenW - EDGE_SIZE && isRightClosed()) {
            active = "right";
        }
        // cerrar sidebars abiertos
        else if (!isLeftClosed()) {
            active = "left";
        } else if (!isRightClosed()) {
            active = "right";
        }

        if (active) disableTransitions();

    }, { passive: true });

    /* =============================
       TOUCH MOVE
    ============================== */

    document.addEventListener("touchmove", (e) => {
        if (!touching || !active) return;

        const touch = e.touches[0];

        currentX = touch.clientX;

        const dx = currentX - startX;
        const dy = touch.clientY - startY;

        // si es gesto vertical => dejar scroll natural
        if (!dragging) {
            if (Math.abs(dy) > Math.abs(dx) * VERTICAL_RATIO) {
                resetDrag();
                return;
            }

            if (Math.abs(dx) < 8) return;

            dragging = true;
        }

        if (active === "left") {
            const w = widthOf(leftSidebar);

            // abrir
            if (isLeftClosed()) {
                const x = Math.min(0, -w + Math.max(0, dx));
                leftSidebar.style.transform = `translateX(${x}px)`;
                showOverlay(Math.min(dx / w, 0.45), 20);
            }
            // cerrar
            else {
                const x = Math.max(-w, Math.min(0, dx));
                leftSidebar.style.transform = `translateX(${x}px)`;
            }
        }

        if (active === "right") {
            const w = widthOf(rightSidebar);

            // abrir
            if (isRightClosed()) {
                const x = Math.max(0, w + Math.min(0, dx));
                rightSidebar.style.transform = `translateX(${x}px)`;
                showOverlay(Math.min(Math.abs(dx) / w, 0.45), 60);
            }
            // cerrar
            else {
                const x = Math.min(w, Math.max(0, dx));
                rightSidebar.style.transform = `translateX(${x}px)`;
            }
        }

    }, { passive: true });

    /* =============================
       TOUCH END
    ============================== */

    document.addEventListener("touchend", () => {
        if (!touching || !active) return;

        enableTransitions();

        const dx = currentX - startX;

        if (active === "left") {
            leftSidebar.style.transform = "";

            if (isLeftClosed()) {
                dx > TRIGGER ? openLeft() : closeLeft();
            } else {
                dx < -TRIGGER ? closeLeft() : openLeft();
            }
        }

        if (active === "right") {
            rightSidebar.style.transform = "";

            if (isRightClosed()) {
                dx < -TRIGGER ? openRight() : closeRight();
            } else {
                dx > TRIGGER ? closeRight() : openRight();
            }
        }

        resetDrag();

    }, { passive: true });

    /* =============================
       CLICK OVERLAY
    ============================== */

    overlay.addEventListener("click", () => {
        closeLeft();
        closeRight();
    });
});
