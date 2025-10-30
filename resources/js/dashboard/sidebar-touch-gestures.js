        document.addEventListener("DOMContentLoaded", () => {
            const leftSidebar = document.getElementById("logo-sidebar");
            const userSidebar = document.getElementById("userSidebar");
            const overlay = document.getElementById("overlay");
            const hamburgerBtn = document.getElementById("userSidebarToggle");

            let startX = 0;
            let startY = 0;
            let currentX = 0;
            let touchStarted = false;
            let activeSidebar = null;

            document.addEventListener("touchstart", (e) => {
                if (e.touches.length !== 1) return;
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
                currentX = startX;
                touchStarted = true;

                // Detectar desde qué borde empieza el gesto
                if (startX < 40 && leftSidebar.classList.contains("-translate-x-full")) {
                    activeSidebar = "left"; // posible apertura izquierda
                } else if (startX > window.innerWidth - 40 && userSidebar.classList.contains(
                        "translate-x-full")) {
                    activeSidebar = "right"; // posible apertura derecha
                } else if (!leftSidebar.classList.contains("-translate-x-full")) {
                    activeSidebar = "left"; // posible cierre izquierda
                } else if (!userSidebar.classList.contains("translate-x-full")) {
                    activeSidebar = "right"; // posible cierre derecha
                } else {
                    activeSidebar = null;
                }

                // Quitar transición durante arrastre
                if (activeSidebar === "left") leftSidebar.style.transition = "none";
                if (activeSidebar === "right") userSidebar.style.transition = "none";
            });

            document.addEventListener("touchmove", (e) => {
                if (!touchStarted || !activeSidebar) return;

                const touch = e.touches[0];
                currentX = touch.clientX;
                const deltaX = currentX - startX;

                // Movimiento horizontal más fuerte
                if (Math.abs(deltaX) < 10) return;

                // Sidebar izquierdo
                if (activeSidebar === "left") {
                    if (startX < 40 && deltaX > 0) {
                        const translate = Math.min(0, -leftSidebar.offsetWidth + deltaX);
                        leftSidebar.style.transform = `translateX(${translate}px)`;
                        showOverlay(20, Math.min(deltaX / leftSidebar.offsetWidth, 0.5));
                    } else if (deltaX < 0 && !leftSidebar.classList.contains("-translate-x-full")) {
                        const translate = Math.max(-leftSidebar.offsetWidth, deltaX);
                        leftSidebar.style.transform = `translateX(${translate}px)`;
                    }
                }

                // Sidebar derecho
                if (activeSidebar === "right") {
                    if (startX > window.innerWidth - 40 && deltaX < 0) {
                        const translate = Math.max(0, userSidebar.offsetWidth + deltaX);
                        userSidebar.style.transform = `translateX(${translate}px)`;
                        showOverlay(60, Math.min(Math.abs(deltaX) / userSidebar.offsetWidth, 0.5));
                    } else if (deltaX > 0 && !userSidebar.classList.contains("translate-x-full")) {
                        const translate = Math.min(userSidebar.offsetWidth, deltaX);
                        userSidebar.style.transform = `translateX(${translate}px)`;
                    }
                }
            });

            document.addEventListener("touchend", () => {
                if (!touchStarted || !activeSidebar) return;

                const deltaX = currentX - startX;

                // Restaurar transición
                leftSidebar.style.transition = "";
                userSidebar.style.transition = "";

                // Evaluar apertura/cierre
                if (activeSidebar === "left") {
                    if (deltaX > 80) openLeftSidebar();
                    else if (deltaX < -80) closeLeftSidebar();
                    else restoreSidebar(leftSidebar, leftSidebar.classList.contains("-translate-x-full"));
                }

                if (activeSidebar === "right") {
                    if (deltaX < -80) openUserSidebar();
                    else if (deltaX > 80) closeUserSidebar();
                    else restoreSidebar(userSidebar, userSidebar.classList.contains("translate-x-full"));
                }

                touchStarted = false;
                activeSidebar = null;
            });

            // ==== FUNCIONES ====

            function openLeftSidebar() {
                leftSidebar.classList.remove("-translate-x-full");
                leftSidebar.style.transform = "";
                if (!userSidebar.classList.contains("translate-x-full")) closeUserSidebar();
                showOverlay(20);
            }

            function closeLeftSidebar() {
                leftSidebar.classList.add("-translate-x-full");
                leftSidebar.style.transform = "";
                hideOverlay();
            }

            function openUserSidebar() {
                userSidebar.classList.remove("translate-x-full");
                userSidebar.style.transform = "";
                hamburgerBtn?.classList.add("active");
                if (!leftSidebar.classList.contains("-translate-x-full")) closeLeftSidebar();
                showOverlay(60);
            }

            function closeUserSidebar() {
                userSidebar.classList.add("translate-x-full");
                userSidebar.style.transform = "";
                hamburgerBtn?.classList.remove("active");
                hideOverlay();
            }

            function restoreSidebar(sidebar, isClosed) {
                sidebar.style.transform = "";
                if (isClosed) sidebar.classList.add(sidebar === userSidebar ? "translate-x-full" :
                    "-translate-x-full");
            }

            function showOverlay(zIndex = 50, opacityFactor = 0.5) {
                overlay.classList.remove("hidden", "opacity-0");
                overlay.style.zIndex = String(zIndex);
                overlay.style.opacity = opacityFactor;
                document.body.style.overflow = "hidden";
            }

            function hideOverlay() {
                overlay.classList.remove("opacity-100");
                setTimeout(() => {
                    overlay.classList.add("hidden");
                    overlay.style.opacity = "";
                    overlay.style.zIndex = "";
                    document.body.style.overflow = "";
                }, 300);
            }

            function showOverlay(zIndex = 50, opacityFactor = 0.5) {
                overlay.classList.remove("hidden", "opacity-0");
                overlay.style.zIndex = String(zIndex);
                overlay.style.opacity = opacityFactor;
                document.body.style.overflow = "hidden";
            }

            // ✅ NUEVA VERSIÓN de hideOverlay
            function hideOverlay() {
                // Solo ocultar si ambos sidebars están cerrados
                const leftClosed = leftSidebar.classList.contains("-translate-x-full");
                const rightClosed = userSidebar.classList.contains("translate-x-full");

                if (leftClosed && rightClosed) {
                    overlay.classList.remove("opacity-100");
                    setTimeout(() => {
                        overlay.classList.add("hidden");
                        overlay.style.opacity = "";
                        overlay.style.zIndex = "";
                        document.body.style.overflow = "";
                    }, 300);
                }
            }

        });
    