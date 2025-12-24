// ========================================
// 📊 DATATABLE MANAGER - Sistema Modular Reutilizable
// ========================================

/**
 * Clase para gestionar tablas DataTables con todas las funcionalidades del sistema
 * Incluye: selección múltiple, exportación, filtros, paginación custom, etc.
 *
 * @example
 * const tableManager = new DataTableManager('#tabla', {
 *     moduleName: 'families',
 *     entityNameSingular: 'familia',
 *     deleteRoute: '/admin/families',
 *     statusRoute: '/admin/families/{id}/status',
 *     exportRoutes: {
 *         excel: '/admin/families/export/excel',
 *         csv: '/admin/families/export/csv',
 *         pdf: '/admin/families/export/pdf'
 *     }
 * });
 */
class DataTableManager {
    constructor(tableSelector, options = {}) {
        this.tableSelector = tableSelector;
        this.$table = $(tableSelector);

        // Configuración por defecto
        this.config = {
            moduleName: options.moduleName || this.extractModuleName(),
            entityNameSingular: options.entityNameSingular || 'registro',
            entityNamePlural: options.entityNamePlural || 'registros',
            deleteRoute: options.deleteRoute,
            statusRoute: options.statusRoute,
            exportRoutes: options.exportRoutes || {},
            csrfToken: options.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content,

            // Configuración de DataTable
            pageLength: options.pageLength || 10,
            lengthMenu: options.lengthMenu || [5, 10, 25, 50],
            defaultOrder: options.defaultOrder || null, // Se calculará automáticamente

            // Columnas especiales (se calculan automáticamente)
            columns: {
                id: options.idColumn || null,
                name: options.nameColumn || null,
                date: options.dateColumn || null,
                status: options.statusColumn || null
            },

            // Características activadas
            features: {
                selection: options.selection !== false,
                export: options.export !== false,
                filters: options.filters !== false,
                statusToggle: options.statusToggle !== false,
                responsive: options.responsive !== false,
                customPagination: options.customPagination !== false
            },

            // Callbacks personalizados
            callbacks: {
                onDraw: options.onDraw || null,
                onStatusChange: options.onStatusChange || null,
                onDelete: options.onDelete || null,
                onExport: options.onExport || null
            }
        };

        // Estado interno
        this.selectedItems = new Map();
        this.table = null;

        // Inicializar
        this.init();
    }

    // 🎬 INICIALIZACIÓN

    init() {
        if (!this.$table.length) {
            console.error(`❌ Tabla no encontrada: ${this.tableSelector}`);
            return;
        }

        this.detectColumns();
        this.initDataTable();
        this.initControls();

        if (this.config.features.selection) {
            this.initSelection();
        }

        if (this.config.features.export) {
            this.initExport();
        }

        if (this.config.features.filters) {
            this.initFilters();
        }

        if (this.config.features.statusToggle) {
            this.initStatusToggle();
        }

        this.initHighlight();

        console.log(`✅ DataTableManager inicializado para módulo: ${this.config.moduleName}`);
    }

    // 🔍 DETECCIÓN AUTOMÁTICA

    extractModuleName() {
        const path = window.location.pathname;
        const parts = path.split('/').filter(Boolean);
        return parts[1] || 'entities';
    }

    detectColumns() {
        const headers = this.$table.find('thead th');

        headers.each((index, th) => {
            const $th = $(th);

            if ($th.hasClass('column-id-th')) this.config.columns.id = index;
            if ($th.hasClass('column-name-th')) this.config.columns.name = index;
            if ($th.hasClass('column-date-th')) this.config.columns.date = index;
            if ($th.hasClass('column-status-th')) this.config.columns.status = index;
        });

        // Orden por defecto: ID descendente
        if (!this.config.defaultOrder && this.config.columns.id !== null) {
            this.config.defaultOrder = [[this.config.columns.id, 'desc']];
        }
    }

    // 📊 INICIALIZAR DATATABLE

