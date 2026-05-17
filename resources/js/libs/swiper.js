import Swiper from 'swiper';

import {
    Navigation,
    Pagination,
    Autoplay,
    Thumbs,
    EffectFade,
} from 'swiper/modules';

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
