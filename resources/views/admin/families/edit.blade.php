<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-warning"><i class="ri-edit-circle-line"></i></div>
        <div class="page-edit-title">
            <span class="page-subtitle">Editar Familia</span>
            {{ $family->name }}
        </div>
    </x-slot>
    <x-slot name="action">
        <a href="{{ route('admin.families.index') }}" class="boton boton-secondary">
            <span class="boton-icon"><i class="ri-arrow-go-back-line"></i></span>
            <span class="boton-text">Volver</span>
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

        {{-- Banner de errores de backend (solo si JS fue omitido o falló) --}}
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

        <x-alert
            type="info"
            title="Información:"
            :dismissible="true"
            :items="[
                'Los campos con asterisco (<i class=\'ri-asterisk text-accent\'></i>) son obligatorios.'
            ]"
        />

        <div class="form-columns-row">
            <div class="form-column">
                <!-- === Nombre === -->
                <div class="input-group">
                    <label for="name" class="label-form">
                        Nombre de la familia
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-price-tag-2-line input-icon"></i>
                        <input type="text" name="name" id="name" class="input-form"
                            placeholder="Ingrese el nombre" required value="{{ old('name', $family->name) }}"
                            data-validate="required|min:3|max:100|alphanumeric">
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
                        <select name="status" id="status" class="select-form" required
                            data-validate="required|selected">
                            <option value="" disabled>Seleccione un estado</option>

                            <option value="1" @selected(old('status', $family->status) == 1)>
                                Activo
                            </option>

                            <option value="0" @selected(old('status', $family->status) == 0)>
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
                            data-validate="min:10|max:500">{{ old('description', $family->description) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="form-column">
                <!-- === Imagen === -->
                <div class="image-upload-section">
                    <label class="label-form">Imagen de la familia</label>
                    <input type="file" name="image" id="image" class="file-input" accept="image/*"
                        data-validate="image|maxSizeMB:3">
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
            <a href="{{ url()->previous() }}" class="boton-form boton-volver">
                <span class="boton-form-icon">
                    <i class="ri-arrow-left-circle-fill"></i>
                </span>
                <span class="boton-form-text">Cancelar</span>
            </a>

            <button class="boton-form boton-accent" type="submit" id="submitBtn">
                <span class="boton-form-icon"><i class="ri-loop-left-line"></i></span>
                <span class="boton-form-text">Actualizar Familia</span>
            </button>
        </div>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Inicializar manejador de imágenes
                const imageHandler = initImageUpload({
                    mode: 'edit',
                    hasExistingImage: {{ $family->image && file_exists(public_path('storage/' . $family->image)) ? 'true' : 'false' }},
                    existingImageFilename: '{{ $family->image ? basename($family->image) : '' }}'
                });

                // 1. Inicializar submit loader PRIMERO
                const submitLoader = initSubmitLoader({
                    formId: 'familyForm',
                    buttonId: 'submitBtn',
                    loadingText: 'Actualizando...'
                });

                // 2. Inicializar validación de formulario DESPUÉS
                const formValidator = initFormValidator('#familyForm', {
                    validateOnBlur: true,
                    validateOnInput: false,
                    scrollToFirstError: true
                });
            });
        </script>
    @endpush
</x-admin-layout>
