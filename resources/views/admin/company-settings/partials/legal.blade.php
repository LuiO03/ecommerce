<section id="companySettingsSectionLegal" class="settings-section" data-section="legal"
    role="tabpanel" aria-labelledby="tab-legal">
    <div class="form-body">
        <div class="card-header">
            <span class="card-title">Aspectos legales</span>
            <p class="card-description">
                Configura los documentos legales de tu empresa que se mostrarán en el sitio web y estarán disponibles para los usuarios.
            </p>
        </div>
    </div>

    <div class="form-columns-row">
        <div class="form-column">
            <div class="input-group">
                <label for="terms_conditions" class="label-form">Términos y condiciones</label>
                <textarea name="terms_conditions" id="terms_conditions" class="textarea-form" rows="6"
                    data-validate="requiredText|minText:20|max:12000">{{ old('terms_conditions', $setting->terms_conditions) }}</textarea>
            </div>
            <div class="form-row">
                <div class="input-group">
                    <label for="privacy_policy" class="label-form">Política de privacidad</label>
                    <textarea name="privacy_policy" id="privacy_policy" class="textarea-form" rows="6"
                        data-validate="requiredText|minText:20|max:12000">{{ old('privacy_policy', $setting->privacy_policy) }}</textarea>
                </div>
            </div>

            <div class="form-row">
                <div class="input-group">
                    <label for="claims_book_information" class="label-form">Libro de reclamaciones</label>
                    <textarea name="claims_book_information" id="claims_book_information" class="textarea-form" rows="6"
                        data-validate="requiredText|minText:10|max:8000">{{ old('claims_book_information', $setting->claims_book_information) }}</textarea>
                </div>
            </div>
        </div>
    </div>
    <div class="form-footer">
        <a href="{{ route('admin.dashboard') }}" class="boton-form boton-volver">
            <span class="boton-form-icon"><i class="ri-home-smile-2-fill"></i></span>
            <span class="boton-form-text">Volver al inicio</span>
        </a>
        <button class="boton-form boton-accent" type="submit" id="submitBtn">
            <span class="boton-form-icon"><i class="ri-save-3-line"></i></span>
            <span class="boton-form-text">Guardar Información</span>
        </button>
    </div>
</section>
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
            });
        </script>
    @endpush
