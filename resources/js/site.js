// site.js - Entry point para el sitio público
import './bootstrap';
import './site-modules/sidebar-manager';
import './site-modules/user-dropdown';
import './site-modules/infinite-products';
import './site-modules/search-autocomplete';
import './site-modules/product-detail';
import './site-modules/quantity-counter';
import './modules/custom-select';

// Swiper Slider
import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

window.Swiper = Swiper;
window.SwiperModules = {
	Navigation,
	Pagination,
	Autoplay,
};

// Listener global para toasts disparados desde Livewire (browser events)
window.addEventListener('toast', (event) => {
	const detail = event?.detail;
	if (typeof window.showToast === 'function' && detail) {
		window.showToast(detail);
	}
});

