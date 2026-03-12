<x-app-layout>
    <div class="auth-wrapper">
        <div class="auth-logo">
            <img src="{{ asset('images/logos/logo-geckommerce.png') }}" alt="Logo">
            <div class="sidebar-logo-texto"><strong>Gecko</strong><span>merce</span></div>
        </div>
        <div class="auth-card" style="max-width: 820px;">
            <!-- Header con logo -->
            <div class="auth-header">
                <h2 class="auth-title">Crear cuenta</h2>
                <p class="auth-subtitle">Ingresa tus datos para registrarte en la tienda.</p>
            </div>
            {{-- Boton de google con socialite
            <a href="{{ route('google.redirect') }}" class="boton-google">
                <i class="ri-google-line boton-icon"></i>
                Regístrate con Google
            </a>
            --}}
            <div class="auth-provider-logins">
                <div id="g_id_onload" data-client_id="{{ config('services.google.client_id') }}"
                    data-callback="handleGoogleLogin">
                </div>

                <div class="g_id_signin" data-type="standard" data-size="large" data-theme="outline"
                    data-text="continue_with" data-shape="rectangular">
                </div>
            </div>

            <div class="auth-divider">
                <hr>
                <span>o</span>
                <hr>
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
                    <x-alert type="success" title="¡Éxito!">
                        {{ $value }}
                    </x-alert>
                @endsession

                <form method="POST" action="{{ route('register') }}" id="registerForm">
                    @csrf

                    <div class="form-row-fill">
                        <div class="input-group">
                            <label for="name" class="label-form">
                                Nombre <i class="ri-asterisk text-accent"></i>
                            </label>
                            <div class="input-icon-container">
                                <i class="ri-user-line input-icon"></i>
                                <input type="text" id="name" name="name" class="input-form"
                                    placeholder="Ingresa tu nombre" value="{{ old('name') }}" required autocomplete="off"
                                    data-validate="required|alpha|min:3|max:50">
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="last_name" class="label-form">
                                Apellido <i class="ri-asterisk text-accent"></i>
                            </label>
                            <div class="input-icon-container">
                                <i class="ri-user-3-line input-icon"></i>
                                <input type="text" id="last_name" name="last_name" class="input-form"
                                    placeholder="Ingresa tu apellido" value="{{ old('last_name') }}" autocomplete="off"
                                    data-validate="alpha|max:50">
                            </div>
                        </div>
                    </div>
                    <div class="form-row-fill">
                        <div class="input-group">
                            <label for="email" class="label-form">
                                Correo electrónico <i class="ri-asterisk text-accent"></i>
                            </label>
                            <div class="input-icon-container">
                                <i class="ri-mail-line input-icon"></i>
                                <input type="email" id="email" name="email" class="input-form"
                                    placeholder="Ingresa tu correo electrónico" value="{{ old('email') }}" required
                                    autocomplete="off" data-validate="required|email">
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="address" class="label-form">
                                Dirección <i class="ri-asterisk text-accent"></i>
                            </label>
                            <div class="input-icon-container">
                                <i class="ri-map-pin-line input-icon"></i>
                                <input type="text" id="address" name="address" class="input-form"
                                    placeholder="Ingresa tu dirección" value="{{ old('address') }}" autocomplete="off"
                                    data-validate="max:255">
                            </div>
                        </div>
                    </div>
                    <div class="form-row-fill">
                        <div class="input-group">
                            <label for="document_type" class="label-form">
                                Tipo de documento <i class="ri-asterisk text-accent"></i>
                            </label>
                            <div class="input-icon-container">
                                <i class="ri-id-card-line input-icon"></i>
                                <select id="document_type" name="document_type" class="select-form"
                                    data-validate="selected">
                                    <option value="">Seleccione una opción</option>
                                    <option value="DNI" {{ old('document_type') == 'DNI' ? 'selected' : '' }}>DNI</option>
                                    <option value="RUC" {{ old('document_type') == 'RUC' ? 'selected' : '' }}>RUC</option>
                                    <option value="CE" {{ old('document_type') == 'CE' ? 'selected' : '' }}>Carné de extranjería</option>
                                    <option value="PASAPORTE" {{ old('document_type') == 'PASAPORTE' ? 'selected' : '' }}>Pasaporte</option>
                                </select>
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="document_number" class="label-form">
                                Número de documento <i class="ri-asterisk text-accent"></i>
                            </label>
                            <div class="input-icon-container">
                                <i class="ri-hashtag input-icon"></i>
                                <input type="text" id="document_number" name="document_number" class="input-form"
                                    placeholder="Ingresa tu número de documento" value="{{ old('document_number') }}"
                                    autocomplete="off" data-validate="document_number|max:30">
                            </div>
                        </div>
                    </div>
                    <div class="form-row-fill">
                        <div class="input-group">
                            <label for="password" class="label-form">
                                Contraseña <i class="ri-asterisk text-accent"></i>
                            </label>
                            <div class="input-icon-container">
                                <i class="ri-lock-password-line input-icon"></i>
                                <input type="password" id="password" name="password" class="input-form password-input"
                                    placeholder="Crea una contraseña" required autocomplete="off"
                                    data-validate="required|min:6">
                                <button type="button" class="toggle-password" tabindex="-1"
                                    aria-label="Mostrar contraseña">
                                    <i class="ri-eye-line"></i>
                                </button>
                            </div>
                            <p class="input-help-text">
                                La contraseña debe tener al menos 15 caracteres O al menos 8 caracteres, incluyendo un
                                número y una letra minúscula.
                            </p>
                        </div>

                        <div class="input-group">
                            <label for="password_confirmation" class="label-form">
                                Confirmar contraseña
                            </label>
                            <div class="input-icon-container">
                                <i class="ri-lock-line input-icon"></i>
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                    class="input-form password-input" placeholder="Repite tu contraseña" required
                                    autocomplete="off" data-validate="required|confirmed:password">
                                <button type="button" class="toggle-password" tabindex="-1"
                                    aria-label="Mostrar contraseña">
                                    <i class="ri-eye-line"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <hr class="w-full my-0 border-default">
                    <div class="form-footer">
                        <!-- Botón de registro -->
                        <button class="boton-form boton-success py-3" type="submit" id="registerBtn">
                            <span class="boton-form-icon"> <i class="ri-user-add-line"></i> </span>
                            <span class="boton-form-text">Crear cuenta</span>
                        </button>
                        <a href="{{ route('welcome.index') }}" class="boton-form boton-volver py-3">
                            <span class="boton-form-icon">
                                <i class="ri-arrow-left-circle-fill"></i>
                            </span>
                            <span class="boton-form-text">Volver a inicio</span>
                        </a>
                    </div>
                    <p class="input-help-text">
                        Al registrarte, estás creando una cuenta y aceptas las <a href=""
                            class="auth-link">Condiciones de uso</a> y la <a href=""
                            class="auth-link">Política de privacidad</a>
                    </p>
                </form>
            </div>
            <div class="auth-footer">
                <span>
                    ¿Ya tienes una cuenta?
                </span>
                <a href="{{ route('login') }}" class="auth-link-accent">Inicia sesión aquí</a>
            </div>
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
