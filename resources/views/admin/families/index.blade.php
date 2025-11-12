<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-success">
            <i class="ri-apps-line"></i>
        </div>
        Lista de Familias
    </x-slot>
    <x-slot name="action">
        <a href="{{ route('admin.families.create') }}" class="boton boton-accent">
            <span class="boton-icon"><i class="ri-add-box-fill"></i></span>
            <span class="boton-text">Crear Familia</span>
        </a>
    </x-slot>
    <div class="familias-container">
        <!-- === Controles personalizados === -->
        <div class="tabla-controles">
            <div class="tabla-buscador">
                <i class="ri-search-eye-line buscador-icon"></i>
                <input type="text" id="customSearch" placeholder="Buscar familias por nombre" autocomplete="off" />
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
                        <select id="statusFilter">
                            <option value="">Todos los estados</option>
                            <option value="1">Activos</option>
                            <option value="0">Inactivos</option>
                        </select>
                        <i class="ri-filter-3-line selector-icon"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex gap-2 w-full">
            <button id="deleteSelected" class="boton boton-danger" disabled>
                <span class="boton-icon"><i class="ri-delete-bin-7-fill"></i></span>
                <span class="boton-text">Eliminar Seleccionados</span>
            </button>

            <button id="exportSelected" class="boton boton-success">
                <span class="boton-icon"><i class="ri-file-excel-2-fill"></i></span>
                <span class="boton-text">Excel</span>
            </button>
            <button id="exportPdf" class="boton boton-secondary">
                <span class="boton-icon"><i class="ri-file-pdf-2-fill"></i></span>
                <span class="boton-text">PDF</span>
            </button>
        </div>
        <!-- === Tabla === -->
        <div class="tabla-wrapper">
            <table id="tabla" class="tabla-general display">
                <thead>
                    <tr>
                        <th class="control"></th>
                        <th class="column-check-th">
                            <div>
                                <input type="checkbox" id="checkAll" name="checkAll">
                            </div>
                        </th>
                        <th class="column-id-th">ID</th>
                        <th class="column-name-th">Nombre</th>
                        <th class="column-description-th">Descripción</th>
                        <th class="column-status-th">Estado</th>
                        <th class="column-date-th">Fecha</th>
                        <th class="column-actions-th">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($families as $family)
                        <tr data-id="{{ $family->id }}" data-name="{{ $family->name }}">
                            <td class="control" title="Expandir detalles">
                            </td>
                            <td class="column-check-td">
                                <div>
                                    <input type="checkbox" class="check-row" id="check-row-{{ $family->id }}"
                                        name="families[]" value="{{ $family->id }}">
                                </div>
                            </td>
                            <td class="column-id-td">
                                <span class="id-text">{{ $family->id }}</span>
                            </td>
                            <td class="column-name-td">{{ $family->name }}</td>
                            <td class="column-description-td">{{ $family->description }}</td>
                            <td class="column-status-td">
                                <label class="switch-tabla">
                                    <input type="checkbox" class="switch-status" data-id="{{ $family->id }}"
                                        {{ $family->status ? 'checked' : '' }}>
                                    <span class="slider"></span>
                                </label>
                            </td>
                            <td>{{ $family->created_at ? $family->created_at->format('d/m/Y H:i') : 'Sin fecha' }}</td>

                            <td class="column-actions-td">
                                <div class="tabla-botones">
                                    <button class="boton boton-info" data-id="" title="Ver Familia">
                                        <span class="boton-text">Ver</span>
                                        <span class="boton-icon"><i class="ri-eye-2-fill"></i></span>
                                    </button>
                                    <a href="{{ route('admin.families.edit', $family) }}" title="Editar Familia"
                                        class="boton boton-warning">
                                        <span class="boton-icon"><i class="ri-edit-circle-fill"></i></span>
                                        <span class="boton-text">Editar</span>
                                    </a>
                                    <form action="{{ route('admin.families.destroy', $family) }}" method="POST"
                                        class="delete-form" data-entity="familia">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Eliminar Familia" class="boton boton-danger">
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

        <!-- === Footer: info + paginación === -->
        <div class="tabla-footer">
            <div id="tableInfo" class="tabla-info"></div>
            <div id="tablePagination" class="tabla-paginacion"></div>
        </div>
    </div>

    @push('scripts')
        <script>
            // 📦 Configuración del módulo
            const currentPath = window.location.pathname;
            const moduleName = currentPath.split('/').filter(Boolean)[1];

            $(document).ready(function() {
                // ========================================
                // 🔷 Inicializar DataTable Manager
                // ========================================
                const dataTableManager = new DataTableManager({
                    tableSelector: '#tabla',
                    moduleName: moduleName,
                    idColumnClass: '.column-id-th',
                    pageLength: 10,
                    lengthMenu: [5, 10, 25, 50],
                    orderColumn: 'desc',
                    callbacks: {
                        onStatusChange: (response) => {
                            console.log('Estado actualizado:', response);
                        }
                    }
                });

                // ========================================
                // 🔷 Inicializar Export Manager
                // ========================================
                const exportManager = new DataTableExportManager(
                    moduleName,
                    () => dataTableManager.getSelectedIds()
                );

                // ========================================
                // 🔷 Eliminación múltiple
                // ========================================
                $('#deleteSelected').on('click', function() {
                    const selectedIds = dataTableManager.getSelectedIds();
                    
                    function getFamilyNameById(id) {
                        const checkbox = $(`input[value="${id}"]`);
                        const row = checkbox.closest('tr');
                        return row.find('.column-name-td').text().trim();
                    }

                    handleMultipleDelete({
                        selectedIds: new Set(selectedIds),
                        getNameCallback: getFamilyNameById,
                        entityName: moduleName.slice(0, -1),
                        deleteRoute: `/admin/${moduleName}`,
                        csrfToken: '{{ csrf_token() }}',
                        buttonSelector: '#deleteSelected'
                    });
                });
            });
        </script>
    @endpush
</x-admin-layout>
