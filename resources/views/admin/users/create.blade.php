@section('title', 'Nuevo usuario')

<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-success"><i class="ri-add-large-line"></i></div>
        Nuevo Usuario
    </x-slot>
    <x-slot name="action">
        <a href="{{ route('admin.users.index') }}" class="boton-form boton-accent">
            <span class="boton-form-icon"><i class="ri-arrow-go-back-line"></i></span>
            <span class="boton-form-text">Volver</span>
        </a>
    </x-slot>

    <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data" class="form-container"
        autocomplete="off" id="userForm">
        @csrf

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

        <x-note-alert type="info" :dismissible="true">
            Los campos con asterisco (<i class="ri-asterisk text-accent"></i>) son obligatorios.
        </x-note-alert>
        <div class="form-body">
            <div class="form-row-fit">
                <!-- === Nombre === -->
                <div class="input-group">
                    <label for="name" class="label-form">
                        Nombre
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-user-line input-icon"></i>
                        <input type="text" name="name" id="name" class="input-form" required
                            value="{{ old('name') }}" placeholder="Ingrese el nombre"
                            data-validate="required|min:3|max:255|alpha">
                    </div>
                </div>

                <!-- === Apellido === -->
                <div class="input-group">
                    <label for="last_name" class="label-form">Apellido</label>
                    <div class="input-icon-container">
                        <i class="ri-user-line input-icon"></i>
                        <input type="text" name="last_name" id="last_name" class="input-form"
                            value="{{ old('last_name') }}" placeholder="Ingrese el apellido"
                            data-validate="min:3|max:255|alpha">
                    </div>
                </div>
            </div>
            <div class="form-row-fill">
                <!-- === Contraseña === -->
                <div class="input-group">
                    <label for="password" class="label-form">
                        Contraseña
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-lock-line input-icon"></i>
                        <input type="password" name="password" id="password" class="input-form password-input" required
                            placeholder="Mínimo 6 caracteres" data-validate="required|min:6">
                        <button type="button" class="toggle-password" tabindex="-1" aria-label="Mostrar contraseña">
                            <i class="ri-eye-line"></i>
                        </button>
                    </div>
                </div>
                <!-- === Email === -->
                <div class="input-group">
                    <label for="email" class="label-form">
                        Email
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-mail-line input-icon"></i>
                        <input type="email" name="email" id="email" class="input-form" required
                            value="{{ old('email') }}" placeholder="usuario@ejemplo.com"
                            data-validate="required|email">
                    </div>
                </div>
                <!-- === Estado === -->
                <div class="input-group">
                    <label class="label-form">
                        Estado del usuario
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
                <!-- === Rol === -->
                <div class="input-group">
                    <label for="role" class="label-form">
                        Rol
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-shield-user-line input-icon"></i>
                        <select name="role" id="role" class="select-form" required
                            data-validate="required|selected">
                            <option value="" disabled selected>Seleccione un rol</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                    {{ ucfirst($role->name) }}
                                </option>
                            @endforeach
                        </select>
                        <i class="ri-arrow-down-s-line select-arrow"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-user">
            <div class="form-body">
                <!-- === Imagen === -->
                <div class="image-upload-section">
                    <label class="label-form">Foto de perfil</label>
                    <input type="file" name="image" id="image" class="file-input" accept="image/*"
                        data-validate="imageSingle|maxSizeSingleMB:3">

                    <!-- Zona de vista previa -->
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
                    <!-- Nombre del archivo (temporal) -->
                    <div class="image-filename" id="imageFilename" style="display: none;">
                        <i class="ri-file-image-line"></i>
                        <span id="filenameText"></span>
                    </div>
                </div>
            </div>
            <div class="form-body">
                <div class="form-row-fit">
                    <!-- === Dirección === -->
                    <div class="input-group">
                        <label for="address" class="label-form">Dirección</label>
                        <div class="input-icon-container">
                            <i class="ri-map-pin-line input-icon"></i>
                            <input type="text" name="address" id="address" class="input-form"
                                value="{{ old('address') }}" placeholder="Ingrese la dirección"
                                data-validate="max:255">
                        </div>
                    </div>

                    <!-- === Tipo de documento (opcional) === -->
                    <div class="input-group">
                        <label for="document_type" class="label-form">Tipo de documento</label>
                        <div class="input-icon-container">
                            <i class="ri-id-card-line input-icon"></i>
                            <select name="document_type" id="document_type" class="select-form">
                                <option value="">Seleccione una opción</option>
                                <option value="DNI" {{ old('document_type') == 'DNI' ? 'selected' : '' }}>DNI
                                </option>
                                <option value="RUC" {{ old('document_type') == 'RUC' ? 'selected' : '' }}>RUC
                                </option>
                                <option value="CE" {{ old('document_type') == 'CE' ? 'selected' : '' }}>Carné de
                                    extranjería</option>
                                <option value="PASAPORTE" {{ old('document_type') == 'PASAPORTE' ? 'selected' : '' }}>
                                    Pasaporte</option>
                            </select>
                            <i class="ri-arrow-down-s-line select-arrow"></i>
                        </div>
                    </div>
                </div>
                <!-- === Número de documento (depende de tipo) === -->
                <div class="input-group">
                    <label for="document_number" class="label-form">Número de documento</label>
                    <div class="input-icon-container">
                        <i class="ri-hashtag input-icon"></i>
                        <input type="text" name="document_number" id="document_number" class="input-form"
                            value="{{ old('document_number') }}" placeholder="Ingresa el número de documento"
                            data-validate="document_number|max:30|requiredWith:document_type">
                    </div>
                </div>

                <!-- === Teléfono === -->
                <div class="input-group">
                    <label for="phone" class="label-form">Teléfono</label>
                    <div class="input-icon-container">
                        <i class="ri-phone-line input-icon"></i>
                        <input type="text" name="phone" id="phone" class="input-form"
                            value="{{ old('phone') }}" placeholder="9 dígitos" data-validate="phone">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-footer">
            <a href="{{ url()->previous() }}" class="boton-form boton-volver">
                <span class="boton-form-icon">
                    <i class="ri-arrow-left-circle-fill"></i>
                </span>
                <span class="boton-form-text">Cancelar</span>
            </a>
            <!-- botón para limpiar contenido -->
            <button type="reset" class="boton-form boton-warning">
                <span class="boton-form-icon"> <i class="ri-paint-brush-fill"></i> </span>
                <span class="boton-form-text">Limpiar</span>
            </button>

            <button class="boton-form boton-success" type="submit" id="submitBtn">
                <span class="boton-form-icon"> <i class="ri-save-3-fill"></i> </span>
                <span class="boton-form-text">Crear Usuario</span>
            </button>
        </div>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Inicializar manejador de imágenes
                const imageHandler = initImageUpload({
                    mode: 'create'
                });

                // 1. Inicializar submit loader PRIMERO
                const submitLoader = initSubmitLoader({
                    formId: 'userForm',
                    buttonId: 'submitBtn',
                    loadingText: 'Guardando...'
                });

                // 2. Inicializar validación de formulario DESPUÉS
                const formValidator = initFormValidator('#userForm', {
                    validateOnBlur: true,
                    validateOnInput: false,
                    scrollToFirstError: true
                });

                // 3. Deshabilitar número de documento hasta que se elija tipo
                (function setupDocumentFields() {
                    const form = document.getElementById('userForm');
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

                    // Estado inicial
                    updateState();

                    // Actualizar al cambiar el tipo de documento
                    typeField.addEventListener('change', updateState);
                })();

                // Toggle password visibility
                document.querySelectorAll('.toggle-password').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const input = this.parentElement.querySelector('.password-input');
                        if (input.type === 'password') {
                            input.type = 'text';
                            this.querySelector('i').classList.remove('ri-eye-line');
                            this.querySelector('i').classList.add('ri-eye-off-line');
                            this.querySelector('i').style.animation = 'eyeBlink 0.3s';
                        } else {
                            input.type = 'password';
                            this.querySelector('i').classList.remove('ri-eye-off-line');
                            this.querySelector('i').classList.add('ri-eye-line');
                            this.querySelector('i').style.animation = 'eyeBlink 0.3s';
                        }
                        setTimeout(() => {
                            this.querySelector('i').style.animation = '';
                        }, 300);
                    });
                });
            });
        </script>
    @endpush
</x-admin-layout>
