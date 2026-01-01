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

    $primaryColor = $normalizeColor(old('primary_color', $setting->primary_color), '#10B981');
    $secondaryColor = $normalizeColor(old('secondary_color', $setting->secondary_color), '#0EA5E9');
@endphp

<form method="POST" action="{{ route('admin.company-settings.update-identity') }}" enctype="multipart/form-data"
    id="companySettingsIdentityForm">
    @csrf
    @if ($errors->hasBag('identity') && $errors->identity->any())
        <div class="form-error-banner">
            <i class="ri-error-warning-line form-error-icon"></i>
            <div>
                <h4 class="form-error-title">Se encontraron los siguientes errores:</h4>
                <ul>
                    @foreach ($errors->identity->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
    <section id="companySettingsSectionIdentity" data-section="identity" role="tabpanel" aria-labelledby="tab-identity" class="container-section">
        <div class="form-body">
            <div class="card-header">
                <span class="card-title">Identidad visual</span>
                <p class="card-description">
                    Configura los colores y el logotipo de tu empresa para personalizar la apariencia del sitio web y
                    los documentos oficiales.
                </p>
            </div>
        </div>
        <div class="form-body">
            <div class="form-row-fit">
                <div class="input-group">
                    <label for="primary_color" class="label-form">Color primario</label>
                    <div class="color-input-wrapper" data-color-picker data-default-color="{{ $primaryColor }}">
                        <span class="color-preview" data-color-preview style="background-color: {{ $primaryColor }}">
                            <input type="color" class="color-picker-input" value="{{ $primaryColor }}"
                                data-color-input="picker" aria-label="Seleccionar color primario">
                        </span>

                        <div class="input-icon-container">
                            <i class="ri-palette-line input-icon"></i>
                            <input type="text" name="primary_color" id="primary_color"
                                class="input-form color-text-input" value="{{ $primaryColor }}" placeholder="#10B981"
                                data-validate="pattern:^#(?:[0-9a-fA-F]{3}){1,2}$" data-color-input="text"
                                autocomplete="off">
                        </div>
                        <button type="button" class="color-copy-btn" data-color-copy data-copy-label="Copiar"
                            data-copied-label="Copiado" title="Copiar valor de color">
                            <i class="ri-file-copy-line"></i>
                            <span>Copiar</span>
                        </button>
                    </div>
                </div>

                <div class="input-group">
                    <label for="secondary_color" class="label-form">Color secundario</label>
                    <div class="color-input-wrapper" data-color-picker data-default-color="{{ $secondaryColor }}">
                        <span class="color-preview" data-color-preview style="background-color: {{ $secondaryColor }}">
                            <input type="color" class="color-picker-input" value="{{ $secondaryColor }}"
                                data-color-input="picker" aria-label="Seleccionar color secundario">
                        </span>
                        <div class="input-icon-container">
                            <i class="ri-palette-line input-icon"></i>
                            <input type="text" name="secondary_color" id="secondary_color"
                                class="input-form color-text-input" value="{{ $secondaryColor }}" placeholder="#0EA5E9"
                                data-validate="pattern:^#(?:[0-9a-fA-F]{3}){1,2}$" data-color-input="text"
                                autocomplete="off">
                        </div>
                        <button type="button" class="color-copy-btn" data-color-copy data-copy-label="Copiar"
                            data-copied-label="Copiado" title="Copiar valor de color">
                            <i class="ri-file-copy-line"></i>
                            <span>Copiar</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-row-fit">
                <div class="image-upload-section">
                    <label class="label-form">Logotipo</label>
                    <input type="file" name="logo" id="companyLogo" class="file-input" accept="image/png"
                        data-validate="image|maxSize:2048|fileTypes:png">
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
                            <button type="button" class="overlay-btn" id="companyChangeLogoBtn"
                                title="Cambiar logotipo">
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

                    <div class="image-filename" id="companyLogoFilename"
                        style="{{ $hasLogo ? 'display: flex;' : 'display: none;' }}">
                        <i class="ri-file-image-line"></i>
                        <span id="companyLogoFilenameText">{{ $logoFilename }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-footer">
            <a href="{{ route('admin.dashboard') }}" class="boton-form boton-volver">
                <span class="boton-form-icon"><i class="ri-home-smile-2-fill"></i></span>
                <span class="boton-form-text">Volver al inicio</span>
            </a>
            <button class="boton-form boton-accent" type="submit" id="identitySubmitBtn">
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
                formId: 'companySettingsIdentityForm',
                buttonId: 'identitySubmitBtn',
                loadingText: 'Actualizando...'
            });

            initFormValidator('#companySettingsIdentityForm', {
                validateOnBlur: true,
                validateOnInput: false,
                scrollToFirstError: true
            });

            initCompanySettingsColorInputs();

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
