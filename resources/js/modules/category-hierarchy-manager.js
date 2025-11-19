/**
 * ============================================================================
 * CATEGORY HIERARCHY MANAGER - Módulo Global
 * ============================================================================
 * Sistema de selección jerárquica progresiva (cascading selects) para categorías
 * Permite seleccionar la ubicación de una categoría en la jerarquía de forma visual
 * 
 * @author GECKОМERCE
 * @version 1.0.0
 */

export class CategoryHierarchyManager {
    /**
     * @param {Object} config - Configuración del manager
     * @param {Array} config.categoriesData - Array de categorías con estructura jerárquica
     * @param {number} config.currentCategoryId - ID de la categoría actual (solo para modo edit)
     * @param {number} config.initialFamilyId - ID de familia preseleccionada
     * @param {number} config.initialParentId - ID del padre preseleccionado
     */
    constructor(config = {}) {
        // Validar parámetros requeridos
        if (!config.categoriesData) {
            console.error('CategoryHierarchyManager: categoriesData es requerido');
            return;
        }

        // Configuración
        this.config = {
            familySelectId: 'family_select',
            categoryContainerId: 'categoryHierarchySelects',
            noFamilyMessageId: 'noFamilyMessage',
            parentInputId: 'parent_id',
            breadcrumbId: 'hierarchyBreadcrumb',
            breadcrumbPathId: 'breadcrumbPath',
            currentCategoryId: null, // Para modo edit
            initialFamilyId: null,
            initialParentId: null,
            debug: false, // Activar logs de debug
            ...config
        };

        // Datos
        this.categoriesData = config.categoriesData;
        this.selectedPath = [];
        this.currentFamilyId = null;

        // Debug info
        if (this.config.debug) {
            console.log('[CategoryHierarchy] Inicializado con:', {
                totalCategorias: this.categoriesData.length,
                esJerarquico: this.categoriesData.some(cat => cat.children !== undefined),
                currentCategoryId: this.config.currentCategoryId,
                initialFamilyId: this.config.initialFamilyId,
                initialParentId: this.config.initialParentId
            });
        }

        // Elementos del DOM
        this.elements = this.getElements();

        // Validar elementos requeridos
        if (!this.elements.familySelect || !this.elements.categoryContainer) {
            console.error('CategoryHierarchyManager: Elementos requeridos no encontrados');
            return;
        }

        // Inicializar
        this.init();
    }

    /**
     * Obtiene referencias a los elementos del DOM
     */
    getElements() {
        return {
            familySelect: document.getElementById(this.config.familySelectId),
            categoryContainer: document.getElementById(this.config.categoryContainerId),
            noFamilyMessage: document.getElementById(this.config.noFamilyMessageId),
            parentInput: document.getElementById(this.config.parentInputId),
            breadcrumb: document.getElementById(this.config.breadcrumbId),
            breadcrumbPath: document.getElementById(this.config.breadcrumbPathId)
        };
    }

    /**
     * Inicializa event listeners y estado inicial
     */
    init() {
        // Listener para cambio de familia
        this.elements.familySelect.addEventListener('change', (e) => {
            const familyId = parseInt(e.target.value);
            if (familyId) {
                this.loadCategoriesForFamily(familyId);
            } else {
                this.resetCategorySelects();
            }
        });

        // Restaurar selección si hay valores iniciales
        if (this.config.initialFamilyId) {
            setTimeout(() => {
                this.loadCategoriesForFamily(this.config.initialFamilyId);
                
                // Si hay parent_id en modo edit, reconstruir jerarquía
                if (this.config.initialParentId) {
                    setTimeout(() => {
                        this.reconstructHierarchy(this.config.initialParentId);
                    }, 200);
                }
            }, 100);
        }
    }

    /**
     * Carga categorías disponibles para una familia
     */
    loadCategoriesForFamily(familyId) {
        this.currentFamilyId = familyId;
        
        // Función auxiliar para filtrar recursivamente en datos jerárquicos
        const filterCategories = (categories) => {
            return categories.filter(cat => {
                const belongsToFamily = cat.family_id === familyId;
                const isNotCurrent = this.config.currentCategoryId ? cat.id !== this.config.currentCategoryId : true;
                return belongsToFamily && isNotCurrent;
            });
        };
        
        // Determinar si los datos son jerárquicos o planos
        const hasChildrenProperty = this.categoriesData.some(cat => cat.children !== undefined);
        
        let rootCategories;
        if (hasChildrenProperty) {
            // Datos jerárquicos: filtrar categorías de primer nivel
            rootCategories = filterCategories(this.categoriesData);
        } else {
            // Datos planos: filtrar por parent_id null
            rootCategories = this.categoriesData.filter(cat => {
                const belongsToFamily = cat.family_id === familyId;
                const isRoot = cat.parent_id === null;
                const isNotCurrent = this.config.currentCategoryId ? cat.id !== this.config.currentCategoryId : true;
                
                return belongsToFamily && isRoot && isNotCurrent;
            });
        }

        // Limpiar estado
        this.elements.categoryContainer.innerHTML = '';
        this.selectedPath = [];
        this.elements.parentInput.value = '';

        if (rootCategories.length === 0) {
            // No hay categorías disponibles
            this.elements.noFamilyMessage.style.display = 'flex';
            this.elements.noFamilyMessage.innerHTML = this.config.currentCategoryId
                ? '<i class="ri-information-line"></i> No hay categorías disponibles en esta familia. Se mantendrá como categoría raíz.'
                : '<i class="ri-information-line"></i> No hay categorías en esta familia. Se creará como categoría raíz.';
            this.elements.categoryContainer.style.display = 'none';
            this.updateBreadcrumb([]);
        } else {
            // Mostrar selects
            this.elements.noFamilyMessage.style.display = 'none';
            this.elements.categoryContainer.style.display = 'block';
            this.createLevelSelect(0, rootCategories, 'Categoría raíz de la familia');
        }
    }

