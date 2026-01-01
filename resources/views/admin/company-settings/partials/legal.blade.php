<form method="POST" action="{{ route('admin.company-settings.update-legal') }}" id="companySettingsLegalForm">
    @csrf
    @if ($errors->hasBag('legal') && $errors->legal->any())
        <div class="form-error-banner">
            <i class="ri-error-warning-line form-error-icon"></i>
            <div>
                <h4 class="form-error-title">Se encontraron los siguientes errores:</h4>
                <ul>
                    @foreach ($errors->legal->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
    <section id="companySettingsSectionLegal" data-section="legal" role="tabpanel"
        aria-labelledby="tab-legal" class="container-section">
        <div class="form-body">
            <div class="card-header">
                <span class="card-title">Aspectos legales</span>
                <p class="card-description">
                    Configura los documentos legales de tu empresa que se mostrarán en el sitio web y estarán
                    disponibles para los usuarios.
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
        <script>
            // CKEditor para los 3 campos legales
            document.addEventListener("DOMContentLoaded", () => {
                const ckFields = [
                    'terms_conditions',
                    'privacy_policy',
                    'claims_book_information'
                ];
                window._ckEditors = window._ckEditors || {};
                ckFields.forEach(id => {
                    const ta = document.getElementById(id);
                    if (ta) {
                        ClassicEditor.create(ta, {
                            toolbar: [
                                'undo', 'redo',
                                'heading',
                                'bold', 'italic', 'underline', 'strikethrough',
                                'blockQuote',
                                'bulletedList', 'numberedList',
                                'link',
                                'insertTable',
                            ],
                            table: {
                                contentToolbar: [
                                    'tableColumn', 'tableRow', 'mergeTableCells'
                                ]
                            }
                        })
                        .then(editor => {
                            window._ckEditors[id] = editor;
                            // Sincronizar textarea en cada cambio
                            editor.model.document.on('change:data', () => {
                                ta.value = editor.getData();
                            });
                            // Validar al perder foco
                            editor.editing.view.document.on('blur', () => {
                                ta.value = editor.getData();
                                if (window.formValidator) {
                                    window.formValidator.validateField(ta);
                                }
                            });
                        })
                        .catch(error => console.error(error));
                    }
                });
            });
        </script>
        <div class="form-footer">
            <a href="{{ route('admin.dashboard') }}" class="boton-form boton-volver">
                <span class="boton-form-icon"><i class="ri-home-smile-2-fill"></i></span>
                <span class="boton-form-text">Volver al inicio</span>
            </a>
            <button class="boton-form boton-accent" type="submit" id="legalSubmitBtn">
                <span class="boton-form-icon"><i class="ri-save-3-line"></i></span>
                <span class="boton-form-text">Guardar Información</span>
            </button>
        </div>
    </section>
</form>
<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initSubmitLoader({
                formId: 'companySettingsLegalForm',
                buttonId: 'legalSubmitBtn',
                loadingText: 'Actualizando...'
            });

            window.formValidator = initFormValidator('#companySettingsLegalForm', {
                validateOnBlur: true,
                validateOnInput: false,
                scrollToFirstError: true
            });
        });
    </script>
@endpush
