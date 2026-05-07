/**
 * Custom Select - Selector personalizado sin dependencias
 */

class CustomSelect {
    constructor() {
        this.boundGlobalHandlers = false;
        this.init();
    }

    init() {
        document.addEventListener('DOMContentLoaded', () => {
            this.bindGlobalHandlers();
        });

        // Reinicializar después de actualizaciones de Livewire
        document.addEventListener('livewire:navigated', () => {
            this.bindGlobalHandlers();
        });

        // Para Livewire 3
        if (window.Livewire) {
            window.Livewire.hook('morph.updated', () => {
                setTimeout(() => this.bindGlobalHandlers(), 100);
            });
        }
    }

    bindGlobalHandlers() {
        if (this.boundGlobalHandlers) return;
        this.boundGlobalHandlers = true;

        document.addEventListener('click', (e) => {
            const trigger = e.target.closest('.site-select-trigger');
            if (trigger) {
                e.stopPropagation();
                const selectEl = trigger.closest('.site-select');
                if (!selectEl) return;

                const wasActive = selectEl.classList.contains('active');
                this.closeAllSelects();

                if (!wasActive) {
                    selectEl.classList.add('active');
                }
                return;
            }

            const option = e.target.closest('.site-select-option');
            if (option) {
                const selectEl = option.closest('.site-select');
                if (selectEl) {
                    selectEl.classList.remove('active');
                }
                return;
            }

            if (!e.target.closest('.site-select')) {
                this.closeAllSelects();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeAllSelects();
            }
        });
    }

    closeAllSelects() {
        document.querySelectorAll('.site-select.active').forEach(select => {
            select.classList.remove('active');
        });
    }
}

// Exportar para uso global
window.CustomSelect = new CustomSelect();

export default CustomSelect;
