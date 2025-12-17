<!-- Modal para mostrar datos completos del POST -->
<div id="modalShow" class="modal-show hidden modal-horizontal">
    <div class="modal-content">
        <div class="modal-show-header" id="modalHeader">
            <h6>Detalles del Post</h6>
            <button type="button" id="closeModal" class="confirm-close ripple-btn">
                <i class="ri-close-line"></i>
            </button>
        </div>

        <h3 class="modal-title-body" id="post-title">Sin título</h3>

        <div class="modal-show-body">
            <div class="modal-show-row">
                <div class="modal-img-container">
                    <div id="post-image-slider" class="modal-img-slider hidden" aria-live="polite">
                        <div class="slider-main">
                            <button type="button" class="slider-btn prev-btn hidden" id="post-slider-prev" aria-label="Imagen anterior">
                                <i class="ri-arrow-left-s-line"></i>
                            </button>
                            <div class="slider-track" id="post-slider-track"></div>
                            <button type="button" class="slider-btn next-btn hidden" id="post-slider-next" aria-label="Imagen siguiente">
                                <i class="ri-arrow-right-s-line"></i>
                            </button>
                        </div>
                        <div class="slider-thumbnails hidden" id="post-slider-thumbs"></div>
                    </div>
                    <div id="post-image-placeholder" class="modal-img-placeholder">
                        <i class="ri-image-add-fill"></i>
                        Sin imágenes registradas
                    </div>
                    <div class="modal-show-actions">
                        <a href="#" id="modalPostEditBtn" class="boton boton-warning" title="Editar post">
                            <span class="boton-icon"><i class="ri-edit-circle-fill"></i></span>
                            <span class="boton-text">Editar</span>
                        </a>

                        <form id="modalDeleteForm" action="#" method="POST" title="Eliminar post">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="boton boton-danger">
                                <span class="boton-icon"><i class="ri-delete-bin-2-fill"></i></span>
                                <span class="boton-text">Eliminar</span>
                            </button>
                        </form>
                    </div>
                </div>

                <table class="modal-show-table">
                    <tr>
                        <th>ID</th>
                        <td id="post-id">—</td>
                    </tr>
                    <tr>
                        <th>Slug</th>
                        <td id="post-slug">—</td>
                    </tr>
                    <tr>
                        <th>Título</th>
                        <td id="post-title-cell">—</td>
                    </tr>
                    <tr>
                        <th>Estado</th>
                        <td id="post-status">—</td>
                    </tr>
                    <tr>
                        <th>Visibilidad</th>
                        <td id="post-visibility">—</td>
                    </tr>
                    <tr>
                        <th>Vistas</th>
                        <td id="post-views">—</td>
                    </tr>
                    <tr>
                        <th>Comentarios Permitidos</th>
                        <td id="post-allow-comments">—</td>
                    </tr>
                    <tr>
                        <th>Publicado</th>
                        <td id="post-published-at">—</td>
                    </tr>
                    <tr>
                        <th>Tags</th>
                        <td id="post-tags">—</td>
                    </tr>
                    <tr>
                        <th>Creado por</th>
                        <td id="post-created-by">—</td>
                    </tr>
                    <tr>
                        <th>Actualizado por</th>
                        <td id="post-updated-by">—</td>
                    </tr>
                    <tr>
                        <th>Revisado por</th>
                        <td id="post-reviewed-by">—</td>
                    </tr>
                </table>
            </div>

            <div class="modal-content-section">
                <h3 class="card-title">Contenido</h3>
                <div id="post-content" class="post-content">Sin contenido</div>
            </div>
        </div>

        <div class="modal-show-footer">
            <button type="button" class="boton boton-modal-close" id="cancelPostButton" title="Cerrar ventana">
                <span class="boton-icon text-base"><i class="ri-close-line"></i></span>
                <span class="boton-text">Cerrar</span>
            </button>
        </div>
    </div>
