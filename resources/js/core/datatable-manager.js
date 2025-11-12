/**
 * ========================================
 * DataTable Manager
 * ========================================
 * Sistema modular para gestión de tablas DataTable
 * Diseñado para ser reutilizable en cualquier módulo
 */

class DataTableManager {
    constructor(options = {}) {
        // Configuración base
        this.tableSelector = options.tableSelector || '#tabla';
        this.moduleName = options.moduleName || this.getModuleFromUrl();
        this.idColumnClass = options.idColumnClass || '.column-id-th';
        
        // Referencias DOM
        this.$table = $(this.tableSelector);
        this.table = null;
        
        // Control de estado
        this.selectedIds = new Set();
        this.scrollPosition = 0;
        
        // Configuración personalizada
        this.config = {
            pageLength: options.pageLength || 10,
            lengthMenu: options.lengthMenu || [5, 10, 25, 50],
            orderColumn: options.orderColumn || 'desc',
            language: this.getLanguageConfig(),
            ...options.dataTableConfig
        };

        // Callbacks personalizados
        this.callbacks = {
            onStatusChange: options.onStatusChange || null,
            onDelete: options.onDelete || null,
            onExport: options.onExport || null,
        };
        
        this.init();
    }

    /**
     * Inicializar DataTable y todos sus componentes
     */
    init() {
        this.initDataTable();
        this.initEventListeners();
        this.initCustomControls();
        this.updateInfoAndPagination();
        this.animarFilas();
        this.actualizarIconosOrden();
        this.updateDeleteButton();
    }

    /**
     * Obtener nombre del módulo desde la URL
     */
    getModuleFromUrl() {
        const currentPath = window.location.pathname;
        return currentPath.split('/').filter(Boolean)[1];
    }

    /**
     * Configuración de idioma para DataTable
     */
    getLanguageConfig() {
        return {
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
    }

    /**
     * Inicializar DataTable
     */
    initDataTable() {
        const indiceColumnaId = this.$table.find(`thead th${this.idColumnClass}`).index();
        
        this.table = new DataTable(this.tableSelector, {
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
            pageLength: this.config.pageLength,
            lengthMenu: this.config.lengthMenu,
            order: [[indiceColumnaId, this.config.orderColumn]],
            language: this.config.language,
            scrollCollapse: false,
            scroller: false,
            initComplete: () => {
                this.$table.addClass('ready');
                this.actualizarIconosOrden();
            },
        });

        // Event listener para redibujado
        this.table.on('draw', () => this.onTableDraw());
    }

    /**
     * Callback cuando la tabla se redibuja
     */
    onTableDraw() {
        this.reinitControlIcons();
        this.restoreSelectedCheckboxes();
        this.updateCheckAllState();
        this.updateDeleteButton();
        this.updateInfoAndPagination();
        this.animarFilas();
        this.actualizarIconosOrden();
    }

    /**
     * Inicializar listeners de eventos
     */
    initEventListeners() {
        this.initStatusChangeListener();
        this.initCheckboxListeners();
        this.initCellClickListener();
        this.initOutsideClickListener();
        this.initTableEventListeners();
    }

    /**
     * Listener para cambio de estado (switch)
     */
    initStatusChangeListener() {
        this.$table.on('change', '.switch-status', (e) => {
            const $switch = $(e.currentTarget);
            const id = $switch.data('id');
            const isChecked = $switch.is(':checked');
            const url = `/admin/${this.moduleName}/${id}/status`;

            this.scrollPosition = window.pageYOffset || document.documentElement.scrollTop;

            $.ajax({
                url: url,
                type: 'PATCH',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    status: isChecked ? 1 : 0
                },
                success: (response) => {
                    if (response.success) {
                        this.disableAnimations();
                        
                        setTimeout(() => {
                            this.table.draw(false);
                            setTimeout(() => {
                                window.scrollTo(0, this.scrollPosition);
                            }, 50);
                        }, 10);

                        setTimeout(() => {
                            this.enableAnimations();
                        }, 500);

                        if (this.callbacks.onStatusChange) {
                            this.callbacks.onStatusChange(response);
                        }
                    }
                },
                error: () => {
                    this.showToast('error', 'Error al actualizar el estado.');
                }
            });
        });
    }

