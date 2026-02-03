@section('title', 'Editar portada: ' . $cover->title)

<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-warning"><i class="ri-edit-circle-line"></i></div>
        <div class="page-edit-title">
            <span class="page-subtitle">Editar Portada</span>
            {{ $cover->title }}
        </div>
    </x-slot>
    <x-slot name="action">
        <a href="{{ route('admin.covers.index') }}" class="boton boton-secondary">
            <span class="boton-icon"><i class="ri-arrow-go-back-line"></i></span>
            <span class="boton-text">Volver</span>
        </a>
        <form action="{{ route('admin.covers.destroy', $cover->slug) }}" method="POST" class="delete-form"
            data-entity="portada" style="margin: 0;">
            @csrf
            @method('DELETE')
            <button class="boton boton-danger" type="submit">
                <span class="boton-icon"><i class="ri-delete-bin-6-fill"></i></span>
                <span class="boton-text">Eliminar</span>
            </button>
        </form>
    </x-slot>

    <form action="{{ route('admin.covers.update', $cover->slug) }}" method="POST" enctype="multipart/form-data"
        class="form-container" autocomplete="off" id="coverForm">
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

        <x-alert type="info" title="Información:" :dismissible="true" :items="['Los campos con asterisco (<i class=\'ri-asterisk text-accent\'></i>) son obligatorios.']" />

        <div class="form-body">
            <div class="form-row-fit">
                <div class="input-group">
                    <label for="title" class="label-form">
                        Título de la portada
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-text input-icon"></i>
                        <input type="text" name="title" id="title" class="input-form" required
                            value="{{ old('title', $cover->title) }}" placeholder="Ingrese el título"
                            data-validate="required|min:3|max:255">
                    </div>
                </div>
                <div class="input-group">
                    <label for="status" class="label-form">
                        Estado
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-toggle-line input-icon"></i>
                        <select name="status" id="status" class="select-form" required
                            data-validate="required|selected">
                            <option value="" disabled>Seleccione un estado</option>
                            <option value="1" @selected(old('status', $cover->status) == 1)>Activo</option>
                            <option value="0" @selected(old('status', $cover->status) == 0)>Inactivo</option>
                        </select>
                        <i class="ri-arrow-down-s-line select-arrow"></i>
                    </div>
                </div>

                <div class="input-group">
                    <label for="start_at" class="label-form">Fecha de inicio</label>
                    <div class="input-icon-container">
                        <input type="datetime-local" name="start_at" id="start_at" class="input-form"
                            value="{{ old('start_at', $cover->start_at ? $cover->start_at->format('Y-m-d\TH:i') : '') }}">
                    </div>
                </div>

                <div class="input-group">
                    <label for="end_at" class="label-form">Fecha de fin</label>
                    <div class="input-icon-container">
                        <i class="ri-calendar-check-line input-icon"></i>
                        <input type="datetime-local" name="end_at" id="end_at" class="input-form"
                            value="{{ old('end_at', $cover->end_at ? $cover->end_at->format('Y-m-d\TH:i') : '') }}">
                    </div>
                </div>
            </div>
        </div>
        <div class="form-columns-row">
            <div class="form-column">
                <div class="card-header">
                    <span class="card-title">Configuración opcional de la portada</span>
                    <p class="card-description">
                        Define los detalles y la apariencia de la portada que se mostrará en el sitio web.
                    </p>
                </div>
                <hr class="w-full my-0 border-default">
                <div class="input-group">
                    <label for="overlay_text" class="label-form">Texto en la imagen</label>
                    <div class="input-icon-container">
                        <i class="ri-landscape-line input-icon"></i>
                        <input type="text" name="overlay_text" id="overlay_text" class="input-form"
                            placeholder="Texto principal a mostrar sobre la imagen"
                            value="{{ old('overlay_text', $cover->overlay_text) }}" data-validate="max:100">
                    </div>
                </div>

                <div class="input-group">
                    <label for="overlay_subtext" class="label-form">Subtexto</label>
                    <div class="input-icon-container">
                        <i class="ri-landscape-line input-icon"></i>
                        <input type="text" name="overlay_subtext" id="overlay_subtext" class="input-form"
                            placeholder="Texto secundario opcional"
                            value="{{ old('overlay_subtext', $cover->overlay_subtext) }}" data-validate="max:150">
                    </div>
                </div>
                <div class="form-row-fit">
                    <div class="form-column-fit">
                        <div class="input-group">
                            <label for="text_color" class="label-form">Color del texto</label>
                            <div class="input-icon-container">
                                <i class="ri-palette-line input-icon"></i>
                                <input type="text" id="text_color" data-role="text-color" placeholder="#RRGGBB"
                                    style="cursor: pointer" autocomplete="off" data-validate="required|colorCss"
                                    value="{{ old('text_color', $cover->text_color ?? '#FFFFFF') }}" data-coloris>
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="button_text" class="label-form">Texto del botón</label>
                            <div class="input-icon-container">
                                <i class="ri-radio-button-line input-icon"></i>
                                <input type="text" name="button_text" id="button_text" class="input-form"
                                    value="{{ old('button_text', $cover->button_text) }}"
                                    placeholder="Ej: Comprar ahora" data-validate="max:100">
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="button_style" class="label-form">Estilo del botón</label>
                            <div class="input-icon-container">
                                <i class="ri-palette-line input-icon"></i>
                                <select name="button_style" id="button_style" class="select-form">
                                    <option value="primary"
                                        {{ old('button_style', $cover->button_style) == 'primary' ? 'selected' : '' }}>
                                        Principal</option>
                                    <option value="secondary"
                                        {{ old('button_style', $cover->button_style) == 'secondary' ? 'selected' : '' }}>
                                        Secundario</option>
                                    <option value="outline"
                                        {{ old('button_style', $cover->button_style) == 'outline' ? 'selected' : '' }}>
                                        Contorno
                                    </option>
                                    <option value="white"
                                        {{ old('button_style', $cover->button_style) == 'white' ? 'selected' : '' }}>
                                        Blanco
                                    </option>
                                </select>
                                <i class="ri-arrow-down-s-line select-arrow"></i>
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="button_link" class="label-form">URL del botón</label>
                            <div class="input-icon-container">
                                <i class="ri-links-line input-icon"></i>
                                <input type="url" name="button_link" id="button_link" class="input-form"
                                    value="{{ old('button_link', $cover->button_link) }}"
                                    placeholder="https://example.com" data-validate="url">
                            </div>
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="text_position" class="label-form">Posición del texto</label>
                        <input type="hidden" name="text_position" id="text_position"
                            value="{{ old('text_position', $cover->text_position ?? 'center-center') }}">
                        <div class="position-picker" data-target="text_position">
                            <button type="button" class="position-cell" data-position="top-left"
                                aria-label="Superior izquierda"></button>
                            <button type="button" class="position-cell" data-position="top-center"
                                aria-label="Superior centro"></button>
                            <button type="button" class="position-cell" data-position="top-right"
                                aria-label="Superior derecha"></button>
                            <button type="button" class="position-cell" data-position="center-left"
                                aria-label="Centro izquierda"></button>
                            <button type="button" class="position-cell" data-position="center-center"
                                aria-label="Centro"></button>
                            <button type="button" class="position-cell" data-position="center-right"
                                aria-label="Centro derecha"></button>
                            <button type="button" class="position-cell" data-position="bottom-left"
                                aria-label="Inferior izquierda"></button>
                            <button type="button" class="position-cell" data-position="bottom-center"
                                aria-label="Inferior centro"></button>
                            <button type="button" class="position-cell" data-position="bottom-right"
                                aria-label="Inferior derecha"></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-column">
                <div class="image-upload-section">
                    <label class="label-form">Imagen de la portada</label>
                    <input type="file" name="image" id="image" class="file-input" accept="image/*"
                        data-validate="imageSingle|maxSizeSingleMB:3">
                    <input type="hidden" name="remove_image" id="removeImageFlag" value="0">

                    <div class="image-preview-zone {{ $cover->image_path && file_exists(public_path('storage/' . $cover->image_path)) ? 'has-image' : '' }}"
                        id="imagePreviewZone">
                        @if ($cover->image_path && file_exists(public_path('storage/' . $cover->image_path)))
                            <img id="imagePreview" class="image-preview"
                                src="{{ asset('storage/' . $cover->image_path) }}" alt="Imagen actual">
                            <div class="image-overlay" id="imageOverlay">
                                <button type="button" class="overlay-btn" id="changeImageBtn"
                                    title="Cambiar imagen">
                                    <i class="ri-upload-2-line"></i>
                                    <span>Cambiar</span>
                                </button>
                                <button type="button" class="overlay-btn overlay-btn-danger" id="removeImageBtn"
                                    title="Eliminar imagen">
                                    <i class="ri-delete-bin-line"></i>
                                    <span>Eliminar</span>
                                </button>
                            </div>
                        @else
                            <div class="image-placeholder" id="imagePlaceholder">
                                <i class="ri-image-add-line"></i>
                                <p>Arrastra una imagen aquí</p>
                                <span>o haz clic para seleccionar</span>
                                <span>Formatos: PNG, JPG, JPEG (máx. 3 MB)</span>
                            </div>
                            <img id="imagePreview" class="image-preview image-pulse" style="display: none;"
                                alt="Vista previa">
                            <div class="image-overlay" id="imageOverlay" style="display: none;">
                                <button type="button" class="overlay-btn" id="changeImageBtn"
                                    title="Cambiar imagen">
                                    <i class="ri-upload-2-line"></i>
                                    <span>Cambiar</span>
                                </button>
                                <button type="button" class="overlay-btn overlay-btn-danger" id="removeImageBtn"
                                    title="Eliminar imagen">
                                    <i class="ri-delete-bin-line"></i>
                                    <span>Eliminar</span>
                                </button>
                            </div>
                        @endif
                        <div class="cover-overlay-preview pos-center-center" id="coverOverlayPreview"
                            style="display: none;">
                            <div class="cover-overlay-content">
                                <span class="cover-overlay-title" id="coverOverlayTitle"></span>
                                <span class="cover-overlay-subtext" id="coverOverlaySubtext"></span>
                                <span class="cover-overlay-button is-primary" id="coverOverlayButton"></span>
                            </div>
                        </div>
                    </div>
                    <div class="image-filename" id="imageFilename" style="display: none;">
                        <i class="ri-file-image-line"></i>
                        <span id="filenameText"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-footer">
            <a href="{{ url()->previous() }}" class="boton-form boton-volver">
                <span class="boton-form-icon"><i class="ri-arrow-left-circle-fill"></i></span>
                <span class="boton-form-text">Cancelar</span>
            </a>
            <button type="reset" class="boton-form boton-warning">
                <span class="boton-form-icon"><i class="ri-paint-brush-fill"></i></span>
                <span class="boton-form-text">Restablecer</span>
            </button>
            <button class="boton-form boton-primary" type="submit" id="submitBtn">
                <span class="boton-form-icon"><i class="ri-save-3-fill"></i></span>
                <span class="boton-form-text">Guardar Cambios</span>
            </button>
        </div>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() { // Inicializar manejador de imágenes
                const imageHandler = initImageUpload({
                    mode: 'edit',
                    hasExistingImage: {{ $cover->image_path && file_exists(public_path('storage/' . $cover->image_path)) ? 'true' : 'false' }},
                    existingImageFilename: '{{ $cover->image_path ? basename($cover->image_path) : '' }}'
                });

                // 1. Inicializar submit loader PRIMERO
                const submitLoader = initSubmitLoader({
                    formId: 'coverForm',
                    buttonId: 'submitBtn',
                    loadingText: 'Actualizando...'
                });

                // 2. Inicializar validación de formulario DESPUÉS
                const formValidator = initFormValidator('#coverForm', {
                    validateOnBlur: true,
                    validateOnInput: false,
                    scrollToFirstError: true
                });

                const initCoverOverlayPreview = () => {
                    const preview = document.getElementById('coverOverlayPreview');
                    if (!preview) return;

                    const titleInput = document.getElementById('overlay_text');
                    const subtextInput = document.getElementById('overlay_subtext');
                    const positionInput = document.getElementById('text_position');
                    const colorInput = document.getElementById('text_color');
                    const buttonTextInput = document.getElementById('button_text');
                    const buttonStyleInput = document.getElementById('button_style');

                    const titleEl = document.getElementById('coverOverlayTitle');
                    const subtextEl = document.getElementById('coverOverlaySubtext');
                    const buttonEl = document.getElementById('coverOverlayButton');

                    const positionClasses = [
                        'pos-top-left', 'pos-top-center', 'pos-top-right',
                        'pos-center-left', 'pos-center-center', 'pos-center-right',
                        'pos-bottom-left', 'pos-bottom-center', 'pos-bottom-right'
                    ];

                    const applyPosition = (value) => {
                        preview.classList.remove(...positionClasses);
                        const normalized = value ? `pos-${value}` : 'pos-center-center';
                        preview.classList.add(normalized);
                    };

                    const applyButtonStyle = (value) => {
                        buttonEl.classList.remove('is-primary', 'is-secondary', 'is-outline', 'is-white');
                        const normalized = value || 'primary';
                        buttonEl.classList.add(`is-${normalized}`);
                    };

                    const initPositionPicker = () => {
                        const picker = document.querySelector('.position-picker');
                        if (!picker || !positionInput) return;
                        const cells = picker.querySelectorAll('.position-cell');

                        const setActive = (value) => {
                            cells.forEach((cell) => {
                                cell.classList.toggle('is-active', cell.dataset.position === value);
                            });
                        };

                        const current = positionInput.value || 'center-center';
                        setActive(current);

                        cells.forEach((cell) => {
                            cell.addEventListener('click', () => {
                                const value = cell.dataset.position || 'center-center';
                                positionInput.value = value;
                                setActive(value);
                                updatePreview();
                            });
                        });
                    };

                    const updatePreview = () => {
                        const title = titleInput?.value?.trim() || '';
                        const subtext = subtextInput?.value?.trim() || '';
                        const buttonText = buttonTextInput?.value?.trim() || '';
                        const position = positionInput?.value || 'center-center';
                        const color = colorInput?.value || '#FFFFFF';

                        titleEl.textContent = title;
                        subtextEl.textContent = subtext;
                        buttonEl.textContent = buttonText;

                        titleEl.style.display = title ? 'block' : 'none';
                        subtextEl.style.display = subtext ? 'block' : 'none';
                        buttonEl.style.display = buttonText ? 'inline-flex' : 'none';

                        applyPosition(position);
                        applyButtonStyle(buttonStyleInput?.value);
                        preview.style.setProperty('--overlay-text-color', color);

                        const previewZone = document.getElementById('imagePreviewZone');
                        const imageEl = document.getElementById('imagePreview');
                        const hasImage = previewZone?.classList.contains('has-image') || imageEl?.style
                            .display === 'block';
                        const hasContent = Boolean(title || subtext || buttonText);
                        preview.style.display = hasContent && hasImage ? 'flex' : 'none';
                    };

                    const imageInput = document.getElementById('image');
                    const removeBtn = document.getElementById('removeImageBtn');
                    const changeBtn = document.getElementById('changeImageBtn');

                    imageInput?.addEventListener('change', () => setTimeout(updatePreview, 80));
                    removeBtn?.addEventListener('click', () => setTimeout(updatePreview, 80));
                    changeBtn?.addEventListener('click', () => setTimeout(updatePreview, 80));

                    [
                        titleInput,
                        subtextInput,
                        positionInput,
                        colorInput,
                        buttonTextInput,
                        buttonStyleInput
                    ].forEach((el) => {
                        if (!el) return;
                        el.addEventListener('input', updatePreview);
                        el.addEventListener('change', updatePreview);
                    });

                    initPositionPicker();
                    updatePreview();
                };

                initCoverOverlayPreview();
            });
        </script>
    @endpush
</x-admin-layout>
