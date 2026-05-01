@section('title', 'Nueva marca')

<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-success"><i class="ri-add-large-line"></i></div>
        Nueva Marca
    </x-slot>

    <x-slot name="action">
        <a href="{{ route('admin.brands.index') }}" class="boton-form boton-accent">
            <span class="boton-form-icon"><i class="ri-arrow-go-back-line"></i></span>
            <span class="boton-form-text">Volver</span>
        </a>
    </x-slot>

    <form action="{{ route('admin.brands.store') }}" method="POST" enctype="multipart/form-data" class="form-container"
        autocomplete="off" id="brandForm">
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

        <x-alert type="info" title="Guía rápida:" :dismissible="true" :items="[
            'Los campos con asterisco (<i class=\'ri-asterisk text-accent\'></i>) son obligatorios',
            'Puedes subir una imagen opcional para la marca',
        ]" />

        <div class="form-columns-row">
            <div class="form-column">
                <div class="input-group">
                    <label for="name" class="label-form">
                        Nombre
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-bookmark-3-line input-icon"></i>
                        <input type="text" name="name" id="name" class="input-form" required
                            value="{{ old('name') }}" placeholder="Nombre de la marca"
                            data-validate="required|min:2|max:255" />
                    </div>
                </div>

                <div class="input-group">
                    <label class="label-form">
                        Estado
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="binary-switch">
                        <input type="radio" name="status" id="statusActive" value="1"
                            class="switch-input switch-input-on" {{ old('status', 1) == 1 ? 'checked' : '' }}>
                        <input type="radio" name="status" id="statusInactive" value="0"
                            class="switch-input switch-input-off" {{ old('status') == 0 ? 'checked' : '' }}>
                        <div class="switch-slider"></div>
                        <label for="statusActive" class="switch-label switch-label-on"><i
                                class="ri-checkbox-circle-line"></i> Activo</label>
                        <label for="statusInactive" class="switch-label switch-label-off"><i
                                class="ri-close-circle-line"></i> Inactivo</label>
                    </div>
                </div>

                <div class="input-group">
                    <label for="description" class="label-form label-textarea">Descripción</label>
                    <div class="input-icon-container">
                        <textarea name="description" id="description" class="textarea-form" placeholder="Ingrese la descripción" rows="4"
                            data-validate="min:10|max:250">{{ old('description') }}</textarea>
                        <i class="ri-file-text-line input-icon"></i>
                    </div>
                </div>
            </div>

            <div class="form-column">
                <div class="image-upload-section">
                    <label class="label-form">Imagen de la marca</label>

                    <input type="file" name="image" id="image" class="file-input" accept="image/*"
                        data-validate="imageSingle|maxSizeSingleMB:3">

                    <div class="image-preview-zone" id="imagePreviewZone">
                        <div class="image-placeholder" id="imagePlaceholder">
                            <i class="ri-image-add-line"></i>
                            <p>Arrastra una imagen aquí</p>
                            <span>o haz clic para seleccionar</span>
                            <span>Formatos: PNG, JPG, JPEG (máx. 3 MB)</span>
                        </div>

                        <img id="imagePreview" class="image-preview image-pulse" style="display: none;"
                            alt="Vista previa">

                        <div class="image-overlay" id="imageOverlay" style="display: none;">
                            <button type="button" class="boton-form boton-info" id="changeImageBtn">
                                <i class="ri-upload-2-line"></i>
                                <span class="boton-form-text">Cambiar</span>
                            </button>

                            <button type="button" class="boton-form boton-danger" id="removeImageBtn">
                                <i class="ri-delete-bin-line"></i>
                                <span class="boton-form-text">Eliminar</span>
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
                <span class="boton-form-text">Crear Marca</span>
            </button>
        </div>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                initSubmitLoader({
                    formId: 'brandForm',
                    buttonId: 'submitBtn',
                    loadingText: 'Guardando...'
                });

                initFormValidator('#brandForm', {
                    validateOnBlur: true,
                    validateOnInput: false,
                    scrollToFirstError: true
                });

                initImageUpload({
                    mode: 'create'
                });
            });
        </script>
    @endpush
</x-admin-layout>
