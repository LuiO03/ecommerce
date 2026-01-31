<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login - Panel Administrativo | {{ config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Remix Icon -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.7.0/fonts/remixicon.css" rel="stylesheet" />

    <!-- CSS base del dashboard -->
    @vite(['resources/css/admin/layout.css'])
    <!-- CSS de Tailwind y JS del admin -->
    @vite(['resources/css/app.css', 'resources/js/admin.js'])
    @stack('styles')
    @livewireStyles

</head>
<script>
    // Evitar flash blanco: aplicar tema y estado del sidebar antes del renderizado
    (function() {
        try {
            const theme = localStorage.getItem("color-theme");
            const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
            if (theme === "dark" || (!theme && prefersDark)) {
                document.documentElement.classList.add("dark");
            } else {
                document.documentElement.classList.remove("dark");
            }

            // Aplicar estado colapsado del sidebar antes del primer paint
            const sidebarCollapsed = localStorage.getItem("sidebarCollapsed") === "true";
            if (sidebarCollapsed) {
                document.documentElement.classList.add("sidebar-start-collapsed");
            } else {
                document.documentElement.classList.remove("sidebar-start-collapsed");
            }
        } catch (e) {
            // Ignorar errores de acceso a localStorage
        }
    })();
</script>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <!-- Header con logo -->
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="ri-shield-user-line"></i>
                </div>
                <h1 class="auth-title">Panel Administrativo</h1>
                <p class="auth-subtitle">Ingresa tus credenciales para acceder</p>
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
                    <div class="auth-status">
                        {{ $value }}
                    </div>
                @endsession

                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf

                    <div class="input-group">
                        <label for="email" class="label-form">
                            Correo electrónico
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
                            Contraseña
                        </label>
                        <div class="input-icon-container">
                            <i class="ri-lock-password-line input-icon"></i>
                            <input type="password" id="password" name="password" class="input-form password-input"
                                placeholder="Ingresa tu contraseña" value="luis988434679kira" required
                                autocomplete="off" data-validate="required|min:6">
                            <button type="button" class="toggle-password" tabindex="-1" aria-label="Mostrar contraseña">
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
                        @if (Route::has('password.request'))
                            <div class="auth-recovery">
                                <a href="{{ route('password.request') }}" class="auth-link">
                                    ¿Olvidaste tu contraseña?
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="form-footer mt-4">
                        <a href="{{ url()->previous() }}" class="boton-form boton-volver">
                            <span class="boton-form-icon">
                                <i class="ri-arrow-left-circle-fill"></i>
                            </span>
                            <span class="boton-form-text">Atras</span>
                        </a>
                        <!-- Botón de login -->
                        <button class="boton-form boton-success" type="submit" id="loginBtn">
                            <span class="boton-form-icon"> <i class="ri-login-box-line"></i> </span>
                            <span class="boton-form-text">Iniciar Sesión</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
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
</body>

</html>
