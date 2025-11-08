// JS: agrega el efecto en cada click
document.querySelectorAll(".ripple-btn").forEach((button) => {
    let holdRipple = null;
    let holdTimer = null;

    // Cuando se presiona
    const onPointerDown = (e) => {
        const rect = button.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height) * 2;
        const x = (e.clientX || e.touches?.[0].clientX) - rect.left - size / 2;
        const y = (e.clientY || e.touches?.[0].clientY) - rect.top - size / 2;

        const ripple = document.createElement("span");
        ripple.classList.add("ripple");
        ripple.style.width = ripple.style.height = `${size}px`;
        ripple.style.left = `${x}px`;
        ripple.style.top = `${y}px`;
        button.appendChild(ripple);

        // expansión inicial
        requestAnimationFrame(() => {
            ripple.style.transform = "scale(1.5)";
        });

        // Guardamos referencia por si es un “hold”
        holdRipple = ripple;

        // Si se mantiene presionado, se expande lentamente
        holdTimer = setInterval(() => {
            const currentScale = parseFloat(
                ripple.style.transform.replace(/[^0-9.]/g, "") || 1.5
            );
            if (currentScale < 3) {
                ripple.style.transform = `scale(${currentScale + 0.03})`;
            }
        }, 30);

        // El ripple normal desaparece después de cierto tiempo (clic rápido)
        setTimeout(() => {
            if (ripple && ripple !== holdRipple) fadeRipple(ripple);
        }, 600);
    };

    // Cuando se suelta o sale del botón
    const onPointerUp = () => {
        clearInterval(holdTimer);
        if (holdRipple) {
            fadeRipple(holdRipple);
            holdRipple = null;
        }
    };

    // Animación de desvanecimiento suave
    const fadeRipple = (ripple) => {
        ripple.style.opacity = "0";
        setTimeout(() => ripple.remove(), 500);
    };

    // Eventos universales
    button.addEventListener("pointerdown", onPointerDown);
    button.addEventListener("pointerup", onPointerUp);
    button.addEventListener("pointerleave", onPointerUp);
    button.addEventListener("touchend", onPointerUp);
    button.addEventListener("touchcancel", onPointerUp);
});
document.querySelectorAll(".ripple-card").forEach((button) => {
    let holdRipple = null;
    let holdTimer = null;

    // Cuando se presiona
    const onPointerDown = (e) => {
        const rect = button.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height) * 2;
        const x = (e.clientX || e.touches?.[0].clientX) - rect.left - size / 2;
        const y = (e.clientY || e.touches?.[0].clientY) - rect.top - size / 2;

        const ripple = document.createElement("span");
        ripple.classList.add("ripple");
        ripple.style.width = ripple.style.height = `${size}px`;
        ripple.style.left = `${x}px`;
        ripple.style.top = `${y}px`;
        button.appendChild(ripple);

        // expansión inicial
        requestAnimationFrame(() => {
            ripple.style.transform = "scale(1.5)";
        });

        // Guardamos referencia por si es un “hold”
        holdRipple = ripple;

        // Si se mantiene presionado, se expande lentamente
        holdTimer = setInterval(() => {
            const currentScale = parseFloat(
                ripple.style.transform.replace(/[^0-9.]/g, "") || 1.5
            );
            if (currentScale < 3) {
                ripple.style.transform = `scale(${currentScale + 0.03})`;
            }
        }, 30);

        // El ripple normal desaparece después de cierto tiempo (clic rápido)
        setTimeout(() => {
            if (ripple && ripple !== holdRipple) fadeRipple(ripple);
        }, 600);
    };

    // Cuando se suelta o sale del botón
    const onPointerUp = () => {
        clearInterval(holdTimer);
        if (holdRipple) {
            fadeRipple(holdRipple);
            holdRipple = null;
        }
    };

    // Animación de desvanecimiento suave
    const fadeRipple = (ripple) => {
        ripple.style.opacity = "0";
        setTimeout(() => ripple.remove(), 500);
    };

    // Eventos universales
    button.addEventListener("pointerdown", onPointerDown);
    button.addEventListener("pointerup", onPointerUp);
    button.addEventListener("pointerleave", onPointerUp);
    button.addEventListener("touchend", onPointerUp);
    button.addEventListener("touchcancel", onPointerUp);
});
