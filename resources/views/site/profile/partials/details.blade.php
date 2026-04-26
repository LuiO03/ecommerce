<div class="profile-section">
    <x-note-alert type="info" :dismissible="true">
        Los campos con asterisco (<i class="ri-asterisk text-accent"></i>) son obligatorios.
    </x-note-alert>

    <form method="POST" action="{{ route('site.profile.details.update') }}" class="form-container" autocomplete="off"
        id="profileDetailsForm" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="form-user">
            <div class="form-body"
                style="border-radius: var(--radius-card);background-color: var(--color-card-bg);border: none;">
                <div class="card-header">
                    <span class="card-title">Foto de perfil</span>
                    <p class="card-description">Agrega una foto para personalizar tu cuenta.</p>
                </div>
                <!-- === Imagen === -->
                <div class="image-upload-section">
                    <input type="file" name="image" id="image" class="file-input" accept="image/*"
                        data-validate="imageSingle|maxSizeSingleMB:3">
                    <input type="hidden" name="remove_image" id="removeImageFlag" value="0">

                    <!-- Zona de vista previa -->
                    <div class="image-preview-zone {{ $user->image && file_exists(public_path('storage/' . $user->image)) ? 'has-image' : '' }}"
                        id="imagePreviewZone" style="border: 1px solid grey;">
                        @if ($user->image && file_exists(public_path('storage/' . $user->image)))
                            <img id="imagePreview" class="image-preview image-pulse"
                                src="{{ asset('storage/' . $user->image) }}" alt="{{ $user->name }}">
                            <!-- Placeholder oculto inicialmente (se mostrará al eliminar) -->
                            <div class="image-placeholder" id="imagePlaceholder" style="display: none;">
                                <i class="ri-image-add-line"></i>
                                <p>Arrastra una imagen aquí</p>
                                <span>o haz clic para seleccionar</span>
                                <span>Formatos: PNG, JPG, JPEG (máx. 3 MB)</span>
                            </div>
                        @elseif($user->image)
                            <!-- Imagen no encontrada -->
                            <div class="image-error" id="imageError">
                                <i class="ri-folder-close-line"></i>
                                <p>Imagen no encontrada</p>
                                <span>Haz clic para subir una nueva</span>
                                <span>Formatos: PNG, JPG, JPEG (máx. 3 MB)</span>
                            </div>
                        @else
                            <!-- Sin imagen -->
                            <div class="image-placeholder" id="imagePlaceholder">
                                <i class="ri-image-add-line"></i>
                                <p>Arrastra una imagen aquí</p>
                                <span>o haz clic para seleccionar</span>
                                <span>Formatos: PNG, JPG, JPEG (máx. 3 MB)</span>
                            </div>
                        @endif

                        <!-- Imagen nueva cargada (oculta inicialmente) -->
                        <img id="imagePreviewNew" class="image-preview image-pulse" style="display: none;"
                            alt="Vista previa">

                        <!-- Overlay único para todas las imágenes -->
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
                </div>
            </div>
            <div class="form-body"
                style="border-radius: var(--radius-card);background-color: var(--color-card-bg);border: none;">
                <div class="card-header">
                    <span class="card-title">Datos personales</span>
                    <p class="card-description">Mantén tu información actualizada para facilitar tus compras y
                        comunicaciones.
                    </p>
                </div>
                <div class="form-row-fit">

                    <div class="input-group">
                        <label for="name" class="label-form">
                            Nombre
                            <i class="ri-asterisk text-accent"></i>
                        </label>
                        <div class="input-icon-container">
                            <i class="ri-user-line input-icon"></i>
                            <input type="text" name="name" id="name" class="input-form"
                                placeholder="Tu nombre" required value="{{ old('name', $user->name) }}"
                                data-validate="required|min:3|max:255">
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="last_name" class="label-form">Apellidos</label>
                        <div class="input-icon-container">
                            <i class="ri-user-line input-icon"></i>
                            <input type="text" name="last_name" id="last_name" class="input-form"
                                placeholder="Tus apellidos" value="{{ old('last_name', $user->last_name) }}"
                                data-validate="max:255">
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="email" class="label-form">
                            Correo electrónico
                            <i class="ri-asterisk text-accent"></i>
                        </label>
                        <div class="input-icon-container">
                            <i class="ri-mail-line input-icon"></i>
                            <input type="email" name="email" id="email" class="input-form"
                                placeholder="usuario@ejemplo.com" required value="{{ old('email', $user->email) }}"
                                data-validate="required|email">
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="phone" class="label-form">Teléfono </label>
                        <div class="input-icon-container">
                            <i class="ri-phone-line input-icon"></i>

                            <input type="text" name="phone" id="phone" class="input-form"
                                value="{{ old('phone', $user->phone) }}" placeholder="Número de contacto"
                                data-validate="phone">
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="document_type" class="label-form">Tipo de documento</label>
                        <div class="input-icon-container">
                            <i class="ri-id-card-line input-icon"></i>
                            <select name="document_type" id="document_type" class="select-form">
                                <option value="">Selecciona una opción</option>
                                <option value="DNI"
                                    {{ old('document_type', $user->document_type) == 'DNI' ? 'selected' : '' }}>DNI
                                </option>
                                <option value="RUC"
                                    {{ old('document_type', $user->document_type) == 'RUC' ? 'selected' : '' }}>RUC
                                </option>
                                <option value="CE"
                                    {{ old('document_type', $user->document_type) == 'CE' ? 'selected' : '' }}>Carné de
                                    extranjería</option>
                                <option value="PASAPORTE"
                                    {{ old('document_type', $user->document_type) == 'PASAPORTE' ? 'selected' : '' }}>
                                    Pasaporte</option>
                            </select>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="document_number" class="label-form">Número de documento</label>
                        <div class="input-icon-container">
                            <i class="ri-hashtag input-icon"></i>
                            <input type="text" name="document_number" id="document_number" class="input-form"
                                placeholder="Ingresa tu número de documento"
                                value="{{ old('document_number', $user->document_number) }}"
                                data-validate="document_number|max:30|requiredWith:document_type">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-footer-static">
            <button class="boton-form boton-accent" type="submit" id="saveProfileDetailsBtn">
                <span class="boton-form-icon"><i class="ri-save-fill"></i></span>
                <span class="boton-form-text">Guardar cambios</span>
            </button>
        </div>
    </form>

    @push('js')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Inicializar manejador de imágenes
                const imageHandler = initImageUpload({
                    mode: 'edit',
                    hasExistingImage: {{ $user->image && file_exists(public_path('storage/' . $user->image)) ? 'true' : 'false' }},
                    existingImageFilename: '{{ $user->image ? basename($user->image) : '' }}'
                });

                // 1. Inicializar submit loader PRIMERO
                const submitLoader = initSubmitLoader({
                    formId: 'profileDetailsForm',
                    buttonId: 'saveProfileDetailsBtn',
                    loadingText: 'Actualizando...'
                });

                // 2. Inicializar validación de formulario DESPUÉS
                const formValidator = initFormValidator('#profileDetailsForm', {
                    validateOnBlur: true,
                    validateOnInput: false,
                    scrollToFirstError: true
                });

                // 3. Deshabilitar número de documento hasta que se elija tipo
                (function setupDocumentFields() {
                    const form = document.getElementById('profileDetailsForm');
                    if (!form) return;

                    const typeField = form.querySelector('#document_type');
                    const numberField = form.querySelector('#document_number');
                    if (!typeField || !numberField) return;

                    let lastType = String(typeField.value || '').trim();

                    const updateState = () => {
                        const currentType = String(typeField.value || '').trim();
                        const hasType = currentType !== '';

                        // Si cambia de un tipo a otro distinto, limpiar el número para evitar ambigüedad
                        if (hasType && lastType && currentType !== lastType) {
                            numberField.value = '';
                            if (form.__validator) {
                                form.__validator.clearError(numberField);
                                form.__validator.clearSuccess(numberField);
                            }
                        }

                        if (!hasType) {
                            numberField.value = '';
                            numberField.disabled = true;

                            if (form.__validator) {
                                form.__validator.clearError(numberField);
                                form.__validator.clearSuccess(numberField);
                            }
                        } else {
                            numberField.disabled = false;
                        }

                        lastType = currentType;
                    };

                    // Estado inicial (considerando valor actual del usuario)
                    updateState();

                    // Actualizar al cambiar el tipo de documento
                    typeField.addEventListener('change', updateState);
                })();
            });
        </script>
    @endpush
</div>
