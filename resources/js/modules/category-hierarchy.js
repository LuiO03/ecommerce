// ========================================
// 📊 CATEGORY HIERARCHY MANAGER - NATIVO
// Administrador Jerárquico Moderno sin jsTree
// ========================================

import Sortable from 'sortablejs';

class CategoryHierarchyManager {
    constructor() {
        this.treeData = null;
        this.selectedNodes = new Set();
        this.config = window.hierarchyConfig || {};
        this.sortableInstances = [];
        
        this.init();
    }
    
    init() {
        if (!document.getElementById('categoryTree')) return;
        
        console.log('🚀 Inicializando Category Hierarchy Manager nativo...');
        
        this.loadTreeData();
        this.initControls();
        this.initBulkOperations();
        this.initModals();
    }
    
    // ========================================
    // 🌳 CARGAR DATOS DEL ÁRBOL
    // ========================================
    async loadTreeData() {
        try {
            const response = await fetch(this.config.treeDataUrl);
            this.treeData = await response.json();
            this.renderTree();
            
            console.log('✅ Árbol cargado:', this.treeData.length, 'familias');
        } catch (error) {
            console.error('❌ Error cargando árbol:', error);
        }
    }
    
    // ========================================
    // 🎨 RENDERIZAR ÁRBOL COMPLETO
    // ========================================
    renderTree() {
        const container = document.getElementById('categoryTree');
        if (!container || !this.treeData) return;
        
        container.innerHTML = '';
        
        this.treeData.forEach(family => {
            container.appendChild(this.createFamilyCard(family));
        });
        
        container.classList.add('loaded');
    }
    
    // ========================================
    // 🎴 CREAR TARJETA DE FAMILIA
    // ========================================
    createFamilyCard(family) {
        const card = document.createElement('div');
        card.className = 'family-card';
        card.dataset.familyId = family.li_attr['data-id'];
        
        const header = document.createElement('div');
        header.className = 'family-header';
        header.innerHTML = `
            <div class="family-toggle">
                <i class="ri-arrow-down-s-line"></i>
            </div>
            <div class="family-icon">
                <i class="ri-folder-3-fill"></i>
            </div>
            <div class="family-info">
                <div class="family-name">${family.text}</div>
                <div class="family-count">${family.children.length} categorías</div>
            </div>
        `;
        
        header.addEventListener('click', () => this.toggleFamily(card));
        
        const children = document.createElement('div');
        children.className = 'family-children';
        
        if (family.children && family.children.length > 0) {
            family.children.forEach(category => {
                children.appendChild(this.createCategoryItem(category));
            });
            
            // Inicializar Sortable para drag & drop
            this.initSortable(children);
        }
        
        card.appendChild(header);
        card.appendChild(children);
        
        return card;
    }
    