    initDataTable() {
        // Plugin de ordenamiento para fechas con "Sin fecha" al final
        $.fn.dataTable.ext.type.order['date-null-last-pre'] = function (data) {
            if (!data || data === 'Sin fecha' || data.trim() === '') {
                return 999999999999;
            }

            const parts = data.split(' ');
            if (parts.length === 2) {
                const dateParts = parts[0].split('/');
                const timeParts = parts[1].split(':');
                if (dateParts.length === 3 && timeParts.length === 2) {
                    return new Date(
                        parseInt(dateParts[2]),
                        parseInt(dateParts[1]) - 1,
                        parseInt(dateParts[0]),
                        parseInt(timeParts[0]),
                        parseInt(timeParts[1])
                    ).getTime();
                }
            }
            return 0;
        };

        const columnDefs = [
            {
                orderable: false,
                targets: ['column-not-order', 'control']
            }
        ];

        // Aplicar tipo de ordenamiento a columna de fecha
        if (this.config.columns.date !== null) {
            columnDefs.push({
                type: 'date-null-last',
                targets: this.config.columns.date
            });
        }

        // --- Persistencia de página en localStorage ---
        const storageKey = `datatable_page_${this.config.moduleName}`;
        let initialPage = 0;
        try {
            const savedPage = localStorage.getItem(storageKey);
            if (savedPage !== null && !isNaN(Number(savedPage))) {
                initialPage = Number(savedPage);
            }
        } catch (e) {}

        this.table = new DataTable(this.tableSelector, {
            paging: true,
            info: true,
            searching: true,
            ordering: true,
            responsive: this.config.features.responsive,
            columnDefs: columnDefs,
            dom: 't',
            pageLength: this.config.pageLength,
            lengthMenu: this.config.lengthMenu,
            order: this.config.defaultOrder || [[0, 'desc']],
            language: {
                emptyTable: `
                    <div class="tabla-no-data">
                        <i class="ri-folder-warning-line"></i>
                        <span>No hay datos disponibles en la tabla</span>
                    </div>
                `,
                zeroRecords: `
                    <div class="tabla-no-data">
                        <i class="ri-search-eye-line"></i>
                        <span>No se encontraron registros coincidentes</span>
                    </div>
                `,
            },
            scrollCollapse: false,
            scroller: false,
            displayStart: initialPage * (this.config.pageLength || 10),
            initComplete: () => {
                this.$table.addClass('ready');
                this.updateOrderIcons();

                // Forzar actualización de paginación e info después de inicializar
                setTimeout(() => {
                    if (this.config.features.customPagination) {
                        this.updateInfoAndPagination();
                    }
                    this.animateRows();
                }, 50);
            }
        });

        // Guardar página en localStorage al cambiar
        this.table.on('page', () => {
            try {
                const page = this.table.page();
                localStorage.setItem(storageKey, page);
            } catch (e) {}
        });

        // Eventos de redibujado
        this.table.on('draw', () => {
            this.onTableDraw();
        });
    }

    // 🎨 CONTROLES DE INTERFAZ

    initControls() {
        // Buscador personalizado
        const searchInput = $('#customSearch');
        const clearButton = $('#clearSearch');
        const searchContainer = $('.tabla-buscador');

        if (searchInput.length) {
            searchInput.on('keyup', (e) => {
                this.table.search(e.target.value).draw();
                this.toggleClearButton(searchInput, searchContainer);
            });

            clearButton.on('click', () => {
                searchInput.val('');
                this.table.search('').draw();
                this.toggleClearButton(searchInput, searchContainer);
                searchInput.focus();
            });

            this.toggleClearButton(searchInput, searchContainer);
        }

        // Selector de cantidad de filas
        $('#entriesSelect').on('change', (e) => {
            this.table.page.len(e.target.value).draw();
        });

        // Celda activa
        this.$table.on('click', 'td', (e) => {
            if ($(e.target).is('input, button, a, i')) return;
            $(`${this.tableSelector} td.active-cell`).removeClass('active-cell');
            $(e.currentTarget).addClass('active-cell');
        });

        $(document).on('click', (e) => {
            if (!$(e.target).closest(this.tableSelector).length) {
                $(`${this.tableSelector} td.active-cell`).removeClass('active-cell');
            }
        });

        // Íconos de control responsive
        this.initResponsiveControls();
    }

    toggleClearButton(input, container) {
        if (input.val().length > 0) {
            container.addClass('has-text');
        } else {
            container.removeClass('has-text');
        }
    }

