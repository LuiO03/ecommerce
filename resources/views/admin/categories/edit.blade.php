<x-admin-layout>
    <x-slot name="title">
        Editar {{ $category->name }}
    </x-slot>

    <x-slot name="action">
        <a href="{{ route('admin.categories.index') }}" class="boton-form boton-action">
            <span class="boton-form-icon">
                <i class="ri-arrow-left-circle-fill"></i>
            </span>
            <span class="boton-form-text">Volver</span>
        </a>

        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="delete-form"
            data-entity="categoría" style="margin: 0;">
            @csrf
            @method('DELETE')
            <button class="boton boton-danger" type="submit">
                <span class="boton-icon"><i class="ri-delete-bin-6-fill"></i></span>
                <span class="boton-text">Eliminar</span>
            </button>
        </form>
    </x-slot>

    <form action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data"
        class="form-container" autocomplete="off" id="categoryForm">
        @csrf
        @method('PUT')

        <div class="form-info-banner">
            <i class="ri-lightbulb-line form-info-icon"></i>
            <div>
                <strong>Guía rápida:</strong>
                <ul>
                    <li>Primero selecciona la <strong>familia</strong> a la que pertenecerá la categoría</li>
                    <li>Luego elige su ubicación en la jerarquía (opcional - si no eliges nada, será categoría raíz)</li>
                    <li>Los campos con asterisco (<i class="ri-asterisk text-accent"></i>) son obligatorios</li>
                </ul>
            </div>
        </div>

        <div class="form-row">

            <!-- ============================
                 COLUMNA IZQUIERDA
            ============================= -->
            <div class="form-column">
                {{-- FAMILIA (OBLIGATORIO) --}}
                <div class="input-group">
                    <label for="family_select" class="label-form">
                        Familia
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-stack-line input-icon"></i>
                        <select name="family_id" id="family_select" class="select-form" required>
                            <option value="" disabled>Seleccione una familia</option>
                            @foreach ($families as $family)
                                <option value="{{ $family->id }}" 
                                    {{ old('family_id', $category->family_id) == $family->id ? 'selected' : '' }}>
                                    {{ $family->name }}
                                </option>
                            @endforeach
                        </select>
                        <i class="ri-arrow-down-s-line select-arrow"></i>
                    </div>
                </div>

                {{-- JERARQUÍA DE CATEGORÍAS PROGRESIVA --}}
                <div class="input-group">
                    <label class="label-form">
                        Ubicación en la jerarquía
                        <span class="label-hint">(opcional)</span>
                    </label>

                    {{-- Hidden input solo para parent_id --}}
                    <input type="hidden" name="parent_id" id="parent_id" value="{{ old('parent_id', $category->parent_id) }}">

                    {{-- Contenedor dinámico de selects --}}
                    <div id="categoryHierarchySelects" style="display: none;">
                        {{-- Los selects se generarán dinámicamente según la familia --}}
                    </div>

                    <span id="noFamilyMessage" class="label-hint">
                        Primero selecciona una familia para ver las categorías disponibles
                    </span>

                    {{-- Breadcrumb visual de la ruta seleccionada --}}
                    <div id="hierarchyBreadcrumb"
                        style="display: none; margin-top: 0rem; padding: 0.75rem; background: var(--color-info-pastel); border-radius: 8px; font-size: 0.875rem;">
                        <i class="ri-route-line" style="margin-right: 0.5rem; color: var(--color-info);"></i>
                        <strong>Ruta seleccionada:</strong>
                        <span id="breadcrumbPath"
                            style="margin-left: 0.5rem; font-family: 'Courier New', monospace;"></span>
                    </div>
                </div>

                {{-- NAME --}}
                <div class="input-group">
                    <label for="name" class="label-form">
                        Nombre de la categoría
                        <i class="ri-asterisk text-accent"></i>
                    </label>

                    <div class="input-icon-container">
                        <i class="ri-price-tag-3-line input-icon"></i>

                        <input type="text" name="name" id="name" class="input-form" required
                            value="{{ old('name', $category->name) }}" placeholder="Ingrese el nombre">
                    </div>
                </div>

                {{-- STATUS --}}
                <div class="input-group">
                    <label for="status" class="label-form">
                        Estado
                        <i class="ri-asterisk text-accent"></i>
                    </label>

                    <div class="input-icon-container">
                        <i class="ri-focus-2-line input-icon"></i>

                        <select name="status" id="status" class="select-form" required>
                            <option value="" disabled>Seleccione un estado</option>

                            <option value="1" {{ old('status', $category->status) == '1' ? 'selected' : '' }}>
                                Activo
                            </option>

                            <option value="0" {{ old('status', $category->status) == '0' ? 'selected' : '' }}>
                                Inactivo
                            </option>
                        </select>

                        <i class="ri-arrow-down-s-line select-arrow"></i>
                    </div>
                </div>

                {{-- DESCRIPTION --}}
                <div class="input-group">
                    <label for="description" class="label-form label-textarea">
                        Descripción
                    </label>

                    <div class="input-icon-container">
                        <textarea name="description" id="description" class="textarea-form" placeholder="Ingrese la descripción" rows="4">{{ old('description', $category->description) }}</textarea>

                        <i class="ri-file-text-line input-icon"></i>
                    </div>
                </div>

            </div>

            <!-- ============================
                 COLUMNA DERECHA
            ============================= -->
            <div class="form-column">

                {{-- IMAGE UPLOAD --}}
                <div class="image-upload-section">
                    <label class="label-form">Imagen de la categoría</label>

                    <input type="file" name="image" id="image" class="file-input" accept="image/*">
                    <input type="hidden" name="remove_image" id="removeImageFlag" value="0">

                    <!-- Zona de vista previa -->
                    <div class="image-preview-zone {{ $category->image && file_exists(public_path('storage/' . $category->image)) ? 'has-image' : '' }}"
                        id="imagePreviewZone">
                        @if ($category->image && file_exists(public_path('storage/' . $category->image)))
                            <img id="imagePreview" class="image-preview image-pulse"
                                src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}">
                            <!-- Placeholder oculto inicialmente (se mostrará al eliminar) -->
                            <div class="image-placeholder" id="imagePlaceholder" style="display: none;">
                                <i class="ri-image-add-line"></i>
                                <p>Arrastra una imagen aquí</p>
                                <span>o haz clic para seleccionar</span>
                            </div>
                        @elseif($category->image)
                            <!-- Imagen no encontrada -->
                            <div class="image-error" id="imageError">
                                <i class="ri-folder-close-line"></i>
                                <p>Imagen no encontrada</p>
                                <span>Haz clic para subir una nueva</span>
                            </div>
                        @else
                            <!-- Sin imagen -->
                            <div class="image-placeholder" id="imagePlaceholder">
                                <i class="ri-image-add-line"></i>
                                <p>Arrastra una imagen aquí</p>
                                <span>o haz clic para seleccionar</span>
                            </div>
                        @endif

                        <!-- Imagen nueva cargada (oculta inicialmente) -->
                        <img id="imagePreviewNew" class="image-preview image-pulse" style="display: none;"
                            alt="Vista previa">

                        <!-- Overlay único para todas las imágenes -->
                        <div class="image-overlay" id="imageOverlay" style="display: none;">
                            <button type="button" class="overlay-btn" id="changeImageBtn" title="Cambiar imagen">
                                <i class="ri-upload-2-line"></i>
                                <span>Cambiar</span>
                            </button>
                            <button type="button" class="overlay-btn overlay-btn-danger" id="removeImageBtn"
                                title="Eliminar imagen">
                                <i class="ri-delete-bin-line"></i>
                                <span>Eliminar</span>
                            </button>
                        </div>
                    </div>

                    <div class="image-filename" id="imageFilename"
                        style="{{ $category->image && file_exists(public_path('storage/' . $category->image)) ? 'display: flex;' : 'display: none;' }}">
                        <i class="ri-file-image-line"></i>
                        <span id="filenameText">{{ $category->image ? basename($category->image) : '' }}</span>
                    </div>
                </div>

            </div>
        </div>

        <script>
            // SISTEMA DE JERARQUÍA DE CATEGORÍAS PROGRESIVA (CASCADING SELECTS)
            const categoriesData = {!! json_encode($parents->map(function($cat) {
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'family_id' => $cat->family_id,
                    'parent_id' => $cat->parent_id,
                ];
            })) !!};
            
            const currentCategoryId = {{ $category->id }};
            const familySelect = document.getElementById('family_select');
            const categoryContainer = document.getElementById('categoryHierarchySelects');
            const noFamilyMessage = document.getElementById('noFamilyMessage');
            const parentIdInput = document.getElementById('parent_id');
            
            let selectedPath = [];
            let currentFamilyId = null;

            // Listener para cambio de familia
            familySelect.addEventListener('change', function() {
                const familyId = parseInt(this.value);
                if (familyId) {
                    loadCategoriesForFamily(familyId);
                } else {
                    resetCategorySelects();
                }
            });

            function loadCategoriesForFamily(familyId) {
                currentFamilyId = familyId;
                
                // Filtrar categorías raíz de esta familia (excluyendo la categoría actual)
                const rootCategories = categoriesData.filter(cat => 
                    cat.family_id === familyId && 
                    cat.parent_id === null &&
                    cat.id !== currentCategoryId
                );

                // Limpiar container
                categoryContainer.innerHTML = '';
                selectedPath = [];
                parentIdInput.value = '';

                if (rootCategories.length === 0) {
                    // No hay categorías, será raíz de esta familia
                    noFamilyMessage.style.display = 'flex';
                    noFamilyMessage.innerHTML = '<i class="ri-information-line"></i> No hay categorías disponibles en esta familia. Se mantendrá como categoría raíz.';
                    categoryContainer.style.display = 'none';
                    updateBreadcrumb([]);
                } else {
                    noFamilyMessage.style.display = 'none';
                    categoryContainer.style.display = 'block';
                    createLevelSelect(0, rootCategories, 'Categoría raíz de la familia');
                }
            }

            function createLevelSelect(level, categories, parentName = null) {
                const wrapper = document.createElement('div');
                wrapper.className = 'hierarchy-select-wrapper';
                wrapper.setAttribute('data-level', level);
                wrapper.style.marginTop = level > 0 ? '0.75rem' : '0';
                wrapper.style.opacity = '0';
                wrapper.style.transform = 'translateY(-10px)';
                wrapper.style.transition = 'all 0.3s ease';

                if (level > 0 && parentName) {
                    const levelLabel = document.createElement('div');
                    levelLabel.style.fontSize = '0.8125rem';
                    levelLabel.style.fontWeight = '500';
                    levelLabel.style.marginBottom = '0.5rem';
                    levelLabel.style.color = 'var(--color-text-light)';
                    levelLabel.innerHTML = `<i class="ri-corner-down-right-line"></i> Subcategoría de <strong>${parentName}</strong>:`;
                    wrapper.appendChild(levelLabel);
                }

                const selectContainer = document.createElement('div');
                selectContainer.className = 'input-icon-container';
                
                const optionsHtml = categories.map(cat => 
                    `<option value="${cat.id}" data-has-children="${hasChildren(cat.id)}">${cat.name}</option>`
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
                categoryContainer.appendChild(wrapper);

                // Animación
                setTimeout(() => {
                    wrapper.style.opacity = '1';
                    wrapper.style.transform = 'translateY(0)';
                }, 10);

                // Event listener
                const select = wrapper.querySelector('select');
                select.addEventListener('change', function() {
                    handleLevelChange(level, this.value);
                });
            }

            function handleLevelChange(level, selectedId) {
                removeSelectsAfterLevel(level);

                if (!selectedId) {
                    // Truncar path hasta este nivel
                    selectedPath = selectedPath.slice(0, level);
                    parentIdInput.value = selectedPath.length > 0 ? selectedPath[selectedPath.length - 1].id : '';
                    updateBreadcrumb(selectedPath);
                    return;
                }

                const category = findCategoryById(parseInt(selectedId));
                if (!category) return;

                // Actualizar path
                selectedPath = selectedPath.slice(0, level);
                selectedPath.push({
                    id: category.id,
                    name: category.name
                });

                parentIdInput.value = category.id;
                updateBreadcrumb(selectedPath);

                // Si tiene hijos, crear siguiente nivel
                const children = getChildren(category.id);
                if (children.length > 0) {
                    createLevelSelect(level + 1, children, category.name);
                }
            }

            function removeSelectsAfterLevel(level) {
                const selects = categoryContainer.querySelectorAll('[data-level]');
                selects.forEach(wrapper => {
                    const wrapperLevel = parseInt(wrapper.getAttribute('data-level'));
                    if (wrapperLevel > level) {
                        wrapper.style.opacity = '0';
                        wrapper.style.transform = 'translateY(-10px)';
                        setTimeout(() => wrapper.remove(), 200);
                    }
                });
            }

            function resetCategorySelects() {
                categoryContainer.innerHTML = '';
                categoryContainer.style.display = 'none';
                noFamilyMessage.style.display = 'flex';
                noFamilyMessage.innerHTML = '<i class="ri-information-line"></i> Primero selecciona una familia para ver las categorías disponibles';
                selectedPath = [];
                parentIdInput.value = '';
                updateBreadcrumb([]);
            }

            function updateBreadcrumb(path) {
                const breadcrumb = document.getElementById('hierarchyBreadcrumb');
                const breadcrumbPath = document.getElementById('breadcrumbPath');

                if (path.length === 0) {
                    breadcrumb.style.display = 'none';
                    return;
                }

                const pathText = path.map((item, index) => {
                    const arrow = index > 0 ? ' → ' : '';
                    return `${arrow}${item.name}`;
                }).join('');

                const familyName = familySelect.options[familySelect.selectedIndex].text;

                breadcrumbPath.innerHTML = `<span style="color: var(--color-info); font-weight: 600;">[${familyName}]</span> ${pathText}`;
                breadcrumb.style.display = 'block';
            }

            function findCategoryById(id) {
                return categoriesData.find(cat => cat.id === id);
            }

            function hasChildren(parentId) {
                return categoriesData.some(cat => cat.parent_id === parentId);
            }

            function getChildren(parentId) {
                return categoriesData.filter(cat => 
                    cat.parent_id === parentId &&
                    cat.id !== currentCategoryId
                );
            }

            // Restaurar selección actual al cargar
            const initialFamilyId = parseInt('{{ old("family_id", $category->family_id) }}');
            const initialParentId = parseInt('{{ old("parent_id", $category->parent_id ?? 0) }}');
            
            if (initialFamilyId) {
                setTimeout(() => {
                    loadCategoriesForFamily(initialFamilyId);
                    
                    // Si hay parent_id, intentar reconstruir la jerarquía
                    if (initialParentId) {
                        setTimeout(() => {
                            reconstructHierarchy(initialParentId);
                        }, 200);
                    }
                }, 100);
            }

            function reconstructHierarchy(targetParentId) {
                // Construir path desde raíz hasta targetParentId
                const path = [];
                let currentId = targetParentId;
                
                while (currentId) {
                    const cat = findCategoryById(currentId);
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
                            handleLevelChange(currentLevel, item.id);
                            currentLevel++;
                        }
                    }, index * 100);
                });
            }

            // ===================================================================
            // MANEJO DE IMAGEN Y SUBMIT LOADER
            // ===================================================================
            document.addEventListener('DOMContentLoaded', function() {
                const imageHandler = initImageUpload({
                    mode: 'edit',
                    hasExistingImage: {{ $category->image && file_exists(public_path('storage/' . $category->image)) ? 'true' : 'false' }},
                    existingImageFilename: '{{ $category->image ? basename($category->image) : '' }}'
                });

                const submitLoader = initSubmitLoader({
                    formId: 'categoryForm',
                    buttonId: 'submitBtn',
                    loadingText: 'Actualizando...'
                });
            });
        </script>

        <!-- ============================
             FOOTER
        ============================= -->
        <div class="form-footer">
            <a href="{{ route('admin.categories.index') }}" class="boton-form boton-volver">
                <span class="boton-form-icon"> <i class="ri-arrow-left-circle-fill"></i> </span>
                <span class="boton-form-text">Cancelar</span>
            </a>

            <button class="boton-form boton-accent" type="submit" id="submitBtn">
                <span class="boton-form-icon"> <i class="ri-loop-left-line"></i> </span>
                <span class="boton-form-text">Actualizar Categoría</span>
            </button>
        </div>
    </form>
</x-admin-layout>
