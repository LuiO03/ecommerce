// Importa todos los módulos del dashboard
import './sidebar-left/icon-hover-fill.js';
import './sidebar-left/sidebar-submenus.js';
import './sidebar-left/sidebar-toggle.js';
import './sidebar-left/sidebar-tooltips.js';
import './sidebar-left/sidebar-touch-gestures.js';
import './sidebar-left/sidebars-control.js';
import './sidebar-left/theme-toggle.js';

import './utils/material-design.js';

import './modals/modal-confirm.js';
import './components/alert.js';
import './utils/datatable-manager.js';
import './modules/category-hierarchy.js';
import { initImageUpload } from './utils/image-upload-handler.js';
import { initSubmitLoader } from './utils/submit-button-loader.js';
import { initFormValidator } from './utils/form-validator.js';
import { initTextareaAutosize } from './utils/textarea-autosize.js';
import { initConnectionStatusBar } from './utils/connection-status.js';
import { initOptionFeatureForm } from './modules/options-form-feature-manager.js';
import { initOptionInlineManager } from './modules/options-index-inline-manager.js';
import { initProductVariantsManager } from './modules/product-variants-manager.js';
import { initTabsManager } from './modules/tabs-manager.js';
import {
  initPostGalleryCreate,
  initPostGalleryEdit,
  initProductGalleryCreate,
  initProductGalleryEdit,
  initGalleryCreateWithConfig,
  initGalleryEditWithConfig
} from './utils/gallery-manager.js';
import { initMobileFiltersPanel } from './utils/mobile-filters-panel.js';
import { initMobileActionsMenu } from './utils/mobile-actions-menu.js';

// Exportar para uso global
window.initImageUpload = initImageUpload;
window.initSubmitLoader = initSubmitLoader;
window.initFormValidator = initFormValidator;
window.initOptionFeatureForm = initOptionFeatureForm;
window.initOptionInlineManager = initOptionInlineManager;
window.initTabsManager = initTabsManager;
window.initPostGalleryCreate = initPostGalleryCreate;
window.initPostGalleryEdit = initPostGalleryEdit;
window.initProductGalleryCreate = initProductGalleryCreate;
window.initProductGalleryEdit = initProductGalleryEdit;
window.initGalleryCreateWithConfig = initGalleryCreateWithConfig;
window.initGalleryEditWithConfig = initGalleryEditWithConfig;
window.initProductVariantsManager = initProductVariantsManager;


// Si tienes funciones o inicializaciones globales
document.addEventListener('DOMContentLoaded', () => {
  console.log('Dashboard JS inicializado ✅');
  initTextareaAutosize();
  initTabsManager();
  initConnectionStatusBar();
  initMobileFiltersPanel();
  initMobileActionsMenu();
});
