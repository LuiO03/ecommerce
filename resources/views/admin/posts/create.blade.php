@section('title', 'Crear post')

<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-success"><i class="ri-add-large-line"></i></div>
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

        <div class="form-body">
            <div class="form-row-fit">
                <!-- === Imágenes múltiples === -->
                <div class="image-upload-section">
                    <label class="label-form">Imágenes del post</label>
                    <div class="custom-dropzone" id="customDropzone">
                        <i class="ri-multi-image-line"></i>
                        <p>Arrastra imágenes aquí o haz clic</p>
                        <input type="file" name="images[]" id="imageInput" accept="image/*" multiple hidden
                            data-validate="fileRequired|image|maxSizeMB:3|fileTypes:jpg,png,gif,webp|maxFiles:10">
                    </div>
                    <div id="previewContainer" class="preview-container"></div>
                    <input type="hidden" name="primary_image" id="primaryImageInput" value="">
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        if (window.initGalleryCreateWithConfig) {
                            window.initGalleryCreateWithConfig({
                                dropzoneId: 'customDropzone',
                                inputId: 'imageInput',
                                previewContainerId: 'previewContainer',
                                primaryInputId: 'primaryImageInput',
                                formId: 'postForm',
                                labels: {
                                    markTitle: 'Marcar como portada del post',
                                    markText: 'Portada',
                                    markIconClass: 'ri-gallery-line',
                                    badgeIconClass: 'ri-gallery-fill',
                                    badgeText: 'Portada',
                                    deleteTitle: 'Eliminar imagen',
                                    deleteIconClass: 'ri-delete-bin-6-fill',
                                    deleteText: 'Eliminar'
                                }
                            });
                        }
                    });
                </script>
            </div>
        </div>
        <div class="form-body">
            <div class="form-row-fit">
                <!-- === Título === -->
                <div class="input-group">
                    <label for="title" class="label-form">
                        Título
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-text input-icon"></i>
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
                    @can('posts.review')
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
                    @else
                        <div class="binary-switch">
                            <input type="radio" name="status" id="statusPending" value="pending"
                                class="switch-input switch-input-on" {{ old('status', 'pending') === 'pending' ? 'checked' : '' }}>
                            <input type="radio" name="status" id="statusDraft" value="draft"
                                class="switch-input switch-input-off" {{ old('status') === 'draft' ? 'checked' : '' }}>

                            <div class="switch-slider"></div>

                            <label for="statusPending" class="switch-label switch-label-on">
                                <i class="ri-checkbox-circle-line"></i>
                                Pendiente
                            </label>
                            <label for="statusDraft" class="switch-label switch-label-off">
                                <i class="ri-close-circle-line"></i>
                                Borrador
                            </label>
                        </div>
                    @endcan

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
                            class="switch-input switch-input-on" {{ old('allow_comments', 1) == 1 ? 'checked' : '' }}>
                        <input type="radio" name="allow_comments" id="allowNo" value="0"
                            class="switch-input switch-input-off"
                            {{ old('allow_comments', 1) == 0 ? 'checked' : '' }}>

                        <div class="switch-slider"></div>

                        <label for="allowYes" class="switch-label switch-label-on"><i
                                class="ri-checkbox-circle-line"></i>
                            Sí</label>
                        <label for="allowNo" class="switch-label switch-label-off"><i
                                class="ri-close-circle-line"></i>
                            No</label>
                    </div>
                </div>
            </div>

            <div class="form-row-fit">
                <!-- === Contenido === -->
                <div class="input-group">
                    <label for="content" class="label-form">
                        Contenido
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <textarea name="content" id="content" class="textarea-form-post" rows="8"
                        placeholder="Ingrese el contenido del post" data-validate="requiredText|minText:10">{{ old('content') }}</textarea>
                </div>
                <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
                <script>
                    let editorInstance;
                    document.addEventListener("DOMContentLoaded", () => {
                        ClassicEditor.create(document.querySelector('#content'), {
                                toolbar: [
                                    'undo', 'redo',
                                    'heading',
                                    'bold', 'italic', 'underline', 'strikethrough',
                                    'blockQuote',
                                    'bulletedList', 'numberedList',
                                    'link',
                                    'insertTable',
                                ],
                                table: {
                                    contentToolbar: [
                                        'tableColumn', 'tableRow', 'mergeTableCells'
                                    ]
                                }
                            })
                            .then(editor => {
                                editorInstance = editor;
                                window.editorInstance = editor;
                                // Registrar instancia global por id para soporte multi-editor
                                window._ckEditors = window._ckEditors || {};
                                const ta = document.querySelector('#content');
                                if (ta) {
                                    window._ckEditors[ta.id] = editor;
                                }
                            })
                            .catch(error => console.error(error));
                    });
                    // Sincronizar contenido antes de enviar
                    document.getElementById('postForm').addEventListener('submit', function() {
                        if (editorInstance) {
                            document.querySelector('#content').value = editorInstance.getData();
                        }
                    });
                </script>
            </div>

            <div class="form-row-fill">
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
            </div>
        </div>
        <div class="form-footer">
            <a href="{{ url()->previous() }}" class="boton-form boton-volver">
                <span class="boton-form-icon">
                    <i class="ri-arrow-left-circle-fill"></i>
                </span>
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

                // Inicializar validación de formulario y enlazar con CKEditor
                const formValidator = initFormValidator('#postForm', {
                    validateOnBlur: true,
                    validateOnInput: false,
                    scrollToFirstError: true
                });
                window.postFormValidator = formValidator;

                if (window.editorInstance) {
                    const textarea = document.querySelector('#content');
                    const editor = window.editorInstance;
                    // Solo sincroniza el contenido en cambios, sin validar en vivo
                    editor.model.document.on('change:data', () => {
                        textarea.value = editor.getData();
                    });
                    // Valida únicamente cuando el editor pierde el foco
                    editor.editing.view.document.on('blur', () => {
                        // Sincronizar antes de validar para capturar vacío
                        textarea.value = editor.getData();
                        const isValid = formValidator.validateField(textarea);
                        const group = textarea.closest('.input-group');
                        const editable = group ? group.querySelector('.ck-editor__editable') : null;
                        if (editable) {
                            editable.classList.toggle('input-success', isValid);
                            editable.classList.toggle('input-error', !isValid);
                        }
                        // Asegurar mensaje de error inline si es inválido
                        if (!isValid && group) {
                            let errorEl = group.querySelector('.input-error-message');
                            if (!errorEl) {
                                errorEl = document.createElement('div');
                                errorEl.className = 'input-error-message';
                                errorEl.innerHTML =
                                    '<i class="ri-error-warning-line"></i> <span class="error-text"></span>';
                                group.appendChild(errorEl);
                            }
                            const textEl = errorEl.querySelector('.error-text');
                            if (textEl) {
                                // Determinar mensaje según contenido (vacío vs. insuficiente)
                                const div = document.createElement('div');
                                div.innerHTML = textarea.value || '';
                                const plain = (div.textContent || div.innerText || '').replace(/\u00A0|&nbsp;/g,
                                    ' ').trim();
                                textEl.textContent = plain.length === 0 ? 'Este campo es obligatorio' :
                                    'Debe tener al menos 10 caracteres';
                            }
                            errorEl.style.display = 'flex';
                        }
                    });
                }
            });
        </script>
    @endpush
</x-admin-layout>
