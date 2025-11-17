<x-admin-layout :showMobileFab="true" :useSlotContainer="false">
    <x-slot name="title">
        <div class="page-icon card-purple">
            <i class="ri-node-tree"></i>
        </div>
        Administrador Jerárquico
    </x-slot>

    <x-slot name="action">
        <a href="{{ route('admin.categories.index') }}" class="boton boton-secondary">
            <span class="boton-icon"><i class="ri-table-line"></i></span>
            <span class="boton-text">Vista Tabla</span>
        </a>

        <a href="{{ route('admin.categories.create') }}" class="boton boton-primary">
            <span class="boton-icon"><i class="ri-add-box-fill"></i></span>
            <span class="boton-text">Crear Categoría</span>
        </a>
    </x-slot>

    <div class="hierarchy-container">
        <!-- Panel de Estadísticas -->
        <div class="hierarchy-stats">
            <div class="stat-card">
                <div class="stat-icon card-info">
                    <i class="ri-stack-line"></i>
                </div>
                <div class="stat-content">
                    <h1 class="stat-value">{{ $stats['total_categories'] }}</h1>
                    <span class="stat-label">Total Categorías</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon card-success">
                    <i class="ri-folder-line"></i>
                </div>
                <div class="stat-content">
                    <h1 class="stat-value">{{ $stats['root_categories'] }}</h1>
                    <span class="stat-label">Categorías Raíz</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon card-warning">
                    <i class="ri-file-list-3-line"></i>
                </div>
                <div class="stat-content">
                    <h1 class="stat-value">{{ $stats['subcategories'] }}</h1>
                    <span class="stat-label">Subcategorías</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon card-purple">
                    <i class="ri-shopping-bag-3-line"></i>
                </div>
                <div class="stat-content">
                    <h1 class="stat-value">{{ $stats['categories_with_products'] }}</h1>
                    <span class="stat-label">Con Productos</span>
                </div>
            </div>
        </div>
        <div class="hierarchy-layout">
            <!-- Panel Izquierdo: Árbol -->
            <div class="hierarchy-tree-panel">
                <div class="tree-controls">
                    <div class="tree-search">
                        <i class="ri-search-line"></i>
                        <input type="text" id="treeSearch" placeholder="Buscar categorías..." autocomplete="off">
                        <button type="button" id="clearTreeSearch" class="search-clear">
                            <i class="ri-close-circle-fill"></i>
                        </button>
                    </div>
                    <div class="tree-buttons">
                        <button type="button" id="expandAll" class="boton boton-info">
                            <span class="boton-icon"><i class="ri-arrow-down-s-line"></i></span>
                            <span class="boton-text">Expandir Todo</span>
                        </button>

                        <button type="button" id="collapseAll" class="boton boton-info">
                            <span class="boton-icon"><i class="ri-arrow-up-s-line"></i></span>
                            <span class="boton-text">Colapsar Todo</span>
                        </button>
                    </div>
                </div>

                <div class="tree-wrapper" id="categoryTree">
                    <!-- El árbol se generará dinámicamente con JavaScript -->
                </div>
            </div>

            <!-- Panel Derecho: Información y Acciones -->
            <div class="hierarchy-info-panel">

                <!-- Panel vacío (cuando no hay selección) -->
                <div id="emptyPanel" class="info-panel-empty">
                    <i class="ri-information-line"></i>
                    <p>Selecciona una categoría para ver detalles</p>
                </div>

                <!-- Panel de información (cuando hay 1 seleccionado) -->
                <div id="infoPanel" class="info-panel-single" style="display: none;">
                    <div class="panel-header">
                        <h2 class="panel-title" id="infoName">Nombre de categoría</h2>
                        <button type="button" id="closeInfo" class="panel-close">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                    <div class="info-details">
                        <div class="info-row">
                            <span class="info-label">
                                <i class="ri-folder-3-line"></i> Familia:
                            </span>
                            <span id="infoFamily">-</span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">
                                <i class="ri-node-tree"></i> Padre:
                            </span>
                            <span id="infoParent">-</span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">
                                <i class="ri-list-ordered"></i> Nivel:
                            </span>
                            <span id="infoLevel">-</span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">
                                <i class="ri-file-list-3-line"></i> Subcategorías:
                            </span>
                            <span id="infoChildren">0</span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">
                                <i class="ri-shopping-bag-3-line"></i> Productos:
                            </span>
                            <span id="infoProducts">0</span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">
                                <i class="ri-toggle-line"></i> Estado:
                            </span>
                            <span id="infoStatus">-</span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">
                                <i class="ri-link"></i> Slug:
                            </span>
                            <span id="infoSlug" class="info-slug">-</span>
                        </div>
                    </div>
                    <div class="panel-actions">
                        <a href="#" id="editCategory" class="boton boton-warning btn-block">
                            <span class="boton-icon"><i class="ri-edit-line"></i></span>
                            <span class="boton-text">Editar</span>
                        </a>

                        <button type="button" id="createChild" class="boton boton-success btn-block">
                            <span class="boton-icon"><i class="ri-add-circle-line"></i></span>
                            <span class="boton-text">Crear Subcategoría</span>
                        </button>
                    </div>
                </div>

                <!-- Panel de operaciones masivas (cuando hay múltiples seleccionados) -->
                <div id="bulkPanel" class="info-panel-bulk" style="display: none;">
                    <div class="panel-header">
                        <h3 class="panel-title">
                            <i class="ri-checkbox-multiple-line"></i>
                            <span id="bulkCount">0</span> seleccionados
                        </h3>
                        <button type="button" id="closeBulk" class="panel-close">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>

                    <div class="panel-content">
                        <div class="bulk-move-section">
                            <h4>
                                <i class="ri-arrow-left-right-line"></i>
                                Mover Categorías
                            </h4>

                            <div class="bulk-move-controls">
                                <label>Destino:</label>
                                <select id="bulkMoveTarget" class="form-select">
                                    <option value="">Seleccionar destino...</option>
                                    <optgroup label="Convertir a Raíz">
                                        <option value="root">📍 Categoría Raíz (sin padre)</option>
                                    </optgroup>
                                    <optgroup label="Familias">
                                        @foreach ($families as $family)
                                            <option value="family_{{ $family->id }}">
                                                📁 {{ $family->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Categorías" id="categoryTargetGroup">
                                        <!-- Se llenará dinámicamente -->
                                    </optgroup>
                                </select>

                                <button type="button" id="previewMove" class="boton boton-info btn-block">
                                    <span class="boton-icon"><i class="ri-eye-line"></i></span>
                                    <span class="boton-text">Preview</span>
                                </button>

                                <button type="button" id="executeBulkMove" class="boton boton-primary btn-block">
                                    <span class="boton-icon"><i class="ri-arrow-right-line"></i></span>
                                    <span class="boton-text">Mover Ahora</span>
                                </button>
                            </div>
                        </div>

                        <div class="bulk-actions-section">
                            <h4>
                                <i class="ri-tools-line"></i>
                                Operaciones Masivas
                            </h4>

                            <button type="button" id="bulkDuplicate" class="boton boton-secondary btn-block">
                                <span class="boton-icon"><i class="ri-file-copy-line"></i></span>
                                <span class="boton-text">Duplicar Seleccionados</span>
                            </button>

                            <button type="button" id="bulkDelete" class="boton boton-danger btn-block">
                                <span class="boton-icon"><i class="ri-delete-bin-line"></i></span>
                                <span class="boton-text">Eliminar Seleccionados</span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- Modal de Preview de Movimiento -->
    <div id="previewModal" class="modal-overlay" style="display: none;">
        <div class="modal-container modal-medium">
            <div class="modal-header">
                <h3>
                    <i class="ri-eye-line"></i>
                    Preview de Movimiento
                </h3>
                <button type="button" class="modal-close" id="closePreviewModal">
                    <i class="ri-close-line"></i>
                </button>
            </div>

            <div class="modal-body">
                <div class="preview-summary">
                    <h4>Resumen de Cambios</h4>

                    <div class="preview-row">
                        <i class="ri-folder-transfer-line"></i>
                        <span>Mover <strong id="previewCategoriesCount">0</strong> categorías</span>
                    </div>

                    <div class="preview-row">
                        <i class="ri-arrow-right-line"></i>
                        <span>Destino: <strong id="previewTargetName">-</strong></span>
                    </div>

                    <div class="preview-impact">
                        <h5>Impacto de la Operación</h5>

                        <div class="impact-row">
                            <i class="ri-shopping-bag-3-line"></i>
                            <span><strong id="previewProductsCount">0</strong> productos serán reasignados</span>
                        </div>

                        <div class="impact-row">
                            <i class="ri-file-list-3-line"></i>
                            <span><strong id="previewSubcategoriesCount">0</strong> subcategorías se moverán
                                también</span>
                        </div>

                        <div class="impact-row" id="seoWarning" style="display: none;">
                            <i class="ri-alert-line"></i>
                            <span class="text-warning">⚠️ Esto puede afectar el SEO (cambio de URLs)</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="boton boton-secondary" id="cancelPreview">
                    Cancelar
                </button>
                <button type="button" class="boton boton-primary" id="confirmMove">
                    <i class="ri-check-line"></i>
                    Confirmar Movimiento
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Configuración global
            window.hierarchyConfig = {
                treeDataUrl: '{{ route('admin.categories.hierarchy.tree-data') }}',
                bulkMoveUrl: '{{ route('admin.categories.hierarchy.bulk-move') }}',
                previewMoveUrl: '{{ route('admin.categories.hierarchy.preview-move') }}',
                bulkDeleteUrl: '{{ route('admin.categories.hierarchy.bulk-delete') }}',
                bulkDuplicateUrl: '{{ route('admin.categories.hierarchy.bulk-duplicate') }}',
                editCategoryUrl: '{{ route('admin.categories.edit', ':id') }}',
                csrfToken: '{{ csrf_token() }}'
            };
        </script>

        <!-- Módulo de jerarquía nativo -->
        @vite(['resources/js/modules/category-hierarchy.js'])
    @endpush
</x-admin-layout>
