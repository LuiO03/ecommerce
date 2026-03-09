<x-app-layout>
    <div class="auth-wrapper">
        <div class="auth-logo">
            <img src="{{ asset('images/logos/logo-geckomerce.png') }}" alt="Logo">
            <div class="sidebar-logo-texto"><strong>Gecko</strong><span>merce</span></div>
        </div>
        <div class="auth-card">
            <!-- Header con logo -->
            <div class="auth-header">
                <h2 class="auth-title">Crear cuenta</h2>
                <p class="auth-subtitle">Ingresa tus datos para registrarte en la tienda.</p>
            </div>

            <!-- Body del formulario -->
            <div class="auth-body">
                <!-- Errores de validación -->

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

                <!-- Mensaje de estado -->
                @session('status')
                    <div class="auth-status">
                        {{ $value }}
                    </div>
                @endsession

                <form method="POST" action="{{ route('register') }}" id="registerForm">
                    @csrf

                    <div class="input-group">
                        <label for="name" class="label-form">
                            Nombre
                        </label>
                        <div class="input-icon-container">
                            <i class="ri-user-line input-icon"></i>
                            <input type="text" id="name" name="name" class="input-form"
                                placeholder="Ingresa tu nombre" value="{{ old('name') }}" required autocomplete="off"
                                data-validate="required|alpha|min:3|max:50"
                                data-validate-messages='{
                                    "required":"El nombre es obligatorio",
                                    "alpha":"Solo se permiten letras",
                                    "min":"Mínimo 3 caracteres",
                                    "max":"Máximo 50 caracteres"
                                }'>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="last_name" class="label-form">
                            Apellido
                        </label>
                        <div class="input-icon-container">
                            <i class="ri-user-3-line input-icon"></i>
                            <input type="text" id="last_name" name="last_name" class="input-form"
                                placeholder="Ingresa tu apellido" value="{{ old('last_name') }}" autocomplete="off"
                                data-validate="alpha|max:50"
                                data-validate-messages='{
                                    "alpha":"Solo se permiten letras",
                                    "max":"Máximo 50 caracteres"
                                }'>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="email" class="label-form">
                            Correo electrónico
                        </label>
                        <div class="input-icon-container">
                            <i class="ri-mail-line input-icon"></i>
                            <input type="email" id="email" name="email" class="input-form"
                                placeholder="Ingresa tu correo electrónico" value="{{ old('email') }}" required
                                autocomplete="off" data-validate="required|email"
                                data-validate-messages='{
                                    "required":"El correo es obligatorio",
                                    "email":"Ingresa un correo válido"
                                }'>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="address" class="label-form">
                            Dirección
                        </label>
                        <div class="input-icon-container">
                            <i class="ri-map-pin-line input-icon"></i>
                            <input type="text" id="address" name="address" class="input-form"
                                placeholder="Ingresa tu dirección" value="{{ old('address') }}" autocomplete="off"
                                data-validate="max:255"
                                data-validate-messages='{
                                    "max":"Máximo 255 caracteres"
                                }'>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="password" class="label-form">
                            Contraseña
                        </label>
                        <div class="input-icon-container">
                            <i class="ri-lock-password-line input-icon"></i>
                            <input type="password" id="password" name="password" class="input-form password-input"
                                placeholder="Crea una contraseña" required autocomplete="off"
                                data-validate="required|min:6"
                                data-validate-messages='{
                                    "required":"La contraseña es obligatoria",
                                    "min":"Debe tener al menos 6 caracteres"
                                }'>
                            <button type="button" class="toggle-password" tabindex="-1"
                                aria-label="Mostrar contraseña">
                                <i class="ri-eye-line"></i>
                            </button>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="password_confirmation" class="label-form">
                            Confirmar contraseña
                        </label>
                        <div class="input-icon-container">
                            <i class="ri-lock-line input-icon"></i>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                class="input-form password-input" placeholder="Repite tu contraseña" required
                                autocomplete="off" data-validate="required|confirmed:password"
                                data-validate-messages='{
                                    "required":"La confirmación es obligatoria",
                                    "confirmed":"Las contraseñas no coinciden"
                                }'>
                            <button type="button" class="toggle-password" tabindex="-1"
                                aria-label="Mostrar contraseña">
                                <i class="ri-eye-line"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-footer mt-4">
                        <a href="{{ route('welcome.index') }}" class="boton-form boton-volver">
                            <span class="boton-form-icon">
                                <i class="ri-arrow-left-circle-fill"></i>
                            </span>
                            <span class="boton-form-text">Atrás</span>
                        </a>
                        <!-- Botón de registro -->
                        <button class="boton-form boton-success" type="submit" id="registerBtn">
                            <span class="boton-form-icon"> <i class="ri-user-add-line"></i> </span>
                            <span class="boton-form-text">Crear cuenta</span>
                        </button>
                    </div>
                </form>
            </div>
            <div class="auth-footer">
                <span>
                    ¿Ya tienes una cuenta?
                </span>
                <a href="{{ route('login') }}" class="auth-link-accent">Inicia sesión aquí</a>
            </div>

            <div class="auth-divider">
                <hr>
                <span>o</span>
                <hr>
            </div>
            <a href="{{ route('google.redirect') }}" class="boton-google">
                <i class="ri-google-line boton-icon"></i>
                Regístrate con Google
            </a>
        </div>
    </div>

    <script>
        console.log('=== SCRIPT INLINE REGISTER EJECUTÁNDOSE ===');

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeRegister);
        } else {
            initializeRegister();
        }

        function initializeRegister() {
            console.log('=== INICIALIZANDO REGISTER ===');
            console.log('initFormValidator:', window.initFormValidator);
            console.log('initSubmitLoader:', window.initSubmitLoader);

            if (typeof window.initFormValidator !== 'function') {
                console.error('❌ initFormValidator NO está disponible');
                return;
            }

            if (typeof window.initSubmitLoader !== 'function') {
                console.error('❌ initSubmitLoader NO está disponible');
                return;
            }

            try {
                const formValidator = window.initFormValidator('#registerForm', {
                    validateOnBlur: true,
                    validateOnInput: false,
                    scrollToFirstError: true,
                    showSuccessIndicators: true,
                });
                console.log('✅ FormValidator (register) inicializado:', formValidator);

                const submitLoader = window.initSubmitLoader({
                    formId: 'registerForm',
                    buttonId: 'registerBtn',
                    loadingText: 'Creando cuenta...'
                });
                console.log('✅ SubmitLoader (register) inicializado:', submitLoader);

                document.querySelectorAll('.toggle-password').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const input = this.parentElement.querySelector('.password-input');
                        if (!input) return;

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

                console.log('=== INICIALIZACIÓN REGISTER COMPLETA ===');
            } catch (error) {
                console.error('❌ Error durante inicialización de registro:', error);
            }
        }
    </script>
</x-app-layout>
