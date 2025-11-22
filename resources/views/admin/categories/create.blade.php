<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-success"><i class="ri-add-circle-line"></i></div>
        Nueva Categoría
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

        {{-- Banner de errores de backend (solo si JS fue omitido o falló) --}}
        @if ($errors->any())
            <div class="form-error-banner">
                <i class="ri-error-warning-line form-error-icon"></i>
                <div>
                    <h4 class="form-error-title">Se encontraron los siguientes errores:</h4>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <x-alert 
            type="info" 
            title="Guía rápida:" 
            :dismissible="true"
            :items="[
                'Los campos con asterisco (<i class=\'ri-asterisk text-accent\'></i>) son obligatorios',
                'Primero selecciona la <strong>familia</strong> a la que pertenecerá la categoría',
                'Luego elige su ubicación en la jerarquía (opcional - si no eliges nada, será categoría raíz)'
            ]"
        />

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
                        <select name="family_id" id="family_select" class="select-form @error('family_id') input-error @enderror"
                            data-validate="required|selected"
                            data-validate-messages='{"required":"Debe seleccionar una familia","selected":"Debe seleccionar una familia válida"}'>
                            <option value="" disabled selected>Seleccione una familia</option>
                            @foreach ($families as $family)
                                <option value="{{ $family->id }}" {{ old('family_id') == $family->id ? 'selected' : '' }}>
                                    {{ $family->name }}
                                </option>
                            @endforeach
                        </select>
                        <i class="ri-arrow-down-s-line select-arrow"></i>
                    </div>
                    @error('family_id')
                        <span class="input-error-message">
                            <i class="ri-error-warning-fill"></i>
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- JERARQUÍA DE CATEGORÍAS PROGRESIVA --}}
                <div class="input-group">
                    <label class="label-form">
                        Ubicación en la jerarquía <span class="label-italic">(opcional)</span>
                    </label>

                    {{-- Hidden input solo para parent_id --}}
                    <input type="hidden" name="parent_id" id="parent_id" value="{{ old('parent_id') }}">

                    {{-- Contenedor dinámico de selects --}}
                    <div id="categoryHierarchySelects" style="display: none;">
                        {{-- Los selects se generarán dinámicamente según la familia --}}
                    </div>

                    <div id="noFamilyMessage" class="label-hint">
                        <i class="ri-information-line"></i>
                        <span>Primero selecciona una familia para ver las categorías disponibles</span>
                    </div>

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

                        <input type="text" name="name" id="name" class="input-form @error('name') input-error @enderror" 
                            value="{{ old('name') }}" placeholder="Ingrese el nombre"
                            data-validate="required|alphanumeric|min:3|max:100"
                            data-validate-messages='{"required":"El nombre es obligatorio","alphanumeric":"El nombre debe contener al menos una letra","min":"El nombre debe tener al menos 3 caracteres","max":"El nombre no puede exceder 100 caracteres"}'>
                    </div>
                    @error('name')
                        <span class="input-error-message">
                            <i class="ri-error-warning-fill"></i>
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- STATUS --}}
                <div class="input-group">
                    <label for="status" class="label-form">
                        Estado
                        <i class="ri-asterisk text-accent"></i>
                    </label>

                    <div class="input-icon-container">
                        <i class="ri-focus-2-line input-icon"></i>

                        <select name="status" id="status" class="select-form @error('status') input-error @enderror"
                            data-validate="required|selected"
                            data-validate-messages='{"required":"Debe seleccionar un estado","selected":"Debe seleccionar un estado válido"}'>
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
                    @error('status')
                        <span class="input-error-message">
                            <i class="ri-error-warning-fill"></i>
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- DESCRIPTION --}}
                <div class="input-group">
                    <label for="description" class="label-form label-textarea">
                        Descripción
                    </label>

                    <div class="input-icon-container">
                        <textarea name="description" id="description" class="textarea-form" placeholder="Ingrese la descripción" rows="4" data-validate="min:10|max:250">{{ old('description') }}</textarea>

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

                    <input type="file" name="image" id="image" class="file-input" accept="image/*"
                        data-validate="image|maxSizeMB:3">

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
                // 1. Inicializar submit loader PRIMERO
                const submitLoader = initSubmitLoader({
                    formId: 'categoryForm',
                    buttonId: 'submitBtn',
                    loadingText: 'Guardando...'
                });

                // 2. Inicializar validación de formulario DESPUÉS
                const formValidator = initFormValidator('#categoryForm', {
                    validateOnBlur: true,
                    validateOnInput: false,
                    scrollToFirstError: true
                });

                // 3. Inicializar jerarquía de categorías
                const hierarchyManager = initCategoryHierarchy({
                    categoriesData: {!! json_encode($allCategories) !!},
                    initialFamilyId: '{{ old("family_id") }}' ? parseInt('{{ old("family_id") }}') : null,
                    initialParentId: '{{ old("parent_id") }}' ? parseInt('{{ old("parent_id") }}') : null
                });

                // 4. Inicializar manejador de imágenes
                const imageHandler = initImageUpload({
                    mode: 'create'
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
