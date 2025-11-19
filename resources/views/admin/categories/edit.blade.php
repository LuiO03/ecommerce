<x-admin-layout>
    <x-slot name="title">
        Editar {{ $category->name }}
    </x-slot>

    <x-slot name="action">
        <a href="{{ route('admin.categories.index') }}" class="boton-form boton-action">
            <span class="boton-form-icon">
                <i class="ri-arrow-left-circle-fill"></i>
            </span>
            <span class="boton-form-text">Volver</span>
        </a>

        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="delete-form"
            data-entity="categoría" style="margin: 0;">
            @csrf
            @method('DELETE')
            <button class="boton boton-danger" type="submit">
                <span class="boton-icon"><i class="ri-delete-bin-6-fill"></i></span>
                <span class="boton-text">Eliminar</span>
            </button>
        </form>
    </x-slot>

    <form action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data"
        class="form-container" autocomplete="off" id="categoryForm">
        @csrf
        @method('PUT')

        <div class="form-info-banner">
            <i class="ri-lightbulb-line form-info-icon"></i>
            <div>
                <h4 class="form-info-title">Guía rápida:</h4>
                <ul>
                    <li>Los campos con asterisco (<i class="ri-asterisk text-accent"></i>) son obligatorios</li>
                    <li>Primero selecciona la <strong>familia</strong> a la que pertenecerá la categoría</li>
                    <li>Luego elige su ubicación en la jerarquía (opcional - si no eliges nada, será categoría raíz)
                    </li>
                </ul>
            </div>
        </div>

        <div class="form-row">

            <!-- ============================
                 COLUMNA IZQUIERDA
            ============================= -->
            <div class="form-column">
                {{-- FAMILIA (OBLIGATORIO) --}}
                <div class="input-group">
                    <label for="family_select" class="label-form">
                        Familia
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-stack-line input-icon"></i>
                        <select name="family_id" id="family_select" class="select-form" required>
                            <option value="" disabled>Seleccione una familia</option>
                            @foreach ($families as $family)
                                <option value="{{ $family->id }}"
                                    {{ old('family_id', $category->family_id) == $family->id ? 'selected' : '' }}>
                                    {{ $family->name }}
                                </option>
                            @endforeach
                        </select>
                        <i class="ri-arrow-down-s-line select-arrow"></i>
                    </div>
                </div>

                {{-- JERARQUÍA DE CATEGORÍAS PROGRESIVA --}}
                <div class="input-group">
                    <label class="label-form">
                        Ubicación en la jerarquía
                        <span class="label-hint">(opcional)</span>
                    </label>

                    {{-- Hidden input solo para parent_id --}}
                    <input type="hidden" name="parent_id" id="parent_id"
                        value="{{ old('parent_id', $category->parent_id) }}">

                    {{-- Contenedor dinámico de selects --}}
                    <div id="categoryHierarchySelects" style="display: none;">
                        {{-- Los selects se generarán dinámicamente según la familia --}}
                    </div>

                    <span id="noFamilyMessage" class="label-hint">
                        Primero selecciona una familia para ver las categorías disponibles
                    </span>

                    {{-- Breadcrumb visual de la ruta seleccionada --}}
                    <div id="hierarchyBreadcrumb"
                        style="display: none; margin-top: 0rem; padding: 0.75rem; background: var(--color-info-pastel); border-radius: 8px; font-size: 0.875rem;">
                        <i class="ri-route-line" style="margin-right: 0.5rem; color: var(--color-info);"></i>
                        <strong>Ruta seleccionada:</strong>
                        <span id="breadcrumbPath"
                            style="margin-left: 0.5rem; font-family: 'Courier New', monospace;"></span>
                    </div>
                </div>

                {{-- NAME --}}
                <div class="input-group">
                    <label for="name" class="label-form">
                        Nombre de la categoría
                        <i class="ri-asterisk text-accent"></i>
                    </label>

                    <div class="input-icon-container">
                        <i class="ri-price-tag-3-line input-icon"></i>

                        <input type="text" name="name" id="name" class="input-form" required
                            value="{{ old('name', $category->name) }}" placeholder="Ingrese el nombre">
                    </div>
                </div>



                {{-- DESCRIPTION --}}
                <div class="input-group">
                    <label for="description" class="label-form label-textarea">
                        Descripción
                    </label>

                    <div class="input-icon-container">
                        <textarea name="description" id="description" class="textarea-form" placeholder="Ingrese la descripción" rows="4">{{ old('description', $category->description) }}</textarea>

                        <i class="ri-file-text-line input-icon"></i>
                    </div>
                </div>

            </div>

            <!-- ============================
                 COLUMNA DERECHA
            ============================= -->
            <div class="form-column">
                {{-- STATUS --}}
                <div class="input-group">
                    <label for="status" class="label-form">
                        Estado
                        <i class="ri-asterisk text-accent"></i>
                    </label>

                    <div class="input-icon-container">
                        <i class="ri-focus-2-line input-icon"></i>

                        <select name="status" id="status" class="select-form" required>
                            <option value="" disabled>Seleccione un estado</option>

                            <option value="1" {{ old('status', $category->status) == '1' ? 'selected' : '' }}>
                                Activo
                            </option>

                            <option value="0" {{ old('status', $category->status) == '0' ? 'selected' : '' }}>
                                Inactivo
                            </option>
                        </select>

                        <i class="ri-arrow-down-s-line select-arrow"></i>
                    </div>
                </div>
                {{-- IMAGE UPLOAD --}}
                <div class="image-upload-section">
                    <label class="label-form">Imagen de la categoría</label>

                    <input type="file" name="image" id="image" class="file-input" accept="image/*">
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
                            </div>
                        @elseif($category->image)
                            <!-- Imagen no encontrada -->
                            <div class="image-error" id="imageError">
                                <i class="ri-folder-close-line"></i>
                                <p>Imagen no encontrada</p>
                                <span>Haz clic para subir una nueva</span>
                            </div>
                        @else
                            <!-- Sin imagen -->
                            <div class="image-placeholder" id="imagePlaceholder">
                                <i class="ri-image-add-line"></i>
                                <p>Arrastra una imagen aquí</p>
                                <span>o haz clic para seleccionar</span>
                            </div>
                        @endif

                        <!-- Imagen nueva cargada (oculta inicialmente) -->
                        <img id="imagePreviewNew" class="image-preview image-pulse" style="display: none;"
                            alt="Vista previa">

                        <!-- Overlay único para todas las imágenes -->
                        <div class="image-overlay" id="imageOverlay" style="display: none;">
                            <button type="button" class="overlay-btn" id="changeImageBtn" title="Cambiar imagen">
                                <i class="ri-upload-2-line"></i>
                                <span>Cambiar</span>
                            </button>
                            <button type="button" class="overlay-btn overlay-btn-danger" id="removeImageBtn"
                                title="Eliminar imagen">
                                <i class="ri-delete-bin-line"></i>
                                <span>Eliminar</span>
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
            {{-- SUBCATEGORÍAS --}}
            <div class="form-column">
                @if (count($subcategories) > 0)
                    <div class="input-group">
                        <label class="label-form">
                            Subcategorías
                            <span class="label-hint">({{ count($subcategories) }} total)</span>
                        </label>

                        <div class="subcategories-table-container">
                            <table class="subcategories-table" id="table">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Productos</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($subcategories as $subcat)
                                        <tr>
                                            <td>
                                                {{ $subcat['id'] }}
                                            </td>
                                            <td>
                                                <div class="subcategory-name">
                                                    <span class="level-indent"
                                                        style="margin-left: {{ $subcat['level'] * 1.5 }}rem;">
                                                        @for ($i = 0; $i < $subcat['level']; $i++)
                                                            @if ($i == $subcat['level'] - 1)
                                                                <i class="ri-corner-down-right-line"></i>
                                                            @endif
                                                        @endfor
                                                    </span>
                                                    <i class="ri-folder-line folder-icon"></i>
                                                    <span class="name-text">{{ $subcat['name'] }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                {{ $subcat['description'] }}
                                            </td>
                                            <td class="text-center">
                                                @if ($subcat['status'])
                                                    <span class="badge boton-success">
                                                        <i class="ri-checkbox-circle-fill"></i>
                                                        Activo
                                                    </span>
                                                @else
                                                    <span class="badge boton-danger">
                                                        <i class="ri-close-circle-fill"></i>
                                                        Inactivo
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-gray">
                                                    <i class="ri-archive-line"></i>
                                                    {{ $subcat['products_count'] }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="tabla-botones">
                                                    <button class="boton-sm boton-info">
                                                        <span class="boton-sm-icon"><i
                                                                class="ri-eye-2-fill"></i></span>
                                                    </button>
                                                    <a href="{{ route('admin.categories.edit', $subcat['slug']) }}"
                                                        class="boton-sm boton-warning">
                                                        <span class="boton-sm-icon"><i
                                                                class="ri-quill-pen-fill"></i></span>
                                                    </a>
                                                    <form
                                                        action="{{ route('admin.categories.destroy', $subcat['slug']) }}"
                                                        method="POST" class="delete-form" data-entity="categoría">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="boton-sm boton-danger">
                                                            <span class="boton-sm-icon"><i
                                                                    class="ri-delete-bin-2-fill"></i></span>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="subcategories-warning">
                            <i class="ri-information-line"></i>
                            <div class="subcategories-warning-text">
                                <h4 class="form-info-title">Importante:</h4>
                                <p>
                                    Esta categoría tiene <strong>{{ count($subcategories) }} subcategoría(s)</strong>.
                                    Si cambias su familia o ubicación, todas sus subcategorías se verán afectadas.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

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
                            console.log(`✅ Estado actualizado: ID ${id} -> ${status ? 'Activo' : 'Inactivo'}`);
                        },
                        onDelete: () => {
                            console.log('🗑️ Registros eliminados');
                        },
                        onExport: (type, format, count) => {
                            console.log(`📤 Exportación: ${type} (${format}) - ${count || 'todos'} registros`);
                        }
                    }
                });
            });
        </script>
    @endpush

        <script>
            // SISTEMA DE JERARQUÍA DE CATEGORÍAS PROGRESIVA
            document.addEventListener('DOMContentLoaded', function() {
                // Inicializar jerarquía de categorías
                const hierarchyManager = initCategoryHierarchy({
                    categoriesData: {!! json_encode(
                        $parents->map(function ($cat) {
                            return [
                                'id' => $cat->id,
                                'name' => $cat->name,
                                'family_id' => $cat->family_id,
                                'parent_id' => $cat->parent_id,
                            ];
                        }),
                    ) !!},
                    currentCategoryId: {{ $category->id }},
                    initialFamilyId: parseInt('{{ old('family_id', $category->family_id) }}'),
                    initialParentId: parseInt('{{ old('parent_id', $category->parent_id ?? 0) }}') || null
                });

                // MANEJO DE IMAGEN Y SUBMIT LOADER
                const imageHandler = initImageUpload({
                    mode: 'edit',
                    hasExistingImage: {{ $category->image && file_exists(public_path('storage/' . $category->image)) ? 'true' : 'false' }},
                    existingImageFilename: '{{ $category->image ? basename($category->image) : '' }}'
                });

                const submitLoader = initSubmitLoader({
                    formId: 'categoryForm',
                    buttonId: 'submitBtn',
                    loadingText: 'Actualizando...'
                });
            });
        </script>

        <!-- ============================
             FOOTER
        ============================= -->
        <div class="form-footer">
            <a href="{{ route('admin.categories.index') }}" class="boton-form boton-volver">
                <span class="boton-form-icon"> <i class="ri-arrow-left-circle-fill"></i> </span>
                <span class="boton-form-text">Cancelar</span>
            </a>

            <button class="boton-form boton-accent" type="submit" id="submitBtn">
                <span class="boton-form-icon"> <i class="ri-loop-left-line"></i> </span>
                <span class="boton-form-text">Actualizar Categoría</span>
            </button>
        </div>


    </form>
</x-admin-layout>