</div>
@push('scripts')
    <script>
        const postPlaceholderContent = '<i class="ri-image-add-fill"></i> Sin imágenes registradas';
        const postSliderAutoDelay = 4500;
        let postSliderState = null;
        let currentPostModalSlug = null;
        let pendingPostModalSlug = null;
        let postModalRequest = null;
        let isPostModalLoading = false;

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

        function openPostModal() {
            $('#modalShow').removeClass('hidden');
            $('#modalShow .modal-content').removeClass('animate-out').addClass('animate-in');
            $('#modalShow').appendTo('body');
            $('#approveForm').hide();
            $('#rejectForm').hide();
            document.addEventListener('keydown', escPostListener);
            document.addEventListener('mousedown', clickOutsidePostListener);
        }

        function closePostModal() {
            destroyPostSlider({ restorePlaceholder: false });
            pendingPostModalSlug = null;
            currentPostModalSlug = null;

            if (postModalRequest) {
                try {
                    postModalRequest.abort();
                } catch (error) {}
                postModalRequest = null;
                isPostModalLoading = false;
            }

            const $content = $('#modalShow .modal-content');
            $content.removeClass('animate-in').addClass('animate-out');
            setTimeout(() => {
                $('#modalShow').addClass('hidden');
                setLoadingPostFields();
                document.removeEventListener('keydown', escPostListener);
                document.removeEventListener('mousedown', clickOutsidePostListener);
            }, 250);
        }

        function destroyPostSlider({ restorePlaceholder = true } = {}) {
            if (postSliderState?.cleanup) {
                postSliderState.cleanup();
            }
            if (postSliderState?.intervalId) {
                clearInterval(postSliderState.intervalId);
            }
            postSliderState = null;

            const $slider = $('#post-image-slider');
            const $track = $('#post-slider-track');
            const $thumbs = $('#post-slider-thumbs');
            const $prev = $('#post-slider-prev');
            const $next = $('#post-slider-next');
            const $placeholder = $('#post-image-placeholder');

            $slider.addClass('hidden');
            $track.empty();
            $thumbs.empty().addClass('hidden');
            $prev.off('.postSlider').addClass('hidden').css('top', '');
            $next.off('.postSlider').addClass('hidden').css('top', '');
            $thumbs.off('.postSlider');

            if (restorePlaceholder) {
                $placeholder.removeClass('hidden').html(postPlaceholderContent);
            }
        }

        function setLoadingPostFields() {
            destroyPostSlider({ restorePlaceholder: false });
            currentPostModalSlug = null;

            $('#post-title').html('<div class="shimmer shimmer-cell shimmer-title" style="width: 220px;"></div>');
            $('#post-id').html('<div class="shimmer shimmer-cell" style="width: 60px;"></div>');
            $('#post-slug').html('<div class="shimmer shimmer-cell" style="width: 120px;"></div>');
            $('#post-title-cell').html('<div class="shimmer shimmer-cell" style="width: 180px;"></div>');
            $('#post-status').html('<div class="shimmer shimmer-cell" style="width: 150px;"></div>');
            $('#post-visibility').html('<div class="shimmer shimmer-cell" style="width: 120px;"></div>');
            $('#post-views').html('<div class="shimmer shimmer-cell" style="width: 80px;"></div>');
            $('#post-allow-comments').html('<div class="shimmer shimmer-cell" style="width: 110px;"></div>');
            $('#post-published-at').html('<div class="shimmer shimmer-cell" style="width: 140px;"></div>');
            $('#post-tags').html('<div class="shimmer shimmer-cell" style="width: 220px;"></div>');
            $('#post-created-by').html('<div class="shimmer shimmer-cell" style="width: 220px;"></div>');
            $('#post-updated-by').html('<div class="shimmer shimmer-cell" style="width: 220px;"></div>');
            $('#post-reviewed-by').html('<div class="shimmer shimmer-cell" style="width: 220px;"></div>');
            $('#post-content').html('<div class="shimmer shimmer-cell" style="height: 120px;"></div>');
            $('#post-gallery').html('<div class="shimmer shimmer-img" style="width:100%;height:120px;"></div>');
            $('#post-image-placeholder').removeClass('hidden').html('<div class="shimmer shimmer-img"></div>');
            $('#modalPostEditBtn').attr('href', '#');
            $('#modalDeleteForm').attr('action', '#');
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
                probe.onload = () => resolve({ ...image });
                probe.onerror = () => resolve(null);
                probe.src = image.url;
            }));

            return Promise.all(loaders).then(results => results.filter(Boolean));
        }

        function initializePostSlider(images, postTitle) {
            const $slider = $('#post-image-slider');
            const $placeholder = $('#post-image-placeholder');
            const $track = $('#post-slider-track');
            const $main = $slider.find('.slider-main');
            const $thumbs = $('#post-slider-thumbs');
            const $prev = $('#post-slider-prev');
            const $next = $('#post-slider-next');

            destroyPostSlider({ restorePlaceholder: false });

            if (!images.length) {
                $placeholder.removeClass('hidden').html(postPlaceholderContent);
                return;
            }

            const safeTitle = escapeHtml(postTitle ?? 'Post');
            $placeholder.addClass('hidden').html(postPlaceholderContent);
            $slider.removeClass('hidden');

            images.forEach((image, index) => {
                const altLabel = image.alt ? escapeHtml(image.alt) : `${safeTitle} imagen ${index + 1}`;
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

            postSliderState = {
                currentIndex: 0,
                total: images.length,
                intervalId: null,
                $track,
                $thumbs,
                $prev,
                $next,
                $main,
                minHeight: parseFloat($main.css('min-height')) || 0
            };

            const state = postSliderState;
            state.$track.css('transform', 'translateX(0%)');

            state.syncLayout = () => {
                window.requestAnimationFrame(() => {
                    if (postSliderState !== state) {
                        return;
                    }

                    const mainNode = state.$main.get(0);
                    if (!mainNode) {
                        return;
                    }

                    const activeImg = state.$track.find('.slider-item.active img').get(0);
                    if (!activeImg) {
                        state.$main.css('height', '');
                        state.$prev.css('top', '');
                        state.$next.css('top', '');
                        return;
                    }

                    const imgRect = activeImg.getBoundingClientRect();
                    const mainRect = mainNode.getBoundingClientRect();

                    const renderedHeight = activeImg.offsetHeight || imgRect.height || 0;
                    if (renderedHeight) {
                        const targetHeight = Math.max(renderedHeight, state.minHeight);
                        state.$main.css('height', `${targetHeight}px`);
                    } else {
                        state.$main.css('height', '');
                    }

                    if (!imgRect.height) {
                        state.$prev.css('top', '');
                        state.$next.css('top', '');
                        return;
                    }

                    const offsetY = imgRect.top - mainRect.top + (imgRect.height / 2);
                    state.$prev.css('top', `${offsetY}px`);
                    state.$next.css('top', `${offsetY}px`);
                });
            };

            state.updateActive = (index) => {
                state.currentIndex = index;
                state.$track.find('.slider-item').each(function () {
                    const itemIndex = Number($(this).data('index'));
                    const isActive = itemIndex === index;
                    $(this).toggleClass('active', isActive).attr('aria-hidden', isActive ? 'false' : 'true');
                });
                state.$track.css('transform', `translateX(-${index * 100}%)`);

                if (state.total > 1) {
                    state.$thumbs.find('.thumbnail-img').each(function () {
                        const thumbIndex = Number($(this).data('index'));
                        $(this).toggleClass('active-thumb', thumbIndex === index);
                    });
                }

                state.syncLayout();
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
                }, postSliderAutoDelay);
            };

            const handleWindowResize = () => state.syncLayout();
            state.cleanup = () => {
                state.stopAuto();
                state.$track.find('.slider-item img').off('.postSlider');
                $(window).off('resize.postSlider', handleWindowResize);
                state.$main.css('height', '');
                state.$prev.css('top', '');
                state.$next.css('top', '');
            };

            const repositionAfterLoad = () => state.syncLayout();
            state.$track.find('.slider-item img').each(function () {
                if (this.complete && this.naturalHeight) {
                    return;
                }
                $(this).on('load.postSlider error.postSlider', repositionAfterLoad);
            });

            $(window).on('resize.postSlider', handleWindowResize);

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

            state.$prev.off('.postSlider').on('click.postSlider', () => {
                state.stopAuto();
                const nextIndex = (state.currentIndex - 1 + state.total) % state.total;
                state.updateActive(nextIndex);
            });

            state.$next.off('.postSlider').on('click.postSlider', () => {
                state.stopAuto();
                const nextIndex = (state.currentIndex + 1) % state.total;
                state.updateActive(nextIndex);
            });

            state.$thumbs.off('.postSlider').on('click.postSlider', '.thumbnail-img', function () {
                const targetIndex = Number($(this).data('index'));
                state.stopAuto();
                state.updateActive(targetIndex);
            });
        }

        function renderPostGallery(images) {
            const $gallery = $('#post-gallery');

            if (!images.length) {
                $gallery.html('<p class="label-hint"><i class="ri-information-line"></i> No se registraron imágenes para este post.</p>');
                return;
            }

            const cards = images.map((image, index) => {
                const orderLabel = typeof image.order === 'number' ? image.order : index + 1;
                const altAttr = escapeHtml(image.alt ?? 'Imagen del post');
                const altLabel = image.alt ? `<p class="gallery-card-alt">${escapeHtml(image.alt)}</p>` : '';
                const mainBadge = image.is_main
                    ? '<span class="badge badge-warning-light"><i class="ri-star-smile-fill"></i> Principal</span>'
                    : '';

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

        function renderPostModal(data) {
            currentPostModalSlug = data.slug ?? null;
            const safeSlug = escapeHtml(data.slug ?? '—');
            const safeTitle = data.title ?? 'Sin título';

            $('#post-id').text(data.id ?? '—');
            $('#post-slug').html(`<span class="badge badge-primary slug-mono">${safeSlug}</span>`);
            $('#post-title').text(safeTitle);
            $('#post-title-cell').text(safeTitle);
            $('#post-content').html(data.content ?? 'Sin contenido');

            const statusBadge = (status) => {
                const mapping = {
                    draft: { label: '<i class="ri-pencil-line"></i> Borrador', klass: 'badge-gray' },
                    pending: { label: '<i class="ri-time-line"></i> Pendiente', klass: 'badge-warning' },
                    published: { label: '<i class="ri-check-line"></i> Publicado', klass: 'badge-success' },
                    rejected: { label: '<i class="ri-close-line"></i> Rechazado', klass: 'badge-danger' }
                };
                const config = mapping[status] ?? { label: escapeHtml(String(status ?? 'Desconocido')), klass: 'badge-secondary' };
                return `<span class="badge ${config.klass}">${config.label}</span>`;
            };

            $('#post-status').html(statusBadge(data.status));
            $('#post-visibility').text(data.visibility ?? '—');
            $('#post-views').text(data.views ?? '0');

            $('#post-allow-comments').html(data.allow_comments
                ? '<span class="badge boton-success"><i class="ri-checkbox-circle-line"></i> Sí</span>'
                : '<span class="badge boton-danger"><i class="ri-close-circle-line"></i> No</span>'
            );

            $('#post-published-at').text(data.published_at ?? '—');

            const tags = Array.isArray(data.tags) ? data.tags : [];
            $('#post-tags').html(tags.length
                ? tags.map(tag => `<span class="badge badge-warning">${escapeHtml(tag)}</span>`).join(' ')
                : '—'
            );

            $('#post-created-by').html(`
                <div class="show-cell-content">
                    <span class="font-bold">${escapeHtml(data.created_by_name ?? 'Sistema')}</span>
                    <span class="show-date"><i class="ri-time-fill"></i> ${escapeHtml(data.created_at ?? '—')}</span>
                </div>
            `);

            $('#post-updated-by').html(`
                <div class="show-cell-content">
                    <span class="font-bold">${escapeHtml(data.updated_by_name ?? '—')}</span>
                    <span class="show-date"><i class="ri-time-fill"></i> ${escapeHtml(data.updated_at ?? '—')}</span>
                </div>
            `);

            $('#post-reviewed-by').html(`
                <div class="show-cell-content">
                    <span class="font-bold">${escapeHtml(data.reviewed_by_name ?? 'Sin revisión')}</span>
                    <span class="show-date"><i class="ri-time-fill"></i> ${escapeHtml(data.reviewed_at ?? '—')}</span>
                </div>
            `);

            $('#modalPostEditBtn').attr('href', `/admin/posts/${data.slug}/edit`);
            $('#modalDeleteForm').attr('action', `/admin/posts/${data.slug}`);

            const rawImages = Array.isArray(data.images) ? data.images : [];

            loadValidImages(rawImages)
                .then((validImages) => {
                    if (currentPostModalSlug !== (data.slug ?? null)) {
                        return;
                    }

                    const sourceImages = validImages.length
                        ? validImages
                        : rawImages.filter((image) => image && image.url);

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

                    initializePostSlider(sortedImages, data.title);
                    renderPostGallery(sortedImages);
                })
                .catch(() => {
                    if (currentPostModalSlug !== (data.slug ?? null)) {
                        return;
                    }
                    destroyPostSlider();
                    $('#post-image-placeholder').removeClass('hidden').html('<i class="ri-error-warning-line"></i> Error al cargar la galería');
                    $('#post-gallery').html('<p class="label-hint"><i class="ri-error-warning-line"></i> No se pudieron renderizar las imágenes.</p>');
                });

            pendingPostModalSlug = null;
        }

        function loadPostModal(slug) {
            pendingPostModalSlug = slug;
            setLoadingPostFields();
            openPostModal();

            if (postModalRequest) {
                try {
                    postModalRequest.abort();
                } catch (error) {}
                postModalRequest = null;
            }

            isPostModalLoading = true;

            $.ajax({
                url: `/admin/posts/${slug}/show`,
                method: 'GET',
                beforeSend(xhr) {
                    postModalRequest = xhr;
                },
                success(response) {
                    postModalRequest = null;
                    isPostModalLoading = false;

                    if (!response || response.slug !== slug || slug !== pendingPostModalSlug) {
                        return;
                    }

                    renderPostModal(response);
                },
                error(_, textStatus) {
                    postModalRequest = null;
                    isPostModalLoading = false;
                    pendingPostModalSlug = null;
                    currentPostModalSlug = null;
                    destroyPostSlider();

                    if (textStatus === 'abort') {
                        return;
                    }

                    $('#post-title').text('Error al cargar');
                    $('#post-content').text('No se pudo obtener la información del post.');
                    $('#post-image-placeholder').removeClass('hidden').html('<i class="ri-error-warning-line"></i> Error al cargar la galería');
                    $('#post-gallery').html('<p class="label-hint"><i class="ri-error-warning-line"></i> Ocurrió un problema al cargar las imágenes.</p>');
                }
            });
        }

        $(document).on('click', '.btn-ver-post', function () {
            if (isPostModalLoading) {
                return;
            }
            loadPostModal($(this).data('slug'));
        });

        $('#closeModal, #cancelPostButton').on('click', closePostModal);

        function escPostListener(event) {
            if (event.key === 'Escape') {
                closePostModal();
            }
        }

        function clickOutsidePostListener(event) {
            const overlay = document.getElementById('modalShow');
            const content = document.querySelector('#modalShow .modal-content');

            if (event.target === overlay && !content.contains(event.target)) {
                closePostModal();
            }
        }

        $(document).on('submit', '#rejectForm', function (event) {
            event.preventDefault();
            const form = this;

            document.removeEventListener('mousedown', clickOutsidePostListener);

            window.showConfirm({
                type: 'danger',
                header: 'Rechazar Post',
                title: '¿Estás seguro?',
                message: 'Vas a rechazar el post <strong>' + $('#post-title').text() + '</strong>.',
                confirmText: 'Sí, rechazar',
                cancelText: 'No, cancelar',
                onConfirm() {
                    document.addEventListener('mousedown', clickOutsidePostListener);
                    form.submit();
                },
                onCancel() {
                    restoreOutsideClick();
                }
            });
        });

        $(document).on('submit', '#approveForm', function (event) {
            event.preventDefault();
            const form = this;

            document.removeEventListener('mousedown', clickOutsidePostListener);

            window.showConfirm({
                type: 'success',
                header: 'Aprobar Post',
                title: '¿Confirmar aprobación?',
                message: 'Vas a aprobar el post <strong>' + $('#post-title').text() + '</strong>.',
                confirmText: 'Sí, aprobar',
                cancelText: 'Cancelar',
                onConfirm() {
                    document.addEventListener('mousedown', clickOutsidePostListener);
                    form.submit();
                },
                onCancel() {
                    restoreOutsideClick();
                }
            });
        });

        $(document).on('submit', '#modalDeleteForm', function (event) {
            event.preventDefault();
            const form = this;

            document.removeEventListener('click', clickOutsidePostListener);

            window.showConfirm({
                type: 'danger',
                header: 'Eliminar Post',
                title: '¿Estás seguro?',
                message: 'Esta acción no se puede deshacer.<br>Se eliminará el post <strong>' + $('#post-title').text() + '</strong> del sistema.',
                confirmText: 'Sí, eliminar',
                cancelText: 'No, cancelar',
                onConfirm() {
                    document.addEventListener('click', clickOutsidePostListener);
                    form.submit();
                },
                onCancel() {
                    restoreOutsideClick();
                }
            });
        });

        function restoreOutsideClick() {
            document.addEventListener('click', clickOutsidePostListener);
        }
    </script>
@endpush
