document.addEventListener('DOMContentLoaded', () => {
    // Selecciona todos los formularios de eliminación del sistema
    document.querySelectorAll('form.delete-form').forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault(); // evita el envío inmediato

            // Obtiene el nombre del módulo o entidad si existe
            const entityName = this.dataset.entity || 'registro';

            // Llama al modal global
            showConfirm({
                title: `¿Eliminar ${entityName}?`,
                message: `¿Estás seguro de que deseas eliminar este ${entityName}? Esta acción no se puede deshacer.`,
                type: 'danger',
                confirmText: 'Sí, eliminar',
                cancelText: 'No, cancelar',
                onConfirm: () => this.submit()
            });
        });
    });
});