    initResponsiveControls() {
        this.$table.find('td.control').each((i, cell) => {
            if (!$(cell).find('.control-icon').length) {
                $(cell).html('<i class="ri-arrow-right-s-line control-icon"></i>');
            }
        });

        this.$table.on('click', 'td.control', function () {
            $(this).find('.control-icon')
                .toggleClass('ri-arrow-right-s-line ri-arrow-down-s-line');
        });
    }

    // ✅ SISTEMA DE SELECCIÓN

    initSelection() {
        // Click en celda checkbox
        this.$table.on('click', 'td.column-check-td', (e) => {
            if (e.target.tagName === 'INPUT') return;
            const checkbox = $(e.currentTarget).find('input[type="checkbox"]');
            checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
        });

        // Checkbox individual
        this.$table.on('change', '.check-row', (e) => {
            const $checkbox = $(e.currentTarget);
            const id = $checkbox.val();
            const $tr = $checkbox.closest('tr');
            const name = $tr.data('name');

            if ($checkbox.is(':checked')) {
                this.selectedItems.set(id, name);
                $tr.addClass('row-selected');
            } else {
                this.selectedItems.delete(id);
                $tr.removeClass('row-selected');
            }

            this.updateCheckAllState();
            this.updateSelectionBar();
        });

        // Seleccionar todos
        $('#checkAll').on('change', (e) => {
            const checked = $(e.currentTarget).is(':checked');

            this.$table.find('tbody .check-row').each((i, checkbox) => {
                const $checkbox = $(checkbox);
                const id = $checkbox.val();
                const $tr = $checkbox.closest('tr');
                const name = $tr.data('name');

                $checkbox.prop('checked', checked);

                if (checked) {
                    this.selectedItems.set(id, name);
                    $tr.addClass('row-selected');
                } else {
                    this.selectedItems.delete(id);
                    $tr.removeClass('row-selected');
                }
            });

            this.updateSelectionBar();
        });

        // Deseleccionar todo
        $('#clearSelection').on('click', () => {
            this.clearSelection();
        });

        // Eliminar seleccionados
        $('#deleteSelected').on('click', () => {
            this.handleMultipleDelete();
        });
    }

    updateCheckAllState() {
        const all = this.$table.find('tbody .check-row').length;
        const checked = this.$table.find('tbody .check-row:checked').length;
        const checkAll = $('#checkAll')[0];

        if (!checkAll) return;

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
    }

    updateSelectionBar() {
        const count = this.selectedItems.size;

        $('#selectionBar').toggleClass('active', count > 0);
        $('#selectionCount').text(`${count} seleccionado${count !== 1 ? 's' : ''}`);

        // Actualizar badges
        ['excel', 'csv', 'pdf', 'delete'].forEach(type => {
            $(`#${type}Badge`).text(count);
        });
    }

    clearSelection() {
        this.selectedItems.clear();
        this.$table.find('tbody .check-row').prop('checked', false);
        this.$table.find('tbody tr').removeClass('row-selected');
        $('#checkAll').prop('checked', false).prop('indeterminate', false);
        this.updateSelectionBar();
    }

    // 📤 EXPORTACIÓN

    initExport() {
        const moduleName = this.config.moduleName;

        // Menú desplegable de exportación
        const exportMenuBtn = $('#exportMenuBtn');
        const exportDropdown = $('#exportDropdown');

        if (exportMenuBtn.length) {
            exportMenuBtn.on('click', (e) => {
                e.stopPropagation();
                exportDropdown.toggleClass('active');
            });

            $(document).on('click', (e) => {
                if (!$(e.target).closest('.export-menu-container').length) {
                    exportDropdown.removeClass('active');
                }
            });
        }

        // Exportar todo
        $('#exportAllExcel').on('click', () => this.exportAll('excel'));
        $('#exportAllCsv').on('click', () => this.exportAll('csv'));
        $('#exportAllPdf').on('click', () => this.exportAll('pdf'));

        // Exportar seleccionados
        $('#exportSelectedExcel').on('click', () => this.exportSelected('excel'));
        $('#exportSelectedCsv').on('click', () => this.exportSelected('csv'));
        $('#exportSelectedPdf').on('click', () => this.exportSelected('pdf'));
    }