    /**
     * Crea un select para un nivel de la jerarquía
     */
    createLevelSelect(level, categories, parentName = null) {
        const wrapper = document.createElement('div');
        wrapper.className = 'hierarchy-select-wrapper';
        wrapper.setAttribute('data-level', level);
        wrapper.style.marginTop = level > 0 ? '0.75rem' : '0';
        wrapper.style.opacity = '0';
        wrapper.style.transform = 'translateY(-10px)';
        wrapper.style.transition = 'all 0.3s ease';

        // Label para niveles anidados
        if (level > 0 && parentName) {
            const levelLabel = document.createElement('div');
            levelLabel.style.fontSize = '0.8125rem';
            levelLabel.style.fontWeight = '500';
            levelLabel.style.marginBottom = '0.5rem';
            levelLabel.style.color = 'var(--color-text-light)';
            levelLabel.innerHTML = `<i class="ri-corner-down-right-line"></i> Subcategoría de <strong>${parentName}</strong>:`;
            wrapper.appendChild(levelLabel);
        }

        // Container del select
        const selectContainer = document.createElement('div');
        selectContainer.className = 'input-icon-container';
        
        // Generar opciones
        const optionsHtml = categories.map(cat => 
            `<option value="${cat.id}" data-has-children="${this.hasChildren(cat.id)}">${cat.name}</option>`
        ).join('');

        const defaultOption = level === 0 
            ? 'Nueva categoría raíz (sin padre)' 
            : `Crear aquí (como hijo de "${parentName}")`;

        selectContainer.innerHTML = `
            <i class="ri-folder-line input-icon"></i>
            <select class="select-form category-level-select" data-level="${level}">
                <option value="">${defaultOption}</option>
                ${optionsHtml}
            </select>
            <i class="ri-arrow-down-s-line select-arrow"></i>
        `;

        wrapper.appendChild(selectContainer);
        this.elements.categoryContainer.appendChild(wrapper);

        // Animación de entrada
        setTimeout(() => {
            wrapper.style.opacity = '1';
            wrapper.style.transform = 'translateY(0)';
        }, 10);

        // Event listener del select
        const select = wrapper.querySelector('select');
        select.addEventListener('change', () => {
            this.handleLevelChange(level, select.value);
        });
    }

    /**
     * Maneja el cambio de selección en un nivel
     */
    handleLevelChange(level, selectedId) {
        this.removeSelectsAfterLevel(level);

        if (!selectedId) {
            // Opción vacía seleccionada
            this.selectedPath = this.selectedPath.slice(0, level);
            this.elements.parentInput.value = this.selectedPath.length > 0 
                ? this.selectedPath[this.selectedPath.length - 1].id 
                : '';
            this.updateBreadcrumb(this.selectedPath);
            return;
        }

        const category = this.findCategoryById(parseInt(selectedId));
        if (!category) return;

        // Actualizar path
        this.selectedPath = this.selectedPath.slice(0, level);
        this.selectedPath.push({
            id: category.id,
            name: category.name
        });

        this.elements.parentInput.value = category.id;
        this.updateBreadcrumb(this.selectedPath);

        // Si tiene hijos, crear siguiente nivel
        const children = this.getChildren(category.id);
        if (children.length > 0) {
            this.createLevelSelect(level + 1, children, category.name);
        }
    }

    /**
     * Remueve selects posteriores a un nivel
     */
    removeSelectsAfterLevel(level) {
        const selects = this.elements.categoryContainer.querySelectorAll('[data-level]');
        selects.forEach(wrapper => {
            const wrapperLevel = parseInt(wrapper.getAttribute('data-level'));
            if (wrapperLevel > level) {
                wrapper.style.opacity = '0';
                wrapper.style.transform = 'translateY(-10px)';
                setTimeout(() => wrapper.remove(), 200);
            }
        });
    }

