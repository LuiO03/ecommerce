/**
 * ========================================
 * DataTable Export Manager
 * ========================================
 * Módulo para manejar exportaciones (Excel, PDF)
 */

class DataTableExportManager {
    constructor(moduleName, selectedIdsCallback) {
        this.moduleName = moduleName;
        this.getSelectedIds = selectedIdsCallback;
        this.initExportListeners();
    }

    /**
     * Inicializar listeners de exportación
     */
    initExportListeners() {
        this.initExcelExport();
        this.initPdfExport();
    }

    /**
     * Exportar a Excel
     */
    initExcelExport() {
        $('#exportSelected').on('click', () => {
            const selected = this.getSelectedIds();
            const exportUrl = `/admin/${this.moduleName}/export/excel`;
            
            this.submitExportForm(exportUrl, selected);
        });
    }

    /**
     * Exportar a PDF
     */
    initPdfExport() {
        $('#exportPdf').on('click', () => {
            const selected = this.getSelectedIds();
            const exportUrl = `/admin/${this.moduleName}/export/pdf`;
            
            this.submitExportForm(exportUrl, selected, true);
        });
    }

    /**
     * Crear y enviar formulario de exportación
     */
    submitExportForm(url, ids, allowAll = false) {
        const form = $('<form>', {
            method: 'POST',
            action: url
        });

        // Token CSRF
        form.append($('<input>', {
            type: 'hidden',
            name: '_token',
            value: $('meta[name="csrf-token"]').attr('content')
        }));

        // Si hay IDs seleccionados
        if (ids.length > 0) {
            ids.forEach(id => {
                form.append($('<input>', {
                    type: 'hidden',
                    name: 'ids[]',
                    value: id
                }));
            });
        } else if (allowAll) {
            // Exportar todos
            form.append($('<input>', {
                type: 'hidden',
                name: 'export_all',
                value: '1'
            }));
        }

        $('body').append(form);
        form.submit();
        form.remove();
    }

    /**
     * Exportar registros visibles (después de filtros)
     */
    exportVisible(table, url) {
        const form = $('<form>', {
            method: 'POST',
            action: url
        });

        form.append($('<input>', {
            type: 'hidden',
            name: '_token',
            value: $('meta[name="csrf-token"]').attr('content')
        }));

        // Obtener IDs visibles después de filtros
        const visibleIds = [];
        table.rows({ search: 'applied' }).every(function() {
            const data = this.data();
            const id = $(this.node()).data('id');
            if (id) visibleIds.push(id);
        });

        visibleIds.forEach(id => {
            form.append($('<input>', {
                type: 'hidden',
                name: 'ids[]',
                value: id
            }));
        });

        $('body').append(form);
        form.submit();
        form.remove();
    }
}

// Exportar para uso global
window.DataTableExportManager = DataTableExportManager;