    exportAll(format) {
        const route = this.config.exportRoutes[format] || `/admin/${this.config.moduleName}/export/${format}`;

        const form = $('<form>', {
            method: 'POST',
            action: route
        });

        form.append($('<input>', {
            type: 'hidden',
            name: '_token',
            value: this.config.csrfToken
        }));

        form.append($('<input>', {
            type: 'hidden',
            name: 'export_all',
            value: '1'
        }));

        $('body').append(form);
        form.submit();

        $('#exportDropdown').removeClass('active');

        showToast({
            type: 'info',
            title: `Exportando a ${format.toUpperCase()}`,
            message: 'Preparando archivo con todos los registros...',
            duration: 3000
        });

        this.config.callbacks.onExport?.('all', format);
    }

    exportSelected(format) {
        const selected = Array.from(this.selectedItems.keys());
        const count = selected.length;

        if (count === 0) {
            showToast({
                type: 'warning',
                title: 'Sin selección',
                message: 'No hay registros seleccionados para exportar.',
                duration: 3000
            });
            return;
        }

        const route = this.config.exportRoutes[format] || `/admin/${this.config.moduleName}/export/${format}`;

        const form = $('<form>', {
            method: 'POST',
            action: route
        });

        form.append($('<input>', {
            type: 'hidden',
            name: '_token',
            value: this.config.csrfToken
        }));

        selected.forEach(id => {
            form.append($('<input>', {
                type: 'hidden',
                name: 'ids[]',
                value: id
            }));
        });

        $('body').append(form);
        form.submit();

        showToast({
            type: 'success',
            title: `Exportando a ${format.toUpperCase()}`,
            message: `Preparando archivo con ${count} ${count === 1 ? 'registro seleccionado' : 'registros seleccionados'}...`,
            duration: 3000
        });

        this.config.callbacks.onExport?.('selected', format, count);
    }

    // 🔍 FILTROS

    initFilters() {
        const idColumn = this.config.columns.id;
        const nameColumn = this.config.columns.name;
        const dateColumn = this.config.columns.date;

        /* -------------------------------------------
        1. Filtro de ORDENAMIENTO (sortFilter)
        ------------------------------------------- */
        const $sortFilter = $('#sortFilter');
        if ($sortFilter.length) {
            $sortFilter.on('change', (e) => {
                const sortValue = $(e.currentTarget).val();
                const wrapper = $(e.currentTarget).closest('.tabla-select-wrapper');

                if (sortValue === "") {
                    wrapper.removeClass('filter-active');
                    this.table.order([[idColumn, 'desc']]).draw();
                } else {
                    wrapper.addClass('filter-active');

                    switch(sortValue) {
                        case 'name-asc':
                            this.table.order([[nameColumn, 'asc']]).draw();
                            break;
                        case 'name-desc':
                            this.table.order([[nameColumn, 'desc']]).draw();
                            break;
                        case 'date-desc':
                            this.table.order([[dateColumn, 'desc']]).draw();
                            break;
                        case 'date-asc':
                            this.table.order([[dateColumn, 'asc']]).draw();
                            break;
                    }
                }
            });
        }

        /* -------------------------------------------
        2. Filtro por ESTADO (statusFilter)
        ------------------------------------------- */
        const $statusFilter = $('#statusFilter');

        if ($statusFilter.length) {

            // Extensión de búsqueda para DataTables (solo si existe el filtro)
            $.fn.dataTable.ext.search.push((settings, data, dataIndex) => {
                if (settings.nTable.id !== this.tableSelector.replace('#', '')) return true;

                const selectedStatus = $statusFilter.val();
                if (selectedStatus === "") return true;

                const statusCell = $(this.table.row(dataIndex).node())
                    .find('.switch-status')
                    .is(':checked')
                    ? "1"
                    : "0";

                return statusCell === selectedStatus;
            });

            $statusFilter.on('change', (e) => {
                const wrapper = $(e.currentTarget).closest('.tabla-select-wrapper');
                wrapper.toggleClass('filter-active', $(e.currentTarget).val() !== "");
                this.table.draw();
            });
        }

        /* -------------------------------------------
        3. Activar wrapper si un filtro YA tiene valor
        (genérico: detecta TODOS los selects dentro de .tabla-filtros)
        ------------------------------------------- */
        $('.tabla-filtros .selector select').each(function() {
            const $select = $(this);
            if ($select.attr('id') === 'entriesSelect') return; // no aplicar estilo al selector de paginación
            if ($select.val() !== '') {
                $select.closest('.tabla-select-wrapper').addClass('filter-active');
            } else {
                $select.closest('.tabla-select-wrapper').removeClass('filter-active');
            }
        });

        /* -------------------------------------------
        4. Botón para limpiar filtros (opcional)
        ------------------------------------------- */
        if ($('#clearFiltersBtn').length) {
            $('#clearFiltersBtn').on('click', () => this.clearFilters());
        }

        /* -------------------------------------------
        5. Verificación genérica de filtros activos
        (escucha cambios en búsqueda y TODOS los selects de .tabla-filtros)
        ------------------------------------------- */
        const $genericSelects = $('.tabla-filtros .selector select').filter(function(){
            return $(this).attr('id') !== 'entriesSelect';
        });
        const filterSelectors = ['#customSearch'];
        $genericSelects.each(function() {
            filterSelectors.push('#' + $(this).attr('id'));
        });

        if (filterSelectors.length > 0) {
            $(filterSelectors.join(', ')).on('input change keyup', () => {
                this.checkFiltersActive();
            });
        }

        this.checkFiltersActive();
    }