    /**
     * Resetea los selects de categorías
     */
    resetCategorySelects() {
        this.elements.categoryContainer.innerHTML = '';
        this.elements.categoryContainer.style.display = 'none';
        this.elements.noFamilyMessage.style.display = 'flex';
        this.elements.noFamilyMessage.innerHTML = '<i class="ri-information-line"></i> Primero selecciona una familia para ver las categorías disponibles';
        this.selectedPath = [];
        this.elements.parentInput.value = '';
        this.updateBreadcrumb([]);
    }

    /**
     * Actualiza el breadcrumb visual
     */
    updateBreadcrumb(path) {
        if (!this.elements.breadcrumb || !this.elements.breadcrumbPath) return;

        if (path.length === 0) {
            this.elements.breadcrumb.style.display = 'none';
            return;
        }

        const pathText = path.map((item, index) => {
            const arrow = index > 0 ? ' → ' : '';
            return `${arrow}${item.name}`;
        }).join('');

        const familyName = this.elements.familySelect.options[this.elements.familySelect.selectedIndex].text;

        this.elements.breadcrumbPath.innerHTML = `<span style="color: var(--color-info); font-weight: 600;">[${familyName}]</span> ${pathText}`;
        this.elements.breadcrumb.style.display = 'block';
    }

    /**
     * Busca una categoría por ID (soporta datos planos y jerárquicos)
     */
    findCategoryById(id) {
        // Primero intentar búsqueda plana
        const flatResult = this.categoriesData.find(cat => cat.id === id);
        if (flatResult) return flatResult;
        
        // Si no se encuentra, intentar búsqueda recursiva (datos jerárquicos)
        return this.findCategoryByIdRecursive(id);
    }

    /**
     * Busca una categoría por ID en estructura jerárquica
     */
    findCategoryByIdRecursive(id, categories = this.categoriesData) {
        for (let category of categories) {
            if (category.id === id) return category;
            if (category.children && category.children.length > 0) {
                const found = this.findCategoryByIdRecursive(id, category.children);
                if (found) return found;
            }
        }
        return null;
    }

    /**
     * Verifica si una categoría tiene hijos
     */
    hasChildren(parentId) {
        // Primero intentar encontrar la categoría
        const category = this.findCategoryById(parentId);
        
        // Si tiene propiedad children (datos jerárquicos)
        if (category && category.children !== undefined) {
            return category.children && category.children.length > 0;
        }
        
        // Si no, buscar por parent_id (datos planos)
        return this.categoriesData.some(cat => cat.parent_id === parentId);
    }

    /**
     * Obtiene los hijos de una categoría
     */
    getChildren(parentId) {
        // Primero intentar encontrar la categoría
        const category = this.findCategoryById(parentId);
        
        if (this.config.debug) {
            console.log('[CategoryHierarchy] getChildren:', {
                parentId,
                category,
                hasChildrenProperty: category && category.children !== undefined
            });
        }
        
        // Si tiene propiedad children (datos jerárquicos)
        if (category && category.children !== undefined) {
            const children = category.children || [];
            // Filtrar categoría actual si existe
            if (this.config.currentCategoryId) {
                const filtered = children.filter(cat => cat.id !== this.config.currentCategoryId);
                if (this.config.debug) {
                    console.log('[CategoryHierarchy] Children jerárquicos filtrados:', filtered);
                }
                return filtered;
            }
            if (this.config.debug) {
                console.log('[CategoryHierarchy] Children jerárquicos:', children);
            }
            return children;
        }
        
        // Si no, buscar por parent_id (datos planos)
        const flatChildren = this.categoriesData.filter(cat => {
            const isChild = cat.parent_id === parentId;
            const isNotCurrent = this.config.currentCategoryId ? cat.id !== this.config.currentCategoryId : true;
            return isChild && isNotCurrent;
        });
        
        if (this.config.debug) {
            console.log('[CategoryHierarchy] Children planos:', flatChildren);
        }
        
        return flatChildren;
    }

    /**
     * Reconstruye la jerarquía desde un parent_id (para modo edit)
     */
    reconstructHierarchy(targetParentId) {
        // Construir path desde raíz hasta targetParentId
        const path = [];
        let currentId = targetParentId;
        
        while (currentId) {
            const cat = this.findCategoryById(currentId);
            if (!cat) break;
            path.unshift({ id: cat.id, name: cat.name });
            currentId = cat.parent_id;
        }

        // Simular selección en cascada
        let currentLevel = 0;
        path.forEach((item, index) => {
            setTimeout(() => {
                const select = document.querySelector(`select[data-level="${currentLevel}"]`);
                if (select) {
                    select.value = item.id;
                    this.handleLevelChange(currentLevel, item.id);
                    currentLevel++;
                }
            }, index * 100);
        });
    }

    /**
     * Destruye la instancia y limpia referencias
     */
    destroy() {
        this.elements = null;
        this.config = null;
        this.categoriesData = null;
        this.selectedPath = null;
    }
}

/**
 * Factory function para inicialización rápida
 * @param {Object} config - Configuración del manager
 * @returns {CategoryHierarchyManager}
 */
export function initCategoryHierarchy(config) {
    return new CategoryHierarchyManager(config);
}
