<x-app-layout>

    <section class="site-container checkout-payment-page">
        <div class="checkout-payment-card payment-card-failure">
            <div class="checkout-payment-icon payment-icon-failure">
                <i class="ri-error-warning-fill"></i>
            </div>

            <h2 class="checkout-payment-title">
                No pudimos verificar tu correo
            </h2>

            <p class="checkout-payment-message">
                Parece que el enlace de verificación no es válido o ha expirado.
                Es posible que haya sido modificado o que ya haya sido utilizado.
            </p>

            <p class="checkout-payment-message mt-2">
                Si aún no has verificado tu cuenta, puedes solicitar un nuevo correo de verificación ingresando el correo con el que te registraste.
            </p>

            <form method="POST" action="{{ route('site.verification.resend') }}" class="mt-4 space-y-3 max-w-md w-full">
                @csrf
                <div class="input-group">
                    <label for="email" class="label-form">Correo electrónico</label>
                    <div class="input-icon-container">
                        <i class="ri-mail-line input-icon"></i>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="input-form"
                            placeholder="Ingresa tu correo registrado"
                            required
                            autocomplete="email"
                        >
                    </div>
                </div>

                <p class="input-help-text">
                    Por seguridad, solo podremos reenviar el correo si tu cuenta existe y aún no ha sido verificada.
                    Para evitar abusos, este enlace solo se puede reenviar cada pocos minutos.
                </p>

                <button type="submit" class="boton-form boton-info py-3">
                    <span class="boton-form-icon">
                        <i class="ri-mail-send-line"></i>
                    </span>
                    <span class="boton-form-text">
                        <span>Reenviar correo de verificación</span>
                    </span>
                </button>
            </form>

            <div class="checkout-payment-actions">
                <a href="{{ route('site.home') }}" class="boton-form boton-success py-3">
                    <span class="boton-form-icon">
                        <i class="ri-store-2-fill"></i>
                    </span>
                    <span class="boton-form-text">
                        <span>Volver a la tienda</span>
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