    checkFiltersActive() {
        const hasSearch = $('#customSearch').val()?.trim() !== '';

        // Detectar TODOS los selects dentro de .tabla-filtros (sin depender del sufijo "Filter")
        let anyFilterActive = hasSearch;

        $('.tabla-filtros .selector select').each(function () {
            const $select = $(this);
            if ($select.attr('id') === 'entriesSelect') return; // ignorar selector de paginación
            const hasValue = ($select.val() ?? '') !== '';
            $select.closest('.tabla-select-wrapper').toggleClass('filter-active', hasValue);
            if (hasValue) anyFilterActive = true;
        });

        $('#clearFiltersBtn').toggleClass('active', anyFilterActive);
    }

    clearFilters() {
        // Contar filtros activos dinámicamente
        const hasSearch = $('#customSearch').val()?.trim() !== '';
        let activeFiltersCount = hasSearch ? 1 : 0;

        $('.tabla-filtros .selector select').each(function () {
            if ($(this).attr('id') === 'entriesSelect') return; // no contar paginación como filtro
            if (($(this).val() ?? '') !== '') {
                activeFiltersCount++;
            }
        });

        if (activeFiltersCount === 0) return;

        // Limpiar búsqueda
        $('#customSearch').val('').trigger('keyup');

        // Limpiar TODOS los selects dentro de .tabla-filtros
        $('.tabla-filtros .selector select').each(function () {
            if ($(this).attr('id') === 'entriesSelect') return; // no limpiar paginación
            $(this).val('');
            $(this).trigger('change');
        });

        $('#clearFiltersBtn').addClass('clearing');
        setTimeout(() => {
            $('#clearFiltersBtn').removeClass('clearing');
        }, 300);

        this.checkFiltersActive();

        showToast({
            type: 'info',
            title: 'Filtros limpiados',
            message: `Se ${activeFiltersCount === 1 ? 'limpió 1 filtro' : `limpiaron ${activeFiltersCount} filtros`} correctamente.`,
            duration: 3000
        });
    }

    // ⚙️ TOGGLE DE ESTADO

