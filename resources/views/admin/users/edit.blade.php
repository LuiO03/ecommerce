<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-info"><i class="ri-edit-circle-line"></i></div>
        <div class="page-edit-title">
            <span class="page-subtitle">Editar Usuario</span>
            {{ $user->name }} {{ $user->last_name }}
        </div>
    </x-slot>
    <x-slot name="action">
        <a href="{{ route('admin.users.index') }}" class="boton boton-secondary">
            <span class="boton-icon"><i class="ri-arrow-go-back-line"></i></span>
            <span class="boton-text">Volver</span>
        </a>
        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="delete-form"
            data-entity="usuario" style="margin: 0;">
            @csrf
            @method('DELETE')
            <button class="boton boton-danger" type="submit"
                @if (Auth::id() === $user->id) disabled title="No puedes eliminar tu propia cuenta" @endif>
                <span class="boton-icon"><i class="ri-delete-bin-6-fill"></i></span>
                <span class="boton-text">Eliminar</span>
            </button>
        </form>

    </x-slot>

    <!-- === FORMULARIO DE ACTUALIZACIÓN === -->
    <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data"
        class="form-container" autocomplete="off" id="userForm">
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

        <x-alert type="info" title="Información:" :dismissible="true" :items="['Los campos con asterisco (<i class=\'ri-asterisk text-accent\'></i>) son obligatorios.']" />

        <div class="form-row">
            <div class="form-column">
                <!-- === Nombre === -->
                <div class="input-group">
                    <label for="name" class="label-form">
                        Nombre
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-user-line input-icon"></i>
                        <input type="text" name="name" id="name" class="input-form"
                            placeholder="Ingrese el nombre" required value="{{ old('name', $user->name) }}"
                            data-validate="required|min:3|max:255|alpha">
                    </div>
                </div>

                <!-- === Apellido === -->
                <div class="input-group">
                    <label for="last_name" class="label-form">Apellido</label>
                    <div class="input-icon-container">
                        <i class="ri-user-line input-icon"></i>
                        <input type="text" name="last_name" id="last_name" class="input-form"
                            value="{{ old('last_name', $user->last_name) }}" placeholder="Ingrese el apellido"
                            data-validate="min:3|max:255|alpha">
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
                            value="{{ old('email', $user->email) }}" placeholder="usuario@ejemplo.com"
                            data-validate="required|email">
                    </div>
                </div>

                <!-- === Rol === -->
                <div class="input-group select-group">
                    <label for="role" class="label-form">
                        Rol
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-shield-user-line input-icon"></i>
                        <select name="role" id="role" class="select-form" required
                            data-validate="required|selected">
                            <option value="" disabled>Seleccione un rol</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}" @selected(old('role', $user->roles->first()?->name) == $role->name)>
                                    {{ ucfirst($role->name) }}
                                </option>
                            @endforeach
                        </select>
                        <i class="ri-arrow-down-s-line select-arrow"></i>
                    </div>
                </div>

                <!-- === Estado === -->
                <div class="input-group select-group">
                    <label for="status" class="label-form">
                        Estado
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-focus-2-line input-icon"></i>
                        <select name="status" id="status" class="select-form" required
                            data-validate="required|selected">
                            <option value="" disabled>Seleccione un estado</option>

                            <option value="1" @selected(old('status', $user->status) == 1)>
                                Activo
                            </option>

                            <option value="0" @selected(old('status', $user->status) == 0)>
                                Inactivo
                            </option>
                        </select>

                        <i class="ri-arrow-down-s-line select-arrow"></i>
                    </div>
                </div>
            </div>

            <div class="form-column">
                <!-- === Imagen === -->
                <div class="image-upload-section">
                    <label class="label-form">Foto de perfil</label>
                    <input type="file" name="image" id="image" class="file-input" accept="image/*"
                        data-validate="image|maxSizeMB:3">
                    <input type="hidden" name="remove_image" id="removeImageFlag" value="0">

                    <!-- Zona de vista previa -->
                    <div class="image-preview-zone {{ $user->image && file_exists(public_path('storage/' . $user->image)) ? 'has-image' : '' }}"
                        id="imagePreviewZone">
                        @if ($user->image && file_exists(public_path('storage/' . $user->image)))
                            <img id="imagePreview" class="image-preview image-pulse"
                                src="{{ asset('storage/' . $user->image) }}" alt="{{ $user->name }}">
                            <!-- Placeholder oculto inicialmente (se mostrará al eliminar) -->
                            <div class="image-placeholder" id="imagePlaceholder" style="display: none;">
                                <i class="ri-image-add-line"></i>
                                <p>Arrastra una imagen aquí</p>
                                <span>o haz clic para seleccionar</span>
                            </div>
                        @elseif($user->image)
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
                        style="{{ $user->image && file_exists(public_path('storage/' . $user->image)) ? 'display: flex;' : 'display: none;' }}">
                        <i class="ri-file-image-line"></i>
                        <span id="filenameText">{{ $user->image ? basename($user->image) : '' }}</span>
                    </div>
                </div>

                <!-- === Dirección === -->
                <div class="input-group">
                    <label for="address" class="label-form">Dirección</label>
                    <div class="input-icon-container">
                        <i class="ri-map-pin-line input-icon"></i>
                        <input type="text" name="address" id="address" class="input-form"
                            value="{{ old('address', $user->address) }}" placeholder="Ingrese la dirección"
                            data-validate="max:255">
                    </div>
                </div>

                <!-- === DNI === -->
                <div class="input-group">
                    <label for="dni" class="label-form">DNI</label>
                    <div class="input-icon-container">
                        <i class="ri-id-card-line input-icon"></i>
                        <input type="text" name="dni" id="dni" class="input-form"
                            value="{{ old('dni', $user->dni) }}" placeholder="8 dígitos" data-validate="dni">
                    </div>
                </div>

                <!-- === Teléfono === -->
                <div class="input-group">
                    <label for="phone" class="label-form">Teléfono</label>
                    <div class="input-icon-container">
                        <i class="ri-phone-line input-icon"></i>
                        <input type="text" name="phone" id="phone" class="input-form"
                            value="{{ old('phone', $user->phone) }}" placeholder="9 dígitos" data-validate="phone">
                    </div>
                </div>
            </div>
        </div>

        <!-- === FOOTER DE ACCIONES === -->
        <div class="form-footer">
            <a href="{{ route('admin.users.index') }}" class="boton-form boton-volver">
                <span class="boton-form-icon"><i class="ri-arrow-left-circle-fill"></i></span>
                <span class="boton-form-text">Cancelar</span>
            </a>

            <button class="boton-form boton-accent" type="submit" id="submitBtn">
                <span class="boton-form-icon"><i class="ri-loop-left-line"></i></span>
                <span class="boton-form-text">Actualizar Usuario</span>
            </button>
        </div>
    </form>

    @push('scripts')
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
                    formId: 'userForm',
                    buttonId: 'submitBtn',
                    loadingText: 'Actualizando...'
                });

                // 2. Inicializar validación de formulario DESPUÉS
                const formValidator = initFormValidator('#userForm', {
                    validateOnBlur: true,
                    validateOnInput: false,
                    scrollToFirstError: true
                });
            });
        </script>
    @endpush
</x-admin-layout>
