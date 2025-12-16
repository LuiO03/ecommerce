<div id="modalShowProduct" class="modal-show hidden modal-horizontal">
    <div class="modal-content">
        <div class="modal-show-header">
            <h6>Detalles del Producto</h6>
            <button type="button" id="closeProductModal" class="confirm-close ripple-btn">
                <i class="ri-close-line"></i>
            </button>
        </div>

        <h3 class="modal-title-body" id="product-name-title">—</h3>
        <div class="modal-show-body">
            <div class="modal-show-row">
                <table class="modal-show-table">
                    <tr>
                        <th>ID</th>
                        <td id="product-id">—</td>
                    </tr>
                    <tr>
                        <th>Slug</th>
                        <td id="product-slug">—</td>
                    </tr>
                    <tr>
                        <th>SKU</th>
                        <td id="product-sku">—</td>
                    </tr>
                    <tr>
                        <th>Nombre</th>
                        <td id="product-name">—</td>
                    </tr>
                    <tr>
                        <th>Categoría</th>
                        <td id="product-category">—</td>
                    </tr>
                    <tr>
                        <th>Precio</th>
                        <td id="product-price">—</td>
                    </tr>
                    <tr>
                        <th>Descuento</th>
                        <td id="product-discount">—</td>
                    </tr>
                    <tr>
                        <th>Estado</th>
                        <td id="product-status">—</td>
                    </tr>
                    <tr>
                        <th>Variantes</th>
                        <td id="product-variants">—</td>
                    </tr>
                    <tr>
                        <th>Creado por</th>
                        <td id="product-created-by">—</td>
                    </tr>
                    <tr>
                        <th>Actualizado por</th>
                        <td id="product-updated-by">—</td>
                    </tr>
                    <tr>
                        <th>Descripción</th>
                        <td id="product-description">—</td>
                    </tr>
                </table>
                <div class="modal-img-container">
                    <div id="product-image-slider" class="modal-img-slider hidden" aria-live="polite">
                        <div class="slider-thumbnails hidden" id="product-slider-thumbs"></div>
                        <div class="slider-main">
                            <button type="button" class="slider-btn prev-btn hidden" id="product-slider-prev"
                                aria-label="Imagen anterior">
                                <i class="ri-arrow-left-s-line"></i>
                            </button>
                            <div class="slider-track" id="product-slider-track"></div>
                            <button type="button" class="slider-btn next-btn hidden" id="product-slider-next"
                                aria-label="Imagen siguiente">
                                <i class="ri-arrow-right-s-line"></i>
                            </button>
                        </div>
                    </div>
                    <div id="product-image-placeholder" class="modal-img-placeholder">
                        <i class="ri-image-add-fill"></i>
                        Sin imágenes registradas
                    </div>
                    <div class="modal-show-actions">
                        <a href="#" id="modalProductEditBtn" class="boton boton-warning" title="Editar producto">
                            <span class="boton-icon"><i class="ri-edit-circle-fill"></i></span>
                            <span class="boton-text">Editar</span>
                        </a>

                        <form id="modalProductDeleteForm" action="#" method="POST" title="Eliminar producto">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="boton boton-danger">
                                <span class="boton-icon"><i class="ri-delete-bin-2-fill"></i></span>
                                <span class="boton-text">Eliminar</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-gallery" id="product-gallery"></div>
        </div>

        <div class="modal-show-footer">
            <button type="button" class="boton boton-modal-close" id="cancelProductModal" title="Cerrar ventana">
                <span class="boton-icon text-base"><i class="ri-close-line"></i></span>
                <span class="boton-text">Cerrar</span>
            </button>
        </div>
    </div>
