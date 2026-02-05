// site.js - Entry point para el sitio público
import './bootstrap';
import './site-modules/sidebar-manager';
import './site-modules/user-dropdown';
import './modules/custom-select';

// Swiper Slider
import Swiper from 'swiper';
import {
	Navigation,
	Pagination,
	Autoplay,
	EffectFade,
	EffectCoverflow,
	EffectCards,
	EffectCube,
	EffectFlip,
	EffectCreative,
} from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import 'swiper/css/effect-fade';
import 'swiper/css/effect-coverflow';
import 'swiper/css/effect-cards';
import 'swiper/css/effect-cube';
import 'swiper/css/effect-flip';
import 'swiper/css/effect-creative';

window.Swiper = Swiper;
window.SwiperModules = {
	Navigation,
	Pagination,
	Autoplay,
	EffectFade,
	EffectCoverflow,
	EffectCards,
	EffectCube,
	EffectFlip,
	EffectCreative,
};
