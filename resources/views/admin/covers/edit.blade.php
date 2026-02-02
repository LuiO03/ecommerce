@section('title', 'Editar portada: ' . $cover->title)

<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-warning"><i class="ri-edit-circle-line"></i></div>
        <div class="page-edit-title">
            <span class="page-subtitle">Editar Portada</span>
            {{ $cover->title }}
        </div>
    </x-slot>
    <x-slot name="action">
        <a href="{{ route('admin.covers.index') }}" class="boton boton-secondary">
            <span class="boton-icon"><i class="ri-arrow-go-back-line"></i></span>
            <span class="boton-text">Volver</span>
        </a>
        <form action="{{ route('admin.covers.destroy', $cover->slug) }}" method="POST" class="delete-form"
            data-entity="portada" style="margin: 0;">
            @csrf
            @method('DELETE')
            <button class="boton boton-danger" type="submit">
                <span class="boton-icon"><i class="ri-delete-bin-6-fill"></i></span>
                <span class="boton-text">Eliminar</span>
            </button>
        </form>
    </x-slot>

    <form action="{{ route('admin.covers.update', $cover->slug) }}" method="POST" enctype="multipart/form-data"
        class="form-container" autocomplete="off" id="coverForm">
        @csrf
        @method('PUT')

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
            <div class="image-upload-section">
                <label class="label-form">Imagen de la portada</label>
                <input type="file" name="image" id="image" class="file-input" accept="image/*"
                    data-validate="imageSingle|maxSizeSingleMB:3">
                <input type="hidden" name="remove_image" id="removeImageFlag" value="0">

                <div class="image-preview-zone {{ $cover->image_path && file_exists(public_path('storage/' . $cover->image_path)) ? 'has-image' : '' }}"
                    id="imagePreviewZone">
                    @if ($cover->image_path && file_exists(public_path('storage/' . $cover->image_path)))
                        <img id="imagePreview" class="image-preview" src="{{ asset('storage/' . $cover->image_path) }}"
                            alt="Imagen actual">
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
                            <span>Formatos: PNG, JPG, JPEG (máx. 3 MB)</span>
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
                    @endif
                </div>
                <div class="image-filename" id="imageFilename" style="display: none;">
                    <i class="ri-file-image-line"></i>
                    <span id="filenameText"></span>
                </div>
            </div>
        </div>
        <div class="form-body">
            <div class="form-row-fit">
                <div class="input-group">
                    <label for="title" class="label-form">
                        Título de la portada
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-text input-icon"></i>
                        <input type="text" name="title" id="title" class="input-form" required
                            value="{{ old('title', $cover->title) }}" placeholder="Ingrese el título"
                            data-validate="required|min:3|max:255">
                    </div>
                </div>

                <div class="input-group">
                    <label for="position" class="label-form">
                        Posición
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-sort-number-asc input-icon"></i>
                        <input type="number" name="position" id="position" class="input-form" required
                            value="{{ old('position', $cover->position) }}" min="0"
                            placeholder="Orden de aparición" data-validate="required|numeric|min:0">
                    </div>
                </div>

                <div class="input-group">
                    <label for="status" class="label-form">
                        Estado
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-toggle-line input-icon"></i>
                        <select name="status" id="status" class="select-form" required
                            data-validate="required|selected">
                            <option value="" disabled>Seleccione un estado</option>
                            <option value="1" @selected(old('status', $cover->status) == 1)>Activo</option>
                            <option value="0" @selected(old('status', $cover->status) == 0)>Inactivo</option>
                        </select>
                        <i class="ri-arrow-down-s-line select-arrow"></i>
                    </div>
                </div>

                <div class="input-group">
                    <label for="description" class="label-form label-textarea">Descripción</label>
                    <div class="input-icon-container">
                        <textarea name="description" id="description" class="textarea-form" placeholder="Descripción opcional"
                            rows="4" data-validate="max:500">{{ old('description', $cover->description) }}</textarea>
                        <i class="ri-file-text-line input-icon"></i>
                    </div>
                </div>

                <div class="input-group">
                    <label for="start_at" class="label-form">Fecha de inicio</label>
                    <div class="input-icon-container">
                        <input type="datetime-local" name="start_at" id="start_at" class="input-form"
                            value="{{ old('start_at', $cover->start_at ? $cover->start_at->format('Y-m-d\TH:i') : '') }}">
                    </div>
                </div>

                <div class="input-group">
                    <label for="end_at" class="label-form">Fecha de fin</label>
                    <div class="input-icon-container">
                        <i class="ri-calendar-check-line input-icon"></i>
                        <input type="datetime-local" name="end_at" id="end_at" class="input-form"
                            value="{{ old('end_at', $cover->end_at ? $cover->end_at->format('Y-m-d\TH:i') : '') }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-footer">
            <a href="{{ url()->previous() }}" class="boton-form boton-volver">
                <span class="boton-form-icon"><i class="ri-arrow-left-circle-fill"></i></span>
                <span class="boton-form-text">Cancelar</span>
            </a>
            <button type="reset" class="boton-form boton-warning">
                <span class="boton-form-icon"><i class="ri-paint-brush-fill"></i></span>
                <span class="boton-form-text">Restablecer</span>
            </button>
            <button class="boton-form boton-primary" type="submit" id="submitBtn">
                <span class="boton-form-icon"><i class="ri-save-3-fill"></i></span>
                <span class="boton-form-text">Guardar Cambios</span>
            </button>
        </div>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {// Inicializar manejador de imágenes
                const imageHandler = initImageUpload({
                    mode: 'edit',
                    hasExistingImage: {{ $cover->image_path && file_exists(public_path('storage/' . $cover->image_path)) ? 'true' : 'false' }},
                    existingImageFilename: '{{ $cover->image_path ? basename($cover->image_path) : '' }}'
                });

                // 1. Inicializar submit loader PRIMERO
                const submitLoader = initSubmitLoader({
                    formId: 'coverForm',
                    buttonId: 'submitBtn',
                    loadingText: 'Actualizando...'
                });

                // 2. Inicializar validación de formulario DESPUÉS
                const formValidator = initFormValidator('#coverForm', {
                    validateOnBlur: true,
                    validateOnInput: false,
                    scrollToFirstError: true
                });
            });
        </script>
    @endpush
</x-admin-layout>