    initStatusToggle() {
        this.$table.on('change', '.switch-status', (e) => {
            const $switch = $(e.currentTarget);
            const id = $switch.data('id');
            const routeKey = $switch.data('key') ?? id;
            const placeholder = this.config.statusRoute.includes('{key}') ? '{key}' : '{id}';
            const isChecked = $switch.is(':checked');
            const scrollPosition = window.pageYOffset || document.documentElement.scrollTop;

            if (routeKey === undefined || routeKey === null) {
                console.error('❌ switch-status sin data-key ni data-id definido.');
                $switch.prop('checked', !isChecked);
                return;
            }

            if (!this.config.statusRoute || !this.config.statusRoute.includes(placeholder)) {
                console.error('❌ statusRoute inválida o sin placeholder esperado:', this.config.statusRoute);
                $switch.prop('checked', !isChecked);
                return;
            }

            const url = this.config.statusRoute.replace(placeholder, encodeURIComponent(routeKey));

            $.ajax({
                url: url,
                type: 'PATCH',
                data: {
                    _token: this.config.csrfToken,
                    status: isChecked ? 1 : 0
                },
                success: (response) => {
                    if (response.success) {
                        // Deshabilitar animación temporalmente
                        this.$table.addClass('no-animate');

                        this.$table.find('tbody tr').each((i, row) => {
                            row.style.animation = 'none';
                            row.style.animationDelay = '0s';
                        });

                        setTimeout(() => {
                            this.table.draw(false);
                            setTimeout(() => window.scrollTo(0, scrollPosition), 50);
                        }, 10);

                        setTimeout(() => this.$table.removeClass('no-animate'), 500);

                        showToast({
                            type: 'success',
                            title: 'Estado actualizado',
                            message: response.message || 'El estado se actualizó correctamente.',
                            duration: 3000
                        });

                        this.config.callbacks.onStatusChange?.(id, isChecked, response);
                    }
                },
                error: () => {
                    $switch.prop('checked', !isChecked);

                    showToast({
                        type: 'danger',
                        title: 'Error',
                        message: 'No se pudo actualizar el estado.',
                        duration: 4000
                    });
                }
            });
        });
    }

    // 🗑️ ELIMINACIÓN

    handleMultipleDelete() {
        if (typeof window.handleMultipleDelete !== 'function') {
            console.error('❌ Función handleMultipleDelete no encontrada');
            return;
        }

        const getNameById = (id) => {
            return this.selectedItems.get(id) || `ID: ${id}`;
        };

        window.handleMultipleDelete({
            selectedIds: new Set(this.selectedItems.keys()),
            getNameCallback: getNameById,
            entityName: this.config.entityNameSingular,
            deleteRoute: this.config.deleteRoute,
            csrfToken: this.config.csrfToken,
            buttonSelector: '#deleteSelected',
            onSuccess: () => {
                this.clearSelection();
                this.config.callbacks.onDelete?.();
            }
        });
    }

    // 🎨 PAGINACIÓN Y VISUALIZACIÓN

    updateInfoAndPagination() {
        const info = this.table.page.info();
        const pagination = $('#tablePagination');

        if (!pagination.length) return;

        pagination.html('');

        const totalPages = info.pages;
        const currentPage = info.page;
        const windowSize = 1;

        // Siempre mostrar la información de registros
        $('#tableInfo').html(
            `Mostrando <strong>${info.start + 1}</strong> a <strong>${info.end}</strong> de <strong>${info.recordsDisplay}</strong> registros`
        );

        // Si solo hay 1 página o menos, no mostrar botones de paginación
        if (totalPages <= 1) {
            pagination.hide();
            return;
        }

        pagination.show();

        const addPageButton = (page) => {
            const btn = $('<button>', {
                text: page + 1,
                class: 'pagina-btn' + (page === currentPage ? ' activo' : ''),
                click: () => this.table.page(page).draw('page')
            });
            pagination.append(btn);
        };

        // Botón "Primero"
        const firstBtn = $('<button>', {
            html: '<i class="ri-skip-left-line"></i> <span class="btn-text">Primero</span>',
            class: 'pagina-btn',
            disabled: currentPage === 0,
            click: () => this.table.page(0).draw('page')
        });
        pagination.append(firstBtn);

        // Botón "Anterior"
        const prevBtn = $('<button>', {
            html: '<i class="ri-arrow-left-s-line"></i> <span class="btn-text">Anterior</span>',
            class: 'pagina-btn',
            disabled: currentPage === 0,
            click: () => this.table.page('previous').draw('page')
        });
        pagination.append(prevBtn);

        // Páginas numéricas
        if (currentPage > windowSize) addPageButton(0);
        if (currentPage - windowSize > 1) {
            pagination.append($('<span>', { text: '...', class: 'puntos' }));
        }

        const start = Math.max(0, currentPage - windowSize);
        const end = Math.min(totalPages - 1, currentPage + windowSize);
        for (let i = start; i <= end; i++) addPageButton(i);

        if (currentPage + windowSize < totalPages - 2) {
            pagination.append($('<span>', { text: '...', class: 'puntos' }));
        }
        if (currentPage < totalPages - windowSize - 1) addPageButton(totalPages - 1);

        // Botón "Siguiente"
        const nextBtn = $('<button>', {
            html: '<span class="btn-text">Siguiente</span> <i class="ri-arrow-right-s-line"></i>',
            class: 'pagina-btn',
            disabled: currentPage === totalPages - 1,
            click: () => this.table.page('next').draw('page')
        });
        pagination.append(nextBtn);

        // Botón "Último"
        const lastBtn = $('<button>', {
            html: '<span class="btn-text">Último</span> <i class="ri-skip-right-line"></i>',
            class: 'pagina-btn',
            disabled: currentPage === totalPages - 1,
            click: () => this.table.page(totalPages - 1).draw('page')
        });
        pagination.append(lastBtn);
    }

