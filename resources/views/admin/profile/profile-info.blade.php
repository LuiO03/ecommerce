<form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="form-container"
    autocomplete="off" id="profileForm">
    @csrf
    @method('PUT')

    <x-alert type="info" title="Información:" :dismissible="true" :items="['Los campos con asterisco (<i class=\'ri-asterisk text-accent\'></i>) son obligatorios.']" />

    <div class="form-row">

        <div class="form-profile-column">
            <span class="card-title">Información Personal</span>
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
            <span class="card-title">Información Adicional</span>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Desactivar el botón principal al seleccionar fondo
            const submitBtn = document.getElementById('submitBtn');
            const saveBackgroundBtn = document.getElementById('saveBackgroundBtn');
            document.querySelectorAll('.gallery-option').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.gallery-option').forEach(b => b.classList.remove('selected'));
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
            // Inicialmente desactivar el botón de fondo
            if (saveBackgroundBtn) saveBackgroundBtn.disabled = true;
        });
    </script>
    <div class="form-footer">
        <a href="{{ route('admin.dashboard') }}" class="boton-form boton-volver">
            <span class="boton-form-icon"><i class="ri-home-smile-2-fill"></i></span>
            <span class="boton-form-text">Volver al inicio</span>
        </a>
        <button class="boton-form boton-accent" type="submit" id="submitBtn" disabled>
            <span class="boton-form-icon"><i class="ri-save-line"></i></span>
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
        <span class="card-title">Fondo de perfil</span>
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
    <div class="form-footer">
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
