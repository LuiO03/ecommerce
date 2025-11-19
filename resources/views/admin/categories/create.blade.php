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

        <small class="form-aviso">
            Los campos con asterisco (<i class="ri-asterisk text-accent"></i>) son obligatorios.
        </small>

        <div class="form-row">

            <!-- ============================
                 COLUMNA IZQUIERDA
            ============================= -->
            <div class="form-column">
                <div class="form-inside-row">
                    {{-- FAMILY --}}
                    <div class="input-group">
                        <label for="family_id" class="label-form">
                            Familia
                            <i class="ri-asterisk text-accent"></i>
                        </label>
                        <div class="input-icon-container">
                            <i class="ri-stack-line input-icon"></i>

                            <select name="family_id" id="family_id" class="select-form" required>
                                <option value="" disabled selected>Seleccione una familia</option>

                                @foreach ($families as $family)
                                    <option value="{{ $family->id }}"
                                        {{ old('family_id') == $family->id ? 'selected' : '' }}>
                                        {{ $family->name }}
                                    </option>
                                @endforeach
                            </select>

                            <i class="ri-arrow-down-s-line select-arrow"></i>
                        </div>
                    </div>
                    {{-- PARENT --}}
                    <div class="input-group">
                        <label for="parent_id" class="label-form">
                            Categoría padre
                        </label>
                        <div class="input-icon-container">
                            <i class="ri-git-branch-line input-icon"></i>

                            <select name="parent_id" id="parent_id" class="select-form">
                                <option value="" selected>Sin categoría padre</option>

                                @foreach ($parents as $parent)
                                    <option value="{{ $parent->id }}"
                                        {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                        {{ $parent->name }}
                                    </option>
                                @endforeach
                            </select>

                            <i class="ri-arrow-down-s-line select-arrow"></i>
                        </div>
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

        <!-- ============================
             SCRIPTS DE IMAGEN
        ============================= -->
        <script>
            // === MANEJO DE IMAGEN ===
            const imageInput = document.getElementById('image');
            const imagePreviewZone = document.getElementById('imagePreviewZone');
            const imagePlaceholder = document.getElementById('imagePlaceholder');
            const imagePreview = document.getElementById('imagePreview');
            const imageOverlay = document.getElementById('imageOverlay');
            const changeImageBtn = document.getElementById('changeImageBtn');
            const removeImageBtn = document.getElementById('removeImageBtn');
            const imageFilename = document.getElementById('imageFilename');
            const filenameText = document.getElementById('filenameText');

            // Función para mostrar vista previa
            function showImagePreview(file) {
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imagePreview.src = e.target.result;
                        imagePreview.style.display = 'block';
                        imagePlaceholder.style.display = 'none';
                        imageOverlay.style.display = 'flex';
                        imagePreviewZone.classList.add('has-image');

                        // Mostrar nombre de archivo original
                        filenameText.textContent = file.name;
                        imageFilename.style.display = 'flex';
                    };
                    reader.readAsDataURL(file);
                }
            }

            // Función para limpiar vista previa
            function clearImagePreview() {
                imagePreview.src = '';
                imagePreview.style.display = 'none';
                imagePlaceholder.style.display = 'flex';
                imageOverlay.style.display = 'none';
                imagePreviewZone.classList.remove('has-image');
                imageInput.value = '';
                imageFilename.style.display = 'none';
                filenameText.textContent = '';
            }

            // Botones del overlay
            changeImageBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                imageInput.click();
            });

            removeImageBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                clearImagePreview();
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
            imagePreviewZone.addEventListener('click', () => {
                if (!imagePreviewZone.classList.contains('has-image')) {
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
                btnText.textContent = 'Guardando...';
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
