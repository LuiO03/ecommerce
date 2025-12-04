<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-success"><i class="ri-add-circle-line"></i></div>
        Nuevo Usuario
    </x-slot>
    <x-slot name="action">
        <a href="{{ route('admin.users.index') }}" class="boton boton-secondary">
            <span class="boton-icon"><i class="ri-arrow-go-back-line"></i></span>
            <span class="boton-text">Volver</span>
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

        <x-alert type="info" title="Información:" :dismissible="true" :items="['Los campos con asterisco (<i class=\'ri-asterisk text-accent\'></i>) son obligatorios.']" />

        <div class="form-row">
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
        <div class="form-row">
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
                        value="{{ old('email') }}" placeholder="usuario@ejemplo.com" data-validate="required|email">
                </div>
            </div>
            <!-- === Estado === -->
            <div class="input-group">
                <label for="status" class="label-form">
                    Estado
                    <i class="ri-asterisk text-accent"></i>
                </label>
                <div class="input-icon-container">
                    <i class="ri-focus-2-line input-icon"></i>
                    <select name="status" id="status" class="select-form" required
                        data-validate="required|selected">
                        <option value="" disabled selected>Seleccione un estado</option>
                        <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Activo</option>
                        <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                    <i class="ri-arrow-down-s-line select-arrow"></i>
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

        <div class="form-columns-row">
            <div class="form-column">
                <!-- === Imagen === -->
                <div class="image-upload-section">
                    <label class="label-form">Foto de perfil</label>
                    <input type="file" name="image" id="image" class="file-input" accept="image/*"
                        data-validate="image|maxSizeMB:3">

                    <!-- Zona de vista previa -->
                    <div class="image-preview-zone" id="imagePreviewZone">
                        <div class="image-placeholder" id="imagePlaceholder">
                            <i class="ri-image-add-line"></i>
                            <p>Arrastra una imagen aquí</p>
                            <span>o haz clic para seleccionar</span>
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
                    </div>
                    <!-- Nombre del archivo (temporal) -->
                    <div class="image-filename" id="imageFilename" style="display: none;">
                        <i class="ri-file-image-line"></i>
                        <span id="filenameText"></span>
                    </div>
                </div>
            </div>
            <div class="form-column">
                <!-- === Dirección === -->
                <div class="input-group">
                    <label for="address" class="label-form">Dirección</label>
                    <div class="input-icon-container">
                        <i class="ri-map-pin-line input-icon"></i>
                        <input type="text" name="address" id="address" class="input-form"
                            value="{{ old('address') }}" placeholder="Ingrese la dirección" data-validate="max:255">
                    </div>
                </div>

                <!-- === DNI === -->
                <div class="input-group">
                    <label for="dni" class="label-form">DNI</label>
                    <div class="input-icon-container">
                        <i class="ri-id-card-line input-icon"></i>
                        <input type="text" name="dni" id="dni" class="input-form"
                            value="{{ old('dni') }}" placeholder="8 dígitos" data-validate="dni">
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
            <a href="{{ route('admin.users.index') }}" class="boton-form boton-volver">
                <span class="boton-form-icon"> <i class="ri-arrow-left-circle-fill"></i> </span>
                <span class="boton-form-text">Cancelar</span>
            </a>
            <!-- boton para limpiar contenido -->
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
