import PhotoSwipeLightbox from 'photoswipe/lightbox';
import 'photoswipe/style.css';

export function initPhotoSwipe() {

    document.querySelectorAll('.pswp-gallery').forEach((galleryEl, index) => {

        // =========================
        // Asignar nombre automático
        // =========================

        if (!galleryEl.dataset.pswpGallery) {
            galleryEl.dataset.pswpGallery = `gallery-${index}`;
        }

        // =========================
        // Detectar tipo
        // =========================

        const isSingleLink =
            galleryEl.tagName.toLowerCase() === 'a';

        // =========================
        // Crear Lightbox
        // =========================

        const lightbox = new PhotoSwipeLightbox({

            gallery:
                `.pswp-gallery[data-pswp-gallery="${galleryEl.dataset.pswpGallery}"]`,

            children: isSingleLink ? '' : 'a',

            pswpModule: () => import('photoswipe'),

            showHideAnimationType: 'zoom',

            wheelToZoom: true,

            paddingFn: () => ({
                top: 40,
                bottom: 100,
                left: 20,
                right: 20,
            }),

        });

        // =========================
        // UI personalizada
        // =========================

        lightbox.on('uiRegister', () => {

            // =========================
            // Caption
            // =========================

            lightbox.pswp.ui.registerElement({

                name: 'custom-caption',

                order: 9,

                isButton: false,

                appendTo: 'root',

                onInit: (el, pswp) => {

                    pswp.on('change', () => {

                        const currSlide =
                            pswp.currSlide?.data?.element;

                        const title =
                            currSlide?.dataset?.title || '';

                        const description =
                            currSlide?.dataset?.description || '';

                        if (!title && !description) {

                            el.innerHTML = '';

                            return;

                        }

                        el.innerHTML = `
                            <div class="pswp-custom-caption">
                                ${title ? `<h4>${title}</h4>` : ''}
                                ${description ? `<p>${description}</p>` : ''}
                            </div>
                        `;

                    });

                }

            });

            // =========================
            // Fullscreen
            // =========================

            lightbox.pswp.ui.registerElement({

                name: 'fullscreen-button',

                order: 8,

                isButton: true,

                tagName: 'button',

                appendTo: 'bar',

                html: `
                    <i class="ri-fullscreen-line"></i>
                `,

                onClick: () => {

                    if (!document.fullscreenElement) {

                        document.documentElement.requestFullscreen();

                    } else {

                        document.exitFullscreen();

                    }

                }

            });

        });

        // =========================
        // Calcular dimensiones reales
        // =========================

        const links = isSingleLink
            ? [galleryEl]
            : galleryEl.querySelectorAll('a');

        links.forEach((link) => {

            if (
                link.dataset.pswpWidth &&
                link.dataset.pswpHeight
            ) {
                return;
            }

            const img = new Image();

            img.onload = () => {

                link.dataset.pswpWidth =
                    img.naturalWidth;

                link.dataset.pswpHeight =
                    img.naturalHeight;

            };

            img.src = link.href;

        });

        // =========================
        // Inicializar
        // =========================

        lightbox.init();

        // =========================
        // Botón manual productos
        // =========================

        if (!isSingleLink) {

            const openBtn = galleryEl
                .closest('.product-media')
                ?.querySelector('.open-product-gallery');

            if (openBtn) {

                openBtn.addEventListener('click', (e) => {

                    e.preventDefault();

                    const slides = galleryEl
                        .closest('.product-media')
                        ?.querySelectorAll('.product-gallery-slide');

                    let activeIndex = 0;

                    const activeSlide = galleryEl
                        .closest('.product-media')
                        ?.querySelector('.product-gallery-slide.active');

                    if (activeSlide && slides) {

                        activeIndex =
                            Array.from(slides)
                                .indexOf(activeSlide);

                    }

                    lightbox.loadAndOpen(activeIndex);

                });

            }

        }

    });

}
