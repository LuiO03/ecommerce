{{-- 
    ========================================
    EJEMPLO DE IMPLEMENTACIÓN: DataTableManager
    Módulo: Products (Productos)
    ========================================
    
    Este es un ejemplo completo de cómo implementar DataTableManager
    en un nuevo módulo. Copia esta estructura para tus propios módulos.
--}}

<x-admin-layout :showMobileFab="true">
    <x-slot name="title">
        <div class="page-icon card-primary">
            <i class="ri-shopping-bag-line"></i>
        </div>
        Lista de Productos
    </x-slot>
    
    <x-slot name="action">
        <!-- Menú desplegable de exportación -->
        <div class="export-menu-container">
            <button type="button" class="boton-form boton-action" id="exportMenuBtn">
                <span class="boton-form-icon"><i class="ri-download-2-fill"></i></span>
                <span class="boton-form-text">Exportar</span>
                <i class="ri-arrow-down-s-line"></i>
            </button>
            <div class="export-dropdown" id="exportDropdown">
                <button type="button" class="export-option" id="exportAllExcel">
                    <i class="ri-file-excel-2-fill"></i>
                    <span>Exportar todo a Excel</span>
                </button>
                <button type="button" class="export-option" id="exportAllCsv">
                    <i class="ri-file-text-fill"></i>
                    <span>Exportar todo a CSV</span>
                </button>
                <button type="button" class="export-option" id="exportAllPdf">
                    <i class="ri-file-pdf-2-fill"></i>
                    <span>Exportar todo a PDF</span>
                </button>
            </div>
        </div>
        
        <a href="{{ route('admin.products.create') }}" class="boton boton-primary">
            <span class="boton-icon"><i class="ri-add-box-fill"></i></span>
            <span class="boton-text">Crear Producto</span>
        </a>
    </x-slot>
    
    <div class="actions-container">
        {{-- === CONTROLES PERSONALIZADOS === --}}
        <div class="tabla-controles">
            <div class="tabla-buscador">
                <i class="ri-search-eye-line buscador-icon"></i>
                <input type="text" id="customSearch" placeholder="Buscar productos por nombre o SKU" autocomplete="off" />
                <button type="button" id="clearSearch" class="buscador-clear" title="Limpiar búsqueda">
                    <i class="ri-close-circle-fill"></i>
                </button>
            </div>

            <div class="tabla-filtros">
                <div class="tabla-select-wrapper">
                    <div class="selector">
                        <select id="entriesSelect">
                            <option value="5">5/pág.</option>
                            <option value="10" selected>10/pág.</option>
                            <option value="25">25/pág.</option>
                            <option value="50">50/pág.</option>
                        </select>
                        <i class="ri-arrow-down-s-line selector-icon"></i>
                    </div>
                </div>

                <div class="tabla-select-wrapper">
                    <div class="selector">
                        <select id="sortFilter">
                            <option value="">Ordenar por</option>
                            <option value="name-asc">Nombre (A-Z)</option>
                            <option value="name-desc">Nombre (Z-A)</option>
                            <option value="date-desc">Más recientes</option>
                            <option value="date-asc">Más antiguos</option>
                        </select>
                        <i class="ri-sort-asc selector-icon"></i>
                    </div>
                </div>

                <div class="tabla-select-wrapper">
                    <div class="selector">
                        <select id="statusFilter">
                            <option value="">Todos los estados</option>
                            <option value="1">Activos</option>
                            <option value="0">Inactivos</option>
                        </select>
                        <i class="ri-filter-3-line selector-icon"></i>
                    </div>
                </div>
            </div>

            <button type="button" id="clearFiltersBtn" class="boton-clear-filters" title="Limpiar todos los filtros">
                <span class="boton-icon"><i class="ri-filter-off-line"></i></span>
                <span class="boton-text">Limpiar filtros</span>
            </button>
        </div>

        {{-- === BARRA CONTEXTUAL DE SELECCIÓN === --}}
        <div class="selection-bar" id="selectionBar">
            <div class="selection-actions">
                <button id="exportSelectedExcel" class="boton-selection boton-success">
                    <span class="boton-selection-icon"><i class="ri-file-excel-2-fill"></i></span>
                    <span class="boton-selection-text">Excel</span>
                    <span class="selection-badge" id="excelBadge">0</span>
                </button>
                <button id="exportSelectedCsv" class="boton-selection boton-orange">
                    <span class="boton-selection-icon"><i class="ri-file-text-fill"></i></span>
                    <span class="boton-selection-text">CSV</span>
                    <span class="selection-badge" id="csvBadge">0</span>
                </button>
                <button id="exportSelectedPdf" class="boton-selection boton-secondary">
                    <span class="boton-selection-icon"><i class="ri-file-pdf-2-fill"></i></span>
                    <span class="boton-selection-text">PDF</span>
                    <span class="selection-badge" id="pdfBadge">0</span>
                </button>
            </div>
            <button id="deleteSelected" class="boton-selection boton-danger">
                <span class="boton-selection-icon"><i class="ri-delete-bin-line"></i></span>
                <span class="boton-selection-text">Eliminar</span>
                <span class="selection-badge" id="deleteBadge">0</span>
            </button>
            <div class="selection-info">
                <span id="selectionCount">0 seleccionados</span>
                <button class="selection-close" id="clearSelection" title="Deseleccionar todo">
                    <i class="ri-close-large-fill"></i>
                </button>
            </div>
        </div>
        
        {{-- === TABLA === --}}
        <div class="tabla-wrapper">
            <table id="tabla" class="tabla-general display">
                <thead>
                    <tr>
                        <th class="control"></th>
                        <th class="column-check-th column-not-order">
                            <div><input type="checkbox" id="checkAll" name="checkAll"></div>
                        </th>
                        <th class="column-id-th">ID</th>
                        <th class="column-name-th">Nombre</th>
                        <th class="column-description-th">SKU</th>
                        <th class="column-status-th">Estado</th>
                        <th class="column-date-th">Fecha</th>
                        <th class="column-actions-th column-not-order">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr data-id="{{ $product->id }}" data-name="{{ $product->name }}">
                            <td class="control"></td>
                            
                            <td class="column-check-td">
                                <div>
                                    <input type="checkbox" class="check-row" 
                                           id="check-row-{{ $product->id }}" 
                                           name="products[]" 
                                           value="{{ $product->id }}">
                                </div>
                            </td>
                            
                            <td class="column-id-td">
                                <span class="id-text">{{ $product->id }}</span>
                            </td>
                            
                            <td class="column-name-td">{{ $product->name }}</td>
                            <td class="column-description-td">{{ $product->sku }}</td>
                            
                            <td class="column-status-td">
                                <label class="switch-tabla">
                                    <input type="checkbox" class="switch-status" 
                                           data-id="{{ $product->id }}" 
                                           {{ $product->status ? 'checked' : '' }}>
                                    <span class="slider"></span>
                                </label>
                            </td>
                            
                            <td>{{ $product->created_at ? $product->created_at->format('d/m/Y H:i') : 'Sin fecha' }}</td>

                            <td class="column-actions-td">
                                <div class="tabla-botones">
                                    <a href="{{ route('admin.products.show', $product) }}" 
                                       class="boton boton-info" 
                                       title="Ver Producto">
                                        <span class="boton-text">Ver</span>
                                        <span class="boton-icon"><i class="ri-eye-2-fill"></i></span>
                                    </a>
                                    
                                    <a href="{{ route('admin.products.edit', $product) }}" 
                                       class="boton boton-warning" 
                                       title="Editar Producto">
                                        <span class="boton-icon"><i class="ri-quill-pen-fill"></i></span>
                                        <span class="boton-text">Editar</span>
                                    </a>
                                    
                                    <form action="{{ route('admin.products.destroy', $product) }}" 
                                          method="POST" 
                                          class="delete-form" 
                                          data-entity="producto">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="boton boton-danger" title="Eliminar Producto">
                                            <span class="boton-text">Borrar</span>
                                            <span class="boton-icon"><i class="ri-delete-bin-2-fill"></i></span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- === FOOTER: INFO + PAGINACIÓN === --}}
        <div class="tabla-footer">
            <div id="tableInfo" class="tabla-info"></div>
            <div id="tablePagination" class="tabla-paginacion"></div>
        </div>
    </div>
    
    @push('scripts')
        <script>
            $(document).ready(function() {
                // ========================================
                // 📊 INICIALIZACIÓN CON DATATABLEMANAGER
                // ========================================
                const tableManager = new DataTableManager('#tabla', {
                    // Configuración básica del módulo
                    moduleName: 'products',
                    entityNameSingular: 'producto',
                    entityNamePlural: 'productos',
                    
                    // Rutas del backend
                    deleteRoute: '/admin/products',
                    statusRoute: '/admin/products/{id}/status',
                    exportRoutes: {
                        excel: '/admin/products/export/excel',
                        csv: '/admin/products/export/csv',
                        pdf: '/admin/products/export/pdf'
                    },
                    
                    // Token CSRF
                    csrfToken: '{{ csrf_token() }}',
                    
                    // Configuración de DataTable (opcional)
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
                            console.log('🔄 Tabla de productos redibujada');
                        },
                        onStatusChange: (id, status, response) => {
                            console.log(`✅ Producto ${id}: ${status ? 'Activo' : 'Inactivo'}`);
                            
                            // Aquí puedes agregar lógica adicional
                            // Por ejemplo, actualizar un contador en tiempo real
                        },
                        onDelete: () => {
                            console.log('🗑️ Productos eliminados');
                            
                            // Aquí puedes refrescar datos relacionados
                        },
                        onExport: (type, format, count) => {
                            console.log(`📤 Exportación: ${type} (${format}) - ${count || 'todos'} productos`);
                        }
                    }
                });

                // ========================================
                // 🎨 RESALTAR FILA CREADA/EDITADA
                // ========================================
                @if (Session::has('highlightRow'))
                    const highlightId = {{ Session::get('highlightRow') }};
                    setTimeout(() => {
                        const row = $(`#tabla tbody tr[data-id="${highlightId}"]`);
                        if (row.length) {
                            row.addClass('row-highlight');
                            row[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                            setTimeout(() => row.removeClass('row-highlight'), 3000);
                        }
                    }, 100);
                @endif
                
                // ========================================
                // 🛠️ FUNCIONALIDADES ADICIONALES (Opcional)
                // ========================================
                
                // Ejemplo: Agregar funcionalidad personalizada al botón Ver
                $('#tabla').on('click', '.boton-info', function(e) {
                    // Aquí puedes agregar lógica adicional antes de navegar
                    console.log('Ver producto:', $(this).closest('tr').data('id'));
                });
                
                // Ejemplo: Acceder a la API del TableManager
                window.productTableManager = tableManager; // Exponer globalmente si es necesario
                
                // Ejemplo: Obtener selección actual
                // const selected = tableManager.getSelectedItems();
                // console.log('Items seleccionados:', selected);
                
                // Ejemplo: Refrescar tabla programáticamente
                // tableManager.refresh();
            });
        </script>
    @endpush
    
</x-admin-layout>
