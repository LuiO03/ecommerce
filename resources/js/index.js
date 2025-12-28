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
import './components/alert.js';
import './utils/datatable-manager.js';
import './modules/category-hierarchy.js';
import { initImageUpload } from './utils/image-upload-handler.js';
import { initSubmitLoader } from './utils/submit-button-loader.js';
import { initCategoryHierarchy } from './modules/category-hierarchy-manager.js';
import { initFormValidator } from './utils/form-validator.js';
import { initCompanySettingsTabs, initCompanySettingsEditors, initCompanySettingsColorInputs } from './modules/company-settings-tabs.js';
import { initOptionFeatureForm } from './modules/options-form-feature-manager.js';
import { initOptionInlineManager } from './modules/options-index-inline-manager.js';
import { initProductVariantsManager } from './modules/product-variants-manager.js';
import {
  initPostGalleryCreate,
  initPostGalleryEdit,
  initProductGalleryCreate,
  initProductGalleryEdit,
  initGalleryCreateWithConfig,
  initGalleryEditWithConfig
} from './utils/gallery-manager.js';

// Exportar para uso global
window.initImageUpload = initImageUpload;
window.initSubmitLoader = initSubmitLoader;
window.initCategoryHierarchy = initCategoryHierarchy;
window.initFormValidator = initFormValidator;
window.initCompanySettingsTabs = initCompanySettingsTabs;
window.initCompanySettingsEditors = initCompanySettingsEditors;
window.initCompanySettingsColorInputs = initCompanySettingsColorInputs;
window.initOptionFeatureForm = initOptionFeatureForm;
window.initOptionInlineManager = initOptionInlineManager;
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
});
