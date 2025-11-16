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

        <small class="form-aviso">
            Los campos con asterisco (<i class="ri-asterisk text-accent"></i>) son obligatorios.
        </small>

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
        // === MANEJO DE IMAGEN ===
        const imageInput = document.getElementById('image');
        const imagePreviewZone = document.getElementById('imagePreviewZone');
        const imagePlaceholder = document.getElementById('imagePlaceholder');
        const imageError = document.getElementById('imageError');
        const imagePreview = document.getElementById('imagePreview');
        const imagePreviewNew = document.getElementById('imagePreviewNew');
        const imageOverlay = document.getElementById('imageOverlay');
        const changeImageBtn = document.getElementById('changeImageBtn');
        const removeImageBtn = document.getElementById('removeImageBtn');
        const imageFilename = document.getElementById('imageFilename');
        const filenameText = document.getElementById('filenameText');
        const removeFlag = document.getElementById('removeImageFlag');

        const hasExistingImage =
            {{ $family->image && file_exists(public_path('storage/' . $family->image)) ? 'true' : 'false' }};

        // Mostrar overlay si hay imagen existente
        if (hasExistingImage) {
            imageOverlay.style.display = 'flex';
        }

        // Función para mostrar vista previa de nueva imagen
        function showImagePreview(file) {
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Ocultar elementos anteriores
                    if (imagePreview) imagePreview.style.display = 'none';
                    if (imagePlaceholder) imagePlaceholder.style.display = 'none';
                    if (imageError) imageError.style.display = 'none';

                    // Mostrar nueva imagen
                    imagePreviewNew.src = e.target.result;
                    imagePreviewNew.style.display = 'block';
                    imageOverlay.style.display = 'flex';
                    imagePreviewZone.classList.add('has-image');

                    // Restaurar flag de eliminación
                    removeFlag.value = '0';

                    // Mostrar nombre de archivo original
                    filenameText.textContent = file.name;
                    imageFilename.style.display = 'flex';
                };
                reader.readAsDataURL(file);
            }
        }

        // Función para limpiar nueva imagen
        function clearImagePreview() {
            imagePreviewNew.src = '';
            imagePreviewNew.style.display = 'none';
            imageInput.value = '';

            // Restaurar flag de eliminación
            removeFlag.value = '0';

            // Restaurar estado original
            if (hasExistingImage && imagePreview) {
                imagePreview.style.display = 'block';
                imageOverlay.style.display = 'flex';
                imagePreviewZone.classList.add('has-image');
                // Restaurar nombre de archivo original
                filenameText.textContent = '{{ $family->image ? basename($family->image) : '' }}';
                imageFilename.style.display = 'flex';
            } else if (imageError) {
                imageError.style.display = 'flex';
                imageOverlay.style.display = 'none';
                imagePreviewZone.classList.remove('has-image');
                imageFilename.style.display = 'none';
            } else if (imagePlaceholder) {
                imagePlaceholder.style.display = 'flex';
                imageOverlay.style.display = 'none';
                imagePreviewZone.classList.remove('has-image');
                imageFilename.style.display = 'none';
            }
        }

        // Función para eliminar imagen (existente o nueva)
        function removeImage() {
            // Limpiar input y vistas previas
            imageInput.value = '';
            imagePreviewNew.src = '';
            imagePreviewNew.style.display = 'none';
            
            if (imagePreview) imagePreview.style.display = 'none';
            
            // Activar flag de eliminación si hay imagen existente
            if (hasExistingImage) {
                removeFlag.value = '1';
            }
            
            // Ocultar overlay y mostrar placeholder
            imageOverlay.style.display = 'none';
            imagePreviewZone.classList.remove('has-image');
            imageFilename.style.display = 'none';
            
            // Mostrar placeholder o error según corresponda
            if (imageError) {
                imageError.style.display = 'flex';
            } else if (imagePlaceholder) {
                imagePlaceholder.style.display = 'flex';
            }
        }

        // Botón cambiar imagen
        changeImageBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            imageInput.click();
        });

        // Botón eliminar imagen
        removeImageBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            removeImage();
        });

        // Cambio de archivo
        imageInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                showImagePreview(this.files[0]);
            }
        });

        // Drag and drop
        imagePreviewZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            imagePreviewZone.classList.add('drag-over');
        });

        imagePreviewZone.addEventListener('dragleave', () => {
            imagePreviewZone.classList.remove('drag-over');
        });

        imagePreviewZone.addEventListener('drop', (e) => {
            e.preventDefault();
            imagePreviewZone.classList.remove('drag-over');

            const files = e.dataTransfer.files;
            if (files.length > 0 && files[0].type.startsWith('image/')) {
                imageInput.files = files;
                showImagePreview(files[0]);
            }
        });

        // Click en zona vacía para subir
        imagePreviewZone.addEventListener('click', (e) => {
            // Solo si no tiene imagen o es placeholder/error
            if (!imagePreviewZone.classList.contains('has-image') || imageError || imagePlaceholder) {
                imageInput.click();
            }
        });

        // Animación de loading en el botón submit
        document.getElementById('familyForm').addEventListener('submit', function() {
            const submitBtn = document.getElementById('submitBtn');
            const btnIcon = submitBtn.querySelector('.boton-form-icon i');
            const btnText = submitBtn.querySelector('.boton-form-text');

            // Deshabilitar botón
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.7';
            submitBtn.style.cursor = 'not-allowed';

            // Cambiar icono a loading
            btnIcon.className = 'ri-loader-4-line';
            btnIcon.style.animation = 'spin 1s linear infinite';
            btnText.textContent = 'Actualizando...';
        });
    </script>
</x-admin-layout>
