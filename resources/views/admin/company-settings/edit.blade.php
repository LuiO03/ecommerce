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
                <button type="button" class="settings-tab-button" data-target="general" id="tab-general"
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

                <div id="tab-general">
                    @include('admin.company-settings.partials.general')
                </div>
                <div id="tab-contact">
                    @include('admin.company-settings.partials.contact')
                </div>
                <div id="tab-social">
                    @include('admin.company-settings.partials.social')
                </div>
                <div id="tab-identity">
                    @include('admin.company-settings.partials.identity')
                </div>
                <div id="tab-legal">
                    @include('admin.company-settings.partials.legal')
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animación y persistencia de tab activo igual que profile
            function showTab(tabKey) {
                document.querySelectorAll('.settings-tabs-sections > div').forEach(tab => {
                    if(tab.id === 'tab-' + tabKey) {
                        tab.classList.remove('hidden');
                        setTimeout(() => tab.classList.add('fade-in'), 10);
                    } else {
                        if(!tab.classList.contains('hidden')) tab.classList.add('hidden');
                        tab.classList.remove('fade-in');
                    }
                });
                document.querySelectorAll('.settings-tab-button').forEach(b => b.classList.remove('is-active'));
                const btn = document.querySelector('.settings-tab-button[data-target="' + tabKey + '"]');
                if (btn) btn.classList.add('is-active');
            }

            // Inicializar tab activo
            let initialTab = localStorage.getItem('companySettingsActiveTab') || 'general';
            if(window.location.hash === '#legal') initialTab = 'legal';
            // Si el tab guardado no existe, fallback a general
            const validTabs = Array.from(document.querySelectorAll('.settings-tab-button')).map(b => b.dataset.target);
            if (!validTabs.includes(initialTab)) initialTab = 'general';
            showTab(initialTab);

            // Listener de tabs
            document.querySelectorAll('.settings-tab-button').forEach(btn => {
                btn.addEventListener('click', function() {
                    const target = this.dataset.target;
                    showTab(target);
                    localStorage.setItem('companySettingsActiveTab', target);
                });
            });
        });
    </script>
</x-admin-layout>
