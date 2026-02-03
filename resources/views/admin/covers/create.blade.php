@section('title', 'Nueva portada')

<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-info"><i class="ri-add-large-line"></i></div>
        Nueva Portada
    </x-slot>
    <x-slot name="action">
        <a href="{{ route('admin.covers.index') }}" class="boton boton-secondary">
            <span class="boton-icon"><i class="ri-arrow-go-back-line"></i></span>
            <span class="boton-text">Volver</span>
        </a>
    </x-slot>

    <form action="{{ route('admin.covers.store') }}" method="POST" enctype="multipart/form-data" class="form-container"
        autocomplete="off" id="coverForm">
        @csrf

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
                <label class="label-form">
                    Imagen de la portada
                    <i class="ri-asterisk text-accent"></i>
                </label>
                <input type="file" name="image" id="image" class="file-input" accept="image/*" required
                    data-validate="imageSingle|maxSizeSingleMB:3">

                <div class="image-preview-zone" id="imagePreviewZone">
                    <div class="image-placeholder" id="imagePlaceholder">
                        <i class="ri-image-add-line"></i>
                        <p>Arrastra una imagen aquí</p>
                        <span>o haz clic para seleccionar</span>
                        <span>Formatos: PNG, JPG, JPEG (máx. 3 MB)</span>
                    </div>
                    <img id="imagePreview" class="image-preview image-pulse" style="display: none;" alt="Vista previa">
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
                            value="{{ old('title') }}" placeholder="Ingrese el título"
                            data-validate="required|min:3|max:255">
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
                            <option value="" disabled selected>Seleccione un estado</option>
                            <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                        <i class="ri-arrow-down-s-line select-arrow"></i>
                    </div>
                </div>

                <div class="input-group">
                    <label for="description" class="label-form label-textarea">Descripción</label>
                    <div class="input-icon-container">
                        <textarea name="description" id="description" class="textarea-form" placeholder="Descripción opcional"
                            rows="4" data-validate="max:500">{{ old('description') }}</textarea>
                        <i class="ri-file-text-line input-icon"></i>
                    </div>
                </div>

                <div class="input-group">
                    <label for="start_at" class="label-form">Fecha de inicio</label>
                    <div class="input-icon-container">
                        <i class="ri-calendar-line input-icon"></i>
                        <input type="datetime-local" name="start_at" id="start_at" class="input-form"
                            value="{{ old('start_at', now()->format('Y-m-d\TH:i')) }}">
                    </div>
                </div>

                <div class="input-group">
                    <label for="end_at" class="label-form">Fecha de fin (Opcional)</label>
                    <div class="input-icon-container">
                        <i class="ri-calendar-check-line input-icon"></i>
                        <input type="datetime-local" name="end_at" id="end_at" class="input-form"
                            value="{{ old('end_at') }}">
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
                <span class="boton-form-text">Limpiar</span>
            </button>
            <button class="boton-form boton-success" type="submit" id="submitBtn">
                <span class="boton-form-icon"><i class="ri-save-3-fill"></i></span>
                <span class="boton-form-text">Crear Portada</span>
            </button>
        </div>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const imageHandler = initImageUpload({
                    mode: 'create'
                });
                const submitLoader = initSubmitLoader({
                    formId: 'coverForm',
                    buttonId: 'submitBtn',
                    loadingText: 'Guardando...'
                });

                const formValidator = initFormValidator('#coverForm', {
                    validateOnBlur: true,
                    validateOnInput: false,
                    scrollToFirstError: true
                });
            });
        </script>
    @endpush
</x-admin-layout>
