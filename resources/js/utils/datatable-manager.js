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
    // Resalta coincidencias en celdas de texto sin perder HTML original
    highlightMatches(search) {
        if (!search || search.length < 2) {
            // Limpiar resaltado
            this.$table.find('tbody tr').each((i, tr) => {
                $(tr).find('td').each((j, td) => {
                    this.restoreOriginalCell(td);
                });
            });
            return;
        }
        const regex = new RegExp(`(${search.replace(/[.*+?^${}()|[\\]\\]/g, '\\$&')})`, 'gi');
        this.$table.find('tbody tr:visible').each((i, tr) => {
            $(tr).find('td').each((j, td) => {
                this.restoreOriginalCell(td);
                this.highlightTextNodes(td, regex);
            });
        });
    }

    // Guarda el HTML original de la celda si no está guardado
    storeOriginalCell(td) {
        if (!td.hasAttribute('data-original-html')) {
            td.setAttribute('data-original-html', td.innerHTML);
        }
    }

    // Restaura el HTML original de la celda
    restoreOriginalCell(td) {
        if (td.hasAttribute('data-original-html')) {
            td.innerHTML = td.getAttribute('data-original-html');
        }
    }

    // Resalta solo los nodos de texto dentro de la celda
    highlightTextNodes(td, regex) {
        this.storeOriginalCell(td);
        const walk = (node) => {
            if (node.nodeType === 3) { // Text node
                const val = node.nodeValue;
                if (val && regex.test(val)) {
                    const span = document.createElement('span');
                    span.innerHTML = val.replace(regex, '<span class="datatable-highlight">$1</span>');
                    node.replaceWith(...span.childNodes);
                }
            } else if (node.nodeType === 1 && node.childNodes && node.childNodes.length) {
                Array.from(node.childNodes).forEach(walk);
            }
        };
        walk(td);
    }
    constructor(tableSelector, options = {}) {
        this.tableSelector = tableSelector;
        this.$table = $(tableSelector);

        const serverSideOptions = typeof options.serverSide === 'object' && options.serverSide !== null
            ? options.serverSide
            : { enabled: options.serverSide === true };

        // Configuración por defecto
        this.config = {
            moduleName: options.moduleName || this.extractModuleName(),
            entityNameSingular: options.entityNameSingular || 'registro',
            entityNamePlural: options.entityNamePlural || 'registros',
            deleteRoute: options.deleteRoute,
            statusRoute: options.statusRoute,
            exportRoutes: options.exportRoutes || {},
            csrfToken: options.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content,

            // Modo server-side (opcional)
            serverSide: {
                enabled: serverSideOptions.enabled === true,
                processing: serverSideOptions.processing ?? (options.processing ?? serverSideOptions.enabled === true),
                ajax: serverSideOptions.ajax ?? options.ajax ?? null,
                method: (serverSideOptions.method ?? options.ajaxMethod ?? 'GET').toUpperCase(),
                // Permite agregar params extra al request (d) antes de enviarlo
                extraParams: serverSideOptions.extraParams ?? options.ajaxData ?? null,
                // Contenedor de filtros que se enviarán al backend (por defecto .tabla-filtros)
                filtersSelector: serverSideOptions.filtersSelector || '.tabla-filtros'
            },

            // Persistencia UI (buscador + filtros) en localStorage
            persistUiState: options.persistUiState !== false,
            uiStateStorageKey: options.uiStateStorageKey || null,

            // Permite extender/configurar DataTables directamente sin tocar el core
            dataTable: options.dataTable || {},

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
        this.uiState = null;

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

        // Restaurar buscador/filtros ANTES de inicializar DataTable,
        // para que el primer request AJAX ya incluya filtros.
        this.restoreUiState();

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

        // Aplicar/restaurar filtros de manera efectiva:
        // - En muchos módulos existen filtros “locales” que registran sus propios handlers
        // - Restaurar solo el value del select no ejecuta esos handlers
        // Por eso disparamos eventos en el siguiente tick.
        setTimeout(() => {
            this.applyRestoredUiStateAfterInit();
        }, 0);

        console.log(`✅ DataTableManager inicializado para módulo: ${this.config.moduleName}`);
    }

    getFiltersContainer() {
        return $(this.config.serverSide?.filtersSelector || '.tabla-filtros');
    }

    findFilterElement(key, $container = null) {
        const $ctx = $container && $container.length ? $container : this.getFiltersContainer();

        // Preferir búsqueda por id exacto dentro del contenedor (evita colisiones entre módulos)
        let $el = $ctx.find(`[id="${key}"]`);
        if ($el.length) return $el;

        // Fallback global
        $el = $(`[id="${key}"]`);
        if ($el.length) return $el;

        // Búsqueda por name
        $el = $ctx.find(`[name="${key}"]`);
        if ($el.length) return $el;

        return $(`[name="${key}"]`);
    }

    // ============================
    // 💾 Persistencia UI (buscador + filtros)
    // ============================
    getUiStateStorageKey() {
        return this.config.uiStateStorageKey || `datatable_ui_${this.config.moduleName}`;
    }

    loadUiState() {
        if (!this.config.persistUiState) return null;
        try {
            const raw = localStorage.getItem(this.getUiStateStorageKey());
            if (!raw) return null;
            const parsed = JSON.parse(raw);
            return parsed && typeof parsed === 'object' ? parsed : null;
        } catch (_) {
            return null;
        }
    }

    saveUiState(partial = null) {
        if (!this.config.persistUiState) return;
        try {
            const current = this.uiState || this.loadUiState() || {};
            const next = {
                ...current,
                ...(partial || this.collectCurrentUiState())
            };
            localStorage.setItem(this.getUiStateStorageKey(), JSON.stringify(next));
            this.uiState = next;
        } catch (_) {
            // noop
        }
    }

    collectCurrentUiState() {
        const $container = this.getFiltersContainer();

        const state = {
            search: String($('#customSearch').val() || ''),
            filters: {},
            updatedAt: Date.now(),
        };

        // Capturar TODOS los filtros dentro del contenedor configurado
        // (no asumir que el HTML use siempre .selector)
        if ($container.length) {
            $container.find('select, input, textarea').each(function () {
                const $el = $(this);
                const id = $el.attr('id');
                const name = $el.attr('name');
                const key = id || name;

                if (!key) return;
                if (key === 'entriesSelect') return;
                if (key === 'customSearch') return;

                const type = ($el.attr('type') || '').toLowerCase();
                if (type === 'checkbox') {
                    state.filters[key] = $el.is(':checked') ? '1' : '0';
                    return;
                }
                if (type === 'radio') {
                    if ($el.is(':checked')) state.filters[key] = $el.val();
                    return;
                }

                state.filters[key] = $el.val();
            });
        }

        // Guardar también entriesSelect (aunque esté fuera de .tabla-filtros)
        const entriesValue = $('#entriesSelect').val();
        if (entriesValue !== undefined) {
            state.filters.entriesSelect = String(entriesValue);
        }

        return state;
    }

    restoreUiState() {
        const saved = this.loadUiState();
        if (!saved) return;

        this.uiState = saved;

        const $container = this.getFiltersContainer();

        if (typeof saved.search === 'string') {
            $('#customSearch').val(saved.search);
        }

        const filters = saved.filters && typeof saved.filters === 'object' ? saved.filters : {};
        Object.keys(filters).forEach((key) => {
            const value = filters[key];

            if (key === 'entriesSelect') return; // se maneja abajo

            const $el = this.findFilterElement(key, $container);
            if (!$el.length) return;

            const type = ($el.attr('type') || '').toLowerCase();
            if (type === 'checkbox') {
                $el.prop('checked', String(value) === '1' || String(value).toLowerCase() === 'true');
                return;
            }
            if (type === 'radio') {
                // Para radios, marcar el que coincida
                const name = $el.attr('name') || key;
                const $radio = ($container.length ? $container : $(document)).find(`input[type="radio"][name="${name}"][value="${value}"]`);
                if ($radio.length) $radio.prop('checked', true);
                return;
            }

            if ($el.is('select') || $el.is('input') || $el.is('textarea')) {
                $el.val(value);
            }
        });

        // Si se guardó entriesSelect, sincronizar pageLength inicial
        const entries = filters.entriesSelect ?? $('#entriesSelect').val();
        if (entries && !isNaN(Number(entries))) {
            this.config.pageLength = Number(entries);
        }

        // Reflejar entriesSelect en UI si existe
        if (filters.entriesSelect !== undefined && $('#entriesSelect').length) {
            $('#entriesSelect').val(String(filters.entriesSelect));
        }
    }

    applyRestoredUiStateAfterInit() {
        if (!this.config.persistUiState) return;
        if (!this.uiState || !this.uiState.filters) return;
        if (!this.table) return;

        const filters = this.uiState.filters && typeof this.uiState.filters === 'object' ? this.uiState.filters : {};
        const keys = Object.keys(filters);
        if (keys.length === 0) return;

        const $container = this.getFiltersContainer();

        // Disparar eventos para que filtros “locales” (del index) apliquen su lógica
        keys.forEach((key) => {
            if (key === 'entriesSelect') return;
            const $el = this.findFilterElement(key, $container);
            if (!$el.length) return;

            if ($el.is('select')) {
                $el.trigger('change');
                return;
            }

            // Para inputs/textarea, muchos módulos escuchan input o change
            $el.trigger('input');
            $el.trigger('change');
        });

        // En server-side, los filtros deben reflejarse en una llamada AJAX
        if (this.isServerSideEnabled()) {
            const anyActive = keys.some((k) => k !== 'entriesSelect' && String(filters[k] ?? '') !== '');
            if (anyActive) {
                this.table.draw();
            }
        }

        // Si el módulo usa un botón explícito para “aplicar filtros”, auto-ejecutarlo
        // cuando hay filtros/búsqueda persistidos, para que la UI y los resultados coincidan.
        const hasSearch = typeof this.uiState.search === 'string' && this.uiState.search.trim() !== '';
        const hasActiveFilters = keys.some((k) => k !== 'entriesSelect' && String(filters[k] ?? '') !== '');
        const $applyBtn = $('#applyFiltersBtn');
        if ($applyBtn.length && (hasSearch || hasActiveFilters)) {
            // Evitar submit accidental si está dentro de un form
            if ($applyBtn.is('button') && !$applyBtn.attr('type')) {
                $applyBtn.attr('type', 'button');
            }
            $applyBtn.trigger('click');
        }

        // Sincronizar estado visual
        this.checkFiltersActive();
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

    isServerSideEnabled() {
        return this.config.serverSide?.enabled === true;
    }

    // Enviar filtros al backend de manera genérica (sin acoplarse a un módulo)
    collectFilterParams() {
        const container = this.getFiltersContainer();
        if (!container.length) return {};

        const params = {};

        // Selects dentro del contenedor (ignorando paginación)
        container.find('select').each((_, el) => {
            const $el = $(el);
            const id = $el.attr('id');
            const name = $el.attr('name');
            const key = id || name;
            if (!key) return;
            if (key === 'entriesSelect') return;
            if (key === 'customSearch') return;
            params[key] = $el.val();
        });

        // Inputs/checkboxes/radios (si se usan como filtros)
        container.find('input, textarea').each((_, el) => {
            const $el = $(el);
            const id = $el.attr('id');
            const name = $el.attr('name');
            const key = id || name;
            if (!key) return;
            if (key === 'customSearch') return; // DataTables maneja search[value]
            if (key === 'entriesSelect') return;
            if ($el.attr('type') === 'checkbox') {
                params[key] = $el.is(':checked') ? '1' : '0';
                return;
            }
            if ($el.attr('type') === 'radio') {
                if ($el.is(':checked')) params[key] = $el.val();
                return;
            }
            params[key] = $el.val();
        });

        return params;
    }

    buildColumnClassDefs() {
        const defs = [];
        const headers = this.$table.find('thead th');

        headers.each((index, th) => {
            const $th = $(th);
            let className = '';

            if ($th.hasClass('control')) className = 'control';
            if ($th.hasClass('column-check-th')) className = 'column-check-td';
            if ($th.hasClass('column-id-th')) className = 'column-id-td';
            if ($th.hasClass('column-name-th')) className = 'column-name-td';
            if ($th.hasClass('column-date-th')) className = 'column-date-td';
            if ($th.hasClass('column-status-th')) className = 'column-status-td';
            if ($th.hasClass('column-actions-th')) className = 'column-actions-td';

            if (className) {
                defs.push({
                    targets: index,
                    className
                });
            }
        });

        return defs;
    }

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

        // Asegurar clases en TD cuando la tabla sea poblada por Ajax
        columnDefs.push(...this.buildColumnClassDefs());

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
        } catch (e) { }

        // Si el usuario activa serverSide, lo ideal es no renderizar filas en Blade
        // (evita cargar miles de registros en HTML). Si ya existen, las limpiamos.
        if (this.isServerSideEnabled()) {
            this.$table.find('tbody').empty();
        }

        const initialSearch = (this.uiState && typeof this.uiState.search === 'string')
            ? this.uiState.search
            : String($('#customSearch').val() || '');

        const dataTableOptions = {
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
            search: {
                search: initialSearch
            },
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

                // Asegurar que el selector de entries refleje el pageLength inicial
                const entriesSelect = $('#entriesSelect');
                if (entriesSelect.length) {
                    entriesSelect.val(String(this.config.pageLength));
                }

                // Forzar actualización de paginación e info después de inicializar
                setTimeout(() => {
                    if (this.config.features.customPagination) {
                        this.updateInfoAndPagination();
                    }
                    this.animateRows();
                }, 50);
            }
        };

        // Server-side opcional
        if (this.isServerSideEnabled()) {
            dataTableOptions.processing = this.config.serverSide.processing === true;
            dataTableOptions.serverSide = true;

            const ajaxSource = this.config.serverSide.ajax;
            if (!ajaxSource) {
                console.warn('⚠️ serverSide habilitado pero no se definió ajax/URL.');
            } else {
                const ajaxConfig = typeof ajaxSource === 'string'
                    ? { url: ajaxSource, type: this.config.serverSide.method || 'GET' }
                    : { ...ajaxSource };

                const userDataFn = ajaxConfig.data;
                ajaxConfig.data = (d) => {
                    // 1) Respetar data() del usuario (si existe)
                    if (typeof userDataFn === 'function') {
                        userDataFn(d);
                    }

                    // 2) Enviar filtros genéricos
                    const currentFilters = this.collectFilterParams();
                    d.filters = { ...(d.filters || {}), ...currentFilters };

                    // 3) Params extra (hook)
                    if (typeof this.config.serverSide.extraParams === 'function') {
                        this.config.serverSide.extraParams(d, this);
                    }

                    return d;
                };

                dataTableOptions.ajax = ajaxConfig;
            }

            // Si no se definieron columnas, mapear arrays por índice automáticamente
            if (!Object.prototype.hasOwnProperty.call(this.config.dataTable || {}, 'columns')) {
                const headerCount = this.$table.find('thead th').length;
                dataTableOptions.columns = Array.from({ length: headerCount }, (_, i) => ({ data: i }));
            }

            // En server-side, asegurar data-id/data-name en cada fila para features como highlight y selection
            const userCreatedRow = (this.config.dataTable || {}).createdRow;
            dataTableOptions.createdRow = (row, data) => {
                try {
                    if (Array.isArray(data)) {
                        const idIndex = this.config.columns.id;
                        const nameIndex = this.config.columns.name;

                        if (typeof idIndex === 'number') {
                            const raw = data[idIndex];
                            const txt = String(raw ?? '').replace(/<[^>]*>/g, '').trim();
                            if (txt) $(row).attr('data-id', txt);
                        }

                        if (typeof nameIndex === 'number') {
                            const rawName = data[nameIndex];
                            const txtName = String(rawName ?? '').replace(/<[^>]*>/g, '').trim();
                            if (txtName) $(row).attr('data-name', txtName);
                        }
                    }
                } catch (_) { }

                if (typeof userCreatedRow === 'function') {
                    userCreatedRow(row, data);
                }
            };
        }

        // Permitir override/extensión de opciones de DataTables
        // Nota: se aplica primero el override y luego nuestra base,
        // excepto en server-side donde necesitamos envolver createdRow.
        const mergedOptions = {
            ...(this.config.dataTable || {}),
            ...dataTableOptions
        };

        if (this.isServerSideEnabled() && dataTableOptions.createdRow) {
            mergedOptions.createdRow = dataTableOptions.createdRow;
        }

        this.table = new DataTable(this.tableSelector, mergedOptions);

        // Guardar página en localStorage al cambiar
        this.table.on('page', () => {
            try {
                const page = this.table.page();
                localStorage.setItem(storageKey, page);
            } catch (e) { }
        });

        // Eventos de redibujado
        this.table.on('draw', () => {
            this.onTableDraw();
            const searchInput = $('#customSearch');
            const value = searchInput.length ? searchInput.val() : '';
            this.highlightMatches(value);
        });
    }

    // 🎨 CONTROLES DE INTERFAZ

    initControls() {
        // Buscador personalizado
        const searchInput = $('#customSearch');
        const searchBtn = $('#searchBtn');
        const clearButton = $('#clearSearch');
        const searchContainer = $('.tabla-buscador');

        if (searchInput.length) {
            const applySearch = () => {
                const value = searchInput.val();
                this.table.search(value).draw();
                this.toggleClearButton(searchInput, searchContainer);
                setTimeout(() => this.highlightMatches(value), 20);
                this.saveUiState({ search: String(value || '') });
            };

            if (this.isServerSideEnabled()) {
                // En server-side: NO buscar por cada letra (evita peticiones AJAX excesivas)
                // Buscar solo al presionar Enter o al hacer click en el botón de buscar.
                searchInput.on('input', () => {
                    this.toggleClearButton(searchInput, searchContainer);
                    this.saveUiState({ search: String(searchInput.val() || '') });
                });

                searchInput.on('keydown', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        applySearch();
                    }
                });

                if (searchBtn.length) {
                    searchBtn.on('click', (e) => {
                        e.preventDefault();
                        applySearch();
                    });
                }
            } else {
                // Client-side: búsqueda en vivo
                searchInput.on('keyup', (e) => {
                    this.table.search(e.target.value).draw();
                    this.toggleClearButton(searchInput, searchContainer);
                    setTimeout(() => this.highlightMatches(e.target.value), 20);
                    this.saveUiState({ search: String(e.target.value || '') });
                });
            }

            clearButton.on('click', () => {
                searchInput.val('');
                this.table.search('').draw();
                this.toggleClearButton(searchInput, searchContainer);
                searchInput.focus();
                setTimeout(() => this.highlightMatches(''), 20);
                this.saveUiState({ search: '' });
            });

            this.toggleClearButton(searchInput, searchContainer);
        }

        // Selector de cantidad de filas
        $('#entriesSelect').on('change', (e) => {
            this.table.page.len(e.target.value).draw();
            // Guardar pageLength como parte de filtros para restauración
            this.saveUiState({
                filters: {
                    ...(this.uiState?.filters || {}),
                    entriesSelect: String(e.target.value)
                }
            });
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

                    switch (sortValue) {
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
            // En modo client-side, se filtra vía ext.search.
            // En modo server-side, el filtro debe aplicarse en backend (se envía en d.filters).
            if (!this.isServerSideEnabled()) {
                $.fn.dataTable.ext.search.push((settings, data, dataIndex) => {
                    if (settings.nTable.id !== this.tableSelector.replace('#', '')) return true;

                    const selectedStatus = $statusFilter.val();
                    if (selectedStatus === "") return true;

                    const row = this.table.row(dataIndex).node();
                    const statusCell = $(row)
                        .find('.column-status-td')
                        .data('status')
                        .toString();

                    return statusCell === selectedStatus;
                });
            }

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
        const $filtersContainer = this.getFiltersContainer();
        $filtersContainer.find('select').each(function () {
            const $select = $(this);
            // no aplicar estilo al selector de paginación
            if ($select.attr('id') === 'entriesSelect') return;


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
        const $genericSelects = $filtersContainer.find('select').filter(function () {
            return $(this).attr('id') !== 'entriesSelect';
        });
        const filterSelectors = ['#customSearch'];
        $genericSelects.each(function () {
            filterSelectors.push('#' + $(this).attr('id'));
        });

        if (filterSelectors.length > 0) {
            $(filterSelectors.join(', ')).on('input change keyup', () => {
                this.checkFiltersActive();
            });
        }

        this.checkFiltersActive();

        // Persistir cambios en cualquier filtro dentro del contenedor (incluye filtros locales y generales)
        $filtersContainer.find('select, input, textarea').on('change input', (e) => {
            // Evitar interferir con DataTables search; se maneja aparte
            if ($(e.currentTarget).attr('id') === 'customSearch') return;
            this.saveUiState();
        });
    }

    checkFiltersActive() {
        const hasSearch = $('#customSearch').val()?.trim() !== '';

        // Detectar TODOS los selects dentro de .tabla-filtros (sin depender del sufijo "Filter")
        let anyFilterActive = hasSearch;

        const $filtersContainer = this.getFiltersContainer();
        $filtersContainer.find('select').each(function () {
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

        const $filtersContainer = this.getFiltersContainer();

        $filtersContainer.find('select').each(function () {
            if ($(this).attr('id') === 'entriesSelect') return; // no contar paginación como filtro
            if (($(this).val() ?? '') !== '') {
                activeFiltersCount++;
            }
        });

        if (activeFiltersCount === 0) return;

        // Limpiar búsqueda (funciona igual en client-side y server-side)
        $('#customSearch').val('');
        this.table.search('').draw();
        this.saveUiState({ search: '' });

        // Limpiar TODOS los selects dentro del contenedor de filtros
        $filtersContainer.find('select').each(function () {
            if ($(this).attr('id') === 'entriesSelect') return; // no limpiar paginación
            $(this).val('');
            $(this).trigger('change');
        });

        $('#clearFiltersBtn').addClass('clearing');
        setTimeout(() => {
            $('#clearFiltersBtn').removeClass('clearing');
        }, 300);

        this.checkFiltersActive();

        // En server-side, asegurar que el backend reciba filtros vacíos
        if (this.isServerSideEnabled()) {
            this.table.draw();
        }

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

                        const row = $switch.closest('tr');

                        // 🔥 sincronizar fuente de verdad
                        row.find('.column-status-td')
                            .data('status', isChecked ? 1 : 0);

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
            `Mostrando ${info.start + 1} a ${info.end} de ${info.recordsDisplay} registros`
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
