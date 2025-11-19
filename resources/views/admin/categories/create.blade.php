<x-admin-layout>
    <x-slot name="title">
        Agregar Categoría
    </x-slot>

    <x-slot name="action">
        <a href="{{ route('admin.categories.index') }}" class="boton-form boton-action">
            <span class="boton-form-icon">
                <i class="ri-arrow-left-circle-fill"></i>
            </span>
            <span class="boton-form-text">Volver</span>
        </a>
    </x-slot>

    <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data"
        class="form-container" autocomplete="off" id="categoryForm">
        @csrf
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
                            <option value="" disabled selected>Seleccione una familia</option>
                            @foreach ($families as $family)
                                <option value="{{ $family->id }}" {{ old('family_id') == $family->id ? 'selected' : '' }}>
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
                    <input type="hidden" name="parent_id" id="parent_id" value="{{ old('parent_id') }}">

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
                            value="{{ old('name') }}" placeholder="Ingrese el nombre">
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
                            <option value="" disabled selected>Seleccione un estado</option>

                            <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>
                                Activo
                            </option>

                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>
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
                        <textarea name="description" id="description" class="textarea-form" placeholder="Ingrese la descripción" rows="4">{{ old('description') }}</textarea>

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

                    <!-- Zona de vista previa -->
                    <div class="image-preview-zone" id="imagePreviewZone">
                        <div class="image-placeholder" id="imagePlaceholder">
                            <i class="ri-image-add-line"></i>
                            <p>Arrastra una imagen aquí</p>
                            <span>o haz clic para seleccionar</span>
                        </div>

                        <img id="imagePreview" class="image-preview image-pulse" style="display: none;"
                            alt="Vista previa">

                        <div class="image-overlay" id="imageOverlay" style="display: none;">
                            <button type="button" class="overlay-btn" id="changeImageBtn">
                                <i class="ri-upload-2-line"></i>
                                <span>Cambiar</span>
                            </button>

                            <button type="button" class="overlay-btn overlay-btn-danger" id="removeImageBtn">
                                <i class="ri-delete-bin-line"></i>
                                <span>Eliminar</span>
                            </button>
                        </div>
                    </div>

                    <div class="image-filename" id="imageFilename" style="display: none;">
                        <i class="ri-file-image-line"></i>
                        <span id="filenameText"></span>
                    </div>
                </div>

            </div>
        </div>

        <script>
            // SISTEMA DE JERARQUÍA DE CATEGORÍAS PROGRESIVA (CASCADING SELECTS)
            const categoriesData = {!! json_encode($allCategories) !!};
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
                
                // Filtrar categorías raíz de esta familia
                const rootCategories = categoriesData.filter(cat => 
                    cat.family_id === familyId && cat.parent_id === null
                );

                // Limpiar container
                categoryContainer.innerHTML = '';
                selectedPath = [];
                parentIdInput.value = '';

                if (rootCategories.length === 0) {
                    // No hay categorías, será raíz de esta familia
                    noFamilyMessage.style.display = 'flex';
                    noFamilyMessage.innerHTML = '<i class="ri-information-line"></i> No hay categorías en esta familia. Se creará como categoría raíz.';
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
                    `<option value="${cat.id}" data-has-children="${cat.children && cat.children.length > 0}">${cat.name}</option>`
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
                if (category.children && category.children.length > 0) {
                    createLevelSelect(level + 1, category.children, category.name);
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

            function findCategoryById(id, categories = categoriesData) {
                for (let category of categories) {
                    if (category.id === id) return category;
                    if (category.children && category.children.length > 0) {
                        const found = findCategoryById(id, category.children);
                        if (found) return found;
                    }
                }
                return null;
            }

            // Restaurar old() si existe
            const oldFamilyId = '{{ old("family_id") }}';
            const oldParentId = '{{ old("parent_id") }}';
            if (oldFamilyId) {
                setTimeout(() => loadCategoriesForFamily(parseInt(oldFamilyId)), 100);
            }

            // ===================================================================
            // MANEJO DE IMAGEN Y SUBMIT LOADER
            // ===================================================================
            document.addEventListener('DOMContentLoaded', function() {
                const imageHandler = initImageUpload({
                    mode: 'create'
                });

                const submitLoader = initSubmitLoader({
                    formId: 'categoryForm',
                    buttonId: 'submitBtn',
                    loadingText: 'Guardando...'
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

            <button type="reset" class="boton-form boton-warning">
                <span class="boton-form-icon"> <i class="ri-paint-brush-fill"></i> </span>
                <span class="boton-form-text">Limpiar</span>
            </button>

            <button class="boton-form boton-success" type="submit" id="submitBtn">
                <span class="boton-form-icon"> <i class="ri-save-3-fill"></i> </span>
                <span class="boton-form-text">Crear Categoría</span>
            </button>
        </div>
    </form>
</x-admin-layout>
