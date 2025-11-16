$(document).ready(function () {

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

    function animarFilas() {
        const tabla = document.getElementById('tabla');
        if (tabla.classList.contains('no-animate')) return;

        const filas = document.querySelectorAll('#tabla tbody tr');
        filas.forEach((fila, i) => {
            fila.style.animation = 'slideInLeft 0.3s ease-in-out';
            fila.style.animationDelay = `${i * 0.02}s`;
        });
    }

    function actualizarIconosOrden() {
        document.querySelectorAll('#tabla thead th').forEach(th => {
            if (
                th.classList.contains('column-check-th') ||
                th.classList.contains('column-actions-th') ||
                th.classList.contains('control')
            ) return;

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

        const firstBtn = document.createElement('button');
        firstBtn.innerHTML = '<i class="ri-skip-left-line"></i> <span class="btn-text">Primero</span>';
        firstBtn.className = 'pagina-btn';
        firstBtn.disabled = currentPage === 0;
        firstBtn.addEventListener('click', () => table.page(0).draw('page'));
        pagination.appendChild(firstBtn);

        const prevBtn = document.createElement('button');
        prevBtn.innerHTML = '<i class="ri-arrow-left-s-line"></i> <span class="btn-text">Anterior</span>';
        prevBtn.className = 'pagina-btn';
        prevBtn.disabled = currentPage === 0;
        prevBtn.addEventListener('click', () => table.page('previous').draw('page'));
        pagination.appendChild(prevBtn);

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

        const nextBtn = document.createElement('button');
        nextBtn.innerHTML = '<span class="btn-text">Siguiente</span> <i class="ri-arrow-right-s-line"></i>';
        nextBtn.className = 'pagina-btn';
        nextBtn.disabled = currentPage === totalPages - 1;
        nextBtn.addEventListener('click', () => table.page('next').draw('page'));
        pagination.appendChild(nextBtn);

        const lastBtn = document.createElement('button');
        lastBtn.innerHTML = '<span class="btn-text">Último</span> <i class="ri-skip-right-line"></i>';
        lastBtn.className = 'pagina-btn';
        lastBtn.disabled = currentPage === totalPages - 1;
        lastBtn.addEventListener('click', () => table.page(totalPages - 1).draw('page'));
        pagination.appendChild(lastBtn);

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
        columnDefs: [
            {
                orderable: false,
                targets: ['column-check-th', 'column-actions-th', 'control']
            }
        ],
        dom: 't',
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50],
        order: [[indiceColumnaId, 'desc']],
        language: language_es,
        scrollCollapse: false,
        scroller: false,
        initComplete: function () {
            $('#tabla').addClass('ready');
            actualizarIconosOrden();
        },
    });

    // ========================================
    // 🎨 CONTROLES DE INTERFAZ GENERALES
    // ========================================

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

    searchInput.addEventListener('keyup', function () {
        table.search(this.value).draw();
        toggleClearButton();
    });

    clearButton.addEventListener('click', function () {
        searchInput.value = '';
        table.search('').draw();
        toggleClearButton();
        searchInput.focus();
    });

    toggleClearButton();

    document.getElementById('entriesSelect').addEventListener('change', function () {
        table.page.len(this.value).draw();
    });

    $('#tabla').on('click', 'td', function (e) {
        if ($(e.target).is('input, button, a, i')) return;
        $('#tabla td.active-cell').removeClass('active-cell');
        $(this).addClass('active-cell');
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('#tabla').length) {
            $('#tabla td.active-cell').removeClass('active-cell');
        }
    });

    $('#tabla').on('order.dt page.dt search.dt', function () {
        $('#tabla td.active-cell').removeClass('active-cell');
    });

    document.querySelectorAll('#tabla td.control').forEach(cell => {
        cell.innerHTML = '<i class="ri-arrow-right-s-line control-icon"></i>';
    });

    $('#tabla').on('click', 'td.control', function () {
        $(this).find('.control-icon')
            .toggleClass('ri-arrow-right-s-line ri-arrow-down-s-line');
    });

    // ========================================
    // ✅ SISTEMA DE SELECCIÓN
    // ========================================

    let selectedIds = new Set();

    function updateSelectionBar() {
        const selectionBar = document.getElementById('selectionBar');
        const selectionCount = document.getElementById('selectionCount');
        const excelBadge = document.getElementById('excelBadge');
        const csvBadge = document.getElementById('csvBadge');
        const pdfBadge = document.getElementById('pdfBadge');
        const deleteBadge = document.getElementById('deleteBadge');
        const count = selectedIds.size;

        if (count > 0) {
            selectionBar.classList.add('active');
            selectionCount.textContent = `${count} seleccionado${count > 1 ? 's' : ''}`;
            excelBadge.textContent = count;
            csvBadge.textContent = count;
            pdfBadge.textContent = count;
            deleteBadge.textContent = count;
        } else {
            selectionBar.classList.remove('active');
        }
    }

    $('#tabla').on('click', 'td.column-check-td', function (e) {
        if (e.target.tagName === 'INPUT') return;
        const checkbox = $(this).find('input[type="checkbox"]');
        checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
    });

    $('#tabla').on('change', '.check-row', function () {
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

        updateSelectionBar();
    });

    $('#checkAll').on('change', function () {
        const checked = $(this).is(':checked');

        $('#tabla tbody .check-row').each(function () {
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

        updateSelectionBar();
    });

    $('#clearSelection').on('click', function () {
        selectedIds.clear();

        $('#tabla tbody .check-row').prop('checked', false);
        $('#tabla tbody tr').removeClass('row-selected');

        $('#checkAll').prop('checked', false).prop('indeterminate', false);

        updateSelectionBar();
    });

    // ========================================
    // 📤 MENÚ DESPLEGABLE DE EXPORTACIÓN
    // ========================================

    const exportMenuBtn = document.getElementById('exportMenuBtn');
    const exportDropdown = document.getElementById('exportDropdown');

    exportMenuBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        exportDropdown.classList.toggle('active');
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.export-menu-container')) {
            exportDropdown.classList.remove('active');
        }
    });

});
