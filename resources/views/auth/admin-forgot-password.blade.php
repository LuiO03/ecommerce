<x-app-layout>
    @section('title', 'Restablecer contraseña')
    <div class="auth-wrapper">
        <div class="auth-logo">
            @include('partials.admin.company-brand')
        </div>
        <div class="auth-card">
            <div class="auth-header">
                <h2 class="auth-title">¿Olvidaste tu contraseña?</h2>
                <p class="auth-subtitle">
                    Por favor, introduce tu correo electrónico. Recibirás un enlace para crear una contraseña nueva por correo electrónico.
                </p>
            </div>

            <div class="auth-body">
                @if ($errors->any())
                    <div class="form-error-banner">
                        <i class="ri-error-warning-line form-error-icon"></i>
                        <div>
                            <p>{{ $errors->first() }}</p>
                        </div>
                    </div>
                @endif

                @session('status')
                    <x-alert type="success" title="¡Éxito!">
                        {{ $value }}
                    </x-alert>
                @endsession

                <form method="POST" action="{{ route('password.email') }}" id="forgotPasswordForm">
                    @csrf

                    <div class="input-group">
                        <label for="email" class="label-form">
                            Correo electrónico <i class="ri-asterisk text-accent"></i>
                        </label>
                        <div class="input-icon-container">
                            <i class="ri-mail-line input-icon"></i>
                            <input type="email" id="email" name="email" class="input-form"
                                placeholder="Ingresa tu correo electrónico" value="{{ old('email') }}" required
                                autofocus autocomplete="off" data-validate="required|email">
                        </div>
                    </div>
                    <hr class="w-full my-0 border-default">
                    <div class="auth-form-footer">
                        <button class="boton-form boton-success w-full py-3" type="submit" id="forgotPasswordBtn">
                            <span class="boton-form-icon"> <i class="ri-mail-send-fill"></i> </span>
                            <span class="boton-form-text">Enviar enlace</span>
                        </button>
                        <a href="{{ route('login') }}" class="boton-form boton-volver py-3">
                            <span class="boton-form-icon">
                                <i class="ri-arrow-left-circle-fill"></i>
                            </span>
                            <span class="boton-form-text">Volver al inicio de sesión</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        console.log('=== INICIALIZANDO FORGOT PASSWORD ===');

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeForgotPassword);
        } else {
            initializeForgotPassword();
        }

        function initializeForgotPassword() {
            if (typeof window.initFormValidator !== 'function' || typeof window.initSubmitLoader !== 'function') {
                console.error('❌ FormValidator o SubmitLoader no disponibles en forgot-password');
                return;
            }

            try {
                const formValidator = window.initFormValidator('#forgotPasswordForm', {
                    validateOnBlur: true,
                    validateOnInput: false,
                    scrollToFirstError: true,
                    showSuccessIndicators: true
                });

                const submitLoader = window.initSubmitLoader({
                    formId: 'forgotPasswordForm',
                    buttonId: 'forgotPasswordBtn',
                    loadingText: 'Enviando enlace...'
                });

                console.log('✅ ForgotPassword FormValidator y SubmitLoader inicializados', {
                    formValidator,
                    submitLoader,
                });
            } catch (error) {
                console.error('❌ Error durante inicialización de forgot-password:', error);
            }
        }
    </script>
</x-app-layout>
