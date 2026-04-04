<x-app-layout>
    <div class="auth-wrapper">
        <div class="auth-logo">
            @include('partials.admin.company-brand')
        </div>

        <div class="auth-card" style="max-width: 640px;">
            <div class="auth-header">
                <h2 class="auth-title">Verifica tu correo electrónico</h2>
                <p class="auth-subtitle">
                    Te hemos enviado un enlace de verificación a <strong>{{ $user->email ?? 'tu correo electrónico' }}</strong>.<br>
                    Haz clic en el enlace para activar tu cuenta y poder iniciar sesión.
                </p>
            </div>

            <div class="auth-body">
                @if (session('status'))
                    <x-alert type="success" title="¡Correo reenviado!">
                        {{ session('status') }}
                    </x-alert>
                @endif

                @if ($errors->any())
                    <div class="form-error-banner">
                        <i class="ri-error-warning-line form-error-icon"></i>
                        <div>
                            <p>{{ $errors->first() }}</p>
                        </div>
                    </div>
                @endif

                <p class="mb-6 text-sm text-muted">
                    Si no ves el correo en tu bandeja de entrada, revisa también la carpeta de spam
                    o correo no deseado.
                </p>

                <div class="flex flex-col gap-3">
                    @isset($verificationUrl)
                        <a href="{{ $verificationUrl }}" class="boton-form boton-success py-3 text-center">
                            <span class="boton-form-icon">
                                <i class="ri-mail-check-line"></i>
                            </span>
                            <span class="boton-form-text">Verificar ahora</span>
                        </a>
                    @endisset

                    <a href="{{ route('login') }}" class="boton-form boton-volver py-3 text-center">
                        <span class="boton-form-icon">
                            <i class="ri-arrow-left-circle-fill"></i>
                        </span>
                        <span class="boton-form-text">Volver al inicio de sesión</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
