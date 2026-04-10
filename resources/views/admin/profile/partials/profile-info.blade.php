<form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="form-container"
    autocomplete="off" id="profileForm">
    @csrf
    @method('PUT')

    <x-alert type="info" title="Información:" :dismissible="true" :items="['Los campos con asterisco (<i class=\'ri-asterisk text-accent\'></i>) son obligatorios.']" />

    <div class="form-columns-row">

        <div class="form-profile-column">
            <div class="card-header">
                <span class="card-title">Información Personal</span>
                <p class="card-description">Actualiza la información de tu perfil y dirección de contacto.</p>
            </div>
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
        </div>

        <div class="form-profile-column">
            <div class="card-header">
                <span class="card-title">Información Adicional</span>
                <p class="card-description">Proporciona información adicional para tu perfil.</p>
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

            <!-- === Tipo de documento (opcional) === -->
            <div class="input-group">
                <label for="document_type" class="label-form">Tipo de documento</label>
                <div class="input-icon-container">
                    <i class="ri-id-card-line input-icon"></i>
                    <select name="document_type" id="document_type" class="select-form">
                        <option value="">Seleccione una opción</option>
                        <option value="DNI" {{ old('document_type', $user->document_type) == 'DNI' ? 'selected' : '' }}>DNI</option>
                        <option value="RUC" {{ old('document_type', $user->document_type) == 'RUC' ? 'selected' : '' }}>RUC</option>
                        <option value="CE" {{ old('document_type', $user->document_type) == 'CE' ? 'selected' : '' }}>Carné de extranjería</option>
                        <option value="PASAPORTE" {{ old('document_type', $user->document_type) == 'PASAPORTE' ? 'selected' : '' }}>Pasaporte</option>
                    </select>
                </div>
            </div>

            <!-- === Número de documento === -->
            <div class="input-group">
                <label for="document_number" class="label-form">Número de documento</label>
                <div class="input-icon-container">
                    <i class="ri-hashtag input-icon"></i>
                    <input type="text" name="document_number" id="document_number" class="input-form"
                        value="{{ old('document_number', $user->document_number) }}" placeholder="Ingresa el número de documento"
                        data-validate="document_number|max:30|requiredWith:document_type">
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Desactivar el botón principal al seleccionar fondo
            const submitBtn = document.getElementById('submitBtn');
            const saveBackgroundBtn = document.getElementById('saveBackgroundBtn');
            document.querySelectorAll('.gallery-option').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.gallery-option').forEach(b => b.classList.remove(
                        'selected'));
                    this.classList.add('selected');
                    const bgInput = document.getElementById('background_style');
                    bgInput.value = this.dataset.style;
                    document.querySelectorAll('.gallery-check').forEach(i => i.remove());
                    const check = document.createElement('i');
                    check.className = 'ri-checkbox-circle-fill gallery-check';
                    this.appendChild(check);
                    // Solo activar el botón de fondo
                    if (saveBackgroundBtn) saveBackgroundBtn.disabled = false;
                    if (submitBtn) submitBtn.disabled = true;
                });
            });

            // Deshabilitar número de documento hasta que se elija tipo en el perfil
            const form = document.getElementById('profileForm');
            if (form) {
                const typeField = form.querySelector('#document_type');
                const numberField = form.querySelector('#document_number');

                if (typeField && numberField) {
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
                    typeField.addEventListener('change', updateState);
                }
            }
        });
    </script>
    <div class="form-footer-static">
        <a href="{{ route('admin.dashboard') }}" class="boton-form boton-volver">
            <span class="boton-form-icon"><i class="ri-home-smile-2-fill"></i></span>
            <span class="boton-form-text">Volver al inicio</span>
        </a>
        <button class="boton-form boton-accent" type="submit" id="submitBtn">
            <span class="boton-form-icon"><i class="ri-save-fill"></i></span>
            <span class="boton-form-text">Guardar cambios</span>
        </button>
    </div>
</form>

<form method="POST" action="{{ route('admin.profile.update') }}" class="form-container mt-5" id="backgroundForm"
    autocomplete="off">
    @csrf
    @method('PUT')
    <input type="hidden" name="only_background" value="1">
    <div class="form-profile-column">
        <div class="card-header">
            <span class="card-title">Fondo de perfil</span>
            <p class="card-description">Elige un diseño para el fondo de tu perfil.</p>
        </div>
        <label for="background_style" class="label-form">Elige tu fondo</label>
        <div class="background-gallery">
            @php
                $fondos = [
                    'fondo-estilo-1',
                    'fondo-estilo-2',
                    'fondo-estilo-4',
                    'fondo-estilo-5',
                    'fondo-estilo-6',
                    'fondo-estilo-7',
                    'fondo-estilo-8',
                    'fondo-estilo-9',
                    'fondo-estilo-10',
                    'fondo-estilo-11',
                    'fondo-estilo-12',
                    'fondo-estilo-13',
                    'fondo-estilo-14',
                    'fondo-estilo-15',
                    'fondo-estilo-17',
                    'fondo-estilo-18',
                    'fondo-estilo-19',
                    'fondo-estilo-20',
                    'fondo-estilo-21',
                    'fondo-estilo-22',
                    'fondo-estilo-23',
                    'fondo-estilo-24',
                    'fondo-estilo-25',
                ];
            @endphp
            <input type="hidden" name="background_style" id="background_style"
                value="{{ old('background_style', $user->background_style) }}">
            <div class="gallery-options">
                @foreach ($fondos as $fondo)
                    <button type="button"
                        class="gallery-option {{ $user->background_style == $fondo ? 'selected' : '' }}"
                        data-style="{{ $fondo }}">
                        <div class="gallery-preview {{ $fondo }}"></div>
                        <span class="gallery-label">{{ Str::replace('fondo-estilo-', 'Diseño ', $fondo) }}</span>
                        @if ($user->background_style == $fondo)
                            <i class="ri-checkbox-circle-fill gallery-check"></i>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    </div>
    <div class="form-footer-static">
        <button type="submit" id="saveBackgroundBtn" class="boton-form boton-accent">
            <span class="boton-form-icon"><i class="ri-paint-brush-fill"></i></span>
            <span class="boton-form-text">Guardar fondo</span>
        </button>
    </div>
</form>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const formValidator = initFormValidator('#profileForm', {
                validateOnBlur: true,
                validateOnInput: false,
                scrollToFirstError: true
            });
            // 1. Inicializar submit loader PRIMERO
            const submitLoader = initSubmitLoader({
                formId: 'profileForm',
                buttonId: 'submitBtn',
                loadingText: 'Actualizando...'
            });
        });
    </script>
@endpush
