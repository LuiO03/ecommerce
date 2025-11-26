<form method="POST" action="{{ route('admin.profile.password') }}" class="form-container" autocomplete="off"
    id="passwordForm">
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

    <x-alert type="danger" title="Seguridad:" :dismissible="true" :items="[
        'Para cambiar tu contraseña, ingresa la actual y la nueva dos veces.',
        'Si no deseas cambiar tu contraseña, deja los campos en blanco.',
    ]" />

    <div class="form-row">
        <div class="form-profile-column column-password">
            <!-- === Contraseña actual === -->
            <div class="input-group">
                <label for="current_password" class="label-form">
                    Contraseña actual
                    <i class="ri-asterisk text-accent"></i>
                </label>
                <div class="input-icon-container">
                    <i class="ri-lock-line input-icon"></i>
                    <input type="password" name="current_password" id="current_password"
                        class="input-form password-input" placeholder="Ingresa tu contraseña actual" required
                        data-validate="required">
                    <button type="button" class="toggle-password" tabindex="-1" aria-label="Mostrar contraseña">
                        <i class="ri-eye-line"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="form-profile-column column-password">
            <!-- === Nueva contraseña === -->
            <div class="input-group">
                <label for="password" class="label-form">
                    Nueva contraseña
                    <i class="ri-asterisk text-accent"></i>
                </label>
                <div class="input-icon-container">
                    <i class="ri-lock-password-line input-icon"></i>
                    <input type="password" name="password" id="password" class="input-form password-input"
                        placeholder="Ingresa la nueva contraseña" required data-validate="required|min:6">
                    <button type="button" class="toggle-password" tabindex="-1" aria-label="Mostrar contraseña">
                        <i class="ri-eye-line"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="form-profile-column column-password">
            <!-- === Confirmar nueva contraseña === -->
            <div class="input-group">
                <label for="password_confirmation" class="label-form">
                    Confirmar nueva contraseña
                    <i class="ri-asterisk text-accent"></i>
                </label>
                <div class="input-icon-container">
                    <i class="ri-lock-password-line input-icon"></i>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        class="input-form password-input" placeholder="Repite la nueva contraseña" required
                        data-validate="required|confirmed:password">
                    <button type="button" class="toggle-password" tabindex="-1" aria-label="Mostrar contraseña">
                        <i class="ri-eye-line"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- === FOOTER DE ACCIONES === -->
    <div class="form-footer">
        <a href="{{ route('admin.dashboard') }}" class="boton-form boton-volver">
            <span class="boton-form-icon"><i class="ri-home-smile-2-fill"></i></span>
            <span class="boton-form-text">Volver al inicio</span>
        </a>
        <button class="boton-form boton-danger" type="submit" id="submitPasswordBtn">
            <span class="boton-form-icon"><i class="ri-lock-2-fill"></i></span>
            <span class="boton-form-text">Cambiar contraseña</span>
        </button>
    </div>
</form>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const formValidator = initFormValidator('#passwordForm', {
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
