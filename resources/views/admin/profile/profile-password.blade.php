
<form method="POST" action="{{ route('admin.profile.password') }}" class="form-container" autocomplete="off" id="passwordForm">
    @csrf
    @method('PUT')

    <x-alert type="info" title="Seguridad:" :dismissible="true"
        :items="['Para cambiar tu contraseña, ingresa la actual y la nueva dos veces.']" />

    <div class="form-row">
        <div class="form-profile-column column-password">
            <!-- === Contraseña actual === -->
            <div class="input-group">
                <label for="current_password" class="label-form">
                    Contraseña actual
                    <i class="ri-asterisk text-accent"></i>
                </label>
                <div class="input-icon-container">
                    <i class="ri-lock-line input-icon"></i>
                    <input type="password" name="current_password" id="current_password" class="input-form"
                        placeholder="Ingresa tu contraseña actual" required data-validate="required">
                </div>
            </div>
        </div>
        <div class="form-profile-column column-password">
            <!-- === Nueva contraseña === -->
            <div class="input-group">
                <label for="password" class="label-form">
                    Nueva contraseña
                    <i class="ri-asterisk text-accent"></i>
                </label>
                <div class="input-icon-container">
                    <i class="ri-lock-password-line input-icon"></i>
                    <input type="password" name="password" id="password" class="input-form"
                        placeholder="Ingresa la nueva contraseña" required data-validate="required|min:6">
                </div>
            </div>
        </div>
        <div class="form-profile-column column-password">
            <!-- === Confirmar nueva contraseña === -->
            <div class="input-group">
                <label for="password_confirmation" class="label-form">
                    Confirmar nueva contraseña
                    <i class="ri-asterisk text-accent"></i>
                </label>
                <div class="input-icon-container">
                    <i class="ri-lock-password-line input-icon"></i>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="input-form"
                        placeholder="Repite la nueva contraseña" required data-validate="required|same:password">
                </div>
            </div>
        </div>
    </div>

    <!-- === FOOTER DE ACCIONES === -->
    <div class="form-footer">
        <a href="{{ route('admin.dashboard') }}" class="boton-form boton-volver">
            <span class="boton-form-icon"><i class="ri-home-smile-2-fill"></i></span>
            <span class="boton-form-text">Volver al inicio</span>
        </a>
        <button class="boton-form boton-danger" type="submit" id="submitPasswordBtn">
            <span class="boton-form-icon"><i class="ri-lock-2-fill"></i></span>
            <span class="boton-form-text">Cambiar contraseña</span>
        </button>
    </div>
</form>