    /**
     * Listeners para checkboxes
     */
    initCheckboxListeners() {
        // Click en celda para alternar checkbox
        this.$table.on('click', 'td.column-check-td', (e) => {
            if (e.target.tagName === 'INPUT') return;
            const $checkbox = $(e.currentTarget).find('input[type="checkbox"]');
            $checkbox.prop('checked', !$checkbox.prop('checked')).trigger('change');
        });

        // Cambio en checkbox individual
        this.$table.on('change', '.check-row', (e) => {
            const $checkbox = $(e.currentTarget);
            const id = $checkbox.val();
            const $tr = $checkbox.closest('tr');

            if ($checkbox.is(':checked')) {
                this.selectedIds.add(id);
                $tr.addClass('row-selected');
            } else {
                this.selectedIds.delete(id);
                $tr.removeClass('row-selected');
            }

            this.updateCheckAllState();
            this.updateDeleteButton();
        });

        // Checkbox "Seleccionar todo"
        $('#checkAll').on('change', (e) => {
            const checked = $(e.currentTarget).is(':checked');

            this.$table.find('tbody .check-row').each((i, el) => {
                const $checkbox = $(el);
                const id = $checkbox.val();
                $checkbox.prop('checked', checked);

                if (checked) {
                    this.selectedIds.add(id);
                    $checkbox.closest('tr').addClass('row-selected');
                } else {
                    this.selectedIds.delete(id);
                    $checkbox.closest('tr').removeClass('row-selected');
                }
            });

            this.updateDeleteButton();
        });
    }

    /**
     * Listener para click en celda (resaltado)
     */
    initCellClickListener() {
        this.$table.on('click', 'td', (e) => {
            if ($(e.target).is('input, button, a, i')) return;
            
            $(`${this.tableSelector} td.active-cell`).removeClass('active-cell');
            $(e.currentTarget).addClass('active-cell');
        });
    }

    /**
     * Listener para clicks fuera de la tabla
     */
    initOutsideClickListener() {
        $(document).on('click', (e) => {
            if (!$(e.target).closest(this.tableSelector).length) {
                $(`${this.tableSelector} td.active-cell`).removeClass('active-cell');
            }
        });
    }

    /**
     * Listeners para eventos de DataTable
     */
    initTableEventListeners() {
        this.$table.on('order.dt page.dt search.dt', () => {
            $(`${this.tableSelector} td.active-cell`).removeClass('active-cell');
        });
    }

    /**
     * Inicializar controles personalizados
     */
    initCustomControls() {
        this.initSearchControl();
        this.initEntriesControl();
        this.initStatusFilter();
        this.initResponsiveControls();
    }

    /**
     * Control de búsqueda personalizado
     */
    initSearchControl() {
        const searchInput = document.getElementById('customSearch');
        const clearButton = document.getElementById('clearSearch');
        const buscadorContainer = document.querySelector('.tabla-buscador');

        if (!searchInput) return;

        const toggleClearButton = () => {
            if (searchInput.value.length > 0) {
                buscadorContainer.classList.add('has-text');
            } else {
                buscadorContainer.classList.remove('has-text');
            }
        };

        searchInput.addEventListener('keyup', () => {
            this.table.search(searchInput.value).draw();
            toggleClearButton();
        });

        clearButton.addEventListener('click', () => {
            searchInput.value = '';
            this.table.search('').draw();
            toggleClearButton();
            searchInput.focus();
        });

        toggleClearButton();
    }

    /**
     * Control de cantidad de entradas
     */
    initEntriesControl() {
        const entriesSelect = document.getElementById('entriesSelect');
        if (entriesSelect) {
            entriesSelect.addEventListener('change', (e) => {
                this.table.page.len(e.target.value).draw();
            });
        }
    }

    /**
     * Filtro por estado
     */
    initStatusFilter() {
        $.fn.dataTable.ext.search.push((settings, data, dataIndex) => {
            const selectedStatus = $('#statusFilter').val();
            const statusCell = $(this.table.row(dataIndex).node())
                .find('.switch-status').is(':checked') ? "1" : "0";

            if (selectedStatus === "") return true;
            return statusCell === selectedStatus;
        });

        $('#statusFilter').on('change', () => {
            this.table.draw();
        });
    }

