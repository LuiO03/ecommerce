<div id="fabContainer" class="fab-container hidden">
    <!-- Botones secundarios -->
    <div class="fab-buttons">
        <button class="fab-btn" title="Nuevo">
            <i class="ri-add-line"></i>
        </button>
        <button class="fab-btn" title="Editar">
            <i class="ri-edit-line"></i>
        </button>
        <button class="fab-btn" title="Eliminar">
            <i class="ri-delete-bin-line"></i>
        </button>
    </div>

    <!-- Botón principal -->
    <button id="fabToggle" class="fab-main-btn">
        <i class="ri-add-large-fill"></i>
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const fabToggle = document.getElementById('fabToggle');
    const fabContainer = document.getElementById('fabContainer');
    const fabButtons = document.querySelector('.fab-buttons');

    // 🔹 Función para abrir/cerrar
    const toggleFab = (forceClose = false) => {
        const expanding = !forceClose && !fabContainer.classList.contains('expand');

        fabToggle.classList.toggle('active', expanding);
        fabButtons.classList.toggle('show', expanding);
        fabContainer.classList.toggle('expand', expanding);

        if (expanding) {
            // Calcular altura dinámica
            const buttonHeight = 48;
            const gap = 10;
            const numButtons = fabButtons.children.length;
            const totalHeight = (numButtons * buttonHeight) + ((numButtons - 1) * gap) + 58 + 20;
            fabContainer.style.maxHeight = `${totalHeight}px`;
        } else {
            fabContainer.style.maxHeight = '70px';
        }
    };

    // 🔹 Evento click del botón principal
    fabToggle.addEventListener('click', (e) => {
        e.stopPropagation(); // evita que el clic se propague al documento
        toggleFab();
    });

    // 🔹 Cerrar al hacer clic fuera del contenedor
    document.addEventListener('click', (e) => {
        if (fabContainer.classList.contains('expand') && !fabContainer.contains(e.target)) {
            toggleFab(true); // fuerza el cierre
        }
    });
});
</script>
