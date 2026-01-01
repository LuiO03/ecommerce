<form method="POST" action="{{ route('admin.company-settings.update-contact') }}" id="companySettingsContactForm">
    @csrf
    @if ($errors->hasBag('contact') && $errors->contact->any())
        <div class="form-error-banner">
            <i class="ri-error-warning-line form-error-icon"></i>
            <div>
                <h4 class="form-error-title">Se encontraron los siguientes errores:</h4>
                <ul>
                    @foreach ($errors->contact->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
    <section id="companySettingsSectionContact" data-section="contact" role="tabpanel"
        aria-labelledby="tab-contact" class="container-section">

        <div class="form-body">
            <div class="card-header">
                <span class="card-title">Información de contacto</span>
                <p class="card-description">
                    Configura los datos de contacto de tu empresa que se mostrarán en el sitio web y en los documentos
                    oficiales.
                </p>
            </div>
        </div>
        <div class="form-columns-row">
            <div class="form-column">
                <div class="input-group">
                    <label for="email" class="label-form">Correo principal</label>
                    <div class="input-icon-container">
                        <i class="ri-mail-line input-icon"></i>
                        <input type="email" name="email" id="email" class="input-form"
                            value="{{ old('email', $setting->email) }}" placeholder="contacto@empresa.com"
                            data-validate="email|max:255">
                    </div>
                </div>

                <div class="input-group">
                    <label for="support_email" class="label-form">Correo de soporte</label>
                    <div class="input-icon-container">
                        <i class="ri-customer-service-2-line input-icon"></i>
                        <input type="email" name="support_email" id="support_email" class="input-form"
                            value="{{ old('support_email', $setting->support_email) }}"
                            placeholder="soporte@empresa.com" data-validate="email|max:255">
                    </div>
                </div>

                <div class="input-group">
                    <label for="phone" class="label-form">Teléfono principal</label>
                    <div class="input-icon-container">
                        <i class="ri-phone-line input-icon"></i>
                        <input type="text" name="phone" id="phone" class="input-form"
                            value="{{ old('phone', $setting->phone) }}" placeholder="+51 999 888 777"
                            data-validate="max:25|phone|min:9">
                    </div>
                </div>
            </div>

            <div class="form-column">
                <div class="input-group">
                    <label for="support_phone" class="label-form">Teléfono de soporte</label>
                    <div class="input-icon-container">
                        <i class="ri-headphone-line input-icon"></i>
                        <input type="text" name="support_phone" id="support_phone" class="input-form"
                            value="{{ old('support_phone', $setting->support_phone) }}" placeholder="+51 977 888 111"
                            data-validate="max:25|phone|min:7">
                    </div>
                </div>

                <div class="input-group">
                    <label for="address" class="label-form">Dirección</label>
                    <div class="input-icon-container">
                        <i class="ri-map-pin-line input-icon"></i>
                        <input type="text" name="address" id="address" class="input-form"
                            value="{{ old('address', $setting->address) }}" placeholder="Av. Principal 123"
                            data-validate="max:255">
                    </div>
                </div>

                <div class="input-group">
                    <label for="website" class="label-form">Sitio web</label>
                    <div class="input-icon-container">
                        <i class="ri-global-line input-icon"></i>
                        <input type="url" name="website" id="website" class="input-form"
                            value="{{ old('website', $setting->website) }}" placeholder="https://empresa.com"
                            data-validate="url|max:255">
                    </div>
                </div>
            </div>
        </div>
        <div class="form-footer">
            <a href="{{ route('admin.dashboard') }}" class="boton-form boton-volver">
                <span class="boton-form-icon"><i class="ri-home-smile-2-fill"></i></span>
                <span class="boton-form-text">Volver al inicio</span>
            </a>
            <button class="boton-form boton-accent" type="submit" id="contactSubmitBtn">
                <span class="boton-form-icon"><i class="ri-save-3-line"></i></span>
                <span class="boton-form-text">Guardar Información</span>
            </button>
        </div>
    </section>
</form>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initSubmitLoader({
                formId: 'companySettingsContactForm',
                buttonId: 'contactSubmitBtn',
                loadingText: 'Actualizando...'
            });

            initFormValidator('#companySettingsContactForm', {
                validateOnBlur: true,
                validateOnInput: false,
                scrollToFirstError: true
            });
        });
    </script>
@endpush
