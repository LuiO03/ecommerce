@php($logoFilename = $hasLogo ? basename($setting->logo_path) : '')

<x-admin-layout :useSlotContainer="false">
    <x-slot name="title">
        <div class="page-icon card-purple"><i class="ri-building-4-line"></i></div>
        Configuración de la empresa
    </x-slot>

    <div class="form-container" autocomplete="off" id="companySettingsForm">

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
                @include('admin.company-settings.partials.general')

                @include('admin.company-settings.partials.contact')

                @include('admin.company-settings.partials.social')

                @include('admin.company-settings.partials.identity')

                @include('admin.company-settings.partials.legal')
            </div>
        </div>
    </div>

    @push('scripts')
        <script>

        </script>
    @endpush
</x-admin-layout>
