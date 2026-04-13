<x-app-layout>
    @section('title', 'Restablecer contraseña')
    <div class="auth-wrapper">
        <div class="auth-logo">
            @include('partials.admin.company-brand')
        </div>

        <div class="auth-card">
            <div class="auth-header">
                <h2 class="auth-title">Revisa tu correo</h2>
                <p class="auth-subtitle">
                    Si existe una cuenta asociada a ese correo,<br>
                    te hemos enviado un enlace para restablecer tu contraseña.
                </p>
            </div>

            <div class="auth-body">
                <x-alert type="success" title="¡Enlace enviado!">
                    Le hemos enviado por correo electrónico el enlace para restablecer su contraseña.
                </x-alert>

                <p class="mt-4 text-center text-sm text-muted">
                    El correo puede tardar unos minutos en llegar. Revisa también tu carpeta de spam o correo no
                    deseado.
                </p>

                <hr class="w-full my-6 border-default">

                <div class="auth-form-footer">
                    <a href="{{ route('password.request') }}" class="boton-form boton-success py-3">
                        <span class="boton-form-icon">
                            <i class="ri-refresh-line"></i>
                        </span>
                        <span class="boton-form-text">Intentarlo de nuevo</span>
                    </a>

                    <a href="{{ route('login') }}" class="boton-form boton-volver py-3">
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
