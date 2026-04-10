<x-app-layout>

    <section class="site-container checkout-payment-page">
        <div class="checkout-payment-card payment-card-success">
            <div class="checkout-payment-icon payment-icon-success">
                <i class="ri-mail-check-fill"></i>
            </div>

            <h2 class="checkout-payment-title">
                {{ $user->name ?? 'Tu cuenta' }}, tu correo fue verificado correctamente
            </h2>

            <div class="checkout-payment-meta">
                <x-alert type="success" title="Correo verificado">
                    Tu dirección de correo electrónico ha sido confirmada.
                    Ya puedes iniciar sesión y usar tu cuenta en la tienda.
                </x-alert>

                <p class="checkout-payment-message mt-4">
                    Si no reconoces esta acción, te recomendamos cambiar tu contraseña desde la sección de seguridad
                    de tu cuenta una vez que hayas iniciado sesión.
                </p>
            </div>

            <div class="checkout-payment-actions">
                <a href="{{ route('site.home') }}" class="boton-form boton-success py-3">
                    <span class="boton-form-icon">
                        <i class="ri-store-2-fill"></i>
                    </span>
                    <span class="boton-form-text">
                        <span>Ir a la tienda</span>
                    </span>
                </a>

                <a href="{{ route('login') }}" class="boton-form boton-info py-3">
                    <span class="boton-form-icon">
                        <i class="ri-login-box-line"></i>
                    </span>
                    <span class="boton-form-text">
                        <span>Iniciar sesión</span>
                    </span>
                </a>
            </div>
        </div>
    </section>

</x-app-layout>
