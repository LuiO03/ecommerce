<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-warning"><i class="ri-edit-2-line"></i></div>
        Editar Post
    </x-slot>

    <x-slot name="action">
        <a href="{{ route('admin.posts.index') }}" class="boton boton-secondary">
            <span class="boton-icon"><i class="ri-arrow-go-back-line"></i></span>
            <span class="boton-text">Volver</span>
        </a>
    </x-slot>

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
        <div class="form-row">
            <div class="image-upload-section">
                <label class="label-form">Portada del post</label>

                <input type="file" name="image" id="image" class="file-input" accept="image/*">

                <div class="image-preview-zone" id="imagePreviewZone">
                    @if ($post->image)
                        <img id="imagePreview" class="image-preview image-pulse" src="{{ asset('storage/' . $post->image) }}"
                            alt="Vista previa">

                        <div class="image-overlay" id="imageOverlay">
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
                    @else
                        <div class="image-placeholder" id="imagePlaceholder">
                            <i class="ri-image-add-line"></i>
                            <p>Arrastra una imagen aquí</p>
                            <span>o haz clic para seleccionar</span>
                        </div>

                        <img id="imagePreview" class="image-preview image-pulse" style="display: none;">
                        <div class="image-overlay" id="imageOverlay" style="display: none;"></div>
                    @endif

                    <input type="hidden" name="delete_image" id="deleteImage" value="0">
                </div>
            </div>
        </div>

        <!-- ================= CAMPOS PRINCIPALES ================= -->
        <div class="form-row">

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
                        <option value="published" {{ $post->status == 'published' ? 'selected' : '' }}>Publicado</option>
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
                        <option value="private" {{ $post->visibility == 'private' ? 'selected' : '' }}>Privado</option>
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
                        {{ $post->allow_comments == 1 ? 'checked' : '' }}>
                    <input type="radio" name="allow_comments" id="allowNo" value="0"
                        {{ $post->allow_comments == 0 ? 'checked' : '' }}>

                    <div class="switch-slider"></div>
                    <label for="allowYes" class="switch-label">Sí</label>
                    <label for="allowNo" class="switch-label">No</label>
                </div>
            </div>
        </div>

        <!-- ================= CONTENIDO ================= -->
        <div class="form-row">
            <div class="input-group">
                <label class="label-form">Contenido <i class="ri-asterisk text-accent"></i></label>

                <textarea name="content" id="content" class="textarea-form-post" rows="8"
                    >{{ old('content', $post->content) }}</textarea>
            </div>

            <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
            <script>
                let editorInstance;
                ClassicEditor.create(document.querySelector('#content'))
                    .then(editor => editorInstance = editor)
                    .catch(error => console.error(error));

                document.getElementById('postForm').addEventListener('submit', () => {
                    if (editorInstance) {
                        document.querySelector('#content').value = editorInstance.getData();
                    }
                });
            </script>
        </div>

        <!-- ================= TAGS ================= -->
        <div class="form-columns-row">
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
                            <input type="hidden" name="tags[]" value="{{ $tag->id }}" id="tag-hidden-{{ $tag->id }}">
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
                        <input type="file" id="imageInput" accept="image/*" multiple hidden>
                    </div>

                    <div id="previewContainer" class="preview-container">
                        @foreach ($post->images as $img)
                            <div class="preview-item existing-image">
                                <img src="{{ Storage::url($img->image_path) }}">

                                <div class="overlay">
                                    <span class="file-size">Existente</span>
                                    <button type="button" class="delete-existing-btn boton-danger"
                                        data-id="{{ $img->id }}">
                                        <i class="ri-delete-bin-line"></i>
                                        Eliminar
                                    </button>
                                </div>
                            </div>
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
            <a href="{{ route('admin.posts.index') }}" class="boton-form boton-volver">
                <span class="boton-form-icon"><i class="ri-arrow-left-circle-fill"></i></span>
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
                initImageUpload({ mode: 'edit' });

                initSubmitLoader({
                    formId: 'postForm',
                    buttonId: 'submitBtn',
                    loadingText: 'Actualizando...'
                });

                initFormValidator('#postForm', {
                    validateOnBlur: true,
                    validateOnInput: false,
                    scrollToFirstError: true
                });
            });
        </script>
    @endpush
</x-admin-layout>