    animateRows() {
        if (this.$table.hasClass('no-animate')) return;

        this.$table.find('tbody tr').each((i, row) => {
            row.style.animation = 'slideInLeft 0.3s ease-in-out';
            row.style.animationDelay = `${i * 0.02}s`;
        });
    }

    updateOrderIcons() {
        this.$table.find('thead th').each((i, th) => {
            const $th = $(th);

            if ($th.hasClass('column-check-th') ||
                $th.hasClass('column-actions-th') ||
                $th.hasClass('control')) return;

            const orderSpan = $th.find('.dt-column-order');
            if (!orderSpan.length) return;

            orderSpan.html('');

            if ($th.hasClass('dt-ordering-asc')) {
                orderSpan.html('<i class="ri-sort-alphabet-asc orden-icon"></i>');
            } else if ($th.hasClass('dt-ordering-desc')) {
                orderSpan.html('<i class="ri-sort-alphabet-desc orden-icon"></i>');
            } else {
                orderSpan.html('<i class="ri-arrow-up-down-line orden-icon-none"></i>');
            }
        });
    }

    // 🎨 RESALTADO DE FILAS

    initHighlight() {
        // Se ejecutará desde el blade con data-highlight-row
        const highlightId = this.$table.data('highlight-row');

        if (highlightId) {
            setTimeout(() => {
                const $row = this.$table.find(`tbody tr[data-id="${highlightId}"]`);
                if ($row.length) {
                    $row.addClass('row-highlight');

                    $row[0].scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });

                    setTimeout(() => {
                        $row.removeClass('row-highlight');
                    }, 3000);
                }
            }, 100);
        }
    }

    // 🔄 EVENTOS DE REDIBUJADO

    onTableDraw() {
        // Reinsertar íconos control
        this.initResponsiveControls();

        // Restaurar checkboxes seleccionados
        this.$table.find('tbody .check-row').each((i, checkbox) => {
            const $checkbox = $(checkbox);
            const id = $checkbox.val();
            const $tr = $checkbox.closest('tr');

            if (this.selectedItems.has(id)) {
                $checkbox.prop('checked', true);
                $tr.addClass('row-selected');
            } else {
                $checkbox.prop('checked', false);
                $tr.removeClass('row-selected');
            }
        });

        this.updateCheckAllState();
        this.updateSelectionBar();

        if (this.config.features.customPagination) {
            this.updateInfoAndPagination();
        }

        this.animateRows();
        this.updateOrderIcons();

        // Callback personalizado
        this.config.callbacks.onDraw?.();
    }

    // 🛠️ API PÚBLICA

    /**
     * Obtiene la instancia de DataTable
     */
    getTable() {
        return this.table;
    }

    /**
     * Obtiene los items seleccionados
     */
    getSelectedItems() {
        return new Map(this.selectedItems);
    }

    /**
     * Refresca la tabla
     */
    refresh() {
        this.table.draw();
    }

    /**
     * Destruye la instancia
     */
    destroy() {
        if (this.table) {
            this.table.destroy();
            this.table = null;
        }
        this.selectedItems.clear();
    }
}

// ========================================
// 🌐 EXPORTAR GLOBALMENTE
// ========================================
window.DataTableManager = DataTableManager;
