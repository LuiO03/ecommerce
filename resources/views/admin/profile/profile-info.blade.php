<form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="form-container"
    autocomplete="off" id="profileForm">
    @csrf
    @method('PUT')

    <x-alert type="info" title="Información:" :dismissible="true" :items="['Los campos con asterisco (<i class=\'ri-asterisk text-accent\'></i>) son obligatorios.']" />

    <div class="form-row">
        <div class="form-profile-column">
            <span class="card-title">Información Personal</span>
            <!-- === Nombre === -->
            <div class="input-group">
                <label for="name" class="label-form">
                    Nombre
                    <i class="ri-asterisk text-accent"></i>
                </label>
                <div class="input-icon-container">
                    <i class="ri-user-line input-icon"></i>
                    <input type="text" name="name" id="name" class="input-form"
                        placeholder="Ingrese el nombre" required value="{{ old('name', $user->name) }}"
                        data-validate="required|min:3|max:255|alpha">
                </div>
            </div>

            <!-- === Apellido === -->
            <div class="input-group">
                <label for="last_name" class="label-form">Apellido</label>
                <div class="input-icon-container">
                    <i class="ri-user-line input-icon"></i>
                    <input type="text" name="last_name" id="last_name" class="input-form"
                        value="{{ old('last_name', $user->last_name) }}" placeholder="Ingrese el apellido"
                        data-validate="min:3|max:255|alpha">
                </div>
            </div>

            <!-- === Email === -->
            <div class="input-group">
                <label for="email" class="label-form">
                    Email
                    <i class="ri-asterisk text-accent"></i>
                </label>
                <div class="input-icon-container">
                    <i class="ri-mail-line input-icon"></i>
                    <input type="email" name="email" id="email" class="input-form" required
                        value="{{ old('email', $user->email) }}" placeholder="usuario@ejemplo.com"
                        data-validate="required|email">
                </div>
            </div>
        </div>

        <div class="form-profile-column">
            <span class="card-title">Información Adicional</span>
            <!-- === Dirección === -->
            <div class="input-group">
                <label for="address" class="label-form">Dirección</label>
                <div class="input-icon-container">
                    <i class="ri-map-pin-line input-icon"></i>
                    <input type="text" name="address" id="address" class="input-form"
                        value="{{ old('address', $user->address) }}" placeholder="Ingrese la dirección"
                        data-validate="max:255">
                </div>
            </div>

            <!-- === DNI === -->
            <div class="input-group">
                <label for="dni" class="label-form">DNI</label>
                <div class="input-icon-container">
                    <i class="ri-id-card-line input-icon"></i>
                    <input type="text" name="dni" id="dni" class="input-form"
                        value="{{ old('dni', $user->dni) }}" placeholder="8 dígitos" data-validate="dni">
                </div>
            </div>

            <!-- === Teléfono === -->
            <div class="input-group">
                <label for="phone" class="label-form">Teléfono</label>
                <div class="input-icon-container">
                    <i class="ri-phone-line input-icon"></i>
                    <input type="text" name="phone" id="phone" class="input-form"
                        value="{{ old('phone', $user->phone) }}" placeholder="9 dígitos" data-validate="phone">
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
        <button class="boton-form boton-accent" type="submit" id="submitBtn">
            <span class="boton-form-icon"><i class="ri-save-line"></i></span>
            <span class="boton-form-text">Guardar cambios</span>
        </button>
    </div>
</form>
