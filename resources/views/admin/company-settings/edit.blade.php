@php($logoFilename = $hasLogo ? basename($setting->logo_path) : '')

<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-primary"><i class="ri-building-4-line"></i></div>
        Configuración de la empresa
    </x-slot>

    <form action="{{ route('admin.company-settings.update') }}" method="POST" enctype="multipart/form-data"
        class="form-container" autocomplete="off" id="companySettingsForm">
        @csrf
        @method('PUT')

        @if ($errors->any())
            <div class="form-error-banner">
                <i class="ri-error-warning-line form-error-icon"></i>
                <div>
                    <h4 class="form-error-title">Se encontraron los siguientes errores:</h4>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <x-alert type="info" title="Consejo" :dismissible="true"
            :items="[
                'Actualiza los datos de tu empresa para mostrar información consistente en todo el sistema.',
                'El logotipo debe ser una imagen en formato JPG, PNG, SVG o WebP con máximo 2MB.',
            ]" />

        <div class="form-columns-row">
            <div class="form-column">
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
                        <textarea name="about" id="about" class="textarea-form" rows="5" placeholder="Describe brevemente la empresa"
                            data-validate="max:1500">{{ old('about', $setting->about) }}</textarea>
                    </div>
                </div>
            </div>

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
                            value="{{ old('support_email', $setting->support_email) }}" placeholder="soporte@empresa.com"
                            data-validate="email|max:255">
                    </div>
                </div>

                <div class="input-group">
                    <label for="phone" class="label-form">Teléfono principal</label>
                    <div class="input-icon-container">
                        <i class="ri-phone-line input-icon"></i>
                        <input type="text" name="phone" id="phone" class="input-form"
                            value="{{ old('phone', $setting->phone) }}" placeholder="+51 999 888 777"
                            data-validate="max:25">
                    </div>
                </div>

                <div class="input-group">
                    <label for="support_phone" class="label-form">Teléfono de soporte</label>
                    <div class="input-icon-container">
                        <i class="ri-headphone-line input-icon"></i>
                        <input type="text" name="support_phone" id="support_phone" class="input-form"
                            value="{{ old('support_phone', $setting->support_phone) }}" placeholder="+51 977 888 111"
                            data-validate="max:25">
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

        <div class="form-columns-row">
            <div class="form-column">
                <div class="form-section-title">
                    <i class="ri-share-forward-line"></i>
                    Redes sociales
                </div>

                <div class="input-group">
                    <label for="facebook_url" class="label-form">Facebook</label>
                    <div class="input-icon-container">
                        <i class="ri-facebook-circle-line input-icon"></i>
                        <input type="url" name="facebook_url" id="facebook_url" class="input-form"
                            value="{{ old('facebook_url', $setting->facebook_url) }}" placeholder="https://facebook.com/empresa"
                            data-validate="url|max:255">
                    </div>
                </div>

                <div class="input-group">
                    <label for="instagram_url" class="label-form">Instagram</label>
                    <div class="input-icon-container">
                        <i class="ri-instagram-line input-icon"></i>
                        <input type="url" name="instagram_url" id="instagram_url" class="input-form"
                            value="{{ old('instagram_url', $setting->instagram_url) }}" placeholder="https://instagram.com/empresa"
                            data-validate="url|max:255">
                    </div>
                </div>

                <div class="input-group">
                    <label for="twitter_url" class="label-form">Twitter (X)</label>
                    <div class="input-icon-container">
                        <i class="ri-twitter-x-line input-icon"></i>
                        <input type="url" name="twitter_url" id="twitter_url" class="input-form"
                            value="{{ old('twitter_url', $setting->twitter_url) }}" placeholder="https://x.com/empresa"
                            data-validate="url|max:255">
                    </div>
                </div>

                <div class="input-group">
                    <label for="youtube_url" class="label-form">YouTube</label>
                    <div class="input-icon-container">
                        <i class="ri-youtube-line input-icon"></i>
                        <input type="url" name="youtube_url" id="youtube_url" class="input-form"
                            value="{{ old('youtube_url', $setting->youtube_url) }}" placeholder="https://youtube.com/@empresa"
                            data-validate="url|max:255">
                    </div>
                </div>

                <div class="input-group">
                    <label for="tiktok_url" class="label-form">TikTok</label>
                    <div class="input-icon-container">
                        <i class="ri-movie-2-line input-icon"></i>
                        <input type="url" name="tiktok_url" id="tiktok_url" class="input-form"
                            value="{{ old('tiktok_url', $setting->tiktok_url) }}" placeholder="https://www.tiktok.com/@empresa"
                            data-validate="url|max:255">
                    </div>
                </div>

                <div class="input-group">
                    <label for="linkedin_url" class="label-form">LinkedIn</label>
                    <div class="input-icon-container">
                        <i class="ri-linkedin-box-line input-icon"></i>
                        <input type="url" name="linkedin_url" id="linkedin_url" class="input-form"
                            value="{{ old('linkedin_url', $setting->linkedin_url) }}" placeholder="https://www.linkedin.com/company/empresa"
                            data-validate="url|max:255">
                    </div>
                </div>
            </div>

            <div class="form-column">
                <div class="form-section-title">
                    <i class="ri-palette-line"></i>
                    Identidad visual
                </div>

                <div class="input-group">
                    <label for="primary_color" class="label-form">Color primario</label>
                    <div class="input-icon-container">
                        <i class="ri-brush-line input-icon"></i>
                        <input type="text" name="primary_color" id="primary_color" class="input-form"
                            value="{{ old('primary_color', $setting->primary_color) }}" placeholder="#10B981"
                            data-validate="pattern:^#(?:[0-9a-fA-F]{3}){1,2}$">
                    </div>
                </div>

                <div class="input-group">
                    <label for="secondary_color" class="label-form">Color secundario</label>
                    <div class="input-icon-container">
                        <i class="ri-paint-fill input-icon"></i>
                        <input type="text" name="secondary_color" id="secondary_color" class="input-form"
                            value="{{ old('secondary_color', $setting->secondary_color) }}" placeholder="#0EA5E9"
                            data-validate="pattern:^#(?:[0-9a-fA-F]{3}){1,2}$">
                    </div>
                </div>

                <div class="image-upload-section mt-6">
                    <label class="label-form">Logotipo</label>
                    <input type="file" name="logo" id="companyLogo" class="file-input" accept="image/*"
                        data-validate="image|maxSize:2048|fileTypes:jpg,jpeg,png,webp,svg">
                    <input type="hidden" name="remove_logo" id="removeLogoFlag" value="0">

                    <div class="image-preview-zone {{ $hasLogo ? 'has-image' : '' }}" id="companyLogoPreviewZone">
                        @if ($hasLogo)
                            <img id="companyLogoPreview" class="image-preview image-pulse"
                                src="{{ $setting->logo_path && file_exists(public_path('storage/' . $setting->logo_path)) ? asset('storage/' . $setting->logo_path) : '' }}" alt="Logotipo actual">
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

                        <div class="image-overlay" id="companyLogoOverlay" style="display: {{ $hasLogo ? 'flex' : 'none' }};">
                            <button type="button" class="overlay-btn" id="companyChangeLogoBtn" title="Cambiar logotipo">
                                <i class="ri-upload-2-line"></i>
                                <span>Cambiar</span>
                            </button>
                            <button type="button" class="overlay-btn overlay-btn-danger" id="companyRemoveLogoBtn"
                                title="Eliminar logotipo">
                                <i class="ri-delete-bin-line"></i>
                                <span>Eliminar</span>
                            </button>
                        </div>
                    </div>

                    <div class="image-filename" id="companyLogoFilename" style="{{ $hasLogo ? 'display: flex;' : 'display: none;' }}">
                        <i class="ri-file-image-line"></i>
                        <span id="companyLogoFilenameText">{{ $logoFilename }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-footer">
            <button class="boton-form boton-accent" type="submit" id="submitBtn">
                <span class="boton-form-icon"><i class="ri-save-3-line"></i></span>
                <span class="boton-form-text">Guardar cambios</span>
            </button>
        </div>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                initSubmitLoader({
                    formId: 'companySettingsForm',
                    buttonId: 'submitBtn',
                    loadingText: 'Actualizando...'
                });

                initFormValidator('#companySettingsForm', {
                    validateOnBlur: true,
                    validateOnInput: false,
                    scrollToFirstError: true
                });

                const hasLogo = @json($hasLogo);
                const existingLogoFilename = @json($logoFilename);

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
                    hasExistingImage: hasLogo,
                    existingImageFilename: existingLogoFilename
                });
            });
        </script>
    @endpush
</x-admin-layout>
