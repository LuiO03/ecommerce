<x-admin-layout :showMobileFab="true">
    <x-slot name="title">
        <div class="page-icon card-orange">
            <i class="ri-file-text-line"></i>
        </div>
        Lista de Posts
    </x-slot>

    <x-slot name="action">
        <!-- Menú de exportación -->
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

        <a href="{{ route('admin.posts.create') }}" class="boton boton-primary">
            <span class="boton-icon"><i class="ri-add-box-fill"></i></span>
            <span class="boton-text">Crear Post</span>
        </a>
    </x-slot>

    <div class="actions-container">
        <div class="tabla-controles">
            <div class="tabla-buscador">
                <i class="ri-search-eye-line buscador-icon"></i>
                <input type="text" id="customSearch" placeholder="Buscar posts por título" autocomplete="off" />
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
                        <select id="sortFilterTitulo">
                            <option value="">Ordenar por</option>
                            <option value="title-asc">Título (A-Z)</option>
                            <option value="title-desc">Título (Z-A)</option>
                            <option value="date-desc">Más recientes</option>
                            <option value="date-asc">Más antiguos</option>
                            <option value="views-desc">Más vistos</option>
                        </select>
                        <i class="ri-sort-asc selector-icon"></i>
                    </div>
                </div>

                <div class="tabla-select-wrapper">
                    <div class="selector">
                        <select id="statusFilterPost">
                            <option value="">Todos los estados</option>
                            <option value="borrador">Borrador</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="publicado">Publicado</option>
                            <option value="rechazado">Rechazado</option>
                        </select>
                        <i class="ri-honour-line selector-icon"></i>
                    </div>
                </div>
                <div class="tabla-select-wrapper">
                    <div class="selector">
                        <select id="visibilityFilterPost">
                            <option value="">Todas las visibilidades</option>
                            <option value="publico">Público</option>
                            <option value="privado">Privado</option>
                            <option value="registrado">Registrado</option>
                        </select>
                        <i class="ri-target-line selector-icon"></i>
                    </div>
                </div>
                <!-- Botón para limpiar filtros -->
                <button type="button" id="clearFiltersBtn" class="boton-clear-filters" title="Limpiar todos los filtros">
                    <span class="boton-icon"><i class="ri-filter-off-line"></i></span>
                    <span class="boton-text">Limpiar filtros</span>
                </button>
            </div>
        </div>

        <!-- Barra contextual de selección (oculta por defecto) -->
        <div class="selection-bar" id="selectionBar">
            <div class="selection-actions">
                <button id="exportSelectedExcel" class="boton-selection boton-success">
                    <span class="boton-selection-icon">
                        <i class="ri-file-excel-2-line"></i>
                    </span>
                    <span class="boton-selection-text">Excel</span>
                    l
                    <span class="selection-badge" id="excelBadge">0</span>
                </button>
                <button id="exportSelectedCsv" class="boton-selection boton-orange">
                    <span class="boton-selection-icon">
                        <i class="ri-file-text-line"></i>
                    </span>
                    <span class="boton-selection-text">CSV</span>
                    l
                    <span class="selection-badge" id="csvBadge">0</span>
                </button>
                <button id="exportSelectedPdf" class="boton-selection boton-secondary">
                    <span class="boton-selection-icon">
                        <i class="ri-file-pdf-2-line"></i>
                    </span>
                    <span class="boton-selection-text">PDF</span>
                    l
                    <span class="selection-badge" id="pdfBadge">0</span>
                </button>
            </div>
            <button id="deleteSelected" class="boton-selection boton-danger">
                <span class="boton-selection-icon">
                    <i class="ri-delete-bin-line"></i>
                </span>
                <span class="boton-selection-text">Eliminar</span>
                l
                <span class="selection-badge" id="deleteBadge">0</span>
            </button>
            <div class="selection-info">
                <span id="selectionCount">0 seleccionados</span>
                <button class="selection-close" id="clearSelection" title="Deseleccionar todo">
                    <i class="ri-close-large-fill"></i>
                </button>
            </div>
        </div>

        <div class="tabla-wrapper">
            <table id="tabla" class="tabla-general display">
                <thead>
                    <tr>
                        <th class="control"></th>
                        <th class="column-check-th column-not-order">
                            <div><input type="checkbox" id="checkAll"></div>
                        </th>
                        <th class="column-id-th">ID</th>
                        <th class="column-images-th">Imagen</th>
                        <th class="column-name-th">Título</th>
                        <th class="column-views-th">Vistas</th>
                        <th class="column-allow-comments-th">Comentarios</th>
                        <th class="column-status-post-th">Estado</th>
                        <th class="column-visibility-th">Visibilidad</th>
                        <th class="column-created-th">Creado</th>
                        <th class="column-actions-th column-not-order">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($posts as $post)
                        <tr data-id="{{ $post->id }}" data-name="{{ $post->title }}">
                            <td class="control"></td>
                            <td class="column-check-td">
                                <div>
                                    <input type="checkbox" class="check-row" value="{{ $post->id }}">
                                </div>
                            </td>
                            <td class="column-id-td">{{ $post->id }}</td>
                            <td class="column-images-td">
                                <div class="thumbnail-container">
                                    @php $mainImagePath = $post->main_image_path; @endphp
                                    @if ($mainImagePath && Storage::disk('public')->exists($mainImagePath))
                                        <img src="{{ asset('storage/' . $mainImagePath) }}" alt="Imagen del post"
                                            class="table-thumbnail">
                                    @else
                                        <div class="table-no-thumbnail" title="Sin imagen">
                                            <i class="ri-file-close-fill"></i>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="column-name-td">{{ $post->title }}</td>
                            <td class="column-views-td">{{ $post->views }}</td>
                            <td class="column-allow-comments-td">
                                @if ($post->allow_comments)
                                    <span class="badge badge-success"><i class="ri-checkbox-circle-line"></i>
                                        Sí
                                    </span>
                                @else
                                    <span class="badge badge-danger"><i class="ri-close-circle-line"></i> No</span>
                                @endif
                            </td>
                            <td class="column-status-post-td">
                                @php
                                    $statusText = match ($post->status) {
                                        'draft' => '<i class="ri-pencil-line"></i> Borrador',
                                        'pending' => '<i class="ri-time-line"></i> Pendiente',
                                        'published' => '<i class="ri-check-line"></i> Publicado',
                                        'rejected' => '<i class="ri-close-line"></i> Rechazado',
                                        default => ucfirst($post->status),
                                    };
                                    $statusClass = match ($post->status) {
                                        'draft' => 'badge-gray',
                                        'pending' => 'badge-warning',
                                        'published' => 'badge-success',
                                        'rejected' => 'badge-danger',
                                        default => 'badge-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">{!! $statusText !!}</span>
                            </td>
                            <td class="column-visibility-td">
                                @php
                                    $visibilityText = match ($post->visibility) {
                                        'public' => '<i class="ri-global-line"></i> Público',
                                        'private' => '<i class="ri-lock-line"></i> Privado',
                                        'registered' => '<i class="ri-user-line"></i> Registrado',
                                        default => ucfirst($post->visibility),
                                    };
                                    $visibilityClass = match ($post->visibility) {
                                        'public' => 'badge-success',
                                        'private' => 'badge-warning',
                                        'registered' => 'badge-primary',
                                        default => 'badge-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $visibilityClass }}">{!! $visibilityText !!}</span>
                            </td>
                            <td class="column-created-td">{{ $post->created_at->format('d/m/Y H:i') }}</td>
                            <td class="column-actions-td">
                                <div class="tabla-botones">
                                    <button class="boton-sm boton-info btn-ver-post" data-slug="{{ $post->slug }}" title="Ver Post">
                                        <span class="boton-sm-icon"><i class="ri-eye-2-fill"></i></span>
                                    </button>
                                    <a href="{{ route('admin.posts.edit', $post) }}" class="boton-sm boton-warning" title="Editar Post">
                                        <span class="boton-sm-icon"><i class="ri-edit-circle-fill"></i></span>
                                    </a>
                                    <form action="{{ route('admin.posts.destroy', $post) }}" method="POST"
                                        class="delete-form" data-entity="post">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="boton-sm boton-danger" title="Eliminar Post">
                                            <span class="boton-sm-icon"><i class="ri-delete-bin-2-fill"></i></span>
                                        </button>
                                    </form>

                                    @if ($post->status === 'pending')
                                        <form action="{{ route('admin.posts.approve', $post) }}" method="POST"
                                            class="form-approve d-inline">
                                            @csrf
                                            <button type="button" class="boton-sm boton-success btn-approve" title="Aprobar Post">
                                                <i class="ri-send-plane-fill"></i>
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.posts.reject', $post) }}" method="POST"
                                            class="form-reject d-inline">
                                            @csrf
                                            <button type="button" class="boton-sm boton-danger btn-reject" title="Rechazar Post">
                                                <i class="ri-close-circle-fill"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="tabla-footer">
            <div id="tableInfo" class="tabla-info"></div>
            <div id="tablePagination" class="tabla-paginacion"></div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                const tableManager = new DataTableManager('#tabla', {
                    moduleName: 'posts',
                    entityNameSingular: 'post',
                    entityNamePlural: 'posts',
                    deleteRoute: '/admin/posts',
                    exportRoutes: {
                        excel: '/admin/posts/export/excel',
                        csv: '/admin/posts/export/csv',
                        pdf: '/admin/posts/export/pdf'
                    },
                    csrfToken: '{{ csrf_token() }}',
                    pageLength: 10,
                    lengthMenu: [5, 10, 25, 50],
                    features: {
                        selection: true,
                        export: true,
                        filters: true,
                        responsive: true,
                        customPagination: true
                    }
                });

                // -----------------------------
                // FILTRO POR ESTADO POR CLASE
                // -----------------------------
                const $statusFilterPost = $('#statusFilterPost');
                const $visibilityFilterPost = $('#visibilityFilterPost');

                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    // Filtro por estado (texto en columna Estado)
                    const selectedStatus = $statusFilterPost.val();
                    const selectedVisibility = $visibilityFilterPost.val();

                    // Si no hay ningún filtro, mostrar todo
                    if (!selectedStatus && !selectedVisibility) return true;

                    const row = tableManager.table.row(dataIndex).node();
                    // Estado: texto badge dentro de .column-status-post-td
                    const statusText = $(row).find('.column-status-post-td').text().trim().toLowerCase();
                    // Visibilidad: texto badge dentro de .column-visibility-td
                    const visibilityText = $(row).find('.column-visibility-td').text().trim().toLowerCase();

                    const statusOk = selectedStatus ? statusText === selectedStatus.toLowerCase() : true;
                    const visibilityOk = selectedVisibility ? visibilityText === selectedVisibility.toLowerCase() : true;

                    return statusOk && visibilityOk;
                });

                $statusFilterPost.on('change', function() {
                    tableManager.table.draw();
                });

                // -----------------------------
                // FILTRO POR VISIBILIDAD
                // -----------------------------
                $visibilityFilterPost.on('change', function() {
                    tableManager.table.draw();
                });

                // -----------------------------
                // FILTRO POR ORDENAMIENTO
                // -----------------------------
                $('#sortFilterTitulo').on('change', function() {
                    const val = this.value;

                    switch (val) {
                        case 'title-asc':
                            tableManager.table.order([3, 'asc']).draw();
                            break;
                        case 'title-desc':
                            tableManager.table.order([3, 'desc']).draw();
                            break;
                        case 'date-asc':
                            tableManager.table.order([9, 'asc']).draw();
                            break;
                        case 'date-desc':
                            tableManager.table.order([9, 'desc']).draw();
                            break;
                        case 'views-desc':
                            tableManager.table.order([5, 'desc']).draw();
                            break;
                        default:
                            tableManager.table.order([]).draw();
                    }
                });

                // ========================================
                // 🎨 RESALTAR FILA CREADA/EDITADA
                // ========================================
                @if (Session::has('highlightRow'))
                    (function() {
                        const navEntries = (typeof performance !== 'undefined' && typeof performance.getEntriesByType === 'function')
                            ? performance.getEntriesByType('navigation')
                            : [];
                        const legacyNav = (typeof performance !== 'undefined' && performance.navigation)
                            ? performance.navigation.type
                            : null;
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

                                // Scroll suave hacia la fila
                                row[0].scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'center'
                                });

                                // Remover la clase después de la animación
                                setTimeout(() => {
                                    row.removeClass('row-highlight');
                                }, 3000);
                            }
                        }, 100);
                    })();
                @endif
            });

            document.addEventListener("DOMContentLoaded", () => {

                // APROBAR
                document.querySelectorAll(".btn-approve").forEach(btn => {
                    btn.addEventListener("click", function() {

                        const form = this.closest("form");
                        const postTitle = this.closest("tr").dataset.name;

                        showConfirm({
                            type: "success",
                            header: "¡Atención!",
                            title: "¿Aprobar post?",
                            message: `El post <b>${postTitle}</b> será publicado.`,
                            onConfirm: () => form.submit()
                        });
                    });
                });

                // RECHAZAR
                document.querySelectorAll(".btn-reject").forEach(btn => {
                    btn.addEventListener("click", function() {

                        const form = this.closest("form");
                        const postTitle = this.closest("tr").dataset.name;

                        showConfirm({
                            type: "danger",
                            header: "¡Atención!",
                            title: "¿Rechazar post?",
                            message: `El post <b>${postTitle}</b> será marcado como rechazado.`,
                            onConfirm: () => form.submit()
                        });
                    });
                });

            });
        </script>

        </script>
    @endpush
    @include('admin.posts.modals.show-modal-post')
</x-admin-layout>