    /**
     * Controles responsive (iconos de expandir)
     */
    initResponsiveControls() {
        // Inicializar íconos
        document.querySelectorAll(`${this.tableSelector} td.control`).forEach(cell => {
            cell.innerHTML = '<i class="ri-arrow-right-s-line control-icon"></i>';
        });

        // Toggle al hacer click
        this.$table.on('click', 'td.control', function() {
            $(this).find('.control-icon')
                .toggleClass('ri-arrow-right-s-line ri-arrow-down-s-line');
        });
    }

    /**
     * Reinicializar íconos de control después de redraw
     */
    reinitControlIcons() {
        document.querySelectorAll(`${this.tableSelector} td.control`).forEach(cell => {
            if (!cell.querySelector('.control-icon')) {
                cell.innerHTML = '<i class="ri-arrow-right-s-line control-icon"></i>';
            }
        });
    }

    /**
     * Restaurar checkboxes seleccionados después de redraw
     */
    restoreSelectedCheckboxes() {
        this.$table.find('tbody .check-row').each((i, el) => {
            const $checkbox = $(el);
            const id = $checkbox.val();
            const $tr = $checkbox.closest('tr');
            
            if (this.selectedIds.has(id)) {
                $checkbox.prop('checked', true);
                $tr.addClass('row-selected');
            } else {
                $checkbox.prop('checked', false);
                $tr.removeClass('row-selected');
            }
        });
    }

    /**
     * Actualizar estado del checkbox "Seleccionar todo"
     */
    updateCheckAllState() {
        const all = this.$table.find('tbody .check-row').length;
        const checked = this.$table.find('tbody .check-row:checked').length;
        const $checkAll = $('#checkAll');

        if (checked === 0) {
            $checkAll.prop('checked', false).prop('indeterminate', false);
        } else if (checked === all) {
            $checkAll.prop('checked', true).prop('indeterminate', false);
        } else {
            $checkAll.prop('checked', false).prop('indeterminate', true);
        }
    }

    /**
     * Actualizar botón de eliminar seleccionados
     */
    updateDeleteButton() {
        const deleteBtn = document.getElementById('deleteSelected');
        if (!deleteBtn) return;

        const selectedCount = this.selectedIds.size;

        if (selectedCount > 0) {
            deleteBtn.disabled = false;
            deleteBtn.querySelector('.boton-text').textContent = 
                `Eliminar Seleccionados (${selectedCount})`;
        } else {
            deleteBtn.disabled = true;
            deleteBtn.querySelector('.boton-text').textContent = 'Eliminar Seleccionados';
        }
    }

