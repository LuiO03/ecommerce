import Swiper from 'swiper';
import { Navigation } from 'swiper/modules';

document.addEventListener('DOMContentLoaded', () => {
    const galleryRoot = document.querySelector('[data-product-gallery]');

    if (galleryRoot) {
        const mainEl = galleryRoot.querySelector('.product-gallery-main');
        const nextEl = galleryRoot.querySelector('.gallery-next');
        const prevEl = galleryRoot.querySelector('.gallery-prev');

        if (mainEl) {
            const gallerySwiper = new Swiper(mainEl, {
                modules: [Navigation],
                slidesPerView: 1,
                spaceBetween: 12,
                navigation: {
                    nextEl,
                    prevEl,
                },
            });

            const thumbs = Array.from(galleryRoot.querySelectorAll('.product-thumb'));
            const setActiveThumb = (index) => {
                thumbs.forEach((thumb, i) => {
                    thumb.classList.toggle('is-active', i === index);
                });
            };

            setActiveThumb(0);

            thumbs.forEach((thumb, index) => {
                thumb.addEventListener('click', () => {
                    gallerySwiper.slideTo(index);
                    setActiveThumb(index);
                });
            });

            gallerySwiper.on('slideChange', () => {
                setActiveThumb(gallerySwiper.activeIndex);
            });
        }
    }

    const variantRoot = document.querySelector('[data-variant-root]');
    const variantsDataEl = document.getElementById('product-variants-data');

    if (variantRoot && variantsDataEl) {
        const variants = JSON.parse(variantsDataEl.textContent || '[]');
        const basePrice = parseFloat(variantRoot.dataset.basePrice || '0');
        const discount = parseFloat(variantRoot.dataset.discount || '0');
        const priceCurrent = variantRoot.querySelector('[data-price-current]');
        const priceOriginal = variantRoot.querySelector('[data-price-original]');
        const stockEl = variantRoot.querySelector('[data-stock]');
        const helperEl = variantRoot.querySelector('[data-variant-helper]');

        const optionGroups = Array.from(variantRoot.querySelectorAll('[data-option-id]'));
        const optionCount = optionGroups.length;
        const selection = new Map();

        const formatPrice = (value) => `S/.${Number(value).toFixed(2)}`;

        const updatePrice = (priceBase) => {
            const finalPrice = discount > 0 ? priceBase * (1 - discount / 100) : priceBase;
            if (priceCurrent) {
                priceCurrent.textContent = formatPrice(finalPrice);
            }
            if (priceOriginal) {
                priceOriginal.textContent = formatPrice(priceBase);
            }
        };

        const updateVariant = () => {
            if (selection.size < optionCount) {
                updatePrice(basePrice);
                if (helperEl) {
                    helperEl.textContent = 'Selecciona una opcion para ver disponibilidad y precio.';
                }
                if (stockEl) {
                    stockEl.textContent = 'Stock disponible';
                }
                return;
            }

            const match = variants.find((variant) => {
                return Array.from(selection.entries()).every(([optionId, featureId]) => {
                    return variant.features.some((feature) => {
                        return String(feature.id) === String(featureId);
                    });
                });
            });

            if (!match) {
                if (helperEl) {
                    helperEl.textContent = 'La combinacion seleccionada no esta disponible.';
                }
                return;
            }

            const priceBase = match.price && Number(match.price) > 0 ? Number(match.price) : basePrice;
            updatePrice(priceBase);

            if (stockEl) {
                stockEl.textContent = match.stock > 0
                    ? `Stock: ${match.stock}`
                    : 'Sin stock';
            }
            if (helperEl) {
                helperEl.textContent = 'Variacion seleccionada.';
            }
        };

        optionGroups.forEach((group) => {
            const optionId = group.dataset.optionId;
            const buttons = Array.from(group.querySelectorAll('[data-feature-id]'));

            buttons.forEach((button) => {
                button.addEventListener('click', () => {
                    const featureId = button.dataset.featureId;
                    const isSelected = selection.get(optionId) === featureId;

                    buttons.forEach((btn) => {
                        btn.classList.remove('is-selected');
                        btn.setAttribute('aria-pressed', 'false');
                    });

                    if (isSelected) {
                        selection.delete(optionId);
                    } else {
                        selection.set(optionId, featureId);
                        button.classList.add('is-selected');
                        button.setAttribute('aria-pressed', 'true');
                    }

                    updateVariant();
                });
            });
        });

        updateVariant();
    }
});
