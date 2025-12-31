<form method="POST" action="{{ route('admin.company-settings.update-general') }}" id="companySettingsGeneralForm">
    @csrf
    @if ($errors->hasBag('general') && $errors->general->any())
        <div class="form-error-banner">
            <i class="ri-error-warning-line form-error-icon"></i>
            <div>
                <h4 class="form-error-title">Se encontraron los siguientes errores:</h4>
                <ul>
                    @foreach ($errors->general->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
    <section id="companySettingsSectionGeneral" class="settings-section is-active" data-section="general" role="tabpanel"
        aria-labelledby="tab-general">

    <div class="form-body">
        <div class="card-header">
            <span class="card-title">Información general</span>
            <p class="card-description">
                Configura la información básica de tu empresa que se mostrará en el sitio web y en los documentos
                oficiales.
            </p>
        </div>
    </div>

    <div class="form-body">
        <div class="form-row-fit">
            <div class="input-group">
                <label for="name" class="label-form">
                    Nombre comercial
                    <i class="ri-asterisk text-accent"></i>
                </label>
                <div class="input-icon-container">
                    <i class="ri-store-2-line input-icon"></i>
                    <input type="text" name="name" id="name" class="input-form" required
                        value="{{ old('name', $setting->name) }}" placeholder="Ej. GeckoCommerce"
                        data-validate="required|min:3|max:255">
                </div>
            </div>

            <div class="input-group">
                <label for="legal_name" class="label-form">Razón social</label>
                <div class="input-icon-container">
                    <i class="ri-briefcase-4-line input-icon"></i>
                    <input type="text" name="legal_name" id="legal_name" class="input-form"
                        value="{{ old('legal_name', $setting->legal_name) }}" placeholder="Ej. GeckoCommerce S.A.C."
                        data-validate="max:255">
                </div>
            </div>
        </div>
    </div>

    <div class="form-columns-row">
        <div class="form-column">
            <div class="input-group">
                <label for="ruc" class="label-form">RUC</label>
                <div class="input-icon-container">
                    <i class="ri-file-paper-2-line input-icon"></i>
                    <input type="text" name="ruc" id="ruc" class="input-form"
                        value="{{ old('ruc', $setting->ruc) }}" placeholder="11 dígitos" data-validate="ruc">
                </div>
            </div>

            <div class="input-group">
                <label for="slogan" class="label-form">Eslogan</label>
                <div class="input-icon-container">
                    <i class="ri-chat-quote-line input-icon"></i>
                    <input type="text" name="slogan" id="slogan" class="input-form"
                        value="{{ old('slogan', $setting->slogan) }}" placeholder="Mensaje corto"
                        data-validate="max:255">
                </div>
            </div>

            <div class="input-group">
                <label for="about" class="label-form label-textarea">Descripción corta</label>
                <div class="input-icon-container">
                    <i class="ri-file-text-line input-icon"></i>
                    <textarea name="about" id="about" class="textarea-form" rows="5"
                        placeholder="Describe brevemente la empresa" data-validate="max:1500">{{ old('about', $setting->about) }}</textarea>
                </div>
            </div>
        </div>
    </div>
    <div class="form-footer">
        <a href="{{ route('admin.dashboard') }}" class="boton-form boton-volver">
            <span class="boton-form-icon"><i class="ri-home-smile-2-fill"></i></span>
            <span class="boton-form-text">Volver al inicio</span>
        </a>
        <button class="boton-form boton-accent" type="submit" id="generalSubmitBtn">
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
                formId: 'companySettingsGeneralForm',
                buttonId: 'generalSubmitBtn',
                loadingText: 'Actualizando...'
            });

            initFormValidator('#companySettingsGeneralForm', {
                validateOnBlur: true,
                validateOnInput: false,
                scrollToFirstError: true
            });
        });
    </script>
@endpush
