<x-app-layout>
    <div class="auth-wrapper">
        <div class="auth-logo">
            @include('partials.admin.company-brand')
        </div>
        <div class="auth-card">
            <div class="auth-header">
                <h2 class="auth-title">Restablecer contraseña</h2>
                <p class="auth-subtitle">
                    Crea una nueva contraseña para tu cuenta.
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
                    <div class="auth-status">
                        {{ $value }}
                    </div>
                @endsession

                @php
                    $token = $request->route('token');
                    $email = old('email', $request->email);
                @endphp

                <form method="POST" action="{{ route('password.update') }}" id="resetPasswordForm">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="input-group">
                        <label for="email" class="label-form">
                            Correo electrónico <i class="ri-asterisk text-accent"></i>
                        </label>
                        <div class="input-icon-container">
                            <i class="ri-mail-line input-icon"></i>
                            <input type="email" id="email" name="email" class="input-form"
                                   placeholder="Ingresa tu correo electrónico" value="{{ $email }}"
                                   required autocomplete="off" data-validate="required|email">
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="password" class="label-form">
                            Nueva contraseña <i class="ri-asterisk text-accent"></i>
                        </label>
                        <div class="input-icon-container">
                            <i class="ri-lock-password-line input-icon"></i>
                            <input type="password" id="password" name="password" class="input-form password-input"
                                   placeholder="Ingresa tu nueva contraseña" required autocomplete="off"
                                   data-validate="required|min:6|password">
                            <button type="button" class="toggle-password" tabindex="-1" aria-label="Mostrar contraseña">
                                <i class="ri-eye-line"></i>
                            </button>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="password_confirmation" class="label-form">
                            Confirmar contraseña <i class="ri-asterisk text-accent"></i>
                        </label>
                        <div class="input-icon-container">
                            <i class="ri-lock-line input-icon"></i>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                   class="input-form password-input" placeholder="Repite tu nueva contraseña" required
                                   autocomplete="off" data-validate="required|confirmed:password">
                            <button type="button" class="toggle-password" tabindex="-1" aria-label="Mostrar contraseña">
                                <i class="ri-eye-line"></i>
                            </button>
                        </div>
                    </div>
                    <hr class="w-full my-0 border-default">
                    <div class="auth-form-footer">
                        <button class="boton-form boton-success" type="submit" id="resetPasswordBtn">
                            <span class="boton-form-icon"> <i class="ri-shield-keyhole-line"></i> </span>
                            <span class="boton-form-text">Restablecer contraseña</span>
                        </button>
                        <a href="{{ route('login') }}" class="boton-form boton-volver">
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
        console.log('=== INICIALIZANDO RESET PASSWORD ===');

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeResetPassword);
        } else {
            initializeResetPassword();
        }

        function initializeResetPassword() {
            if (typeof window.initFormValidator !== 'function' || typeof window.initSubmitLoader !== 'function') {
                console.error('❌ FormValidator o SubmitLoader no disponibles en reset-password');
                return;
            }

            try {
                const formValidator = window.initFormValidator('#resetPasswordForm', {
                    validateOnBlur: true,
                    validateOnInput: false,
                    scrollToFirstError: true,
                    showSuccessIndicators: true
                });

                const submitLoader = window.initSubmitLoader({
                    formId: 'resetPasswordForm',
                    buttonId: 'resetPasswordBtn',
                    loadingText: 'Actualizando contraseña...'
                });

                document.querySelectorAll('.toggle-password').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const input = this.parentElement.querySelector('.password-input');
                        if (!input) return;

                        if (input.type === 'password') {
                            input.type = 'text';
                            this.querySelector('i').classList.remove('ri-eye-line');
                            this.querySelector('i').classList.add('ri-eye-off-line');
                        } else {
                            input.type = 'password';
                            this.querySelector('i').classList.remove('ri-eye-off-line');
                            this.querySelector('i').classList.add('ri-eye-line');
                        }
                    });
                });

                console.log('✅ ResetPassword FormValidator y SubmitLoader inicializados', {
                    formValidator,
                    submitLoader,
                });
            } catch (error) {
                console.error('❌ Error durante inicialización de reset-password:', error);
            }
        }
    </script>
</x-app-layout>
