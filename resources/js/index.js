// Importa todos los módulos del dashboard
import './dashboard/icon-hover-fill.js';
import './dashboard/sidebar-submenus.js';
import './dashboard/sidebar-toggle.js';
import './dashboard/sidebar-tooltips.js';
import './dashboard/sidebar-touch-gestures.js';
import './dashboard/sidebars-control.js';
import './dashboard/theme-toggle.js';

import './utils/material-design.js';

import './modals/modal-confirm.js';
import './utils/datatable-manager.js';
import './modules/category-hierarchy.js';
import { initImageUpload } from './utils/image-upload-handler.js';
import { initSubmitLoader } from './utils/submit-button-loader.js';

// Exportar para uso global
window.initImageUpload = initImageUpload;
window.initSubmitLoader = initSubmitLoader;


// Si tienes funciones o inicializaciones globales
document.addEventListener('DOMContentLoaded', () => {
  console.log('Dashboard JS inicializado ✅');
});
