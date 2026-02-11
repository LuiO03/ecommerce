document.addEventListener('DOMContentLoaded', () => {
    const galleryRoot = document.querySelector('[data-product-gallery]');

    if (galleryRoot) {
        const mainEl = galleryRoot.querySelector('.product-gallery-main');
        const nextEl = galleryRoot.querySelector('.gallery-next');
        const prevEl = galleryRoot.querySelector('.gallery-prev');

        if (mainEl) {
            const slides = Array.from(mainEl.querySelectorAll('.product-gallery-slide'));
            const thumbs = Array.from(galleryRoot.querySelectorAll('.product-thumb'));
            let activeIndex = 0;
            let autoplayTimer = null;
            const loopEnabled = true;
            const autoplayEnabled = true;
            const autoplayDelay = 4500;
            const swipeThreshold = 40;
            let startX = 0;
            let deltaX = 0;
            let isDragging = false;

            const setActiveThumb = (index) => {
                thumbs.forEach((thumb, i) => {
                    const isActive = i === index;
                    thumb.classList.toggle('is-active', isActive);
                    thumb.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                });
            };

            const clampIndex = (index) => {
                if (!slides.length) {
                    return 0;
                }
                if (loopEnabled) {
                    return (index + slides.length) % slides.length;
                }
                return Math.max(0, Math.min(index, slides.length - 1));
            };

            const setActiveSlide = (index) => {
                if (!slides.length) {
                    return;
                }

                activeIndex = clampIndex(index);
                slides.forEach((slide, i) => {
                    slide.classList.toggle('is-active', i === activeIndex);
                });

                if (!loopEnabled) {
                    if (prevEl) {
                        prevEl.disabled = activeIndex === 0;
                    }
                    if (nextEl) {
                        nextEl.disabled = activeIndex === slides.length - 1;
                    }
                } else {
                    if (prevEl) {
                        prevEl.disabled = false;
                    }
                    if (nextEl) {
                        nextEl.disabled = false;
                    }
                }

                setActiveThumb(activeIndex);
            };

            const stopAutoplay = () => {
                if (autoplayTimer) {
                    window.clearInterval(autoplayTimer);
                    autoplayTimer = null;
                }
            };

            const startAutoplay = () => {
                if (!autoplayEnabled || slides.length <= 1) {
                    return;
                }
                stopAutoplay();
                autoplayTimer = window.setInterval(() => {
                    setActiveSlide(activeIndex + 1);
                }, autoplayDelay);
            };

            const restartAutoplay = () => {
                stopAutoplay();
                startAutoplay();
            };

            thumbs.forEach((thumb, index) => {
                thumb.setAttribute('aria-pressed', 'false');
                thumb.addEventListener('click', () => {
                    setActiveSlide(index);
                    restartAutoplay();
                });
            });

            if (prevEl) {
                prevEl.addEventListener('click', () => {
                    setActiveSlide(activeIndex - 1);
                    restartAutoplay();
                });
            }

            if (nextEl) {
                nextEl.addEventListener('click', () => {
                    setActiveSlide(activeIndex + 1);
                    restartAutoplay();
                });
            }

            mainEl.addEventListener('pointerdown', (event) => {
                if (event.pointerType === 'mouse' && event.button !== 0) {
                    return;
                }
                const isInteractive = event.target instanceof HTMLElement
                    && event.target.closest('.gallery-nav, .product-thumb, button, a, input, textarea');
                if (isInteractive) {
                    return;
                }
                isDragging = true;
                startX = event.clientX;
                deltaX = 0;
                mainEl.classList.add('is-dragging');
                mainEl.setPointerCapture(event.pointerId);
                stopAutoplay();
            });

            mainEl.addEventListener('pointermove', (event) => {
                if (!isDragging) {
                    return;
                }
                deltaX = event.clientX - startX;
            });

            const endSwipe = (event) => {
                if (!isDragging) {
                    return;
                }
                mainEl.classList.remove('is-dragging');
                if (event?.pointerId !== undefined && mainEl.hasPointerCapture(event.pointerId)) {
                    mainEl.releasePointerCapture(event.pointerId);
                }
                if (Math.abs(deltaX) > swipeThreshold) {
                    setActiveSlide(activeIndex + (deltaX < 0 ? 1 : -1));
                }
                isDragging = false;
                deltaX = 0;
                restartAutoplay();
            };

            mainEl.addEventListener('pointerup', endSwipe);
            mainEl.addEventListener('pointercancel', endSwipe);
            mainEl.addEventListener('pointerleave', endSwipe);

            document.addEventListener('keydown', (event) => {
                if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') {
                    return;
                }
                const target = event.target;
                const isFormField = target instanceof HTMLElement
                    && (target.tagName === 'INPUT'
                        || target.tagName === 'TEXTAREA'
                        || target.isContentEditable);

                if (isFormField) {
                    return;
                }

                if (event.key === 'ArrowLeft') {
                    setActiveSlide(activeIndex - 1);
                } else {
                    setActiveSlide(activeIndex + 1);
                }
                restartAutoplay();
            });

            mainEl.addEventListener('mouseenter', stopAutoplay);
            mainEl.addEventListener('mouseleave', startAutoplay);
            mainEl.addEventListener('focusin', stopAutoplay);
            mainEl.addEventListener('focusout', startAutoplay);

            setActiveSlide(0);
            startAutoplay();
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
        const addToCartBtn = variantRoot.querySelector('[data-add-to-cart]');
        const addToCartLabel = addToCartBtn?.querySelector('[data-add-to-cart-label]');
        const defaultAddToCartText = addToCartBtn?.dataset.defaultText || 'Agregar al carrito';
        const promptAddToCartText = addToCartBtn?.dataset.promptText || 'Seleccione tus opciones';
        const outOfStockText = addToCartBtn?.dataset.outOfStockText || 'Sin stock';

        const quantityRoot = variantRoot.querySelector('[data-quantity-root]');
        const quantityValueEl = quantityRoot?.querySelector('[data-quantity-value]');
        const quantityDecrementBtn = quantityRoot?.querySelector('[data-quantity-decrement]');
        const quantityIncrementBtn = quantityRoot?.querySelector('[data-quantity-increment]');

        let currentStock = null;
        let currentQuantity = 1;

        const optionGroups = Array.from(variantRoot.querySelectorAll('[data-option-id]'));
        const optionCount = optionGroups.length;
        const selection = new Map();

        const hasFeature = (variant, featureId) => {
            return variant.features.some((feature) => String(feature.id) === String(featureId));
        };

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

        const isFeatureAvailable = (optionId, featureId) => {
            return variants.some((variant) => {
                if (!hasFeature(variant, featureId)) {
                    return false;
                }

                return Array.from(selection.entries()).every(([selectedOptionId, selectedFeatureId]) => {
                    if (String(selectedOptionId) === String(optionId)) {
                        return true;
                    }
                    return hasFeature(variant, selectedFeatureId);
                });
            });
        };

        const updateAvailability = () => {
            optionGroups.forEach((group) => {
                const optionId = group.dataset.optionId;
                const buttons = Array.from(group.querySelectorAll('[data-feature-id]'));

                buttons.forEach((button) => {
                    const featureId = button.dataset.featureId;
                    const available = isFeatureAvailable(optionId, featureId);

                    button.classList.toggle('is-disabled', !available);
                    button.disabled = !available;
                });
            });
        };

        const updateAddToCart = (enabled, label) => {
            if (!addToCartBtn || !addToCartLabel) {
                return;
            }
            addToCartBtn.disabled = !enabled;
            addToCartBtn.setAttribute('aria-disabled', enabled ? 'false' : 'true');
            addToCartLabel.textContent = label;
        };

        const updateQuantityUI = () => {
            if (!quantityRoot || !quantityValueEl || !quantityDecrementBtn || !quantityIncrementBtn) {
                return;
            }

            const maxQty = typeof currentStock === 'number' && currentStock > 0 ? currentStock : 1;

            if (currentQuantity < 1) {
                currentQuantity = 1;
            }
            if (currentQuantity > maxQty) {
                currentQuantity = maxQty;
            }

            quantityValueEl.textContent = String(currentQuantity);

            const canDecrement = currentQuantity > 1;
            const canIncrement = currentQuantity < maxQty;

            quantityDecrementBtn.disabled = !canDecrement;
            quantityDecrementBtn.classList.toggle('is-disabled', !canDecrement);

            quantityIncrementBtn.disabled = !canIncrement;
            quantityIncrementBtn.classList.toggle('is-disabled', !canIncrement);

            quantityValueEl.classList.remove('is-changing');
            // Fuerza reflow para reiniciar la animación
            // eslint-disable-next-line no-unused-expressions
            quantityValueEl.offsetWidth;
            quantityValueEl.classList.add('is-changing');

            window.setTimeout(() => {
                quantityValueEl.classList.remove('is-changing');
            }, 200);
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
                updateAddToCart(false, promptAddToCartText);
                updateAvailability();
                currentStock = null;
                updateQuantityUI();
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
                updateAddToCart(false, promptAddToCartText);
                updateAvailability();
                currentStock = null;
                updateQuantityUI();
                return;
            }

            const priceBase = match.price && Number(match.price) > 0 ? Number(match.price) : basePrice;
            updatePrice(priceBase);

            if (stockEl) {
                if (match.stock <= 0) {
                    stockEl.textContent = 'Sin stock';
                } else if (match.stock < 10) {
                    stockEl.innerHTML = `¡Solo quedan <span class="product-stock-highlight">${match.stock} unidades</span> disponibles!`;
                } else {
                    stockEl.innerHTML = 'Unidades disponibles: <span class="product-stock-highlight">10+</span>';
                }
            }
            updateAddToCart(match.stock > 0, match.stock > 0 ? defaultAddToCartText : outOfStockText);
            if (helperEl) {
                helperEl.textContent = 'Variacion seleccionada.';
            }

            currentStock = typeof match.stock === 'number' ? match.stock : null;
            updateQuantityUI();

            updateAvailability();
        };

        if (quantityRoot && quantityValueEl && quantityDecrementBtn && quantityIncrementBtn) {
            quantityDecrementBtn.addEventListener('click', () => {
                if (currentQuantity <= 1) {
                    return;
                }
                currentQuantity -= 1;
                updateQuantityUI();
            });

            quantityIncrementBtn.addEventListener('click', () => {
                const maxQty = typeof currentStock === 'number' && currentStock > 0 ? currentStock : 1;
                if (currentQuantity >= maxQty) {
                    return;
                }
                currentQuantity += 1;
                updateQuantityUI();
            });

            updateQuantityUI();
        }

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
