<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-success"><i class="ri-add-circle-line"></i></div>
        Crear Post
    </x-slot>
    <x-slot name="action">
        <a href="{{ route('admin.posts.index') }}" class="boton boton-secondary">
            <span class="boton-icon"><i class="ri-arrow-go-back-line"></i></span>
            <span class="boton-text">Volver</span>
        </a>
    </x-slot>

    <form action="{{ route('admin.posts.store') }}" method="POST" enctype="multipart/form-data" class="form-container"
        autocomplete="off" id="postForm">
        @csrf

        {{-- Banner de errores --}}
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

        <x-alert type="info" title="Información:" :dismissible="true" :items="['Los campos con asterisco (<i class=\'ri-asterisk text-accent\'></i>) son obligatorios.']" />

        <div class="form-row">
            <!-- === Título === -->
            <div class="input-group">
                <label for="title" class="label-form">
                    Título
                    <i class="ri-asterisk text-accent"></i>
                </label>
                <div class="input-icon-container">
                    <i class="ri-file-text-line input-icon"></i>
                    <input type="text" name="title" id="title" class="input-form" required
                        value="{{ old('title') }}" placeholder="Ingrese el título del post"
                        data-validate="required|min:3|max:255">
                </div>
            </div>
            <!-- === Estado === -->
            <div class="input-group">
                <label for="status" class="label-form">
                    Estado
                    <i class="ri-asterisk text-accent"></i>
                </label>
                <div class="input-icon-container">
                    <i class="ri-focus-2-line input-icon"></i>
                    <select name="status" id="status" class="select-form" required
                        data-validate="required|selected">
                        <option value="" disabled selected>Seleccione un estado</option>
                        <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Borrador</option>
                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pendiente
                        </option>
                        <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Publicado
                        </option>
                        <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>Rechazado
                        </option>
                    </select>
                    <i class="ri-arrow-down-s-line select-arrow"></i>
                </div>
            </div>

            <!-- === Visibilidad === -->
            <div class="input-group">
                <label for="visibility" class="label-form">
                    Visibilidad
                    <i class="ri-asterisk text-accent"></i>
                </label>
                <div class="input-icon-container">
                    <i class="ri-eye-line input-icon"></i>
                    <select name="visibility" id="visibility" class="select-form" required
                        data-validate="required|selected">
                        <option value="" disabled selected>Seleccione visibilidad</option>
                        <option value="public" {{ old('visibility') == 'public' ? 'selected' : '' }}>Público
                        </option>
                        <option value="private" {{ old('visibility') == 'private' ? 'selected' : '' }}>Privado
                        </option>
                        <option value="registered" {{ old('visibility') == 'registered' ? 'selected' : '' }}>
                            Registrado</option>
                    </select>
                    <i class="ri-arrow-down-s-line select-arrow"></i>
                </div>
            </div>
            <!-- === Permitir comentarios === -->
            <div class="input-group">
                <label class="label-form">Permitir comentarios</label>
                <div class="binary-switch">
                    <input type="radio" name="allow_comments" id="allowYes" value="1"
                        {{ old('allow_comments', 1) == 1 ? 'checked' : '' }}>
                    <input type="radio" name="allow_comments" id="allowNo" value="0"
                        {{ old('allow_comments', 1) == 0 ? 'checked' : '' }}>

                    <div class="switch-slider"></div>

                    <label for="allowYes" class="switch-label">Sí</label>
                    <label for="allowNo" class="switch-label">No</label>
                </div>
            </div>
        </div>
        <div class="form-row">
            <!-- === Contenido === -->
            <div class="input-group">
                <label for="content" class="label-form">
                    Contenido
                    <i class="ri-asterisk text-accent"></i>
                </label>
                <textarea name="content" id="content" class="textarea-form-post" rows="8" required
                    placeholder="Ingrese el contenido del post" data-validate="required|min:10">{{ old('content') }}</textarea>
            </div>
            <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
            <script>
                document.addEventListener("DOMContentLoaded", () => {
                    ClassicEditor
                        .create(document.querySelector('#content'), {
                            toolbar: [
                                'undo', 'redo',
                                '|',
                                'heading',
                                '|',
                                'bold', 'italic', 'underline', 'strikethrough',
                                '|',
                                'blockQuote',
                                'bulletedList', 'numberedList',
                                '|',
                                'link',
                                'insertTable',
                            ],
                            table: {
                                contentToolbar: [
                                    'tableColumn', 'tableRow', 'mergeTableCells'
                                ]
                            }
                        })
                        .catch(error => console.error(error));
                });
            </script>

        </div>
        <div class="form-columns-row">
            <div class="form-column">
                <div class="input-group">
                    <label class="label-form">Tags</label>

                    <div class="input-icon-container">
                        <i class="ri-focus-2-line input-icon"></i>

                        <select id="tagSelect" class="select-form">
                            <option value="">Selecciona un tag</option>

                            @foreach ($tags as $tag)
                                <option value="{{ $tag->id }}"
                                    {{ collect(old('tags'))->contains($tag->id) ? 'selected' : '' }}>
                                    {{ $tag->name }}
                                </option>
                            @endforeach
                        </select>

                        <i class="ri-arrow-down-s-line select-arrow"></i>
                    </div>

                    <!-- Contenedor donde aparecerán los tags seleccionados -->
                    <div id="tagContainer" class="tag-container"></div>

                    <!-- Inputs ocultos para enviar al backend -->
                    <div id="tagHiddenInputs"></div>
                </div>
            </div>
            <script>
                document.addEventListener("DOMContentLoaded", () => {

                    const select = document.getElementById("tagSelect");
                    const tagContainer = document.getElementById("tagContainer");
                    const hiddenInputs = document.getElementById("tagHiddenInputs");

                    // Set para evitar duplicados
                    const selectedTags = new Set();

                    // Si viene con old('tags'), agregarlos automáticamente
                    @if (old('tags'))
                        @foreach (old('tags') as $oldTag)
                            selectedTags.add("{{ $oldTag }}");
                        @endforeach
                    @endif

                    // Inicializar pills de old()
                    @foreach ($tags as $tag)
                        @if (collect(old('tags'))->contains($tag->id))
                            addTagPill("{{ $tag->id }}", "{{ $tag->name }}");
                            addHiddenInput("{{ $tag->id }}");
                        @endif
                    @endforeach

                    // Evento al seleccionar un tag
                    select.addEventListener("change", () => {
                        const tagId = select.value;
                        const tagName = select.options[select.selectedIndex].text;

                        if (!tagId || selectedTags.has(tagId)) return;

                        selectedTags.add(tagId);

                        addTagPill(tagId, tagName);
                        addHiddenInput(tagId);
                    });

                    // Crear la cápsula del tag
                    function addTagPill(id, name) {
                        const pill = document.createElement("div");
                        pill.classList.add("tag-pill");
                        pill.setAttribute("data-id", id);

                        pill.innerHTML = `
                            ${name}
                            <i class="ri-close-line remove-tag"></i>
                        `;

                        // Evento para eliminar el tag
                        pill.querySelector(".remove-tag").addEventListener("click", () => {
                            selectedTags.delete(id);
                            pill.remove();
                            document.getElementById("tag-hidden-" + id)?.remove();
                        });

                        tagContainer.appendChild(pill);
                    }

                    // Crear input hidden para enviar al backend
                    function addHiddenInput(id) {
                        const input = document.createElement("input");
                        input.type = "hidden";
                        input.name = "tags[]";
                        input.value = id;
                        input.id = "tag-hidden-" + id;
                        hiddenInputs.appendChild(input);
                    }
                });
            </script>

            <div class="form-column">
                <!-- === Imagen principal === -->
                <div class="image-upload-section">
                    <label class="label-form">Imagen destacada</label>
                    <input type="file" name="image" id="image" class="file-input" accept="image/*"
                        data-validate="image|maxSizeMB:3">

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
                </div>

                <!-- === Imágenes múltiples === -->
                <div class="image-upload-section">
                    <label class="label-form">Galería de imágenes</label>
                    <input type="file" name="images[]" id="images" class="file-input" accept="image/*"
                        multiple data-validate="image|maxSizeMB:3">
                    <small>Puede subir varias imágenes</small>
                </div>
            </div>
        </div>

        <div class="form-footer">
            <a href="{{ route('admin.posts.index') }}" class="boton-form boton-volver">
                <span class="boton-form-icon"><i class="ri-arrow-left-circle-fill"></i></span>
                <span class="boton-form-text">Cancelar</span>
            </a>
            <button type="reset" class="boton-form boton-warning">
                <span class="boton-form-icon"><i class="ri-paint-brush-fill"></i></span>
                <span class="boton-form-text">Limpiar</span>
            </button>
            <button type="submit" class="boton-form boton-success" id="submitBtn">
                <span class="boton-form-icon"><i class="ri-save-3-fill"></i></span>
                <span class="boton-form-text">Crear Post</span>
            </button>
        </div>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Inicializar manejador de imágenes
                initImageUpload({
                    mode: 'create'
                });

                // Inicializar loader de submit
                initSubmitLoader({
                    formId: 'postForm',
                    buttonId: 'submitBtn',
                    loadingText: 'Guardando...'
                });

                // Inicializar validación de formulario
                initFormValidator('#postForm', {
                    validateOnBlur: true,
                    validateOnInput: false,
                    scrollToFirstError: true
                });
            });
        </script>
    @endpush
</x-admin-layout>