    // ========================================
    // 📦 CREAR ITEM DE CATEGORÍA
    // ========================================
    createCategoryItem(category) {
        const item = document.createElement('div');
        item.className = 'category-item';
        item.dataset.categoryId = category.li_attr['data-id'];
        item.dataset.categoryData = JSON.stringify(category);
        
        const card = document.createElement('div');
        card.className = 'category-card';
        
        const hasChildren = category.children && category.children.length > 0;
        const productsCount = category.li_attr['data-products-count'] || 0;
        const isActive = category.li_attr['data-status'] === '1';
        
        card.innerHTML = `
            <div class="category-drag-handle">
                <i class="ri-draggable"></i>
            </div>
            ${hasChildren ? `
                <div class="category-toggle">
                    <i class="ri-arrow-down-s-line"></i>
                </div>
            ` : '<div style="width: 20px;"></div>'}
            <input type="checkbox" class="category-checkbox">
            <div class="category-icon">
                <i class="ri-folder-line"></i>
            </div>
            <div class="category-info">
                <div class="category-name">${category.text.replace(/\(\d+\)/, '').trim()}</div>
                <div class="category-meta">
                    <span class="category-badge">
                        <i class="ri-shopping-bag-3-line"></i>
                        ${productsCount}
                    </span>
                    <div class="category-status ${isActive ? '' : 'inactive'}"></div>
                </div>
            </div>
            <div class="category-actions">
                <button class="category-action-btn edit" data-action="edit">
                    <i class="ri-edit-line"></i>
                </button>
                <button class="category-action-btn delete" data-action="delete">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
        `;
        
        // Event listeners
        card.addEventListener('click', (e) => this.handleCategoryClick(e, item, category));
        
        const checkbox = card.querySelector('.category-checkbox');
        checkbox.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleCheckbox(item);
        });
        
        if (hasChildren) {
            const toggle = card.querySelector('.category-toggle');
            toggle.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleCategory(item);
            });
        }
        
        // Botones de acción
        const editBtn = card.querySelector('[data-action="edit"]');
        editBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            window.location.href = this.config.editCategoryUrl.replace(':id', category.li_attr['data-id']);
        });
        
        const deleteBtn = card.querySelector('[data-action="delete"]');
        deleteBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.deleteCategory(category.li_attr['data-id'], category.text);
        });
        
        item.appendChild(card);
        
        // Subcategorías
        if (hasChildren) {
            const children = document.createElement('div');
            children.className = 'category-children';
            
            category.children.forEach(child => {
                children.appendChild(this.createCategoryItem(child));
            });
            
            this.initSortable(children);
            item.appendChild(children);
        }
        
        return item;
    }
    
    // ========================================
    // 🎮 INICIALIZAR SORTABLE (Drag & Drop)
    // ========================================
    initSortable(container) {
        const sortable = new Sortable(container, {
            group: 'categories',
            animation: 150,
            handle: '.category-drag-handle',
            ghostClass: 'dragging',
            dragClass: 'drag-over',
            onEnd: (evt) => {
                console.log('📦 Item movido:', evt.item.dataset.categoryId);
                // Aquí puedes implementar la lógica de persistencia
            }
        });
        
        this.sortableInstances.push(sortable);
    }
    
    // ========================================
    // 🎯 CONTROLES DE EXPANSIÓN
    // ========================================
    toggleFamily(card) {
        card.classList.toggle('collapsed');
    }
    
    toggleCategory(item) {
        item.classList.toggle('collapsed');
    }
    
    // ========================================
    // ✅ MANEJO DE CHECKBOXES
    // ========================================
    toggleCheckbox(item) {
        const checkbox = item.querySelector('.category-checkbox');
        const categoryId = item.dataset.categoryId;
        
        if (this.selectedNodes.has(categoryId)) {
            this.selectedNodes.delete(categoryId);
            checkbox.checked = false;
        } else {
            this.selectedNodes.add(categoryId);
            checkbox.checked = true;
        }
        
        this.updateSelection();
    }
    
    // ========================================
    // 🖱️ MANEJO DE CLICS EN CATEGORÍAS
    // ========================================
    handleCategoryClick(e, item, category) {
        // Ignorar si se hizo clic en botones específicos
        if (e.target.closest('.category-checkbox') || 
            e.target.closest('.category-toggle') ||
            e.target.closest('.category-actions')) {
            return;
        }
        
        // Deseleccionar otros
        document.querySelectorAll('.category-card.selected').forEach(card => {
            card.classList.remove('selected');
        });
        
        item.querySelector('.category-card').classList.add('selected');
        this.showSingleInfo(category);
    }
    
    // ========================================
    // 📊 ACTUALIZAR SELECCIÓN
    // ========================================
    updateSelection() {
        const count = this.selectedNodes.size;
        
        if (count === 0) {
            this.showPanel('empty');
        } else {
            // Con checkbox siempre mostrar panel de acciones masivas (1 o más)
            this.showBulkPanel(count);
        }
    }
    
    // ========================================
    // 🎨 PANELES DE INFORMACIÓN
    // ========================================
    showPanel(panel) {
        document.getElementById('emptyPanel').style.display = 'none';
        document.getElementById('infoPanel').style.display = 'none';
        document.getElementById('bulkPanel').style.display = 'none';
        
        if (panel === 'empty') {
            document.getElementById('emptyPanel').style.display = 'flex';
        } else if (panel === 'single') {
            document.getElementById('infoPanel').style.display = 'flex';
        } else if (panel === 'bulk') {
            document.getElementById('bulkPanel').style.display = 'flex';
        }
    }
    
    showSingleInfo(category) {
        const liAttr = category.li_attr;
        
        document.getElementById('infoName').textContent = category.text.replace(/\(\d+\)/, '').trim();
        
        // Obtener la familia (si es subcategoría, obtener la familia del padre raíz)
        const familyName = this.getRootFamilyName(category);
        document.getElementById('infoFamily').textContent = familyName;
        
        // Mostrar nombre del padre o indicar que es raíz
        const parentName = this.getParentName(category);
        document.getElementById('infoParent').textContent = parentName || 'Raíz';
        
        document.getElementById('infoChildren').textContent = category.children ? category.children.length : 0;
        document.getElementById('infoProducts').textContent = liAttr['data-products-count'] || 0;
        document.getElementById('infoStatus').innerHTML = liAttr['data-status'] === '1' 
            ? '<span class="badge badge-success">Activo</span>' 
            : '<span class="badge badge-danger">Inactivo</span>';
        document.getElementById('infoSlug').textContent = liAttr['data-slug'] || '-';
        
        const editUrl = this.config.editCategoryUrl.replace(':id', liAttr['data-id']);
        document.getElementById('editCategory').setAttribute('href', editUrl);
        
        this.showPanel('single');
    }
    
    showBulkPanel(count) {
        document.getElementById('bulkCount').textContent = count;
        this.showPanel('bulk');
    }
    
    getFamilyName(familyId) {
        const family = this.treeData.find(f => f.li_attr['data-id'] === familyId);
        return family ? family.text : '-';
    }
    
    getRootFamilyName(category) {
        // Si tiene family-id directo, usarlo
        const directFamilyId = category.li_attr['data-family-id'];
        if (directFamilyId) {
            return this.getFamilyName(directFamilyId);
        }
        
        // Si es subcategoría, buscar la familia recorriendo hacia arriba
        const parentId = category.li_attr['data-parent-id'];
        if (parentId) {
            const parent = this.findCategoryInTree(parentId);
            if (parent) {
                return this.getRootFamilyName(parent);
            }
        }
        
        return '-';
    }
    
    findCategoryInTree(categoryId) {
        for (const family of this.treeData) {
            const found = this.findCategoryById(family.children, categoryId);
            if (found) return found;
        }
        return null;
    }
    
    getParentName(category) {
        const parentId = category.li_attr['data-parent-id'];
        if (!parentId) return null;
        
        // Buscar el padre en el árbol de datos
        for (const family of this.treeData) {
            const found = this.findCategoryById(family.children, parentId);
            if (found) {
                return found.text.replace(/\(\d+\)/, '').trim();
            }
        }
        return null;
    }
    
    findCategoryById(categories, id) {
        if (!categories) return null;
        
        for (const cat of categories) {
            if (cat.li_attr['data-id'] === id) {
                return cat;
            }
            if (cat.children && cat.children.length > 0) {
                const found = this.findCategoryById(cat.children, id);
                if (found) return found;
            }
        }
        return null;
    }
    
    // ========================================
    // 🎮 CONTROLES GENERALES
    // ========================================
    initControls() {
        // Búsqueda
        const searchInput = document.getElementById('treeSearch');
        const clearSearch = document.getElementById('clearTreeSearch');
        
        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            const value = e.target.value.toLowerCase();
            
            searchTimeout = setTimeout(() => {
                this.searchTree(value);
                clearSearch.style.display = value ? 'block' : 'none';
            }, 300);
        });
        
        clearSearch.addEventListener('click', () => {
            searchInput.value = '';
            this.searchTree('');
            clearSearch.style.display = 'none';
        });
        
        // Expandir/Colapsar todo
        document.getElementById('expandAll').addEventListener('click', () => {
            document.querySelectorAll('.family-card, .category-item').forEach(el => {
                el.classList.remove('collapsed');
            });
        });
        
        document.getElementById('collapseAll').addEventListener('click', () => {
            document.querySelectorAll('.family-card, .category-item').forEach(el => {
                el.classList.add('collapsed');
            });
        });
        
        // Cerrar paneles
        document.getElementById('closeInfo')?.addEventListener('click', () => {
            this.showPanel('empty');
            document.querySelectorAll('.category-card.selected').forEach(card => {
                card.classList.remove('selected');
            });
        });
        
        document.getElementById('closeBulk')?.addEventListener('click', () => {
            this.selectedNodes.clear();
            document.querySelectorAll('.category-checkbox:checked').forEach(cb => {
                cb.checked = false;
            });
            this.showPanel('empty');
        });
    }
    
    // ========================================
    // 🔍 BÚSQUEDA EN EL ÁRBOL
    // ========================================
    searchTree(query) {
        const familyCards = document.querySelectorAll('.family-card');
        const categoryItems = document.querySelectorAll('.category-item');
        
        if (!query) {
            // Si no hay búsqueda, mostrar todo
            familyCards.forEach(card => card.style.display = '');
            categoryItems.forEach(item => item.style.display = '');
            return;
        }
        
        // Ocultar todo inicialmente
        familyCards.forEach(card => card.style.display = 'none');
        categoryItems.forEach(item => item.style.display = 'none');
        
        // Buscar en categorías
        categoryItems.forEach(item => {
            const name = item.querySelector('.category-name')?.textContent.toLowerCase();
            
            if (name && name.includes(query)) {
                // Mostrar el item encontrado
                item.style.display = '';
                
                // Expandir el item si tiene hijos
                item.classList.remove('collapsed');
                
                // Mostrar y expandir todos los ancestros
                let parent = item.parentElement;
                while (parent) {
                    if (parent.classList.contains('family-card')) {
                        parent.style.display = '';
                        parent.classList.remove('collapsed');
                        break;
                    }
                    if (parent.classList.contains('category-item')) {
                        parent.style.display = '';
                        parent.classList.remove('collapsed');
                    }
                    parent = parent.parentElement;
                }
            }
        });
        
        // Buscar en familias
        familyCards.forEach(card => {
            const name = card.querySelector('.family-name')?.textContent.toLowerCase();
            
            if (name && name.includes(query)) {
                card.style.display = '';
                card.classList.remove('collapsed');
                
                // Mostrar todas las categorías hijas de esta familia
                const childCategories = card.querySelectorAll('.category-item');
                childCategories.forEach(child => {
                    child.style.display = '';
                });
            } else {
                // Si la familia no coincide, verificar si tiene categorías visibles
                const visibleChildren = card.querySelectorAll('.category-item[style=""]');
                if (visibleChildren.length > 0) {
                    card.style.display = '';
                }
            }
        });
    }
    
    // ========================================
    // 🗑️ ELIMINAR CATEGORÍA
    // ========================================
    async deleteCategory(id, name) {
        const confirmed = await window.showConfirmModal({
            title: '¿Eliminar categoría?',
            message: `¿Estás seguro de eliminar "${name}"?`,
            confirmText: 'Eliminar',
            confirmClass: 'boton-danger'
        });
        
        if (confirmed) {
            // Implementar lógica de eliminación
            console.log('🗑️ Eliminar categoría:', id);
        }
    }
    
    // ========================================
    // 📦 OPERACIONES MASIVAS
    // ========================================
    initBulkOperations() {
        // Listener para cambio de familia (cargar categorías)
        document.getElementById('bulkFamilyTarget')?.addEventListener('change', (e) => {
            this.loadCategoriesForFamily(e.target.value);
        });
        
        // Preview de movimiento
        document.getElementById('previewMove')?.addEventListener('click', () => {
            this.previewBulkMove();
        });
        
        // Ejecutar movimiento
        document.getElementById('executeBulkMove')?.addEventListener('click', () => {
            this.executeBulkMove();
        });
        
        // Duplicar
        document.getElementById('bulkDuplicate')?.addEventListener('click', () => {
            this.bulkDuplicate();
        });
        
        // Eliminar
        document.getElementById('bulkDelete')?.addEventListener('click', () => {
            this.bulkDelete();
        });
    }
    
    loadCategoriesForFamily(familyValue) {
        const categorySelect = document.getElementById('bulkCategoryTarget');
        
        if (!familyValue || familyValue === 'root') {
            categorySelect.disabled = true;
            categorySelect.innerHTML = '<option value="">Sin categoría padre (raíz de familia)</option>';
            return;
        }
        
        // Extraer el ID de la familia
        const familyId = familyValue.replace('family_', '');
        
        // Buscar la familia en los datos del árbol
        const family = this.treeData.find(f => f.li_attr['data-id'] === familyId);
        
        if (!family || !family.children || family.children.length === 0) {
            categorySelect.disabled = true;
            categorySelect.innerHTML = '<option value="">Esta familia no tiene categorías</option>';
            return;
        }
        
        // Habilitar y llenar el select de categorías
        categorySelect.disabled = false;
        categorySelect.innerHTML = '<option value="">Sin categoría padre (raíz de familia)</option>';
        
        // Agregar categorías recursivamente
        this.addCategoriesToSelect(family.children, categorySelect, 0);
    }
    
    addCategoriesToSelect(categories, selectElement, level) {
        categories.forEach(category => {
            const option = document.createElement('option');
            const indent = '\u00a0\u00a0'.repeat(level); // Espacios para indentación
            const categoryName = category.text.replace(/\(\d+\)/, '').trim();
            
            option.value = `category_${category.li_attr['data-id']}`;
            option.textContent = `${indent}${level > 0 ? '\u2514 ' : ''}${categoryName}`;
            
            selectElement.appendChild(option);
            
            // Agregar subcategorías recursivamente
            if (category.children && category.children.length > 0) {
                this.addCategoriesToSelect(category.children, selectElement, level + 1);
            }
        });
    }
    
    async previewBulkMove() {
        const familyTarget = document.getElementById('bulkFamilyTarget').value;
        const categoryTarget = document.getElementById('bulkCategoryTarget').value;
        
        if (!familyTarget) {
            alert('⚠️ Selecciona una familia destino');
            return;
        }
        
        console.log('👁️ Preview movimiento masivo');
        console.log('Familia:', familyTarget);
        console.log('Categoría padre:', categoryTarget || 'Raíz de familia');
        // Mostrar modal de preview
    }
    
    async executeBulkMove() {
        const familyTarget = document.getElementById('bulkFamilyTarget').value;
        const categoryTarget = document.getElementById('bulkCategoryTarget').value;
        
        if (!familyTarget) {
            alert('⚠️ Selecciona una familia destino');
            return;
        }
        
        console.log('➡️ Ejecutar movimiento masivo');
        console.log('Familia:', familyTarget);
        console.log('Categoría padre:', categoryTarget || 'Raíz de familia');
    }
    
    async bulkDuplicate() {
        console.log('📋 Duplicar seleccionados');
    }
    
    async bulkDelete() {
        console.log('🗑️ Eliminar seleccionados');
    }
    
    // ========================================
    // 🎭 MODALES
    // ========================================
    initModals() {
        // Cerrar modal de preview
        document.getElementById('closePreviewModal')?.addEventListener('click', () => {
            document.getElementById('previewModal').style.display = 'none';
        });
        
        document.getElementById('cancelPreview')?.addEventListener('click', () => {
            document.getElementById('previewModal').style.display = 'none';
        });
    }
}

// ========================================
// 🎬 INICIALIZACIÓN
// ========================================
function initHierarchyManager() {
    if (!document.getElementById('categoryTree')) {
        return;
    }
    
    console.log('🚀 Inicializando CategoryHierarchyManager...');
    window.hierarchyManager = new CategoryHierarchyManager();
}

// Inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHierarchyManager);
} else {
    initHierarchyManager();
}
