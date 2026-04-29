<form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="form-container"
    autocomplete="off" id="profileForm">
    @csrf
    @method('PUT')

    <x-alert type="info" title="Información:" :dismissible="true" :items="['Los campos con asterisco (<i class=\'ri-asterisk text-accent\'></i>) son obligatorios.']" />

    <div class="form-columns-row">
        <div class="form-body">
            <div class="card-header">
                <span class="card-title">Información Personal</span>
                <p class="card-description">Actualiza la información de tu perfil y dirección de contacto.</p>
            </div>
            <div class="form-row-fit">
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

        <div class="form-body">
            <div class="card-header">
                <span class="card-title">Información Adicional</span>
                <p class="card-description">Proporciona información adicional para tu perfil.</p>
            </div>
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

            <div class="form-row-fit">
                <!-- === Tipo de documento (opcional) === -->
                <div class="input-group">
                    <label for="document_type" class="label-form">Tipo de documento</label>
                    <div class="input-icon-container">
                        <i class="ri-id-card-line input-icon"></i>
                        <select name="document_type" id="document_type" class="select-form">
                            <option value="">Seleccione una opción</option>
                            <option value="DNI"
                                {{ old('document_type', $user->document_type) == 'DNI' ? 'selected' : '' }}>DNI</option>
                            <option value="RUC"
                                {{ old('document_type', $user->document_type) == 'RUC' ? 'selected' : '' }}>RUC</option>
                            <option value="CE"
                                {{ old('document_type', $user->document_type) == 'CE' ? 'selected' : '' }}>Carné de
                                extranjería</option>
                            <option value="PASAPORTE"
                                {{ old('document_type', $user->document_type) == 'PASAPORTE' ? 'selected' : '' }}>
                                Pasaporte
                            </option>
                        </select>
                    </div>
                </div>

                <!-- === Número de documento === -->
                <div class="input-group">
                    <label for="document_number" class="label-form">Número de documento</label>
                    <div class="input-icon-container">
                        <i class="ri-hashtag input-icon"></i>
                        <input type="text" name="document_number" id="document_number" class="input-form"
                            value="{{ old('document_number', $user->document_number) }}"
                            placeholder="Ingresa el número de documento"
                            data-validate="document_number|max:30|requiredWith:document_type">
                    </div>
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
    <div class="form-body">
        <div class="card-header">
            <span class="card-title">Fondo de perfil</span>
            <p class="card-description">Elige un diseño para el fondo de tu perfil.</p>
        </div>
        <label for="background_style" class="label-form">Elige tu fondo</label>
        <div class="background-gallery">
            <input type="hidden" name="background_style" id="background_style"
                value="{{ old('background_style', $user->background_style) }}">
            <div class="gallery-options">
                @foreach ($fondos as $fondo)
                    <button type="button"
                        class="gallery-option {{ $user->background_style == $fondo ? 'selected' : '' }}"
                        data-style="{{ $fondo }}">
                        <div class="gallery-preview {{ $fondo }}"></div>
                        <span class="gallery-label">{{ Str::replace('fondo-estilo-', 'Diseño ', $fondo) }}</span>
                        @if ($user->background_style == $fondo)
                            <i class="ri-checkbox-circle-fill gallery-check"></i>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    </div>
    <div class="form-footer">
        <a href="{{ route('admin.dashboard') }}" class="boton-form boton-volver">
            <span class="boton-form-icon"><i class="ri-home-smile-2-fill"></i></span>
            <span class="boton-form-text">Volver al inicio</span>
        </a>
        <button class="boton-form boton-accent" type="submit" id="submitBtn">
            <span class="boton-form-icon"><i class="ri-save-fill"></i></span>
            <span class="boton-form-text">Guardar cambios</span>
        </button>
    </div>
</form>

@push('scripts')
    <script>
    function updateProfileTabIcons() {
        document.querySelectorAll('.profile-tab-btn').forEach(btn => {
            const icon = btn.querySelector('i[data-icon-line][data-icon-fill]');
            if (!icon) return;

            const lineClass = icon.getAttribute('data-icon-line');
            const fillClass = icon.getAttribute('data-icon-fill');

            if (btn.classList.contains('active')) {
                icon.classList.remove(lineClass);
                icon.classList.add(fillClass);
            } else {
                icon.classList.remove(fillClass);
                icon.classList.add(lineClass);
            }
        });
    }

    function showTab(tabName) {
        const tabs = document.querySelectorAll('.profile-tab-content');

        tabs.forEach(tab => {
            tab.classList.remove('fade-in');
            tab.classList.add('hidden');
        });

        const activeTab = document.getElementById('tab-' + tabName);
        if (!activeTab) return;

        activeTab.classList.remove('hidden');

        // reinicia animación correctamente
        void activeTab.offsetWidth;

        activeTab.classList.add('fade-in');
    }

    document.querySelectorAll('.profile-tab-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const tabName = this.dataset.tab;

            showTab(tabName);

            document.querySelectorAll('.profile-tab-btn')
                .forEach(b => b.classList.remove('active'));

            this.classList.add('active');

            localStorage.setItem('profileActiveTab', tabName);

            updateProfileTabIcons();
        });
    });

    // Inicialización
    const savedTab = localStorage.getItem('profileActiveTab');

    let initialTab = savedTab || 'info';

    if (window.location.hash === '#sessions') {
        initialTab = 'sessions';
    }

    document.querySelectorAll('.profile-tab-content')
        .forEach(tab => tab.classList.add('hidden'));

    const initialTabEl = document.getElementById('tab-' + initialTab);

    if (initialTabEl) {
        initialTabEl.classList.remove('hidden');
        initialTabEl.classList.add('fade-in');
    }

    document.querySelectorAll('.profile-tab-btn')
        .forEach(b => b.classList.remove('active'));

    const activeBtn = document.querySelector('.profile-tab-btn[data-tab="' + initialTab + '"]');
    if (activeBtn) activeBtn.classList.add('active');

    updateProfileTabIcons();
</script>
@endpush
