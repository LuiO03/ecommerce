<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-warning"><i class="ri-edit-circle-line"></i></div>
        Editar Post
    </x-slot>

    <x-slot name="action">
        <a href="{{ route('admin.posts.index') }}" class="boton boton-secondary">
            <span class="boton-icon"><i class="ri-arrow-go-back-line"></i></span>
            <span class="boton-text">Volver</span>
        </a>
    </x-slot>

    @php
        $mainImagePath = $post->main_image_path;
        $hasExistingImage = $mainImagePath && file_exists(public_path('storage/' . $mainImagePath));
    @endphp

    <form action="{{ route('admin.posts.update', $post->slug) }}" method="POST" enctype="multipart/form-data"
        class="form-container" autocomplete="off" id="postForm">
        @csrf
        @method('PUT')

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

        <!-- ================= IMAGEN PRINCIPAL ================= -->
        <div class="form-row-fit">
            <!-- === Imagen === -->
            <div class="image-upload-section">
                <label class="label-form">Portada del post</label>
                <input type="file" name="image" id="image" class="file-input" accept="image/*"
                    data-validate="{{ $hasExistingImage ? 'image|maxSizeMB:2' : 'required|image|maxSizeMB:2' }}">
                <input type="hidden" name="remove_image" id="removeImageFlag" value="0">

                <!-- Zona de vista previa -->
                <div class="image-preview-zone {{ $hasExistingImage ? 'has-image' : '' }}"
                    id="imagePreviewZone">
                    @if ($hasExistingImage)
                        <img id="imagePreview" class="image-preview image-pulse"
                            src="{{ asset('storage/' . $mainImagePath) }}" alt="{{ $post->title }}">
                        <!-- Placeholder oculto inicialmente (se mostrará al eliminar) -->
                        <div class="image-placeholder" id="imagePlaceholder" style="display: none;">
                            <i class="ri-image-add-line"></i>
                            <p>Arrastra una imagen aquí</p>
                            <span>o haz clic para seleccionar</span>
                        </div>
                    @elseif($mainImagePath)
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
                    style="{{ $hasExistingImage ? 'display: flex;' : 'display: none;' }}">
                    <i class="ri-file-image-line"></i>
                    <span id="filenameText">{{ $mainImagePath ? basename($mainImagePath) : '' }}</span>
                </div>
            </div>
        </div>

        <!-- ================= CAMPOS PRINCIPALES ================= -->
        <div class="form-row-fill">
            <!-- Título -->
            <div class="input-group">
                <label for="title" class="label-form">
                    Título <i class="ri-asterisk text-accent"></i>
                </label>
                <div class="input-icon-container">
                    <i class="ri-file-text-line input-icon"></i>
                    <input type="text" name="title" id="title" class="input-form"
                        value="{{ old('title', $post->title) }}" data-validate="required|min:3|max:255">
                </div>
            </div>

            <!-- Estado -->
            <div class="input-group">
                <label class="label-form">Estado <i class="ri-asterisk text-accent"></i></label>
                <div class="input-icon-container">
                    <i class="ri-focus-2-line input-icon"></i>
                    <select name="status" class="select-form" data-validate="required|selected">
                        <option value="" disabled>Seleccione un estado</option>
                        <option value="draft" {{ $post->status == 'draft' ? 'selected' : '' }}>Borrador</option>
                        <option value="pending" {{ $post->status == 'pending' ? 'selected' : '' }}>Pendiente</option>
                        <option value="published" {{ $post->status == 'published' ? 'selected' : '' }}>Publicado
                        </option>
                        <option value="rejected" {{ $post->status == 'rejected' ? 'selected' : '' }}>Rechazado</option>
                    </select>
                    <i class="ri-arrow-down-s-line select-arrow"></i>
                </div>
            </div>

            <!-- Visibilidad -->
            <div class="input-group">
                <label class="label-form">Visibilidad <i class="ri-asterisk text-accent"></i></label>
                <div class="input-icon-container">
                    <i class="ri-eye-line input-icon"></i>

                    <select name="visibility" class="select-form" data-validate="required|selected">
                        <option value="" disabled>Seleccione visibilidad</option>
                        <option value="public" {{ $post->visibility == 'public' ? 'selected' : '' }}>Público</option>
                        <option value="private" {{ $post->visibility == 'private' ? 'selected' : '' }}>Privado
                        </option>
                        <option value="registered" {{ $post->visibility == 'registered' ? 'selected' : '' }}>
                            Registrado
                        </option>
                    </select>

                    <i class="ri-arrow-down-s-line select-arrow"></i>
                </div>
            </div>

            <!-- Comentarios -->
            <div class="input-group">
                <label class="label-form">Permitir comentarios</label>
                <div class="binary-switch">
                    <input type="radio" name="allow_comments" id="allowYes" value="1"
                        class="switch-input switch-input-on" {{ $post->allow_comments == 1 ? 'checked' : '' }}>
                    <input type="radio" name="allow_comments" id="allowNo" value="0"
                        class="switch-input switch-input-off" {{ $post->allow_comments == 0 ? 'checked' : '' }}>

                    <div class="switch-slider"></div>
                    <label for="allowYes" class="switch-label switch-label-on"><i class="ri-checkbox-circle-line"></i> Sí</label>
                    <label for="allowNo" class="switch-label switch-label-off"><i class="ri-close-circle-line"></i> No</label>
                </div>
            </div>
        </div>

        <!-- ================= CONTENIDO ================= -->
        <div class="form-row-fit">
            <div class="input-group">
                <label class="label-form">Contenido <i class="ri-asterisk text-accent"></i></label>
                <textarea name="content" id="content" class="textarea-form-post" rows="8"
                    data-validate="requiredText|minText:10">{{ old('content', $post->content) }}</textarea>
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

        <div class="form-columns-row">
            <!-- ================= TAGS ================= -->
            <div class="form-column">
                <div class="input-group">
                    <label class="label-form">Tags</label>

                    <div class="input-icon-container">
                        <i class="ri-price-tag-3-line input-icon"></i>

                        <select id="tagSelect" class="select-form">
                            <option value="">Selecciona un tag</option>

                            @foreach ($tags as $tag)
                                <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                            @endforeach
                        </select>

                        <i class="ri-arrow-down-s-line select-arrow"></i>
                    </div>

                    <div id="tagContainer" class="tag-container">
                        @foreach ($post->tags as $tag)
                            <div class="tag-pill" data-id="{{ $tag->id }}">
                                {{ $tag->name }}
                                <i class="ri-close-line remove-tag"></i>
                            </div>
                        @endforeach
                    </div>

                    <div id="tagHiddenInputs">
                        @foreach ($post->tags as $tag)
                            <input type="hidden" name="tags[]" value="{{ $tag->id }}"
                                id="tag-hidden-{{ $tag->id }}">
                        @endforeach
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener("DOMContentLoaded", () => {
                    const select = document.getElementById("tagSelect");
                    const tagContainer = document.getElementById("tagContainer");
                    const hiddenInputs = document.getElementById("tagHiddenInputs");

                    const selectedTags = new Set(
                        [...document.querySelectorAll("#tagHiddenInputs input")].map(i => i.value)
                    );

                    select.addEventListener("change", () => {
                        const id = select.value;
                        const name = select.options[select.selectedIndex].text;

                        if (!id || selectedTags.has(id)) return;

                        selectedTags.add(id);

                        const pill = document.createElement("div");
                        pill.classList.add("tag-pill");
                        pill.dataset.id = id;
                        pill.innerHTML = `${name} <i class="ri-close-line remove-tag"></i>`;
                        tagContainer.appendChild(pill);

                        const input = document.createElement("input");
                        input.type = "hidden";
                        input.name = "tags[]";
                        input.value = id;
                        input.id = "tag-hidden-" + id;
                        hiddenInputs.appendChild(input);

                        pill.querySelector(".remove-tag").addEventListener("click", () => {
                            selectedTags.delete(id);
                            pill.remove();
                            document.getElementById("tag-hidden-" + id)?.remove();
                        });
                    });

                    document.querySelectorAll(".tag-pill .remove-tag").forEach(btn => {
                        btn.addEventListener("click", () => {
                            const pill = btn.parentElement;
                            const id = pill.dataset.id;
                            selectedTags.delete(id);
                            pill.remove();
                            document.getElementById("tag-hidden-" + id)?.remove();
                        });
                    });
                });
            </script>

            <!-- ================= IMÁGENES ADICIONALES ================= -->
            <div class="form-column">
                <div class="image-upload-section">
                    <label class="label-form">Imágenes adicionales</label>

                    <div class="custom-dropzone" id="customDropzone">
                        <i class="ri-multi-image-line"></i>
                        <p>Arrastra imágenes aquí o haz clic</p>
                        <input type="file" id="imageInput" name="images[]" accept="image/*" multiple hidden>
                    </div>

                    <div id="previewContainer" class="preview-container">
                        @foreach ($post->images as $img)
                            @php
                                $fullPath = public_path('storage/' . $img->path);
                                $exists = file_exists($fullPath);
                            @endphp

                            @if ($exists)
                                <div class="preview-item existing-image">
                                    {{-- Imagen encontrada --}}
                                    <img src="{{ asset('storage/' . $img->path) }}" alt="Imagen adicional">
                                    <div class="overlay">
                                        <span class="file-size">{{ $exists ? 'Existente' : 'No encontrada' }}</span>
                                        <button type="button" class="delete-btn" title="Eliminar imagen"
                                            data-id="{{ $img->id }}">
                                            <span class="boton-icon"><i class="ri-delete-bin-6-fill"></i></span>
                                            <span class="boton-text">Eliminar</span>
                                        </button>
                                    </div>
                                </div>
                            @else
                                {{-- Imagen no encontrada --}}
                                <div class="image-not-found-block">
                                    <i class="ri-file-close-line"></i>
                                    <p>Imagen no encontrada</p>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <input type="hidden" name="deletedImages" id="deletedImages">
                </div>

                <script>
                    const dropzone = document.getElementById("customDropzone");
                    const input = document.getElementById("imageInput");
                    const previewContainer = document.getElementById("previewContainer");

                    let selectedFiles = [];
                    let deletedImages = [];

                    dropzone.addEventListener("click", () => input.click());
                    dropzone.addEventListener("dragover", e => {
                        e.preventDefault();
                        dropzone.classList.add("dragover");
                    });
                    dropzone.addEventListener("dragleave", () => dropzone.classList.remove("dragover"));
                    dropzone.addEventListener("drop", e => {
                        e.preventDefault();
                        dropzone.classList.remove("dragover");
                        handleFiles(e.dataTransfer.files);
                    });
                    input.addEventListener("change", e => handleFiles(e.target.files));

                    function handleFiles(files) {
                        [...files].forEach(file => {
                            if (!file.type.startsWith("image/")) return;
                            selectedFiles.push(file);
                            previewImage(file);
                        });
                    }

                    function previewImage(file) {
                        const reader = new FileReader();

                        reader.onload = e => {
                            const div = document.createElement("div");
                            div.classList.add("preview-item");

                            let size = file.size / 1024;
                            size = size > 1024 ? (size / 1024).toFixed(2) + " MB" : size.toFixed(1) + " KB";

                            div.innerHTML = `
                                <img src="${e.target.result}">
                                <div class="overlay">
                                    <span class="file-size">${size}</span>
                                    <button class="delete-btn" title="Eliminar imagen">
                                        <span class="boton-icon"><i class="ri-delete-bin-6-fill"></i></span>
                                        <span class="boton-text">Eliminar</span>
                                    </button>
                                </div>
                            `;

                            div.querySelector(".delete-btn").addEventListener("click", ev => {
                                ev.stopPropagation();
                                selectedFiles = selectedFiles.filter(f => f !== file);
                                div.remove();
                            });

                            previewContainer.appendChild(div);
                        };

                        reader.readAsDataURL(file);
                    }

                    document.querySelectorAll(".delete-existing-btn").forEach(btn => {
                        btn.addEventListener("click", () => {
                            const id = btn.dataset.id;
                            deletedImages.push(id);

                            document.getElementById("deletedImages").value =
                                JSON.stringify(deletedImages);

                            btn.closest(".preview-item").remove();
                        });
                    });

                    // imagen principal eliminar
                    document.getElementById("removeImageBtn")?.addEventListener("click", () => {
                        document.getElementById("deleteImage").value = 1;
                        document.getElementById("imagePreview").style.display = "none";
                    });
                </script>
            </div>
        </div>

        <!-- ================= FOOTER ================= -->
        <div class="form-footer">
            <a href="{{ url()->previous() }}" class="boton-form boton-volver">
                <span class="boton-form-icon">
                    <i class="ri-arrow-left-circle-fill"></i>
                </span>
                <span class="boton-form-text">Cancelar</span>
            </a>

            <button type="submit" class="boton-form boton-success" id="submitBtn">
                <span class="boton-form-icon"><i class="ri-save-3-fill"></i></span>
                <span class="boton-form-text">Actualizar Post</span>
            </button>
        </div>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Inicializar manejador de imágenes
                const imageHandler = initImageUpload({
                    mode: 'edit',
                    hasExistingImage: {{ $mainImagePath && file_exists(public_path('storage/' . $mainImagePath)) ? 'true' : 'false' }},
                    existingImageFilename: '{{ $mainImagePath ? basename($mainImagePath) : '' }}'
                });

                // 1. Inicializar submit loader PRIMERO
                const submitLoader = initSubmitLoader({
                    formId: 'postForm',
                    buttonId: 'submitBtn',
                    loadingText: 'Actualizando...'
                });

                // 2. Inicializar validación de formulario DESPUÉS
                const formValidator = initFormValidator('#postForm', {
                    validateOnBlur: true,
                    validateOnInput: false,
                    scrollToFirstError: true
                });

                // Enlazar CKEditor: sincronizar en cambios y validar solo al perder foco
                if (window.editorInstance) {
                    const textarea = document.querySelector('#content');
                    const editor = window.editorInstance;
                    editor.model.document.on('change:data', () => {
                        textarea.value = editor.getData();
                    });
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
                        // Asegurar mensaje de error inline si es inválido (vacío vs. insuficiente)
                        if (!isValid && group) {
                            let errorEl = group.querySelector('.input-error-message');
                            if (!errorEl) {
                                errorEl = document.createElement('div');
                                errorEl.className = 'input-error-message';
                                errorEl.innerHTML = '<i class="ri-error-warning-line"></i> <span class="error-text"></span>';
                                group.appendChild(errorEl);
                            }
                            const textEl = errorEl.querySelector('.error-text');
                            if (textEl) {
                                const div = document.createElement('div');
                                div.innerHTML = textarea.value || '';
                                const plain = (div.textContent || div.innerText || '').replace(/\u00A0|&nbsp;/g, ' ').trim();
                                textEl.textContent = plain.length === 0 ? 'Este campo es obligatorio' : 'Debe tener al menos 10 caracteres';
                            }
                            errorEl.style.display = 'flex';
                        }
                    });
                }
            });
        </script>
    @endpush
</x-admin-layout>