    /**
     * Actualizar información y paginación
     */
    updateInfoAndPagination() {
        const info = this.table.page.info();
        const pagination = document.getElementById('tablePagination');
        
        if (!pagination) return;

        pagination.innerHTML = '';

        const totalPages = info.pages;
        const currentPage = info.page;
        const windowSize = 1;

        // Función auxiliar para crear botón de página
        const addPageButton = (page) => {
            const btn = document.createElement('button');
            btn.textContent = page + 1;
            btn.className = 'pagina-btn' + (page === currentPage ? ' activo' : '');
            btn.addEventListener('click', () => this.table.page(page).draw('page'));
            pagination.appendChild(btn);
        };

        // Botón "Primero"
        const firstBtn = document.createElement('button');
        firstBtn.innerHTML = '<i class="ri-skip-left-line"></i> <span class="btn-text">Primero</span>';
        firstBtn.className = 'pagina-btn';
        firstBtn.disabled = currentPage === 0;
        firstBtn.addEventListener('click', () => this.table.page(0).draw('page'));
        pagination.appendChild(firstBtn);

        // Botón "Anterior"
        const prevBtn = document.createElement('button');
        prevBtn.innerHTML = '<i class="ri-arrow-left-s-line"></i> <span class="btn-text">Anterior</span>';
        prevBtn.className = 'pagina-btn';
        prevBtn.disabled = currentPage === 0;
        prevBtn.addEventListener('click', () => this.table.page('previous').draw('page'));
        pagination.appendChild(prevBtn);

        // Primera página
        if (currentPage > windowSize) addPageButton(0);

        // Puntos suspensivos antes
        if (currentPage - windowSize > 1) {
            const dots = document.createElement('span');
            dots.textContent = '...';
            dots.className = 'puntos';
            pagination.appendChild(dots);
        }

        // Rango centrado
        const start = Math.max(0, currentPage - windowSize);
        const end = Math.min(totalPages - 1, currentPage + windowSize);
        for (let i = start; i <= end; i++) addPageButton(i);

        // Puntos suspensivos después
        if (currentPage + windowSize < totalPages - 2) {
            const dots = document.createElement('span');
            dots.textContent = '...';
            dots.className = 'puntos';
            pagination.appendChild(dots);
        }

        // Última página
        if (currentPage < totalPages - windowSize - 1) addPageButton(totalPages - 1);

        // Botón "Siguiente"
        const nextBtn = document.createElement('button');
        nextBtn.innerHTML = '<span class="btn-text">Siguiente</span> <i class="ri-arrow-right-s-line"></i>';
        nextBtn.className = 'pagina-btn';
        nextBtn.disabled = currentPage === totalPages - 1;
        nextBtn.addEventListener('click', () => this.table.page('next').draw('page'));
        pagination.appendChild(nextBtn);

        // Botón "Último"
        const lastBtn = document.createElement('button');
        lastBtn.innerHTML = '<span class="btn-text">Último</span> <i class="ri-skip-right-line"></i>';
        lastBtn.className = 'pagina-btn';
        lastBtn.disabled = currentPage === totalPages - 1;
        lastBtn.addEventListener('click', () => this.table.page(totalPages - 1).draw('page'));
        pagination.appendChild(lastBtn);

        // Información
        const tableInfo = document.getElementById('tableInfo');
        if (tableInfo) {
            tableInfo.innerHTML = 
                `Mostrando <strong>${info.start + 1}</strong> a <strong>${info.end}</strong> de <strong>${info.recordsDisplay}</strong> registros`;
        }
    }

    /**
     * Animar filas
     */
    animarFilas() {
        const tabla = document.querySelector(this.tableSelector);
        
        if (tabla.classList.contains('no-animate')) {
            return;
        }

        const filas = tabla.querySelectorAll('tbody tr');
        filas.forEach((fila, i) => {
            fila.style.animation = 'slideInLeft 0.3s ease-in-out';
            fila.style.animationDelay = `${i * 0.02}s`;
        });
    }

    /**
     * Actualizar íconos de ordenamiento
     */
    actualizarIconosOrden() {
        document.querySelectorAll(`${this.tableSelector} thead th`).forEach(th => {
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

    /**
     * Deshabilitar animaciones temporalmente
     */
    disableAnimations() {
        const tabla = document.querySelector(this.tableSelector);
        tabla.classList.add('no-animate');
        
        document.querySelectorAll(`${this.tableSelector} tbody tr`).forEach(fila => {
            fila.style.animation = 'none';
            fila.style.animationDelay = '0s';
        });
    }

    /**
     * Habilitar animaciones
     */
    enableAnimations() {
        const tabla = document.querySelector(this.tableSelector);
        tabla.classList.remove('no-animate');
    }

    /**
     * Mostrar toast de notificación
     */
    showToast(type, message) {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2500);
    }

    /**
     * Obtener IDs seleccionados
     */
    getSelectedIds() {
        return Array.from(this.selectedIds);
    }

    /**
     * Limpiar selección
     */
    clearSelection() {
        this.selectedIds.clear();
        this.updateDeleteButton();
        this.updateCheckAllState();
        this.$table.find('tbody .check-row').prop('checked', false);
        this.$table.find('tbody tr').removeClass('row-selected');
    }

    /**
     * Destruir instancia
     */
    destroy() {
        if (this.table) {
            this.table.destroy();
        }
        this.selectedIds.clear();
    }
}

// Exportar para uso global
window.DataTableManager = DataTableManager;
