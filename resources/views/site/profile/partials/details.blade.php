<div class="profile-section">
    <div class="alert-info-note">
        <i class="ri-information-fill"></i>
        <span>
            Los campos con asterisco <i class="ri-asterisk text-accent"></i> son obligatorios.
        </span>
    </div>

    <form method="POST" action="{{ route('site.profile.details.update') }}" class="form-container" autocomplete="off">
        @csrf
        @method('PUT')
        <div class="cards-profile">
            <div class="form-body">
                <div class="card-header">
                    <span class="card-title">Foto de perfil</span>
                    <p class="card-description">Agrega una foto para personalizar tu cuenta.</p>
                </div>
            </div>
            <div class="form-body">
                <div class="card-header">
                    <span class="card-title">Datos personales</span>
                    <p class="card-description">Mantén tu información actualizada para facilitar tus compras y comunicaciones.
                    </p>
                </div>
                <div class="form-row-fit">

                    <div class="input-group">
                        <label for="name" class="label-form">
                            Nombre
                            <i class="ri-asterisk text-accent"></i>
                        </label>
                        <div class="input-icon-container">
                            <i class="ri-user-line input-icon"></i>
                            <input type="text" name="name" id="name" class="input-form" placeholder="Tu nombre"
                                required value="{{ old('name', $user->name) }}" data-validate="required|min:3|max:255">
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="last_name" class="label-form">Apellidos</label>
                        <div class="input-icon-container">
                            <i class="ri-user-line input-icon"></i>
                            <input type="text" name="last_name" id="last_name" class="input-form" placeholder="Tus apellidos"
                                value="{{ old('last_name', $user->last_name) }}" data-validate="max:255">
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="email" class="label-form">
                            Correo electrónico
                            <i class="ri-asterisk text-accent"></i>
                        </label>
                        <div class="input-icon-container">
                            <i class="ri-mail-line input-icon"></i>
                            <input type="email" name="email" id="email" class="input-form"
                                placeholder="usuario@ejemplo.com" required value="{{ old('email', $user->email) }}"
                                data-validate="required|email">
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="phone" class="label-form">Teléfono </label>
                        <div class="input-icon-container">
                            <i class="ri-phone-line input-icon"></i>
                            <input type="text" name="phone" id="phone" class="input-form"
                                placeholder="Número de contacto" value="{{ old('phone', $user->phone) }}"
                                data-validate="max:20">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr class="w-full my-0 border-default">
        <div class="card-header">
            <span class="card-title">Datos para facturación </span>
            <p class="card-description">Completa estos campos si deseas agilizar tus futuras compras.</p>
        </div>
        <div class="form-row-fit">
            <div class="input-group">
                <label for="address" class="label-form">Dirección</label>
                <div class="input-icon-container">
                    <i class="ri-map-pin-line input-icon"></i>
                    <input type="text" name="address" id="address" class="input-form"
                        placeholder="Calle, número, barrio" value="{{ old('address', $user->address) }}"
                        data-validate="max:255">
                </div>
            </div>

            <div class="input-group">
                <label for="document_type" class="label-form">Tipo de documento</label>
                <div class="input-icon-container">
                    <i class="ri-id-card-line input-icon"></i>
                    <select name="document_type" id="document_type" class="select-form">
                        <option value="">Selecciona una opción</option>
                        <option value="DNI"
                            {{ old('document_type', $user->document_type) == 'DNI' ? 'selected' : '' }}>DNI</option>
                        <option value="RUC"
                            {{ old('document_type', $user->document_type) == 'RUC' ? 'selected' : '' }}>RUC</option>
                        <option value="CE"
                            {{ old('document_type', $user->document_type) == 'CE' ? 'selected' : '' }}>Carné de
                            extranjería</option>
                        <option value="PASAPORTE"
                            {{ old('document_type', $user->document_type) == 'PASAPORTE' ? 'selected' : '' }}>
                            Pasaporte</option>
                    </select>
                </div>
            </div>

            <div class="input-group">
                <label for="document_number" class="label-form">Número de documento</label>
                <div class="input-icon-container">
                    <i class="ri-hashtag input-icon"></i>
                    <input type="text" name="document_number" id="document_number" class="input-form"
                        placeholder="Ingresa tu número de documento"
                        value="{{ old('document_number', $user->document_number) }}" data-validate="max:30">
                </div>
            </div>
        </div>

        <div class="form-footer-static">
            <button class="boton-form boton-accent" type="submit">
                <span class="boton-form-icon"><i class="ri-save-fill"></i></span>
                <span class="boton-form-text">Guardar cambios</span>
            </button>
        </div>
    </form>
    <hr class="w-full my-0 border-default">
    <div class="form-container" id="password-section">
        <form method="POST" action="{{ route('site.profile.details.password') }}" class="form-container"
            autocomplete="off">
            @csrf
            @method('PUT')

            <x-alert type="danger" title="Advertencia" :dismissible="true" :items="[
                'Para cambiar tu contraseña, ingresa la actual y la nueva dos veces.',
                'Si no deseas cambiar tu contraseña, deja los campos en blanco.',
            ]" />

            <div class="card-header">
                <span class="card-title">Cambiar contraseña</span>
                <p class="card-description">Elige una contraseña segura que solo tú conozcas.</p>
            </div>
            <div class="form-row-fit">
                <div class="input-group">
                    <label for="current_password" class="label-form">
                        Contraseña actual
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-lock-line input-icon"></i>
                        <input type="password" name="current_password" id="current_password" class="input-form"
                            placeholder="Ingresa tu contraseña actual" data-validate="required">
                    </div>
                </div>

                <div class="input-group">
                    <label for="password" class="label-form">
                        Nueva contraseña
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-lock-password-line input-icon"></i>
                        <input type="password" name="password" id="password" class="input-form"
                            placeholder="Ingresa la nueva contraseña" data-validate="required|min:8">
                    </div>
                </div>

                <div class="input-group">
                    <label for="password_confirmation" class="label-form">
                        Confirmar nueva contraseña
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-lock-password-line input-icon"></i>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="input-form" placeholder="Repite la nueva contraseña"
                            data-validate="required|confirmed:password">
                    </div>
                </div>
            </div>

            <div class="form-footer-static">
                <button class="boton-form boton-danger" type="submit">
                    <span class="boton-form-icon"><i class="ri-lock-2-fill"></i></span>
                    <span class="boton-form-text">Actualizar contraseña</span>
                </button>
            </div>
        </form>
    </div>

</div>
