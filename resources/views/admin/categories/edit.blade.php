@section('title', 'Editar categoría: ' . $category->name)

<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-warning"><i class="ri-edit-circle-line"></i></div>
        <div class="page-edit-title">
            <span class="page-subtitle">Editar Categoría</span>
            {{ $category->name }}
        </div>
    </x-slot>

    <x-slot name="action">
        <a href="{{ route('admin.categories.index') }}" class="boton-form boton-accent">
            <span class="boton-form-icon"><i class="ri-arrow-go-back-line"></i></span>
            <span class="boton-form-text">Volver</span>
        </a>

        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="delete-form"
            data-entity="categoría" style="margin: 0;">
            @csrf
            @method('DELETE')
            <button class="boton-form boton-danger" type="submit">
                <span class="boton-form-icon"><i class="ri-delete-bin-6-fill"></i></span>
                <span class="boton-form-text">Eliminar</span>
            </button>
        </form>
    </x-slot>

    <form action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data"
        class="form-container" autocomplete="off" id="categoryForm">
        @csrf
        @method('PUT')

        {{-- Banner de errores de backend (solo si JS fue omitido o falló) --}}
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

        <x-alert type="info" title="Guía rápida:" :dismissible="true" :items="[
            'Los campos con asterisco (<i class=\'ri-asterisk text-accent\'></i>) son obligatorios',
            'Primero selecciona la <strong>familia</strong> a la que pertenecerá la categoría',
            'Si es una subcategoría, selecciona su <strong>categoría padre</strong> (opcional)',
        ]" />

        <div class="form-columns-row">
            <!-- ============================
                 COLUMNA IZQUIERDA
            ============================= -->
            <div class="form-column">
                {{-- FAMILIA (OBLIGATORIO) --}}
                <div class="form-row-fit">
                    <div class="input-group">
                        <label for="family_select" class="label-form">
                            Familia
                            <i class="ri-asterisk text-accent"></i>
                        </label>
                        <div class="input-icon-container">
                            <i class="ri-stack-line input-icon"></i>
                            <select name="family_id" id="family_select" class="select-form" required
                                data-validate="required|selected">
                                <option value="" disabled>Seleccione una familia</option>
                                @foreach ($selectFamilies as $family)
                                    <option value="{{ $family->id }}" @selected(old('family_id', $category->family_id) == $family->id)>
                                        {{ $family->name }}
                                    </option>
                                @endforeach
                            </select>
                            <i class="ri-arrow-down-s-line select-arrow"></i>
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="category_select" class="label-form">
                            Categoría padre
                        </label>
                        <div class="input-icon-container">
                            <i class="ri-node-tree input-icon"></i>
                            <select name="parent_id" id="category_select" class="select-form">

                                {{-- Categoría principal --}}
                                <option value="" @selected(is_null(old('parent_id', $category->parent_id)))>
                                    Categoría principal
                                </option>

                                {{-- Subcategorías --}}
                                @foreach ($selectCategories as $scat)
                                    <option value="{{ $scat->id }}" @selected(old('parent_id', $category->parent_id) == $scat->id)>
                                        {{ $scat->name }}
                                    </option>
                                @endforeach

                            </select>
                            <i class="ri-arrow-down-s-line select-arrow"></i>
                        </div>
                    </div>
                </div>

                {{-- NAME --}}
                <div class="input-group">
                    <label for="name" class="label-form">
                        Nombre de la categoría
                        <i class="ri-asterisk text-accent"></i>
                    </label>

                    <div class="input-icon-container">
                        <i class="ri-price-tag-2-line input-icon"></i>

                        <input type="text" name="name" id="name" class="input-form" required
                            value="{{ old('name', $category->name) }}" placeholder="Ingrese el nombre"
                            data-validate="required|alphanumeric|min:3|max:100">
                    </div>
                </div>

                {{-- STATUS --}}
                <div class="input-group">
                    <label class="label-form">
                        Estado de la categoría
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="binary-switch">
                        <input type="radio" name="status" id="statusActive" value="1"
                            class="switch-input switch-input-on"
                            {{ old('status', (int) $category->status) == 1 ? 'checked' : '' }}>
                        <input type="radio" name="status" id="statusInactive" value="0"
                            class="switch-input switch-input-off"
                            {{ old('status', (int) $category->status) == 0 ? 'checked' : '' }}>
                        <div class="switch-slider"></div>
                        <label for="statusActive" class="switch-label switch-label-on"><i
                                class="ri-checkbox-circle-line"></i> Activo</label>
                        <label for="statusInactive" class="switch-label switch-label-off"><i
                                class="ri-close-circle-line"></i> Inactivo</label>
                    </div>
                </div>

                {{-- DESCRIPTION --}}
                <div class="input-group">
                    <label for="description" class="label-form label-textarea">
                        Descripción
                    </label>
                    <div class="input-icon-container">
                        <textarea name="description" id="description" class="textarea-form" placeholder="Ingrese la descripción" rows="4"
                            data-validate="min:10|max:250">{{ old('description', $category->description) }}</textarea>
                        <i class="ri-file-text-line input-icon"></i>
                    </div>
                </div>

            </div>

            <!-- ============================
                 COLUMNA DERECHA
            ============================= -->
            <div class="form-column">

                {{-- IMAGE UPLOAD --}}
                <div class="image-upload-section">
                    <label class="label-form">Imagen de la categoría</label>

                    <input type="file" name="image" id="image" class="file-input" accept="image/*"
                        data-validate="imageSingle|maxSizeSingleMB:3">
                    <input type="hidden" name="remove_image" id="removeImageFlag" value="0">

                    <!-- Zona de vista previa -->
                    <div class="image-preview-zone {{ $category->image && file_exists(public_path('storage/' . $category->image)) ? 'has-image' : '' }}"
                        id="imagePreviewZone">
                        @if ($category->image && file_exists(public_path('storage/' . $category->image)))
                            <img id="imagePreview" class="image-preview image-pulse"
                                src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}">
                            <!-- Placeholder oculto inicialmente (se mostrará al eliminar) -->
                            <div class="image-placeholder" id="imagePlaceholder" style="display: none;">
                                <i class="ri-image-add-line"></i>
                                <p>Arrastra una imagen aquí</p>
                                <span>o haz clic para seleccionar</span>
                                <span>Formatos: PNG, JPG, JPEG (máx. 3 MB)</span>
                            </div>
                        @elseif($category->image)
                            <!-- Imagen no encontrada -->
                            <div class="image-error" id="imageError">
                                <i class="ri-folder-close-line"></i>
                                <p>Imagen no encontrada</p>
                                <span>Haz clic para subir una nueva</span>
                                <span>Formatos: PNG, JPG, JPEG (máx. 3 MB)</span>
                            </div>
                        @else
                            <!-- Sin imagen -->
                            <div class="image-placeholder" id="imagePlaceholder">
                                <i class="ri-image-add-line"></i>
                                <p>Arrastra una imagen aquí</p>
                                <span>o haz clic para seleccionar</span>
                                <span>Formatos: PNG, JPG, JPEG (máx. 3 MB)</span>
                            </div>
                        @endif

                        <!-- Imagen nueva cargada (oculta inicialmente) -->
                        <img id="imagePreviewNew" class="image-preview image-pulse" style="display: none;"
                            alt="Vista previa">

                        <!-- Overlay único para todas las imágenes -->
                        <div class="image-overlay" id="imageOverlay" style="display: none;">
                            <button type="button" class="boton-form boton-info" id="changeImageBtn"
                                title="Cambiar imagen">
                                <i class="ri-upload-2-line"></i>
                                <span class="boton-form-text">Cambiar</span>
                            </button>
                            <button type="button" class="boton-form boton-danger" id="removeImageBtn"
                                title="Eliminar imagen">
                                <i class="ri-delete-bin-line"></i>
                                <span class="boton-form-text">Eliminar</span>
                            </button>
                        </div>
                    </div>

                    <div class="image-filename" id="imageFilename"
                        style="{{ $category->image && file_exists(public_path('storage/' . $category->image)) ? 'display: flex;' : 'display: none;' }}">
                        <i class="ri-file-image-line"></i>
                        <span id="filenameText">{{ $category->image ? basename($category->image) : '' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // SISTEMA DE JERARQUÍA DE CATEGORÍAS PROGRESIVA
            document.addEventListener('DOMContentLoaded', function() {
                // 1. Inicializar submit loader PRIMERO
                const submitLoader = initSubmitLoader({
                    formId: 'categoryForm',
                    buttonId: 'submitBtn',
                    loadingText: 'Actualizando...'
                });

                // 2. Inicializar validación de formulario DESPUÉS (si lo necesitas)
                const formValidator = initFormValidator('#categoryForm', {
                    validateOnBlur: true,
                    validateOnInput: false,
                    scrollToFirstError: true
                });

                // 4. Inicializar manejador de imagen
                const imageHandler = initImageUpload({
                    mode: 'edit',
                    hasExistingImage: {{ $category->image && file_exists(public_path('storage/' . $category->image)) ? 'true' : 'false' }},
                    existingImageFilename: '{{ $category->image ? basename($category->image) : '' }}'
                });
            });
        </script>

        <!-- ============================
             FOOTER
        ============================= -->
        <div class="form-footer">
            <a href="{{ route('admin.categories.index') }}" class="boton-form boton-volver">
                <span class="boton-form-icon">
                    <i class="ri-arrow-left-circle-fill"></i>
                </span>
                <span class="boton-form-text">Atrás</span>
            </a>
            <button class="boton-form boton-accent" type="submit" id="submitBtn">
                <span class="boton-form-icon"> <i class="ri-loop-left-line"></i> </span>
                <span class="boton-form-text">Actualizar Categoría</span>
            </button>
        </div>
    </form>
    {{-- SUBCATEGORÍAS --}}
    @if (count($subcategories) > 0)
        <x-note-alert type="warning" title="Importante:" :dismissible="true">
            Esta categoría tiene <strong>{{ count($subcategories) }} subcategoría(s)</strong>.
            Si cambias su familia o ubicación, todas sus subcategorías se verán afectadas.
        </x-note-alert>
        <div class="form-body">
            <div class="card-header">
                <span class="card-title">Subcategorías</span>
                <p class="card-description">
                    Esta categoría tiene {{ count($subcategories) }} subcategorías. Puedes ver, editar o eliminar
                    cada una
                    de ellas.
                </p>
            </div>
            @canany(['categorias.export', 'categorias.delete'])
                <!-- Barra contextual -->
                <div class="selection-bar" id="selectionBar">
                    @can('categorias.export')
                        <div class="selection-actions">
                            <button id="exportSelectedExcel" class="boton-selection boton-success">
                                <span class="boton-selection-icon"><i class="ri-file-excel-2-fill"></i></span>
                                <span class="boton-selection-text">Excel</span>
                                <span class="boton-selection-dot">•</span>
                                <span class="selection-badge" id="excelBadge">0</span>
                            </button>
                            <button id="exportSelectedCsv" class="boton-selection boton-orange">
                                <span class="boton-selection-icon"><i class="ri-file-text-fill"></i></span>
                                <span class="boton-selection-text">CSV</span>
                                <span class="boton-selection-dot">•</span>
                                <span class="selection-badge" id="csvBadge">0</span>
                            </button>
                            <button id="exportSelectedPdf" class="boton-selection boton-secondary">
                                <span class="boton-selection-icon"><i class="ri-file-pdf-2-fill"></i></span>
                                <span class="boton-selection-text">PDF</span>
                                <span class="boton-selection-dot">•</span>
                                <span class="selection-badge" id="pdfBadge">0</span>
                            </button>
                        </div>
                    @endcan
                    @can('categorias.delete')
                        <button id="deleteSelected" class="boton-selection boton-danger">
                            <span class="boton-selection-icon"><i class="ri-delete-bin-fill"></i></span>
                            <span class="boton-selection-text">Eliminar</span>
                            <span class="boton-selection-dot">•</span>
                            <span class="selection-badge" id="deleteBadge">0</span>
                        </button>
                    @endcan
                    <div class="selection-info">
                        <span id="selectionCount">0 seleccionados</span>
                        <button class="selection-close" id="clearSelection">
                            <i class="ri-close-large-fill"></i>
                        </button>
                    </div>
                </div>
            @endcanany
            <!-- Tabla -->
            <div class="table-wrapper">
                <table class="tabla-general" id="tabla">
                    <thead>
                        <tr>
                            <th class="control"></th>
                            @canany(['categorias.export', 'categorias.delete'])
                                <th class="column-check-th column-not-order">
                                    <div><input type="checkbox" id="checkAll"></div>
                                </th>
                            @endcanany
                            <th class="column-id-th">Id</th>
                            <th class="column-name-th">Nombre</th>
                            <th class="column-description-th">Descripción</th>
                            <th class="column-status-th">Estado</th>
                            <th class="column-products-th text-center">Productos</th>
                            <th class="column-actions-th text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($subcategories as $cat)
                            <tr>
                                <td class="control"></td>

                                @canany(['categorias.export', 'categorias.delete'])
                                    <td class="column-check-td">
                                        <div>
                                            <input type="checkbox" class="check-row" value="{{ $cat->id }}">
                                        </div>
                                    </td>
                                @endcanany
                                <td class="column-id-td">{{ $cat->id }}</td>
                                <td class="column-name-td">
                                    {{ $cat->name }}
                                </td>
                                <td class="column-description-td">{{ $cat->description }}</td>
                                <!-- ESTADO -->
                                <td class="column-status-td" data-status="{{ $cat->status ? 1 : 0 }}">
                                    @can('categorias.update-status')
                                        <label class="switch-tabla">
                                            <input type="checkbox" class="switch-status" data-id="{{ $cat->id }}"
                                                {{ $cat->status ? 'checked' : '' }}>
                                            <span class="slider"></span>
                                        </label>
                                    @else
                                        @if ($cat->status)
                                            <span class="badge badge-success">
                                                <i class="ri-checkbox-circle-fill"></i>
                                                Activo
                                            </span>
                                        @else
                                            <span class="badge badge-danger">
                                                <i class="ri-close-circle-fill"></i>
                                                Inactivo
                                            </span>
                                        @endif
                                    @endcan
                                </td>
                                <td class="column-products-td">
                                    <span class="badge badge-primary"
                                        title="{{ $cat->products_count }} {{ Str::plural('producto', $cat->products_count) }}">
                                        <i class="ri-box-3-fill"></i>
                                        {{ $cat->products_count }}
                                    </span>
                                </td>
                                <td class="column-actions-td">
                                    <button class="boton-show-actions">
                                        <i class="ri-more-fill"></i>
                                    </button>

                                    <div class="tabla-botones">
                                        <button type="button" class="boton-sm boton-info btn-ver-categoria"
                                            data-slug="{{ $cat->slug }}">
                                            <i class="ri-eye-2-fill"></i>
                                            <span class="boton-sm-text">Ver</span>
                                        </button>

                                        @can('categorias.edit')
                                            <a href="{{ route('admin.categories.edit', $cat) }}"
                                                class="boton-sm boton-warning">
                                                <i class="ri-edit-circle-fill"></i>
                                                <span class="boton-sm-text">Editar</span>
                                            </a>
                                        @endcan

                                        @can('categorias.delete')
                                            <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST"
                                                class="delete-form" data-entity="categoría">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="boton-sm boton-danger">
                                                    <i class="ri-delete-bin-2-fill"></i>
                                                    <span class="boton-sm-text">Eliminar</span>
                                                </button>
                                            </form>
                                        @endcan

                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @push('scripts')
        <script>
            $(document).ready(function() {
                // ========================================
                // 📊 INICIALIZACIÓN CON DATATABLEMANAGER
                // ========================================
                const tableManager = new DataTableManager('#tabla', {

                    moduleName: 'categories',
                    entityNameSingular: 'categoría',
                    entityNamePlural: 'categorías',
                    deleteRoute: '/admin/categories',
                    statusRoute: '/admin/categories/{id}/status',
                    exportRoutes: {
                        excel: '/admin/categories/export/excel',
                        csv: '/admin/categories/export/csv',
                        pdf: '/admin/categories/export/pdf'
                    },
                    csrfToken: '{{ csrf_token() }}',

                    // Configuración de DataTable
                    pageLength: 10,
                    lengthMenu: [5, 10, 25, 50],

                    // Características (todas activadas por defecto)
                    features: {
                        selection: true,
                        export: true,
                        filters: true,
                        statusToggle: true,
                        responsive: true,
                        customPagination: true
                    },

                    // Callbacks personalizados (opcional)
                    callbacks: {
                        onDraw: () => {
                            console.log('🔄 Tabla redibujada');
                        },
                        onStatusChange: (id, status, response) => {
                            console.log(
                                `✅ Estado actualizado: ID ${id} -> ${status ? 'Activo' : 'Inactivo'}`);
                        },
                        onDelete: () => {
                            console.log('🗑️ Registros eliminados');
                        },
                        onExport: (type, format, count) => {
                            console.log(
                                `📤 Exportación: ${type} (${format}) - ${count || 'todos'} registros`);
                        }
                    }
                });

                // ========================================
                // 🔍 FILTROS PERSONALIZADOS
                // ========================================

                // Variables globales para los filtros
                let currentFamilyFilter = '';
                let currentLevelFilter = '';

                // Función de filtrado personalizado para DataTables
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {

                    if (settings.nTable.id !== 'tabla') return true;

                    const row = settings.aoData[dataIndex].nTr;

                    // Filtro Familia
                    if (currentFamilyFilter !== '') {
                        const rowFamilyId = $(row).find('.column-family-td').data('family-id').toString();

                        if (rowFamilyId !== currentFamilyFilter) {
                            return false;
                        }
                    }

                    // Filtro Nivel
                    if (currentLevelFilter !== '') {

                        const hasParent = $(row).find('.column-parent-td').find('.badge-warning').length > 0;

                        if (currentLevelFilter === 'root' && hasParent) {
                            return false;
                        }

                        if (currentLevelFilter === 'subcategory' && !hasParent) {
                            return false;
                        }
                    }

                    return true;
                });

                // Filtro por Familia
                $('#familyFilter').on('change', function() {
                    currentFamilyFilter = this.value;
                    tableManager.table.draw();

                    // Actualizar estado de filtros activos
                    tableManager.checkFiltersActive();

                    console.log(`🔍 Filtro Familia: ${currentFamilyFilter || 'Todas'}`);
                });

                // Filtro por Nivel (Raíz/Subcategoría)
                $('#levelFilter').on('change', function() {
                    currentLevelFilter = this.value;
                    tableManager.table.draw();

                    // Actualizar estado de filtros activos
                    tableManager.checkFiltersActive();

                    console.log(`🔍 Filtro Nivel: ${currentLevelFilter || 'Todos'}`);
                });

                // ========================================
                // 🎨 RESALTAR FILA CREADA/EDITADA
                // ========================================
                @if (Session::has('highlightRow'))
                    (function() {
                        const navEntries = (typeof performance !== 'undefined' && typeof performance
                                .getEntriesByType === 'function') ?
                            performance.getEntriesByType('navigation') : [];
                        const legacyNav = (typeof performance !== 'undefined' && performance.navigation) ?
                            performance.navigation.type :
                            null;
                        const navType = navEntries.length ? navEntries[0].type : legacyNav;
                        const isBackNavigation = navType === 'back_forward' || navType === 2;

                        if (isBackNavigation) {
                            return;
                        }

                        const highlightId = {{ Session::get('highlightRow') }};
                        setTimeout(() => {
                            const row = $(`#tabla tbody tr[data-id="${highlightId}"]`);
                            if (row.length) {
                                row.addClass('row-highlight');

                                row[0].scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'center'
                                });

                                setTimeout(() => {
                                    row.removeClass('row-highlight');
                                }, 3000);
                            }
                        }, 100);
                    })();
                @endif

                // ========================================
                // 🛠️ API DISPONIBLES (Ejemplos de uso)
                // ========================================
                // tableManager.getTable() - Obtiene instancia DataTable
                // tableManager.getSelectedItems() - Obtiene Map de items seleccionados
                // tableManager.refresh() - Refresca la tabla
                // tableManager.clearSelection() - Limpia selección
                // tableManager.destroy() - Destruye la instancia


            });
        </script>
    @endpush

    @include('admin.categories.modals.show-modal-category')
</x-admin-layout>
