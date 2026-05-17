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
            <div class="form-body">
                <div class="form-row-fit">
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
                            <!-- Checkbox real -->
                            <input type="hidden" name="status" value="0">

                            <input type="checkbox" name="status" id="status" class="switch-input" value="1"
                                {{ old('status', $brand->status) == 1 ? 'checked' : '' }} data-validate="required">

                            <!-- Labels visuales -->
                            <label for="status" class="switch-label switch-label-on">
                                <i class="ri-checkbox-circle-line"></i> Activo
                            </label>

                            <label for="status" class="switch-label switch-label-off">
                                <i class="ri-close-circle-line"></i> Inactivo
                            </label>

                            <div class="switch-slider"></div>
                        </div>
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
            <div class="form-body">
                <div class="image-upload-section">
                    <label class="label-form">Imagen de la marca</label>
                    <input type="file" name="image" id="image" class="file-input" accept="image/*"
                        data-validate="imageSingle|maxSizeSingleMB:3">

                    <!-- Zona de vista previa -->
                    <div class="image-preview-zone {{ $brand->image && file_exists(public_path('storage/' . $brand->image)) ? 'has-image' : '' }}"
                        id="imagePreviewZone">

                        @if ($brand->image && file_exists(public_path('storage/' . $brand->image)))
                            <!-- ✅ IMAGEN EXISTENTE: Mostrada directamente -->
                            <img id="imagePreview" class="image-preview image-pulse"
                                src="{{ asset('storage/' . $brand->image) }}" alt="{{ $brand->name }}">

                            <!-- Placeholder oculto (se muestra al eliminar) -->
                            <div class="image-placeholder" id="imagePlaceholder" style="display: none;">
                                <i class="ri-image-add-line"></i>
                                <p>Arrastra una imagen aquí</p>
                                <span>o haz clic para seleccionar</span>
                                <span>Formatos: PNG, JPG, JPEG (máx. 3 MB)</span>
                            </div>
                        @elseif($brand->image)
                            <!-- ⚠️ IMAGEN NO ENCONTRADA: Error si archivo no existe en disk -->
                            <div class="image-error" id="imageError">
                                <i class="ri-folder-close-line"></i>
                                <p>Imagen no encontrada</p>
                                <span>Haz clic para subir una nueva</span>
                                <span>Formatos: PNG, JPG, JPEG (máx. 3 MB)</span>
                            </div>
                        @else
                            <!-- 📭 SIN IMAGEN: Placeholder vacío -->
                            <div class="image-placeholder" id="imagePlaceholder">
                                <i class="ri-image-add-line"></i>
                                <p>Arrastra una imagen aquí</p>
                                <span>o haz clic para seleccionar</span>
                                <span>Formatos: PNG, JPG, JPEG (máx. 3 MB)</span>
                            </div>
                        @endif

                        <!-- Nueva imagen cargada (oculta inicialmente, se muestra al cambiar) -->
                        <img id="imagePreviewNew" class="image-preview image-pulse" style="display: none;"
                            alt="Vista previa">

                        <!-- Overlay de control -->
                        <div class="image-overlay" id="imageOverlay" style="display: none;">
                            <button type="button" class="boton-form boton-info" id="changeImageBtn"
                                title="Cambiar imagen">
                                <span class="boton-form-icon">
                                    <i class="ri-upload-2-line"></i>
                                </span>
                                <span class="boton-form-text">Cambiar</span>
                            </button>
                            <button type="button" class="boton-form boton-danger" id="removeImageBtn"
                                title="Eliminar imagen">
                                <span class="boton-form-icon">
                                    <i class="ri-delete-bin-line"></i>
                                </span>
                                <span class="boton-form-text">Eliminar</span>
                            </button>
                        </div>
                    </div>

                    <!-- Flag oculto para comunicar al servidor la eliminación -->
                    <input type="hidden" name="remove_image" id="removeImageFlag" value="0">

                    <!-- Nombre del archivo con estado inicial correcto -->
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
