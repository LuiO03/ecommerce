<x-admin-layout>
    <x-slot name="title">
        Agregar Familia
    </x-slot>
    <x-slot name="action">
        <a href="{{ route('admin.families.index') }}"class="boton-form boton-action">
            <span class="boton-form-icon">
                <i class="ri-arrow-left-circle-fill"></i>
            </span>
            <span class="boton-form-text">Volver</span>
        </a>
    </x-slot>

    <form action="{{ route('admin.families.store') }}" method="POST" enctype="multipart/form-data" class="form-container"
        autocomplete="off" id="familyForm">
        @csrf
        <div class="form-info-banner">
            <i class="ri-lightbulb-line form-info-icon"></i>
            <div>
                <h4 class="form-info-title">Información:</h4>
                <ul>
                    <li>Los campos con asterisco (<i class="ri-asterisk text-accent"></i>) son obligatorios.</li>
                </ul>
            </div>
        </div>
        <div class="form-row">
            <div class="form-column">
                <!-- === Nombre === -->
                <div class="input-group">
                    <label for="name" class="label-form">
                        Nombre de la familia
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-price-tag-3-line input-icon"></i>
                        <input type="text" name="name" id="name" class="input-form" required
                            value="{{ old('name') }}" placeholder="Ingrese el nombre">
                    </div>
                </div>
                <!-- === Estado === -->
                <div class="input-group">
                    <label for="status" class="label-form">
                        Estado de la familia
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-focus-2-line input-icon"></i>
                        <select name="status" id="status" class="select-form" required>
                            <option value="" disabled selected>Seleccione un estado</option>
                            <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                        <i class="ri-arrow-down-s-line select-arrow"></i>
                    </div>
                </div>
                <!-- === Descripción === -->
                <div class="input-group">
                    <label for="description" class="label-form label-textarea">Descripción de la familia</label>
                    <div class="input-icon-container">
                        <textarea name="description" id="description" class="textarea-form" placeholder="Ingrese la descripción" rows="4"
                            required>{{ old('description') }}</textarea>
                        <i class="ri-file-text-line input-icon"></i>
                    </div>
                </div>
            </div>

            <div class="form-column">
                <!-- === Imagen === -->
                <div class="image-upload-section">
                    <label class="label-form">Imagen de la familia</label>
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
                    <!-- Nombre del archivo (temporal) -->
                    <div class="image-filename" id="imageFilename" style="display: none;">
                        <i class="ri-file-image-line"></i>
                        <span id="filenameText"></span>
                    </div>
                </div>
            </div>
        </div>



        <div class="form-footer">
            <a href="{{ route('admin.families.index') }}" class="boton-form boton-volver">
                <span class="boton-form-icon"> <i class="ri-arrow-left-circle-fill"></i> </span>
                <span class="boton-form-text">Cancelar</span>
            </a>
            <!-- boton para limpiar contenido -->
            <button type="reset" class="boton-form boton-warning">
                <span class="boton-form-icon"> <i class="ri-paint-brush-fill"></i> </span>
                <span class="boton-form-text">Limpiar</span>
            </button>

            <button class="boton-form boton-success" type="submit" id="submitBtn">
                <span class="boton-form-icon"> <i class="ri-save-3-fill"></i> </span>
                <span class="boton-form-text">Crear Familia</span>
            </button>
        </div>
    </form>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar manejador de imágenes
            const imageHandler = initImageUpload({
                mode: 'create'
            });

            // Inicializar loading del botón submit
            const submitLoader = initSubmitLoader({
                formId: 'familyForm',
                buttonId: 'submitBtn',
                loadingText: 'Guardando...'
            });
        });
    </script>
    @endpush
</x-admin-layout>
