// site-modules/categories-slider.js

export function initCategoriesSlider() {
    const el = document.querySelector('.categories-slider');

    if (!el) return;

    new Swiper(el, {
        modules: [
            window.SwiperModules.Navigation,
            window.SwiperModules.Pagination,
            window.SwiperModules.Autoplay,
        ],

        centerInsufficientSlides: true,

        loop: false,

        autoplay: {
            delay: 5500,
        },

        navigation: {
            nextEl: '.categories-slider .swiper-button-next',
            prevEl: '.categories-slider .swiper-button-prev',
        },

        pagination: {
            el: '.categories-slider .swiper-pagination',
            clickable: true,
        },

        breakpoints: {
            320: {
                slidesPerView: 3,
                spaceBetween: 3,
            },

            640: {
                slidesPerView: 4,
                spaceBetween: 5,
            },

            800: {
                slidesPerView: 5,
                spaceBetween: 8,
            },

            1024: {
                slidesPerView: 6,
                spaceBetween: 16,
            },

            1280: {
                slidesPerView: 8,
                spaceBetween: 16,
            },
        },
    });
}
