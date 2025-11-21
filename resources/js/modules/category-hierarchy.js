// ========================================
// 📊 CATEGORY HIERARCHY MANAGER - NATIVO
// Administrador Jerárquico Moderno sin jsTree
// ========================================

import Sortable from 'sortablejs';

class CategoryHierarchyManager {
    constructor() {
        this.treeData = null;
        this.selectedNodes = new Set();
        this.currentCategory = null;
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
        }
        
        // Inicializar Sortable para TODAS las familias (incluso vacías)
        // Esto permite arrastrar categorías a familias sin hijos
        this.initSortable(children);
        
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
            ` : `
                <div class="category-toggle" style="pointer-events: none;">
                    <i class="ri-corner-down-right-line"></i>
                </div>
            `}
            <input type="checkbox" class="category-checkbox">
            <div class="category-icon">
                <i class="${hasChildren ? 'ri-folder-line' : 'ri-file-line'}"></i>
            </div>
            <div class="category-info">
                <div class="category-name">${category.text.replace(/\(\d+\)/, '').trim()}</div>
                <div class="category-meta">
                    <span class="category-badge">
                        <i class="ri-archive-line"></i>
                        ${productsCount}
                    </span>
                    <div class="category-status ${isActive ? '' : 'inactive'}"></div>
                </div>
            </div>
            <div class="category-actions">
                <button class="category-action-btn edit" data-action="edit">
                    <i class="ri-edit-circle-fill"></i>
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
            const slug = category.li_attr['data-slug'];
            window.location.href = this.config.editCategoryUrl.replace(':id', slug);
        });
        
        const deleteBtn = card.querySelector('[data-action="delete"]');
        deleteBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const slug = category.li_attr['data-slug'];
            this.deleteCategory(slug, category.text, category.li_attr['data-id']);
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
            animation: 200,
            handle: '.category-drag-handle',
            ghostClass: 'dragging',
            dragClass: 'drag-over',
            fallbackOnBody: true,
            swapThreshold: 0.65,
            onEnd: async (evt) => {
                // Obtener datos del item movido
                const movedItem = evt.item;
                const categoryId = movedItem.dataset.categoryId;
                const categoryData = JSON.parse(movedItem.dataset.categoryData);
                
                // Determinar el nuevo padre
                const newParentContainer = evt.to;
                let newParentId = null;
                let newFamilyId = null;
                
                // Buscar el contenedor padre
                const parentItem = newParentContainer.closest('.category-item');
                const parentFamily = newParentContainer.closest('.family-card');
                
                if (parentItem) {
                    // Se movió dentro de otra categoría (subcategoría)
                    newParentId = parentItem.dataset.categoryId;
                    const parentData = JSON.parse(parentItem.dataset.categoryData);
                    newFamilyId = parentData.li_attr['data-family-id'];
                } else if (parentFamily) {
                    // Se movió a nivel raíz de una familia
                    newFamilyId = parentFamily.dataset.familyId;
                    newParentId = null;
                } else {
                    console.warn('⚠️ No se pudo determinar el destino');
                    return;
                }
                
                // Verificar si realmente cambió de posición
                const oldParentId = categoryData.li_attr['data-parent-id'] || null;
                const oldFamilyId = categoryData.li_attr['data-family-id'];
                
                if (oldParentId == newParentId && oldFamilyId == newFamilyId) {
                    console.log('ℹ️ Solo cambió el orden, no la jerarquía');
                    return;
                }
                
                console.log('🎯 Moviendo categoría:', {
                    categoryId,
                    categoryName: categoryData.text,
                    from: { familyId: oldFamilyId, parentId: oldParentId },
                    to: { familyId: newFamilyId, parentId: newParentId }
                });
                
                // Persistir el cambio en el backend
                await this.saveCategoryMove(categoryId, newFamilyId, newParentId);
            }
        });
        
        this.sortableInstances.push(sortable);
    }
    
    // ========================================
    // 💾 GUARDAR MOVIMIENTO DE CATEGORÍA
    // ========================================
    async saveCategoryMove(categoryId, newFamilyId, newParentId) {
        try {
            const response = await fetch(this.config.dragMoveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.config.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    category_id: categoryId,
                    family_id: newFamilyId,
                    parent_id: newParentId
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Mostrar toast de éxito
                if (typeof window.showToast === 'function') {
                    window.showToast({
                        type: 'success',
                        title: 'Categoría movida',
                        message: data.message || 'La categoría se movió correctamente',
                        duration: 3000
                    });
                }
                
                // Recargar datos del árbol para reflejar cambios
                await this.loadTreeData();
                
            } else {
                throw new Error(data.message || 'Error al mover categoría');
            }
            
        } catch (error) {
            console.error('❌ Error al guardar movimiento:', error);
            
            // Mostrar toast de error
            if (typeof window.showToast === 'function') {
                window.showToast({
                    type: 'danger',
                    title: 'Error',
                    message: error.message || 'No se pudo mover la categoría',
                    duration: 4000
                });
            }
            
            // Recargar para revertir cambios visuales
            await this.loadTreeData();
        }
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
        const card = item.querySelector('.category-card');
        const categoryId = item.dataset.categoryId;
        
        if (this.selectedNodes.has(categoryId)) {
            this.selectedNodes.delete(categoryId);
            checkbox.checked = false;
            card.classList.remove('selected');
        } else {
            this.selectedNodes.add(categoryId);
            checkbox.checked = true;
            card.classList.add('selected');
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
        
        // Limpiar selecciones múltiples si existen
        if (this.selectedNodes.size > 0) {
            this.selectedNodes.clear();
            document.querySelectorAll('.category-checkbox:checked').forEach(cb => {
                cb.checked = false;
            });
        }
        
        // Deseleccionar otros (solo para selección individual)
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
        const deselectBtn = document.getElementById('deselectAll');
        
        if (count === 0) {
            this.showPanel('empty');
            if (deselectBtn) deselectBtn.style.display = 'none';
        } else {
            // Con checkbox siempre mostrar panel de acciones masivas (1 o más)
            this.showBulkPanel(count);
            if (deselectBtn) deselectBtn.style.display = 'inline-flex';
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
        
        // Almacenar los datos de la categoría actual para uso en botones
        this.currentCategory = {
            id: liAttr['data-id'],
            slug: liAttr['data-slug'],
            name: category.text.replace(/\(\d+\)/, '').trim()
        };
        
        document.getElementById('infoName').textContent = this.currentCategory.name;
        document.getElementById('infoId').textContent = this.currentCategory.id;
        
        // Obtener la familia (si es subcategoría, obtener la familia del padre raíz)
        const familyName = this.getRootFamilyName(category);
        document.getElementById('infoFamily').textContent = familyName;
        
        // Mostrar nombre del padre o indicar que es raíz
        const parentName = this.getParentName(category);
        document.getElementById('infoParent').textContent = parentName || 'Raíz';
        
        document.getElementById('infoChildren').textContent = category.children ? category.children.length : 0;
        document.getElementById('infoProducts').textContent = liAttr['data-products-count'] || 0;
        document.getElementById('infoStatus').innerHTML = liAttr['data-status'] === '1' 
            ? '<span class="badge boton-success"><i class="ri-checkbox-circle-fill"></i>Activo</span>' 
            : '<span class="badge boton-danger"><i class="ri-close-circle-fill"></i>Inactivo</span>';
        document.getElementById('infoSlug').textContent = liAttr['data-slug'] || '-';
        
        const editUrl = this.config.editCategoryUrl.replace(':id', this.currentCategory.slug);
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
                cb.closest('.category-card')?.classList.remove('selected');
            });
            this.showPanel('empty');
        });

        // Deseleccionar todos
        document.getElementById('deselectAll')?.addEventListener('click', () => {
            this.selectedNodes.clear();
            document.querySelectorAll('.category-checkbox:checked').forEach(cb => {
                cb.checked = false;
                cb.closest('.category-card')?.classList.remove('selected');
            });
            this.updateSelection();
        });

        // Botón de crear subcategoría
        document.getElementById('createChild')?.addEventListener('click', () => {
            if (this.currentCategory) {
                // Redirigir a la página de creación con el parent_id en la URL
                window.location.href = `/admin/categories/create?parent_id=${this.currentCategory.id}`;
            }
        });

        // Botón de eliminar desde el panel de información
        document.getElementById('deleteCategory')?.addEventListener('click', () => {
            if (this.currentCategory) {
                this.deleteCategory(this.currentCategory.slug, this.currentCategory.name, this.currentCategory.id);
            }
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
    async deleteCategory(slug, name, id = null) {
        // Limpiar el nombre (remover conteo de productos)
        const cleanName = name.replace(/\(\d+\)/, '').trim();
        
        // Usar el sistema global de confirmación
        window.showConfirm({
            type: 'danger',
            header: 'Confirmar eliminación',
            title: '¿Eliminar categoría?',
            message: `¿Estás seguro de que deseas eliminar la categoría <strong>"${cleanName}"</strong>?<br><span>Esta acción no se puede deshacer.</span>`,
            confirmText: 'Sí, eliminar',
            cancelText: 'No, cancelar',
            onConfirm: async () => {
                await this.performDelete(slug, cleanName);
            }
        });
    }
    
    // ========================================
    // 💾 EJECUTAR ELIMINACIÓN
    // ========================================
    async performDelete(slug, name) {
        try {
            // Crear FormData para enviar la petición DELETE
            const formData = new FormData();
            formData.append('_method', 'DELETE');
            formData.append('_token', this.config.csrfToken);
            
            const response = await fetch(`/admin/categories/${slug}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.config.csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            });
            
            const data = await response.json();
            
            if (response.ok && data.success) {
                // Mostrar toast de éxito
                if (typeof window.showToast === 'function') {
                    window.showToast({
                        type: 'success',
                        title: 'Categoría eliminada',
                        message: `La categoría "${name}" ha sido eliminada exitosamente.`,
                        duration: 4000
                    });
                }
                
                // Recargar el árbol para reflejar cambios
                await this.loadTreeData();
                
                // Cerrar el panel de información si estaba abierto
                this.showPanel('empty');
                
            } else {
                // Error del servidor
                throw new Error(data.message || 'Error al eliminar la categoría');
            }
            
        } catch (error) {
            console.error('❌ Error al eliminar categoría:', error);
            
            // Mostrar modal de error
            if (typeof window.showInfoModal === 'function') {
                window.showInfoModal({
                    type: 'danger',
                    header: 'Error',
                    title: 'No se pudo eliminar',
                    message: error.message || 'Ocurrió un error al intentar eliminar la categoría. Puede que tenga subcategorías o productos asociados.'
                });
            }
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
        
        console.log('🔍 Buscando familia con ID:', familyId);
        console.log('📊 Datos del árbol:', this.treeData);
        
        // Buscar la familia en los datos del árbol (comparar como string)
        const family = this.treeData.find(f => String(f.li_attr['data-id']) === String(familyId));
        
        console.log('✅ Familia encontrada:', family);
        
        if (!family || !family.children || family.children.length === 0) {
            categorySelect.disabled = true;
            categorySelect.innerHTML = '<option value="">Esta familia no tiene categorías</option>';
            return;
        }
        
        // Habilitar y llenar el select de categorías
        categorySelect.disabled = false;
        categorySelect.innerHTML = '<option value="">Sin categoría padre (raíz de familia)</option>';
        
        console.log('📦 Agregando categorías al select:', family.children.length);
        
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
