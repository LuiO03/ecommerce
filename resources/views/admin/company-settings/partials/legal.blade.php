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
            let editorInstance;
            document.addEventListener("DOMContentLoaded", () => {
                ClassicEditor.create(document.querySelector('#content'), {
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
                        editorInstance = editor;
                        window.editorInstance = editor;
                        // Registrar instancia global por id para soporte multi-editor
                        window._ckEditors = window._ckEditors || {};
                        const ta = document.querySelector('#content');
                        if (ta) {
                            window._ckEditors[ta.id] = editor;
                        }
                    })
                    .catch(error => console.error(error));
            });
            // Sincronizar contenido antes de enviar
            document.getElementById('postForm').addEventListener('submit', function() {
                if (editorInstance) {
                    document.querySelector('#content').value = editorInstance.getData();
                }
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

            initFormValidator('#companySettingsLegalForm', {
                validateOnBlur: true,
                validateOnInput: false,
                scrollToFirstError: true
            });

            // Enlazar CKEditor: sincronizar en cambios y validar solo al perder foco
            if (window.editorInstance) {
                const textarea = document.querySelector('#content');
                const editor = window.editorInstance;
                editor.model.document.on('change:data', () => {
                    textarea.value = editor.getData();
                });
                editor.editing.view.document.on('blur', () => {
                    // Sincronizar antes de validar para capturar vacío
                    textarea.value = editor.getData();
                    const isValid = formValidator.validateField(textarea);
                    const group = textarea.closest('.input-group');
                    const editable = group ? group.querySelector('.ck-editor__editable') : null;
                    if (editable) {
                        editable.classList.toggle('input-success', isValid);
                        editable.classList.toggle('input-error', !isValid);
                    }
                    // Asegurar mensaje de error inline si es inválido (vacío vs. insuficiente)
                    if (!isValid && group) {
                        let errorEl = group.querySelector('.input-error-message');
                        if (!errorEl) {
                            errorEl = document.createElement('div');
                            errorEl.className = 'input-error-message';
                            errorEl.innerHTML =
                                '<i class="ri-error-warning-line"></i> <span class="error-text"></span>';
                            group.appendChild(errorEl);
                        }
                        const textEl = errorEl.querySelector('.error-text');
                        if (textEl) {
                            const div = document.createElement('div');
                            div.innerHTML = textarea.value || '';
                            const plain = (div.textContent || div.innerText || '').replace(/\u00A0|&nbsp;/g,
                                ' ').trim();
                            textEl.textContent = plain.length === 0 ? 'Este campo es obligatorio' :
                                'Debe tener al menos 10 caracteres';
                        }
                        errorEl.style.display = 'flex';
                    }
                });
            }
        });
    </script>
@endpush
