@php($logoFilename = $hasLogo ? basename($setting->logo_path) : '')

<x-admin-layout :useSlotContainer="false">
    <x-slot name="title">
        <div class="page-icon card-purple"><i class="ri-building-4-line"></i></div>
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

        <x-alert type="info" title="Consejo" :dismissible="true" :items="[
            'Actualiza los datos de tu empresa para mostrar información consistente en todo el sistema.',
            'El logotipo debe ser una imagen en formato PNG con máximo 2MB.',
        ]" />

        <div id="companySettingsTabs" class="settings-tabs-layout">
            <div class="settings-tabs-nav" role="tablist" aria-label="Secciones de configuración">
                <button type="button" class="settings-tab-button is-active" data-target="general" id="tab-general"
                    role="tab" aria-controls="companySettingsSectionGeneral">
                    <i class="ri-information-fill"></i>
                    <span>Información general</span>
                </button>
                <button type="button" class="settings-tab-button" data-target="contact" id="tab-contact" role="tab"
                    aria-controls="companySettingsSectionContact">
                    <i class="ri-contacts-fill"></i>
                    <span>Contacto</span>
                </button>
                <button type="button" class="settings-tab-button" data-target="social" id="tab-social" role="tab"
                    aria-controls="companySettingsSectionSocial">
                    <i class="ri-share-forward-fill"></i>
                    <span>Redes sociales</span>
                </button>
                <button type="button" class="settings-tab-button" data-target="identity" id="tab-identity"
                    role="tab" aria-controls="companySettingsSectionIdentity">
                    <i class="ri-palette-fill"></i>
                    <span>Identidad visual</span>
                </button>
                <button type="button" class="settings-tab-button" data-target="legal" id="tab-legal" role="tab"
                    aria-controls="companySettingsSectionLegal">
                    <i class="ri-file-shield-fill"></i>
                    <span>Documentación legal</span>
                </button>
            </div>

            <div class="settings-tabs-sections" id="companySettingsSections">
                <div class="settings-tabs-slider">
                    @include('admin.company-settings.partials.general')

                    @include('admin.company-settings.partials.contact')

                    @include('admin.company-settings.partials.social')

                    @include('admin.company-settings.partials.identity')

                    @include('admin.company-settings.partials.legal')
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

    <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
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

                const tabManager = initCompanySettingsTabs();

                const editorManager = initCompanySettingsEditors({
                    termsId: 'terms_conditions',
                    privacyId: 'privacy_policy',
                    claimsId: 'claims_book_information',
                });

                if (editorManager && editorManager.ready) {
                    editorManager.ready.catch((error) => {
                        console.error('No fue posible inicializar los editores de texto:', error);
                    });
                }

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
</x-admin-layout>
