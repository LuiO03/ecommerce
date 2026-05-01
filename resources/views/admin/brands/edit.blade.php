@section('title', 'Editar marca: ' . $brand->name)

<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-warning"><i class="ri-edit-circle-line"></i></div>
        <div class="page-edit-title">
            <span class="page-subtitle">Editar Marca</span>
            {{ $brand->name }}
        </div>
    </x-slot>

    <x-slot name="action">
        <a href="{{ route('admin.brands.index') }}" class="boton-form boton-accent">
            <span class="boton-form-icon"><i class="ri-arrow-go-back-line"></i></span>
            <span class="boton-form-text">Volver</span>
        </a>

        <form action="{{ route('admin.brands.destroy', $brand) }}" method="POST" class="delete-form"
            data-entity="marca" style="margin: 0;">
            @csrf
            @method('DELETE')
            <button class="boton-form boton-danger" type="submit">
                <span class="boton-form-icon"><i class="ri-delete-bin-6-fill"></i></span>
                <span class="boton-form-text">Eliminar</span>
            </button>
        </form>
    </x-slot>

    <form action="{{ route('admin.brands.update', $brand) }}" method="POST" enctype="multipart/form-data"
        class="form-container" autocomplete="off" id="brandForm">
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
                            value="{{ old('name', $brand->name) }}" placeholder="Nombre de la marca"
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
                            class="switch-input switch-input-on"
                            {{ old('status', (int) $brand->status) == 1 ? 'checked' : '' }}>
                        <input type="radio" name="status" id="statusInactive" value="0"
                            class="switch-input switch-input-off"
                            {{ old('status', (int) $brand->status) == 0 ? 'checked' : '' }}>
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
                            data-validate="min:10|max:250">{{ old('description', $brand->description) }}</textarea>
                        <i class="ri-file-text-line input-icon"></i>
                    </div>
                </div>
            </div>

            <div class="form-column">
                <div class="image-upload-section">
                    <label class="label-form">Imagen de la marca</label>

                    <input type="file" name="image" id="image" class="file-input" accept="image/*"
                        data-validate="imageSingle|maxSizeSingleMB:3">
                    <input type="hidden" name="remove_image" id="removeImageFlag" value="0">

                    <div class="image-preview-zone {{ $brand->image && file_exists(public_path('storage/' . $brand->image)) ? 'has-image' : '' }}"
                        id="imagePreviewZone">
                        @if ($brand->image && file_exists(public_path('storage/' . $brand->image)))
                            <img id="imagePreview" class="image-preview image-pulse"
                                src="{{ asset('storage/' . $brand->image) }}" alt="{{ $brand->name }}">
                            <div class="image-placeholder" id="imagePlaceholder" style="display: none;">
                                <i class="ri-image-add-line"></i>
                                <p>Arrastra una imagen aquí</p>
                                <span>o haz clic para seleccionar</span>
                                <span>Formatos: PNG, JPG, JPEG (máx. 3 MB)</span>
                            </div>
                        @elseif($brand->image)
                            <div class="image-error" id="imageError">
                                <i class="ri-folder-close-line"></i>
                                <p>Imagen no encontrada</p>
                                <span>Haz clic para subir una nueva</span>
                                <span>Formatos: PNG, JPG, JPEG (máx. 3 MB)</span>
                            </div>
                        @else
                            <div class="image-placeholder" id="imagePlaceholder">
                                <i class="ri-image-add-line"></i>
                                <p>Arrastra una imagen aquí</p>
                                <span>o haz clic para seleccionar</span>
                                <span>Formatos: PNG, JPG, JPEG (máx. 3 MB)</span>
                            </div>
                        @endif

                        <img id="imagePreviewNew" class="image-preview image-pulse" style="display: none;"
                            alt="Vista previa">

                        <div class="image-overlay" id="imageOverlay" style="display: none;">
                            <button type="button" class="boton-form boton-info" id="changeImageBtn"
                                title="Cambiar imagen">
                                <i class="ri-upload-2-line"></i>
                                <span class="boton-form-text">Cambiar</span>
                            </button>
                            <button type="button" class="boton-form boton-danger" id="removeImageBtn"
                                title="Eliminar imagen">
                                <i class="ri-delete-bin-line"></i>
                                <span class="boton-form-text">Eliminar</span>
                            </button>
                        </div>
                    </div>

                    <div class="image-filename" id="imageFilename"
                        style="{{ $brand->image && file_exists(public_path('storage/' . $brand->image)) ? 'display: flex;' : 'display: none;' }}">
                        <i class="ri-file-image-line"></i>
                        <span id="filenameText">{{ $brand->image ? basename($brand->image) : '' }}</span>
                    </div>
                </div>
            </div>

        </div>
        <div class="form-footer">
            <a href="{{ route('admin.brands.index') }}" class="boton-form boton-volver">
                <span class="boton-form-icon"><i class="ri-arrow-left-circle-fill"></i></span>
                <span class="boton-form-text">Atrás</span>
            </a>

            <button class="boton-form boton-accent" type="submit" id="submitBtn">
                <span class="boton-form-icon"><i class="ri-loop-left-line"></i></span>
                <span class="boton-form-text">Actualizar Marca</span>
            </button>
        </div>
    </form>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initSubmitLoader({
                formId: 'brandForm',
                buttonId: 'submitBtn',
                loadingText: 'Actualizando...'
            });

            initFormValidator('#brandForm', {
                validateOnBlur: true,
                validateOnInput: false,
                scrollToFirstError: true
            });

            initImageUpload({
                mode: 'edit',
                hasExistingImage: {{ $brand->image && file_exists(public_path('storage/' . $brand->image)) ? 'true' : 'false' }},
                existingImageFilename: '{{ $brand->image ? basename($brand->image) : '' }}'
            });
        });
    </script>
</x-admin-layout>
