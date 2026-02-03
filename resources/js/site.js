// site.js - Entry point para el sitio público
import './bootstrap';
import './site-modules/sidebar-manager';
import './site-modules/user-dropdown';

// Swiper Slider
import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay, EffectFade } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import 'swiper/css/effect-fade';

window.Swiper = Swiper;
window.SwiperModules = { Navigation, Pagination, Autoplay, EffectFade };
