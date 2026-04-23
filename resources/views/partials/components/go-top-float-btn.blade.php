<button type="button" class="go-top-float-btn" id="goTopFloatBtn" title="Ir arriba" aria-label="Ir arriba">
    <i class="ri-arrow-up-s-line"></i>
    <span>ir arriba</span>
</button>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('goTopFloatBtn');
        let lastScroll = 0;
        let ticking = false;
        function onScroll() {
            const y = window.scrollY || window.pageYOffset;
            // Mostrar si bajó más de 400px y no está cerca del top
            if (y > 400) {
                btn.classList.add('visible');
            } else {
                btn.classList.remove('visible');
            }
            lastScroll = y;
        }
        window.addEventListener('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(function() {
                    onScroll();
                    ticking = false;
                });
                ticking = true;
            }
        });
        btn.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        // Estado inicial
        onScroll();
    });
</script>