</div>
@push('scripts')
    <script>
        const productPlaceholderContent = '<i class="ri-image-add-fill"></i> Sin imágenes registradas';
        const productSliderAutoDelay = 4500;
        let productSliderState = null;
        let currentProductModalSlug = null;
        let pendingProductModalSlug = null;

        function escapeHtml(value) {
            if (value === null || value === undefined) {
                return '';
            }
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function openProductModal() {
            $('#modalShowProduct').removeClass('hidden');
            $('#modalShowProduct .modal-content').removeClass('animate-out').addClass('animate-in');

            $('#modalShowProduct').appendTo('body');
            document.addEventListener('keydown', escProductListener);
            document.addEventListener('mousedown', clickOutsideProductListener);
        }

        function closeProductModal() {
            destroyProductSlider({
                restorePlaceholder: false
            });
            pendingProductModalSlug = null;
            currentProductModalSlug = null;
            const $content = $('#modalShowProduct .modal-content');
            $content.removeClass('animate-in').addClass('animate-out');
            setTimeout(() => {
                $('#modalShowProduct').addClass('hidden');
                setLoadingProductFields();
                document.removeEventListener('keydown', escProductListener);
                document.removeEventListener('mousedown', clickOutsideProductListener);
            }, 250);
        }

        function destroyProductSlider({
            restorePlaceholder = true
        } = {}) {
            if (productSliderState?.intervalId) {
                clearInterval(productSliderState.intervalId);
            }
            productSliderState = null;

            const $slider = $('#product-image-slider');
            const $track = $('#product-slider-track');
            const $thumbs = $('#product-slider-thumbs');
            const $prev = $('#product-slider-prev');
            const $next = $('#product-slider-next');
            const $placeholder = $('#product-image-placeholder');

            $slider.addClass('hidden');
            $track.empty();
            $thumbs.empty().addClass('hidden');
            $prev.off('.productSlider').addClass('hidden');
            $next.off('.productSlider').addClass('hidden');
            $thumbs.off('.productSlider');

            if (restorePlaceholder) {
                $placeholder.removeClass('hidden').html(productPlaceholderContent);
            }
        }

        function setLoadingProductFields() {
            destroyProductSlider({
                restorePlaceholder: false
            });
            currentProductModalSlug = null;

            $('#product-name-title').html('<div class="shimmer shimmer-cell shimmer-title" style="width: 220px;"></div>');
            $('#product-id').html('<div class="shimmer shimmer-cell" style="width: 40px;"></div>');
            $('#product-slug').html('<div class="shimmer shimmer-cell" style="width: 120px;"></div>');
            $('#product-sku').html('<div class="shimmer shimmer-cell" style="width: 100px;"></div>');
            $('#product-name').html('<div class="shimmer shimmer-cell" style="width: 180px;"></div>');
            $('#product-category').html('<div class="shimmer shimmer-cell" style="width: 140px;"></div>');
            $('#product-price').html('<div class="shimmer shimmer-cell" style="width: 80px;"></div>');
            $('#product-discount').html('<div class="shimmer shimmer-cell" style="width: 60px;"></div>');
            $('#product-status').html('<div class="shimmer shimmer-cell" style="width: 70px;"></div>');
            $('#product-variants').html('<div class="shimmer shimmer-cell" style="width: 90px;"></div>');
            $('#product-created-by').html('<div class="shimmer shimmer-cell" style="width: 180px;"></div>');
            $('#product-updated-by').html('<div class="shimmer shimmer-cell" style="width: 180px;"></div>');
            $('#product-description').html('<div class="shimmer shimmer-cell" style="width: 240px;"></div>');
            $('#product-gallery').html('<div class="shimmer shimmer-img" style="width:100%;height:120px;"></div>');
            $('#product-image-placeholder').removeClass('hidden').html('<div class="shimmer shimmer-img"></div>');
        }

        function loadValidImages(images) {
            const candidates = Array.isArray(images) ? images : [];

            if (!candidates.length) {
                return Promise.resolve([]);
            }

            const loaders = candidates.map((image) => new Promise((resolve) => {
                if (!image || !image.url) {
                    return resolve(null);
                }

                const probe = new Image();
                probe.onload = () => resolve({
                    ...image
                });
                probe.onerror = () => resolve(null);
                probe.src = image.url;
            }));

            return Promise.all(loaders).then(results => results.filter(Boolean));
        }

        function initializeProductSlider(images, productName) {
            const $slider = $('#product-image-slider');
            const $placeholder = $('#product-image-placeholder');
            const $track = $('#product-slider-track');
            const $thumbs = $('#product-slider-thumbs');
            const $prev = $('#product-slider-prev');
            const $next = $('#product-slider-next');

            destroyProductSlider({
                restorePlaceholder: false
            });

            if (!images.length) {
                $placeholder.removeClass('hidden').html(productPlaceholderContent);
                return;
            }

            const safeName = escapeHtml(productName ?? 'Producto');
            $placeholder.addClass('hidden').html(productPlaceholderContent);
            $slider.removeClass('hidden');

            images.forEach((image, index) => {
                const altLabel = image.alt ? escapeHtml(image.alt) : `${safeName} imagen ${index + 1}`;
                $track.append(`
					<div class="slider-item${index === 0 ? ' active' : ''}" data-index="${index}" aria-hidden="${index === 0 ? 'false' : 'true'}">
						<img src="${image.url}" alt="${altLabel}" class="modal-img">
					</div>
				`);

                if (images.length > 1) {
                    $thumbs.append(`
						<button type="button" class="thumbnail-img${index === 0 ? ' active-thumb' : ''}" data-index="${index}" aria-label="Mostrar imagen ${index + 1}">
							<img src="${image.url}" alt="${altLabel}">
						</button>
					`);
                }
            });

            productSliderState = {
                currentIndex: 0,
                total: images.length,
                intervalId: null,
                $track,
                $thumbs,
                $prev,
                $next
            };

            const state = productSliderState;
            state.$track.css('transform', 'translateX(0%)');

            state.updateActive = (index) => {
                state.currentIndex = index;
                state.$track.find('.slider-item').each(function() {
                    const itemIndex = Number($(this).data('index'));
                    const isActive = itemIndex === index;
                    $(this).toggleClass('active', isActive).attr('aria-hidden', isActive ? 'false' : 'true');
                });
                state.$track.css('transform', `translateX(-${index * 100}%)`);

                if (state.total > 1) {
                    state.$thumbs.find('.thumbnail-img').each(function() {
                        const thumbIndex = Number($(this).data('index'));
                        $(this).toggleClass('active-thumb', thumbIndex === index);
                    });
                }
            };

            state.stopAuto = () => {
                if (state.intervalId) {
                    clearInterval(state.intervalId);
                    state.intervalId = null;
                }
            };

            state.startAuto = () => {
                state.stopAuto();
                if (state.total <= 1) {
                    return;
                }
                state.intervalId = setInterval(() => {
                    const nextIndex = (state.currentIndex + 1) % state.total;
                    state.updateActive(nextIndex);
                }, productSliderAutoDelay);
            };

            state.updateActive(0);

            if (state.total > 1) {
                state.$prev.removeClass('hidden');
                state.$next.removeClass('hidden');
                state.$thumbs.removeClass('hidden');
                state.startAuto();
            } else {
                state.$prev.addClass('hidden');
                state.$next.addClass('hidden');
                state.$thumbs.addClass('hidden');
            }

            state.$prev.off('.productSlider').on('click.productSlider', () => {
                state.stopAuto();
                const nextIndex = (state.currentIndex - 1 + state.total) % state.total;
                state.updateActive(nextIndex);
            });

            state.$next.off('.productSlider').on('click.productSlider', () => {
                state.stopAuto();
                const nextIndex = (state.currentIndex + 1) % state.total;
                state.updateActive(nextIndex);
            });

            state.$thumbs.off('.productSlider').on('click.productSlider', '.thumbnail-img', function() {
                const targetIndex = Number($(this).data('index'));
                state.stopAuto();
                state.updateActive(targetIndex);
            });
        }

        function renderProductGallery(images) {
            const $gallery = $('#product-gallery');

            if (!images.length) {
                $gallery.html(
                    '<p class="label-hint"><i class="ri-information-line"></i> No se registraron imágenes para este producto.</p>'
                    );
                return;
            }

            const cards = images.map((image, index) => {
                const orderLabel = typeof image.order === 'number' ? image.order : index + 1;
                const altAttr = escapeHtml(image.alt ?? 'Imagen del producto');
                const altLabel = image.alt ? `<p class="gallery-card-alt">${escapeHtml(image.alt)}</p>` : '';
                const mainBadge = image.is_main ?
                    '<span class="badge badge-warning-light"><i class="ri-star-smile-fill"></i> Principal</span>' :
                    '';

                return `
					<article class="modal-gallery-card">
						<div class="gallery-card-image">
							<img src="${image.url}" alt="${altAttr}">
						</div>
						<div class="gallery-card-meta">
							<span class="badge badge-secondary-light">#${orderLabel}</span>
							${mainBadge}
						</div>
						${altLabel}
					</article>
				`;
            }).join('');

            $gallery.html(`<div class="modal-gallery-grid">${cards}</div>`);
        }

        function renderProductModal(data) {
            currentProductModalSlug = data.slug ?? null;
            const safeSlug = escapeHtml(data.slug ?? '—');
            const safeSku = escapeHtml(data.sku ?? '—');
            const safeName = data.name ?? '—';
            const safeCategoryName = escapeHtml(data.category?.name ?? 'Sin categoría');
            const safeCreatedBy = escapeHtml(data.created_by_name ?? 'Sistema');
            const safeUpdatedBy = escapeHtml(data.updated_by_name ?? '—');
            const safeCreatedAt = escapeHtml(data.created_at ?? '—');
            const safeUpdatedAt = escapeHtml(data.updated_at ?? '—');

            $('#product-id').text(data.id ?? '—');
            $('#product-slug').html(`<span class="badge badge-primary slug-mono">${safeSlug}</span>`);
            $('#product-sku').html(`<span class="badge badge-gray">${safeSku}</span>`);
            $('#product-name-title').text(safeName);
            $('#product-name').text(safeName);

            if (data.category) {
                $('#product-category').html(`
					<span class="badge badge-info">
						<i class="ri-price-tag-3-line"></i>
						${safeCategoryName}
					</span>
				`);
            } else {
                $('#product-category').html('<span class="badge badge-gray">Sin categoría</span>');
            }

            $('#product-price').html(`
				<span class="badge badge-success-light">
					<i class="ri-currency-line"></i>
					${Number(data.price ?? 0).toFixed(2)}
				</span>
			`);

            if (data.discount && Number(data.discount) > 0) {
                $('#product-discount').html(`
					<span class="badge badge-warning-light">
						<i class="ri-discount-percent-line"></i>
						${Number(data.discount).toFixed(2)}
					</span>
				`);
            } else {
                $('#product-discount').html('<span class="text-muted-td">Sin descuento</span>');
            }

            $('#product-status').html(data.status ?
                '<span class="badge boton-success"><i class="ri-checkbox-circle-fill"></i> Activo</span>' :
                '<span class="badge boton-danger"><i class="ri-close-circle-fill"></i> Inactivo</span>'
            );

            const variantsCount = Array.isArray(data.variants) ? data.variants.length : 0;
            if (variantsCount > 0) {
                const variantsList = data.variants.map((variant) => {
                    const variantSku = escapeHtml(variant.sku ?? '—');
                    const variantPrice = Number(variant.price ?? 0).toFixed(2);
                    const variantStock = escapeHtml(String(variant.stock ?? 0));
                    return `
						<li>
							<span class="badge badge-secondary-light">
								<i class="ri-hashtag"></i> ${variantSku}
							</span>
							<span class="badge badge-success-light">
								<i class="ri-currency-line"></i> ${variantPrice}
							</span>
							<span class="badge badge-gray">
								<i class="ri-archive-stack-line"></i> Stock ${variantStock}
							</span>
						</li>
					`;
                }).join('');
                $('#product-variants').html(`<ul class="modal-list">${variantsList}</ul>`);
            } else {
                $('#product-variants').html('<span class="text-muted-td">Sin variantes</span>');
            }

            $('#product-created-by').html(`
				<div class="show-cell-content">
					<span class="font-bold">${safeCreatedBy}</span>
					<span class="show-date"><i class="ri-time-fill"></i> ${safeCreatedAt}</span>
				</div>
			`);

            $('#product-updated-by').html(`
				<div class="show-cell-content">
					<span class="font-bold">${safeUpdatedBy}</span>
					<span class="show-date"><i class="ri-time-fill"></i> ${safeUpdatedAt}</span>
				</div>
			`);

            $('#product-description').text(data.description ?? 'Sin descripción');

            const rawImages = Array.isArray(data.images) ? data.images : [];

            loadValidImages(rawImages)
                .then((validImages) => {
                    if (currentProductModalSlug !== (data.slug ?? null)) {
                        return;
                    }

                    const sourceImages = validImages.length ?
                        validImages :
                        rawImages.filter((image) => image && image.url);

                    const sortedImages = [...sourceImages].sort((a, b) => {
                        if (a.is_main && !b.is_main) {
                            return -1;
                        }
                        if (!a.is_main && b.is_main) {
                            return 1;
                        }
                        const orderA = typeof a.order === 'number' ? a.order : Number.MAX_SAFE_INTEGER;
                        const orderB = typeof b.order === 'number' ? b.order : Number.MAX_SAFE_INTEGER;
                        return orderA - orderB;
                    });

                    initializeProductSlider(sortedImages, data.name);
                    renderProductGallery(sortedImages);
                })
                .catch(() => {
                    if (currentProductModalSlug !== (data.slug ?? null)) {
                        return;
                    }
                    destroyProductSlider();
                    $('#product-image-placeholder').removeClass('hidden').html(
                        '<i class="ri-error-warning-line"></i> Error al cargar la galería');
                    $('#product-gallery').html(
                        '<p class="label-hint"><i class="ri-error-warning-line"></i> No se pudieron renderizar las imágenes.</p>'
                        );
                });

            $('#modalProductEditBtn').attr('href', `/admin/products/${data.slug}/edit`);
            $('#modalProductDeleteForm').attr('action', `/admin/products/${data.slug}`);
            pendingProductModalSlug = null;
        }

        function loadProductModal(slug) {
            pendingProductModalSlug = slug;
            setLoadingProductFields();
            openProductModal();
            $.ajax({
                url: `/admin/products/${slug}/show`,
                method: 'GET',
                success: function(response) {
                    if (!response || response.slug !== pendingProductModalSlug) {
                        return;
                    }
                    renderProductModal(response);
                },
                error: function() {
                    pendingProductModalSlug = null;
                    currentProductModalSlug = null;
                    destroyProductSlider();
                    $('#product-name-title').text('Error al cargar');
                    $('#product-description').text('No se pudo obtener la información del producto.');
                    $('#product-image-placeholder').removeClass('hidden').html(
                        '<i class="ri-error-warning-line"></i> Error al cargar la galería');
                    $('#product-gallery').html(
                        '<p class="label-hint"><i class="ri-error-warning-line"></i> Ocurrió un problema al cargar las imágenes.</p>'
                        );
                }
            });
        }

        $(document).on('click', '.btn-ver-producto', function() {
            const slug = $(this).data('slug');
            loadProductModal(slug);
        });

        $('#closeProductModal, #cancelProductModal').on('click', closeProductModal);

        function escProductListener(event) {
            if (event.key === 'Escape') {
                closeProductModal();
            }
        }

        function clickOutsideProductListener(event) {
            const overlay = document.getElementById('modalShowProduct');
            const content = document.querySelector('#modalShowProduct .modal-content');
            if (event.target === overlay && !content.contains(event.target)) {
                closeProductModal();
            }
        }

        $(document).on('submit', '#modalProductDeleteForm', function(event) {
            event.preventDefault();
            const form = this;

            document.removeEventListener('mousedown', clickOutsideProductListener);

            window.showConfirm({
                type: 'danger',
                header: 'Eliminar producto',
                title: '¿Deseas continuar?',
                message: 'Esta acción es irreversible.<br>Se eliminará el producto <strong>' + $(
                    '#product-name').text() + '</strong>.',
                confirmText: 'Sí, eliminar',
                cancelText: 'No, cancelar',
                onConfirm: function() {
                    document.addEventListener('mousedown', clickOutsideProductListener);
                    form.submit();
                },
                onCancel: function() {
                    document.addEventListener('mousedown', clickOutsideProductListener);
                }
            });
        });
    </script>
@endpush
