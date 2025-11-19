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
                <h4 class="form-info-title">Guía rápida:</h4>
                <ul>
                    <li>Los campos con asterisco (<i class="ri-asterisk text-accent"></i>) son obligatorios</li>
                    <li>Primero selecciona la <strong>familia</strong> a la que pertenecerá la categoría</li>
                    <li>Luego elige su ubicación en la jerarquía (opcional - si no eliges nada, será categoría raíz)</li>
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
            // SISTEMA DE JERARQUÍA DE CATEGORÍAS PROGRESIVA
            document.addEventListener('DOMContentLoaded', function() {
                // Inicializar jerarquía de categorías
                const hierarchyManager = initCategoryHierarchy({
                    categoriesData: {!! json_encode($allCategories) !!},
                    initialFamilyId: '{{ old("family_id") }}' ? parseInt('{{ old("family_id") }}') : null,
                    initialParentId: '{{ old("parent_id") }}' ? parseInt('{{ old("parent_id") }}') : null
                });

                // Inicializar manejador de imágenes
                const imageHandler = initImageUpload({
                    mode: 'create'
                });

                // Inicializar loading del botón submit
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
