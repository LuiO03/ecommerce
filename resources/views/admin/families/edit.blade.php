<x-admin-layout>
    <x-slot name="title">
        Editar {{ $family->name }}
    </x-slot>
    <x-slot name="action">
        <a href="{{ route('admin.families.index') }}"class="boton-form boton-action">
            <span class="boton-form-icon">
                <i class="ri-arrow-left-circle-fill"></i>
            </span>
            <span class="boton-form-text">Volver</span>
        </a>
        <form action="{{ route('admin.families.destroy', $family) }}" method="POST" class="delete-form"
            data-entity="familia" style="margin: 0;">
            @csrf
            @method('DELETE')
            <button class="boton boton-danger" type="submit">
                <span class="boton-icon"><i class="ri-delete-bin-6-fill"></i></span>
                <span class="boton-text">Eliminar</span>
            </button>
        </form>
    </x-slot>

    <!-- === FORMULARIO DE ACTUALIZACIÓN === -->
    <form action="{{ route('admin.families.update', $family) }}" method="POST" enctype="multipart/form-data"
        class="form-container" autocomplete="off" id="familyForm">
        @csrf
        @method('PUT')

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
                        <input type="text" name="name" id="name" class="input-form"
                            placeholder="Ingrese el nombre" required value="{{ old('name', $family->name) }}">
                    </div>
                </div>

                <!-- === Estado === -->
                <div class="input-group select-group">
                    <label for="status" class="label-form">
                        Estado de la familia
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-focus-2-line input-icon"></i>
                        <select name="status" id="status" class="select-form" required>
                            <option value="" disabled hidden></option>
                            <option value="1" {{ old('status', $family->status) == '1' ? 'selected' : '' }}>Activo
                            </option>
                            <option value="0" {{ old('status', $family->status) == '0' ? 'selected' : '' }}>
                                Inactivo
                            </option>
                        </select>
                        <i class="ri-arrow-down-s-line select-arrow"></i>
                    </div>
                </div>

                <!-- === Descripción === -->
                <div class="input-group">
                    <label for="description" class="label-form label-textarea">Descripción de la familia</label>
                    <div class="input-icon-container">
                        <i class="ri-file-text-line input-icon"></i>
                        <textarea name="description" id="description" class="textarea-form" placeholder="Ingrese la descripción" rows="4"
                            required>{{ old('description', $family->description) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="form-column">
                <!-- === Imagen === -->
                <div class="image-upload-section">
                    <label class="label-form">Imagen de la familia</label>
                    <input type="file" name="image" id="image" class="file-input" accept="image/*">
                    <input type="hidden" name="remove_image" id="removeImageFlag" value="0">

                    <!-- Zona de vista previa -->
                    <div class="image-preview-zone {{ $family->image && file_exists(public_path('storage/' . $family->image)) ? 'has-image' : '' }}"
                        id="imagePreviewZone">
                        @if ($family->image && file_exists(public_path('storage/' . $family->image)))
                            <img id="imagePreview" class="image-preview image-pulse"
                                src="{{ asset('storage/' . $family->image) }}" alt="{{ $family->name }}">
                            <!-- Placeholder oculto inicialmente (se mostrará al eliminar) -->
                            <div class="image-placeholder" id="imagePlaceholder" style="display: none;">
                                <i class="ri-image-add-line"></i>
                                <p>Arrastra una imagen aquí</p>
                                <span>o haz clic para seleccionar</span>
                            </div>
                        @elseif($family->image)
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

                    <!-- Nombre del archivo -->
                    <div class="image-filename" id="imageFilename"
                        style="{{ $family->image && file_exists(public_path('storage/' . $family->image)) ? 'display: flex;' : 'display: none;' }}">
                        <i class="ri-file-image-line"></i>
                        <span id="filenameText">{{ $family->image ? basename($family->image) : '' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- === FOOTER DE ACCIONES === -->
        <div class="form-footer">
            <a href="{{ route('admin.families.index') }}" class="boton-form boton-volver">
                <span class="boton-form-icon"><i class="ri-arrow-left-circle-fill"></i></span>
                <span class="boton-form-text">Cancelar</span>
            </a>

            <button class="boton-form boton-accent" type="submit" id="submitBtn">
                <span class="boton-form-icon"><i class="ri-loop-left-line"></i></span>
                <span class="boton-form-text">Actualizar Familia</span>
            </button>
        </div>
    </form>
    <script>
        // Inicializar manejador de imágenes
        const imageHandler = initImageUpload({
            mode: 'edit',
            hasExistingImage: {{ $family->image && file_exists(public_path('storage/' . $family->image)) ? 'true' : 'false' }},
            existingImageFilename: '{{ $family->image ? basename($family->image) : '' }}'
        });

        // Animación de loading en el botón submit
        document.getElementById('familyForm').addEventListener('submit', function() {
            const submitBtn = document.getElementById('submitBtn');
            const btnIcon = submitBtn.querySelector('.boton-form-icon i');
            const btnText = submitBtn.querySelector('.boton-form-text');

            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.7';
            submitBtn.style.cursor = 'not-allowed';

            btnIcon.className = 'ri-loader-4-line';
            btnIcon.style.animation = 'spin 1s linear infinite';
            btnText.textContent = 'Actualizando...';
        });
    </script>
</x-admin-layout>
