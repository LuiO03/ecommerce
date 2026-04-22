@section('title', 'Configuración de la empresa')

@php($logoFilename = $hasLogo ? basename($setting->logo_path) : '')

<x-admin-layout :useSlotContainer="false">
    <x-slot name="title">
        <div class="page-icon card-purple"><i class="ri-building-4-line"></i></div>
        Configuración de la empresa
    </x-slot>

    <div class="form-container" autocomplete="off" id="companySettingsForm">

        @if (function_exists('company_settings_incomplete') && company_settings_incomplete())
            <x-alert type="danger" title="Tu configuración de negocio está incompleta" :dismissible="true"
                :items="[
                    'Por favor, completa los campos obligatorios para evitar problemas en la generación de documentos y la visualización en el sitio web.',
                    'El logotipo debe ser una imagen en formato PNG con máximo 2MB.',
                ]" />
        @else
            <x-alert type="info" title="Consejo" :dismissible="true" :items="[
                'Actualiza los datos de tu empresa para mostrar información consistente en todo el sistema.',
                'El logotipo debe ser una imagen en formato PNG con máximo 2MB.',
            ]" />
        @endif

        <div id="companySettingsTabs" class="settings-tabs-layout">
            <div class="settings-tabs-nav" role="tablist" aria-label="Secciones de configuración">
                <button type="button" class="settings-tab-button" data-target="main" id="tab-main" role="tab"
                    aria-controls="companySettingsSectionMain">
                    <i class="ri-building-4-line"></i>
                    <span>Datos generales</span>
                </button>
                <button type="button" class="settings-tab-button" data-target="shipping" id="tab-shipping"
                    role="tab" aria-controls="companySettingsSectionShipping">
                    <i class="ri-truck-fill"></i>
                    <span>Envío</span>
                </button>
                <button type="button" class="settings-tab-button" data-target="boleta" id="tab-boleta"
                    role="tab" aria-controls="companySettingsSectionBoleta">
                    <i class="ri-file-pdf-2-line"></i>
                    <span>Boleta PDF</span>
                </button>
                <button type="button" class="settings-tab-button" data-target="social" id="tab-social" role="tab"
                    aria-controls="companySettingsSectionSocial">
                    <i class="ri-share-forward-fill"></i>
                    <span>Redes sociales</span>
                </button>
                <button type="button" class="settings-tab-button" data-target="legal" id="tab-legal" role="tab"
                    aria-controls="companySettingsSectionLegal">
                    <i class="ri-file-shield-fill"></i>
                    <span>Documentación legal</span>
                </button>
            </div>

            <div class="settings-tabs-sections" id="companySettingsSections">

                <div id="tab-main">
                    @include('admin.company-settings.partials.main')
                </div>
                <div id="tab-shipping">
                    @include('admin.company-settings.partials.shipping')
                </div>
                <div id="tab-social">
                    @include('admin.company-settings.partials.social')
                </div>
                <div id="tab-boleta">
                    <div class="form-body">
                        <div class="card-header">
                            <span class="card-title">Vista previa de boleta PDF</span>
                            <p class="card-description">Así se verá la boleta de venta generada para tus clientes.</p>
                        </div>
                        <div class="pdf-preview-container">
                            <iframe src="{{ route('admin.company-settings.invoice-preview') }}"
                                title="Vista previa boleta PDF" allowfullscreen>
                            </iframe>
                        </div>
                    </div>
                </div>
                <!-- Sección de identidad visual fusionada en main -->
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
                    if (tab.id === 'tab-' + tabKey) {
                        tab.classList.remove('hidden');
                        setTimeout(() => tab.classList.add('fade-in'), 10);
                    } else {
                        if (!tab.classList.contains('hidden')) tab.classList.add('hidden');
                        tab.classList.remove('fade-in');
                    }
                });
                document.querySelectorAll('.settings-tab-button').forEach(b => b.classList.remove('is-active'));
                const btn = document.querySelector('.settings-tab-button[data-target="' + tabKey + '"]');
                if (btn) btn.classList.add('is-active');
            }

            // Inicializar tab activo
            let initialTab = localStorage.getItem('companySettingsActiveTab') || 'main';
            if (window.location.hash === '#legal') initialTab = 'legal';
            // Si el tab guardado no existe, fallback a main
            const validTabs = Array.from(document.querySelectorAll('.settings-tab-button')).map(b => b.dataset.target);
            if (!validTabs.includes(initialTab)) initialTab = 'main';
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
