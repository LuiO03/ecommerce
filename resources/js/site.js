// site.js - Entry point para el sitio público
import './bootstrap';
import './site-modules/sidebar-manager';
import './site-modules/user-dropdown';
import './site-modules/infinite-products';
import './site-modules/search-autocomplete';
import './site-modules/search-modal';
import './site-modules/product-detail';
import './site-modules/quantity-counter';
import './site-modules/auth-wishlist-modal';
import './site-modules/wishlist-page';
import './site-modules/profile-addresses';
import './modules/custom-select';

import { initImageUpload } from './utils/image-upload-handler.js';
import { initFormValidator } from './utils/form-validator.js';
import { initSubmitLoader } from './utils/submit-button-loader.js';

// Exponer helpers de formularios para vistas públicas (login, etc.)
window.initImageUpload = initImageUpload;
window.initFormValidator = initFormValidator;
window.initSubmitLoader = initSubmitLoader;

// Swiper Slider
import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay, Thumbs } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

window.Swiper = Swiper;
window.SwiperModules = {
    Navigation,
    Pagination,
    Autoplay,
    Thumbs,
};

// Listener global para toasts disparados desde Livewire
document.addEventListener('livewire:init', () => {
	if (typeof window.Livewire === 'undefined') {
		return;
	}

	window.Livewire.on('toast', (...args) => {
		if (typeof window.showToast !== 'function' || !args.length) {
			return;
		}

		let options = {};

		// Caso 1: se envía un solo objeto { type, title, message }
		if (args.length === 1 && typeof args[0] === 'object' && args[0] !== null) {
			const payload = args[0];

			// Si viene anidado tipo { 0: { ... } } (caso raro)
			if (
				!('type' in payload) &&
				!('title' in payload) &&
				!('message' in payload) &&
				payload[0] && typeof payload[0] === 'object'
			) {
				options = payload[0];
			} else {
				options = payload;
			}
		} else {
			// Caso 2: se envían argumentos sueltos (type, title, message)
			const [type, title, message] = args;
			options = { type, title, message };
		}

		window.showToast(options);
	});
});

