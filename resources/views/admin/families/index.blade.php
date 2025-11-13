<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-success">
            <i class="ri-apps-line"></i>
        </div>
        Lista de Familias
    </x-slot>
    <x-slot name="action">
        <!-- boton para descargar -->
        <button type="button" class="boton-form boton-action">
            <span class="boton-form-icon"><i class="ri-download-2-fill"></i></span>
            <span class="boton-form-text">Exportar</span>
        </button>
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

    <!-- Modal Crear Familia -->
    <div id="createFamilyModal" class="modal hidden">
        <div class="modal-dialog">
            <div class="modal-header">
                <div>
                    <div class="page-title">Crear Nueva Familia</div>
                    <p>Complete el siguiente formulario para crear una nueva familia.</p>
                </div>
                <button type="button" id="closeCreateModal" class="close-modal">
                    <i class="ri-close-fill"></i>
                </button>
            </div>
            <div class="modal-body">
                @include('admin.families.partials.create-form')
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            $(document).ready(function() {
                // ========================================
                // 🌍 CONFIGURACIÓN GLOBAL
                // ========================================
                const currentPath = window.location.pathname;
                const moduleName = currentPath.split('/').filter(Boolean)[1];

                const language_es = {
                    emptyTable: `
                        <div class="tabla-no-data">
                            <i class="ri-database-2-line"></i>
                            <span>No hay datos disponibles en la tabla</span>
                        </div>
                    `,
                    zeroRecords: `
                        <div class="tabla-no-data">
                            <i class="ri-search-eye-line"></i>
                            <span>No se encontraron registros coincidentes</span>
                        </div>
                    `,
                };

                // ========================================
                // 🔧 FUNCIONES AUXILIARES GENERALES
                // ========================================

                // Animación de filas al redibujar
                function animarFilas() {
                    const tabla = document.getElementById('tabla');
                    if (tabla.classList.contains('no-animate')) return;

                    const filas = document.querySelectorAll('#tabla tbody tr');
                    filas.forEach((fila, i) => {
                        fila.style.animation = 'slideInLeft 0.3s ease-in-out';
                        fila.style.animationDelay = `${i * 0.02}s`;
                    });
                }

                // Actualizar íconos de orden
                function actualizarIconosOrden() {
                    document.querySelectorAll('#tabla thead th').forEach(th => {
                        if (th.classList.contains('column-check-th') ||
                            th.classList.contains('column-actions-th') ||
                            th.classList.contains('control')) return;

                        const orderSpan = th.querySelector('.dt-column-order');
                        if (!orderSpan) return;
                        orderSpan.innerHTML = '';

                        if (th.classList.contains('dt-ordering-asc')) {
                            orderSpan.innerHTML = '<i class="ri-sort-alphabet-asc orden-icon"></i>';
                        } else if (th.classList.contains('dt-ordering-desc')) {
                            orderSpan.innerHTML = '<i class="ri-sort-alphabet-desc orden-icon"></i>';
                        } else {
                            orderSpan.innerHTML = '<i class="ri-arrow-up-down-line orden-icon-none"></i>';
                        }
                    });
                }

                // Paginación personalizada + info
                function updateInfoAndPagination() {
                    const info = table.page.info();
                    const pagination = document.getElementById('tablePagination');
                    pagination.innerHTML = '';

                    const totalPages = info.pages;
                    const currentPage = info.page;
                    const windowSize = 1;

                    const addPageButton = (page) => {
                        const btn = document.createElement('button');
                        btn.textContent = page + 1;
                        btn.className = 'pagina-btn' + (page === currentPage ? ' activo' : '');
                        btn.addEventListener('click', () => table.page(page).draw('page'));
                        pagination.appendChild(btn);
                    };

                    // Botón "Primero"
                    const firstBtn = document.createElement('button');
                    firstBtn.innerHTML = '<i class="ri-skip-left-line"></i> <span class="btn-text">Primero</span>';
                    firstBtn.className = 'pagina-btn';
                    firstBtn.disabled = currentPage === 0;
                    firstBtn.addEventListener('click', () => table.page(0).draw('page'));
                    pagination.appendChild(firstBtn);

                    // Botón "Anterior"
                    const prevBtn = document.createElement('button');
                    prevBtn.innerHTML = '<i class="ri-arrow-left-s-line"></i> <span class="btn-text">Anterior</span>';
                    prevBtn.className = 'pagina-btn';
                    prevBtn.disabled = currentPage === 0;
                    prevBtn.addEventListener('click', () => table.page('previous').draw('page'));
                    pagination.appendChild(prevBtn);

                    // Páginas numéricas
                    if (currentPage > windowSize) addPageButton(0);
                    if (currentPage - windowSize > 1) {
                        const dots = document.createElement('span');
                        dots.textContent = '...';
                        dots.className = 'puntos';
                        pagination.appendChild(dots);
                    }

                    const start = Math.max(0, currentPage - windowSize);
                    const end = Math.min(totalPages - 1, currentPage + windowSize);
                    for (let i = start; i <= end; i++) addPageButton(i);

                    if (currentPage + windowSize < totalPages - 2) {
                        const dots = document.createElement('span');
                        dots.textContent = '...';
                        dots.className = 'puntos';
                        pagination.appendChild(dots);
                    }
                    if (currentPage < totalPages - windowSize - 1) addPageButton(totalPages - 1);

                    // Botón "Siguiente"
                    const nextBtn = document.createElement('button');
                    nextBtn.innerHTML = '<span class="btn-text">Siguiente</span> <i class="ri-arrow-right-s-line"></i>';
                    nextBtn.className = 'pagina-btn';
                    nextBtn.disabled = currentPage === totalPages - 1;
                    nextBtn.addEventListener('click', () => table.page('next').draw('page'));
                    pagination.appendChild(nextBtn);

                    // Botón "Último"
                    const lastBtn = document.createElement('button');
                    lastBtn.innerHTML = '<span class="btn-text">Último</span> <i class="ri-skip-right-line"></i>';
                    lastBtn.className = 'pagina-btn';
                    lastBtn.disabled = currentPage === totalPages - 1;
                    lastBtn.addEventListener('click', () => table.page(totalPages - 1).draw('page'));
                    pagination.appendChild(lastBtn);

                    // Info
                    document.getElementById('tableInfo').innerHTML =
                        `Mostrando <strong>${info.start + 1}</strong> a <strong>${info.end}</strong> de <strong>${info.recordsDisplay}</strong> registros`;
                }

                // ========================================
                // 📊 INICIALIZACIÓN DATATABLE
                // ========================================
                const indiceColumnaId = $('#tabla thead th.column-id-th').index();
                const table = new DataTable('#tabla', {
                    paging: true,
                    info: true,
                    searching: true,
                    ordering: true,
                    responsive: true,
                    columnDefs: [{
                        orderable: false,
                        targets: ['column-check-th', 'column-actions-th', 'control']
                    }],
                    dom: 't',
                    pageLength: 10,
                    lengthMenu: [5, 10, 25, 50],
                    order: [
                        [indiceColumnaId, 'desc']
                    ],
                    language: language_es,
                    scrollCollapse: false,
                    scroller: false,
                    initComplete: function() {
                        $('#tabla').addClass('ready');
                        actualizarIconosOrden();
                    },
                });

                // ========================================
                // 🎨 CONTROLES DE INTERFAZ GENERALES
                // ========================================

                // Buscador personalizado
                const searchInput = document.getElementById('customSearch');
                const clearButton = document.getElementById('clearSearch');
                const buscadorContainer = document.querySelector('.tabla-buscador');

                function toggleClearButton() {
                    if (searchInput.value.length > 0) {
                        buscadorContainer.classList.add('has-text');
                    } else {
                        buscadorContainer.classList.remove('has-text');
                    }
                }

                searchInput.addEventListener('keyup', function() {
                    table.search(this.value).draw();
                    toggleClearButton();
                });

                clearButton.addEventListener('click', function() {
                    searchInput.value = '';
                    table.search('').draw();
                    toggleClearButton();
                    searchInput.focus();
                });

                toggleClearButton();

                // Selector de cantidad de filas
                document.getElementById('entriesSelect').addEventListener('change', function() {
                    table.page.len(this.value).draw();
                });

                // Borde activo en celdas
                $('#tabla').on('click', 'td', function(e) {
                    if ($(e.target).is('input, button, a, i')) return;
                    $('#tabla td.active-cell').removeClass('active-cell');
                    $(this).addClass('active-cell');
                });

                $(document).on('click', function(e) {
                    if (!$(e.target).closest('#tabla').length) {
                        $('#tabla td.active-cell').removeClass('active-cell');
                    }
                });

                $('#tabla').on('order.dt page.dt search.dt', function() {
                    $('#tabla td.active-cell').removeClass('active-cell');
                });

                // Iconos de control responsive
                document.querySelectorAll('#tabla td.control').forEach(cell => {
                    cell.innerHTML = '<i class="ri-arrow-right-s-line control-icon"></i>';
                });

                $('#tabla').on('click', 'td.control', function() {
                    $(this).find('.control-icon')
                        .toggleClass('ri-arrow-right-s-line ri-arrow-down-s-line');
                });

                // ========================================
                // ✅ SISTEMA DE SELECCIÓN
                // ========================================
                let selectedIds = new Set();

                function updateDeleteButton() {
                    const deleteBtn = document.getElementById('deleteSelected');
                    const selectedCount = selectedIds.size;

                    if (selectedCount > 0) {
                        deleteBtn.disabled = false;
                        deleteBtn.querySelector('.boton-text').textContent =
                            `Eliminar Seleccionados (${selectedCount})`;
                    } else {
                        deleteBtn.disabled = true;
                        deleteBtn.querySelector('.boton-text').textContent = 'Eliminar Seleccionados';
                    }
                }

                // Click en celda checkbox
                $('#tabla').on('click', 'td.column-check-td', function(e) {
                    if (e.target.tagName === 'INPUT') return;
                    const checkbox = $(this).find('input[type="checkbox"]');
                    checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
                });

                // Checkbox individual
                $('#tabla').on('change', '.check-row', function() {
                    const id = $(this).val();
                    const tr = $(this).closest('tr');

                    if ($(this).is(':checked')) {
                        selectedIds.add(id);
                        tr.addClass('row-selected');
                    } else {
                        selectedIds.delete(id);
                        tr.removeClass('row-selected');
                    }

                    const all = $('#tabla tbody .check-row').length;
                    const checked = $('#tabla tbody .check-row:checked').length;
                    const checkAll = document.getElementById('checkAll');

                    if (checked === 0) {
                        checkAll.checked = false;
                        checkAll.indeterminate = false;
                    } else if (checked === all) {
                        checkAll.checked = true;
                        checkAll.indeterminate = false;
                    } else {
                        checkAll.checked = false;
                        checkAll.indeterminate = true;
                    }

                    updateDeleteButton();
                });

                // Seleccionar todos
                $('#checkAll').on('change', function() {
                    const checked = $(this).is(':checked');

                    $('#tabla tbody .check-row').each(function() {
                        const id = $(this).val();
                        $(this).prop('checked', checked);

                        if (checked) {
                            selectedIds.add(id);
                            $(this).closest('tr').addClass('row-selected');
                        } else {
                            selectedIds.delete(id);
                            $(this).closest('tr').removeClass('row-selected');
                        }
                    });

                    updateDeleteButton();
                });

                // ========================================
                // 📤 EXPORTACIÓN (GENERAL)
                // ========================================
                $('#exportSelected').on('click', function() {
                    const selected = Array.from(selectedIds);
                    const form = $('<form>', {
                        method: 'POST',
                        action: `/admin/${moduleName}/export/excel`
                    });

                    form.append('@csrf');
                    selected.forEach(id => {
                        form.append($('<input>', {
                            type: 'hidden',
                            name: 'ids[]',
                            value: id
                        }));
                    });

                    $('body').append(form);
                    form.submit();
                });

                $('#exportPdf').on('click', function() {
                    const selected = Array.from(selectedIds);
                    const form = $('<form>', {
                        method: 'POST',
                        action: `/admin/${moduleName}/export/pdf`
                    });

                    form.append('@csrf');

                    if (selected.length > 0) {
                        selected.forEach(id => {
                            form.append($('<input>', {
                                type: 'hidden',
                                name: 'ids[]',
                                value: id
                            }));
                        });
                    } else {
                        form.append($('<input>', {
                            type: 'hidden',
                            name: 'export_all',
                            value: '1'
                        }));
                    }

                    $('body').append(form);
                    form.submit();
                });

                // ========================================
                // 🔄 EVENTOS DATATABLE
                // ========================================
                table.on('draw', () => {
                    // Reinsertar íconos control
                    document.querySelectorAll('#tabla td.control').forEach(cell => {
                        if (!cell.querySelector('.control-icon')) {
                            cell.innerHTML = '<i class="ri-arrow-right-s-line control-icon"></i>';
                        }
                    });

                    // Restaurar checkboxes seleccionados
                    $('#tabla tbody .check-row').each(function() {
                        const id = $(this).val();
                        const tr = $(this).closest('tr');
                        if (selectedIds.has(id)) {
                            $(this).prop('checked', true);
                            tr.addClass('row-selected');
                        } else {
                            $(this).prop('checked', false);
                            tr.removeClass('row-selected');
                        }
                    });

                    // Actualizar checkbox "Seleccionar todo"
                    const all = $('#tabla tbody .check-row').length;
                    const checked = $('#tabla tbody .check-row:checked').length;
                    $('#checkAll').prop('checked', all > 0 && all === checked);

                    updateDeleteButton();
                    updateInfoAndPagination();
                    animarFilas();
                    actualizarIconosOrden();
                });

                // ========================================
                // 🎯 FUNCIONALIDADES ESPECÍFICAS DEL MÓDULO
                // ========================================

                // Cambiar estado (switch)
                $('#tabla').on('change', '.switch-status', function() {
                    const id = $(this).data('id');
                    const isChecked = $(this).is(':checked');
                    const url = `/admin/${moduleName}/${id}/status`;
                    const scrollPosition = window.pageYOffset || document.documentElement.scrollTop;
                    const switchElement = $(this);

                    $.ajax({
                        url: url,
                        type: 'PATCH',
                        data: {
                            _token: '{{ csrf_token() }}',
                            status: isChecked ? 1 : 0
                        },
                        success: function(response) {
                            if (response.success) {
                                const tabla = document.getElementById('tabla');
                                tabla.classList.add('no-animate');

                                document.querySelectorAll('#tabla tbody tr').forEach(fila => {
                                    fila.style.animation = 'none';
                                    fila.style.animationDelay = '0s';
                                });

                                setTimeout(() => {
                                    table.draw(false);
                                    setTimeout(() => window.scrollTo(0, scrollPosition),
                                    50);
                                }, 10);

                                setTimeout(() => tabla.classList.remove('no-animate'), 500);

                                // 🍞 Mostrar Toast de éxito
                                showToast({
                                    type: 'success',
                                    title: 'Estado actualizado',
                                    message: response.message ||
                                        'El estado se actualizó correctamente.',
                                    duration: 3000
                                });
                            }
                        },
                        error: function() {
                            // Revertir el switch si hay error
                            switchElement.prop('checked', !isChecked);

                            // ⚠️ Mostrar Toast de error
                            showToast({
                                type: 'danger',
                                title: 'Error',
                                message: 'No se pudo actualizar el estado.',
                                duration: 4000
                            });
                        }
                    });
                });

                // Filtro por estado
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    const selectedStatus = $('#statusFilter').val();
                    const statusCell = $(table.row(dataIndex).node()).find('.switch-status').is(':checked') ?
                        "1" : "0";
                    if (selectedStatus === "") return true;
                    return statusCell === selectedStatus;
                });

                $('#statusFilter').on('change', function() {
                    const wrapper = $(this).closest('.tabla-select-wrapper');
                    if ($(this).val() === "") {
                        wrapper.removeClass('filter-active');
                    } else {
                        wrapper.addClass('filter-active');
                    }
                    table.draw();
                });

                // Verificar estado inicial del filtro al cargar la página
                if ($('#statusFilter').val() !== "") {
                    $('#statusFilter').closest('.tabla-select-wrapper').addClass('filter-active');
                }

                // Eliminación múltiple
                $('#deleteSelected').on('click', function() {
                    function getFamilyNameById(id) {
                        const checkbox = $(`input[value="${id}"]`);
                        const row = checkbox.closest('tr');
                        return row.find('.column-name-td').text().trim();
                    }

                    handleMultipleDelete({
                        selectedIds: selectedIds,
                        getNameCallback: getFamilyNameById,
                        entityName: moduleName.slice(0, -1),
                        deleteRoute: `/admin/${moduleName}`,
                        csrfToken: '{{ csrf_token() }}',
                        buttonSelector: '#deleteSelected'
                    });
                });

                // ========================================
                // 🚀 INICIALIZACIÓN FINAL
                // ========================================
                updateInfoAndPagination();
                animarFilas();
                actualizarIconosOrden();
                updateDeleteButton();
            });
        </script>
    @endpush
</x-admin-layout>
