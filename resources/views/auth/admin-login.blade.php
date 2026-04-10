<x-app-layout>


    <div class="auth-wrapper">
        <div class="auth-logo">
            @include('partials.admin.company-brand')
        </div>
        <div class="auth-card">
            <!-- Header con logo -->
            <div class="auth-header">
                <h2 class="auth-title">Bienvenido/a</h2>
                <p class="auth-subtitle">
                    Inicia sesión con tu correo electrónico <br>
                    o registrate para acceder
                </p>
            </div>

            <!-- Body del formulario -->
            <div class="auth-body">
                <!-- Errores de validación -->
                @if ($errors->any())
                    <div class="form-error-banner">
                        <i class="ri-error-warning-line form-error-icon"></i>
                        <div>
                            <p>{{ $errors->first() }}</p>
                        </div>
                    </div>
                @endif
                <!-- Mensaje de estado -->
                @session('status')
                    <x-alert type="success" title="¡Éxito!">
                        {{ $value }}
                    </x-alert>
                @endsession

                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf

                    <div class="input-group">
                        <label for="email" class="label-form">
                            Correo electrónico <i class="ri-asterisk text-accent"></i>
                        </label>
                        <div class="input-icon-container">
                            <i class="ri-mail-line input-icon"></i>
                            <input type="email" id="email" name="email" class="input-form"
                                placeholder="Ingresa tu correo electrónico" value="70098517@institutocajas.info"
                                required autofocus autocomplete="off" data-validate="required|email">
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="password" class="label-form">
                            Contraseña <i class="ri-asterisk text-accent"></i>
                        </label>
                        <div class="input-icon-container">
                            <i class="ri-lock-password-line input-icon"></i>
                            <input type="password" id="password" name="password" class="input-form password-input"
                                placeholder="Ingresa tu contraseña" value="luis988434679kira" required
                                autocomplete="off" data-validate="required|min:6|password">
                            <button type="button" class="toggle-password" tabindex="-1"
                                aria-label="Mostrar contraseña">
                                <i class="ri-eye-line"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember me -->
                    <div class="auth-options">
                        <div class="auth-remember">
                            <input type="checkbox" id="remember_me" name="remember" class="auth-checkbox">
                            <label for="remember_me" class="auth-checkbox-label">Recordarme</label>
                        </div>
                        <!-- Footer con link de recuperación -->
                        <div class="auth-recovery">
                            <a href="{{ route('password.request') }}" class="auth-link">
                                ¿Olvidaste tu contraseña?
                            </a>
                        </div>
                    </div>
                    <hr class="w-full my-0 border-default">
                    <div class="auth-form-footer">

                        <!-- Botón de login -->
                        <button class="boton-form boton-success py-3" type="submit" id="loginBtn">
                            <span class="boton-form-icon"> <i class="ri-login-box-line"></i> </span>
                            <span class="boton-form-text">Iniciar Sesión</span>
                        </button>
                        <a href="{{ route('site.home') }}" class="boton-form boton-volver py-3">
                            <span class="boton-form-icon">
                                <i class="ri-arrow-left-circle-fill"></i>
                            </span>
                            <span class="boton-form-text">Volver a inicio</span>
                        </a>
                    </div>
                </form>
            </div>
            <div class="auth-footer">
                <span>
                    ¿No tienes una cuenta?
                </span>
                <a href="{{ route('register') }}" class="auth-link-accent">Regístrate aquí</a>
            </div>
            <div class="auth-divider">
                <hr>
                <span>o</span>
                <hr>
            </div>
            {{-- Boton de google con socialite
            <a href="{{ route('google.redirect') }}" class="boton-google">
                <i class="ri-google-line boton-icon"></i>
                Iniciar con Google
            </a>
            --}}

            <div>
                <div id="g_id_onload" data-client_id="{{ config('services.google.client_id') }}"
                    data-callback="handleGoogleLogin">
                </div>

                <div class="g_id_signin" data-type="standard" data-size="large" data-theme="outline"
                    data-text="continue_with" data-shape="rectangular">
                </div>

            </div>
        </div>
    </div>

    <script>
        function handleGoogleLogin(response) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            fetch('{{ route('google.one-tap') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        credential: response.credential
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.redirect_url) {
                        window.location.href = data.redirect_url;
                    } else {
                        alert(data.message || "Error al iniciar sesión con Google");
                    }
                })
                .catch(() => {
                    alert("Error de conexión con el servidor");
                });

        }
        console.log('=== SCRIPT INLINE EJECUTÁNDOSE ===');

        // Esperar a que el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeLogin);
        } else {
            initializeLogin();
        }

        function initializeLogin() {
            console.log('=== INICIALIZANDO LOGIN ===');
            console.log('window object:', window);
            console.log('initFormValidator:', window.initFormValidator);
            console.log('initSubmitLoader:', window.initSubmitLoader);
            console.log('typeof initFormValidator:', typeof window.initFormValidator);
            console.log('typeof initSubmitLoader:', typeof window.initSubmitLoader);

            // Verificar si las funciones existen
            if (typeof window.initFormValidator !== 'function') {
                console.error('❌ initFormValidator NO está disponible');
                return;
            }

            if (typeof window.initSubmitLoader !== 'function') {
                console.error('❌ initSubmitLoader NO está disponible');
                return;
            }

            console.log('✅ Ambas funciones disponibles, inicializando...');

            try {
                // 1. Inicializar validación de formulario
                const formValidator = window.initFormValidator('#loginForm', {
                    validateOnBlur: true,
                    validateOnInput: false,
                    scrollToFirstError: true,
                    showSuccessIndicators: true
                });
                console.log('✅ FormValidator inicializado:', formValidator);

                // 2. Inicializar submit loader
                const submitLoader = window.initSubmitLoader({
                    formId: 'loginForm',
                    buttonId: 'loginBtn',
                    loadingText: 'Iniciando sesión...'
                });
                console.log('✅ SubmitLoader inicializado:', submitLoader);

                // 3. Toggle password visibility
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

                console.log('=== INICIALIZACIÓN COMPLETA ===');
            } catch (error) {
                console.error('❌ Error durante inicialización:', error);
            }
        }
    </script>
</x-app-layout>

</html>
