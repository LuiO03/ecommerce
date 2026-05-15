// site.js - Entry point para el sitio público
import './bootstrap';
import './site/modules/sidebar-manager.js';
import './site/modules/user-dropdown.js';
import './site/modules/search-autocomplete.js';
import './site/modules/search-modal.js';
import './site/modules/product-detail.js';
import './site/modules/quantity-counter.js';
import './site/modules/auth-wishlist-modal.js';
import './site/modules/wishlist-page.js';
import './site/modules/profile-addresses.js';
import './site/modules/price-filter-manager.js';
import './site/modules/contact-form.js';
import './site/modules/claims-form.js';
import './utils/custom-select';
import './modals/modal-confirm.js';

import { initImageUpload } from './utils/image-upload-handler.js';
import { initFormValidator } from './utils/form-validator.js';
import { initSubmitLoader } from './utils/submit-button-loader.js';
import { initTextareaAutosize } from './utils/textarea-autosize.js';

// Exponer helpers de formularios para vistas públicas (login, etc.)
window.initImageUpload = initImageUpload;
window.initFormValidator = initFormValidator;
window.initSubmitLoader = initSubmitLoader;
window.initTextareaAutosize = initTextareaAutosize;

// Swiper Slider
import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay, Thumbs, EffectFade } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import 'swiper/css/effect-fade';

window.Swiper = Swiper;
window.SwiperModules = {
    Navigation,
    Pagination,
    Autoplay,
    Thumbs,
    EffectFade,
};

// Listener global para toasts disparados desde Livewire
document.addEventListener('livewire:init', () => {
	if (typeof window.Livewire === 'undefined') {
		return;
	}

	window.Livewire.on('scrollToTop', () => {
		const results = document.getElementById('products-results');

		if (results) {
			results.scrollIntoView({
				behavior: 'smooth',
				block: 'start',
			});
			return;
		}

		window.scrollTo({
			top: 0,
			left: 0,
			behavior: 'smooth',
		});
	});

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



