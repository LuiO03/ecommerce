// ========================================
// 🗑️ ELIMINACIÓN INDIVIDUAL (Existente)
// ========================================
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
                message: `¿Estás seguro de que deseas eliminar este registro? <br> Esta acción no se puede deshacer.`,
                type: 'danger',
                confirmText: 'Sí, eliminar',
                cancelText: 'No, cancelar',
                onConfirm: () => this.submit()
            });
        });
    });
});

// ========================================
// 🗑️ ELIMINACIÓN MÚLTIPLE (Global)
// ========================================

/**
 * Función global para manejar eliminación múltiple de registros
 * @param {Object} options - Configuración de la eliminación múltiple
 * @param {Set|Array} options.selectedIds - IDs de los elementos seleccionados
 * @param {Function} options.getNameCallback - Función para obtener el nombre de un elemento por ID
 * @param {string} options.entityName - Nombre de la entidad (ej: 'familia', 'usuario')
 * @param {string} options.deleteRoute - URL de la ruta de eliminación múltiple
 * @param {string} options.csrfToken - Token CSRF
 * @param {string} [options.buttonSelector] - Selector del botón para deshabilitar durante la eliminación
 */
window.handleMultipleDelete = function(options) {
    const {
        selectedIds,
        getNameCallback,
        entityName = 'registro',
        deleteRoute,
        csrfToken,
        buttonSelector = null
    } = options;

    // Convertir Set a Array si es necesario
    const idsArray = Array.isArray(selectedIds) ? selectedIds : Array.from(selectedIds);
    const selectedCount = idsArray.length;
    
    if (selectedCount === 0) {
        showInfoModal({
            type: 'warning',
            header: 'Sin selección',
            title: 'No hay elementos seleccionados',
            message: `Por favor selecciona al menos un ${entityName} para eliminar.`,
        });
        return;
    }

    // Obtener nombres de los elementos seleccionados
    const selectedNames = [];
    idsArray.forEach(id => {
        const name = getNameCallback(id);
        if (name && name.trim()) {
            selectedNames.push(name.trim());
        } else {
            selectedNames.push(`ID: ${id}`); // Fallback si no se puede obtener el nombre
        }
    });

    let message;
    const entityPlural = getPlural(entityName);
    
    if (selectedCount === 1) {
        message = `¿Estás seguro de que deseas eliminar ${getGenderArticle(entityName)} ${entityName} <strong>"${selectedNames[0]}"</strong>?<br><span>Esta acción no se puede deshacer.</span>`;
    } else {
        const nameList = selectedNames.map(name => `<li><strong>${name}</strong></li>`).join('');
        message = `¿Estás seguro de que deseas eliminar los <strong>${selectedCount} registros</strong> seleccionad${getGenderEnding(entityPlural)}?<br>
        <strong>${capitalizeFirst(entityPlural)} a eliminar:</strong>
        <ul>${nameList}</ul>
        <span>Esta acción no se puede deshacer.</span>`;
    }

    showConfirm({
        type: 'danger',
        header: 'Confirmar eliminación',
        title: selectedCount === 1 ? `¿Eliminar ${entityName}?` : `¿Eliminar ${entityPlural}?`,
        message: message,
        confirmText: 'Sí, eliminar',
        cancelText: 'No, cancelar',
        onConfirm: function() {
            performMultipleDelete(idsArray, deleteRoute, csrfToken, buttonSelector);
        }
    });
};

/**
 * Ejecuta la eliminación múltiple enviando el formulario
 * @private
 */
function performMultipleDelete(selectedIds, deleteRoute, csrfToken, buttonSelector = null) {
    // Crear un formulario dinámicamente
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = deleteRoute;
    
    // Token CSRF
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);
    
    // Método DELETE
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';
    form.appendChild(methodInput);
    
    // Agregar los IDs seleccionados
    selectedIds.forEach(function(id) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]'; // Nombre genérico para cualquier entidad
        input.value = id;
        form.appendChild(input);
    });
    
    // Deshabilitar botón mientras se procesa (si se proporciona)
    if (buttonSelector) {
        const deleteBtn = document.querySelector(buttonSelector);
        if (deleteBtn) {
            deleteBtn.disabled = true;
            const textElement = deleteBtn.querySelector('.boton-text');
            if (textElement) {
                textElement.textContent = 'Eliminando...';
            }
        }
    }
    
    // Agregar el formulario al DOM y enviarlo
    document.body.appendChild(form);
    form.submit();
}

// ========================================
// 🔤 UTILIDADES DE TEXTO
// ========================================

/**
 * Obtiene el plural de una palabra (simple)
 * @private
 */
function getPlural(word) {
    const plurals = {
        'familia': 'familias',
        'usuario': 'usuarios', 
        'producto': 'productos',
        'categoría': 'categorías',
        'categoria': 'categorias',
        'subcategoría': 'subcategorías',
        'subcategoria': 'subcategorias',
        'característica': 'características',
        'caracteristica': 'caracteristicas',
        'opción': 'opciones',
        'opcion': 'opciones',
        'variante': 'variantes',
        'imagen': 'imágenes',
        'registro': 'registros'
    };
    
    return plurals[word.toLowerCase()] || word + 's';
}

/**
 * Obtiene el artículo con género correcto
 * @private
 */
function getGenderArticle(word, isPlural = false) {
    const feminineWords = ['familia', 'categoría', 'categoria', 'subcategoría', 'subcategoria', 
                          'característica', 'caracteristica', 'opción', 'opcion', 'imagen'];
    
    const isFeminine = feminineWords.some(fem => word.toLowerCase().includes(fem));
    
    if (isPlural) {
        return isFeminine ? 'las' : 'los';
    }
    return isFeminine ? 'la' : 'el';
}

/**
 * Obtiene la terminación de género correcta para "seleccionad_"
 * @private
 */
function getGenderEnding(word) {
    const feminineWords = ['familia', 'categoría', 'categoria', 'subcategoría', 'subcategoria', 
                          'característica', 'caracteristica', 'opción', 'opcion', 'imagen'];
    
    const isFeminine = feminineWords.some(fem => word.toLowerCase().includes(fem));
    return isFeminine ? 'as' : 'os';
}

/**
 * Capitaliza la primera letra de una palabra
 * @private
 */
function capitalizeFirst(word) {
    return word.charAt(0).toUpperCase() + word.slice(1);
}

