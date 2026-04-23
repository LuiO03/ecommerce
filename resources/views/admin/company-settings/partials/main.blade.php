@php
    $normalizeColor = static function ($value, $fallback) {
        $color = $value ?: $fallback;
        $color = '#' . ltrim($color, '#');

        if (preg_match('/^#[0-9a-fA-F]{3}$/', $color) === 1) {
            $chars = substr($color, 1);
            $color = '#' . $chars[0] . $chars[0] . $chars[1] . $chars[1] . $chars[2] . $chars[2];
        }

        if (preg_match('/^#[0-9a-fA-F]{6}$/', $color) !== 1) {
            $color = $fallback;
        }

        return strtoupper($color);
    };

@endphp
<form method="POST" action="{{ route('admin.company-settings.update-main') }}" id="companySettingsMainForm"
    enctype="multipart/form-data">

    @csrf
    @if ($errors->hasBag('main') && $errors->main->any())
        <div class="form-error-banner">
            <i class="ri-error-warning-line form-error-icon"></i>
            <div>
                <h4 class="form-error-title">Se encontraron los siguientes errores:</h4>
                <ul>
                    @foreach ($errors->main->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
    <section id="companySettingsSectionMain" class="container-section">
        <div class="form-body">
            <div class="card-header">
                <span class="card-title">Datos generales de la empresa</span>
                <p class="card-description">Completa la información básica, de contacto y la identidad visual de tu
                    empresa.</p>
            </div>
            <div class="form-row-fit">
                <div class="input-group">
                    <label for="name" class="label-form">Nombre comercial <i
                            class="ri-asterisk text-accent"></i></label>
                    <div class="input-icon-container">
                        <i class="ri-store-2-line input-icon"></i>
                        <input type="text" name="name" id="name" class="input-form" required
                            value="{{ old('name', $setting->name) }}" placeholder="Ej. GeckoCommerce"
                            data-validate="required|min:3|max:12">
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
            <div class="form-row-fit">
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
        <div class="form-user">
            <div class="form-body">
                <div class="image-upload-section">
                    <div class="card-header">
                        <span class="card-title">Logotipo</span>
                        <p class="card-description">Sube el logotipo de tu negocio</p>
                    </div>
                    <input type="file" name="logo" id="companyLogo" class="file-input" accept="image/png"
                        data-validate="imageSingle|maxSizeSingleKB:2048|fileTypes:png">
                    <input type="hidden" name="remove_logo" id="removeLogoFlag" value="0">
                    <div class="image-preview-zone {{ $hasLogo ? 'has-image' : '' }}" id="companyLogoPreviewZone">
                        @if ($hasLogo)
                            <img id="companyLogoPreview" class="image-preview image-pulse"
                                src="{{ $setting->logo_path && file_exists(public_path('storage/' . $setting->logo_path)) ? asset('storage/' . $setting->logo_path) : '' }}"
                                alt="Logotipo actual">
                            <div class="image-placeholder" id="companyLogoPlaceholder" style="display: none;">
                                <i class="ri-image-add-line"></i>
                                <p>Arrastra el logotipo aquí</p>
                                <span>o haz clic para seleccionar</span>
                            </div>
                        @else
                            <div class="image-placeholder" id="companyLogoPlaceholder">
                                <i class="ri-image-add-line"></i>
                                <p>Arrastra el logotipo aquí</p>
                                <span>o haz clic para seleccionar</span>
                            </div>
                            <img id="companyLogoPreview" class="image-preview image-pulse" style="display: none;"
                                alt="Vista previa">
                        @endif
                        <img id="companyLogoPreviewNew" class="image-preview image-pulse" style="display: none;"
                            alt="Vista previa">
                        <div class="image-overlay" id="companyLogoOverlay"
                            style="display: {{ $hasLogo ? 'flex' : 'none' }};">
                            <button type="button" class="boton-form boton-info" id="companyChangeLogoBtn"
                                title="Cambiar imagen">
                                <i class="ri-upload-2-line"></i>
                                <span class="boton-form-text">Cambiar</span>
                            </button>
                            <button type="button" class="boton-form boton-danger" id="companyRemoveLogoBtn"
                                title="Eliminar imagen">
                                <i class="ri-delete-bin-line"></i>
                                <span class="boton-form-text">Eliminar</span>
                            </button>
                        </div>
                    </div>
                    <div class="image-filename" id="companyLogoFilename"
                        style="{{ $hasLogo ? 'display: flex;' : 'display: none;' }}">
                        <i class="ri-file-image-line"></i>
                        <span id="companyLogoFilenameText">{{ $logoFilename }}</span>
                    </div>
                </div>
            </div>
            <div class="form-body">
                <div class="card-header">
                    <span class="card-title">Información de contacto</span>
                    <p class="card-description">
                        Proporciona los datos de contacto que deseas mostrar a tus clientes en el sitio web y en los
                        documentos oficiales.
                    </p>
                </div>
                <div class="form-row-fit">
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
                    <div class="input-group">
                        <label for="support_phone" class="label-form">Teléfono de soporte</label>
                        <div class="input-icon-container">
                            <i class="ri-headphone-line input-icon"></i>
                            <input type="text" name="support_phone" id="support_phone" class="input-form"
                                value="{{ old('support_phone', $setting->support_phone) }}"
                                placeholder="+51 977 888 111" data-validate="max:25|phone|min:7">
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
        </div>
        <div class="form-footer-static">
            <a href="{{ route('admin.dashboard') }}" class="boton-form boton-volver">
                <span class="boton-form-icon"><i class="ri-home-smile-2-fill"></i></span>
                <span class="boton-form-text">Volver al inicio</span>
            </a>
            <button class="boton-form boton-accent" type="submit" id="mainSubmitBtn">
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
                formId: 'companySettingsMainForm',
                buttonId: 'mainSubmitBtn',
                loadingText: 'Actualizando...'
            });
            initFormValidator('#companySettingsMainForm', {
                validateOnBlur: true,
                validateOnInput: false,
                scrollToFirstError: true
            });

            // Inicializar manejador de imágenes
            initImageUpload({
                inputId: 'companyLogo',
                previewZoneId: 'companyLogoPreviewZone',
                placeholderId: 'companyLogoPlaceholder',
                previewId: 'companyLogoPreview',
                previewNewId: 'companyLogoPreviewNew',
                overlayId: 'companyLogoOverlay',
                changeBtnId: 'companyChangeLogoBtn',
                removeBtnId: 'companyRemoveLogoBtn',
                filenameContainerId: 'companyLogoFilename',
                filenameTextId: 'companyLogoFilenameText',
                removeFlagId: 'removeLogoFlag',
                mode: 'edit',
                hasExistingImage: {{ $setting->logo_path && file_exists(public_path('storage/' . $setting->logo_path)) ? 'true' : 'false' }},
                existingImageFilename: '{{ $setting->logo_path ? basename($setting->logo_path) : '' }}'
            });
        });
    </script>
@endpush
