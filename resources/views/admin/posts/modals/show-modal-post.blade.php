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
                <table class="modal-show-table">
                    <tr>
                        <th>ID</th>
                        <td id="post-id">-</td>
                    </tr>
                    <tr>
                        <th>Slug</th>
                        <td id="post-slug">-</td>
                    </tr>
                    <tr>
                        <th>Título</th>
                        <td id="post-title-cell">-</td>
                    </tr>
                    <tr>
                        <th>Estado</th>
                        <td id="post-status">-</td>
                    </tr>
                    <tr>
                        <th>Visibilidad</th>
                        <td id="post-visibility">-</td>
                    </tr>
                    <tr>
                        <th>Vistas</th>
                        <td id="post-views">-</td>
                    </tr>
                    <tr>
                        <th>Comentarios Permitidos</th>
                        <td id="post-allow-comments">-</td>
                    </tr>
                    <tr>
                        <th>Publicado</th>
                        <td id="post-published-at">-</td>
                    </tr>
                    <tr>
                        <th>Tags</th>
                        <td id="post-tags">-</td>
                    </tr>

                    <tr>
                        <th>Creado por</th>
                        <td id="post-created-by">-</td>
                    </tr>
                    <tr>
                        <th>Actualizado por</th>
                        <td id="post-updated-by">-</td>
                    </tr>
                    <tr>
                        <th>Revisado por</th>
                        <td id="post-reviewed-by">-</td>
                    </tr>
                </table>
                <div class="modal-img-container">
                    <div class="slider-thumbnails" id="sliderThumbnails"></div>

                    <!-- 🔵 BLOQUE PLACEHOLDER PARA CUANDO NO HAY IMÁGENES -->
                    <div id="post-no-image" class="modal-img-placeholder hidden">
                        <i class="ri-image-line"></i>
                        <p>No hay imágenes disponibles</p>
                    </div>
                    <!-- 🔵 FIN DEL PLACEHOLDER -->

                    <div class="modal-img-slider" id="post-image-slider">
                        <button class="slider-btn prev-btn"><i class="ri-arrow-left-s-line"></i></button>
                        <div class="slider-main" id="sliderMain">
                            <div class="shimmer shimmer-image" id="shimmerPlaceholder"></div>
                            <img src="/storage/default.png" id="sliderActiveImage" alt="Imagen principal">
                        </div>
                        <button class="slider-btn next-btn"><i class="ri-arrow-right-s-line"></i></button>
                    </div>
                    <div class="modal-show-actions">
                        <a href="#" id="modalPostEditBtn" class="boton boton-warning">
                            <span class="boton-icon"><i class="ri-edit-circle-fill"></i></span>
                            <span class="boton-text">Editar</span>
                        </a>

                        <form id="modalDeleteForm" action="#" method="POST">
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
            <hr class="w-full my-0 border-default">
            <div class="modal-content-section">
                <h3 class="card-title">Contenido</h3>
                <div id="post-content" class="post-content"></div>
            </div>
        </div>

        <div class="modal-show-footer">
            <button type="button" class="boton boton-modal-close" id="cancelPostButton">
                <span class="boton-icon text-base"><i class="ri-close-line"></i></span>
                <span class="boton-text">Cerrar</span>
            </button>

            <form id="approveForm" method="POST" action="#">
                @csrf
                <button type="submit" class="boton boton-success">
                    <span class="boton-icon"><i class="ri-send-plane-fill"></i> </span>
                    <span class="boton-text">Aprobar Post</span>
                </button>
            </form>

            <form id="rejectForm" method="POST" action="#">
                @csrf
                <button type="submit" class="boton boton-danger">
                    <span class="boton-icon"><i class="ri-close-circle-fill"></i></span>
                    <span class="boton-text">Rechazar Post</span>
                </button>
            </form>
        </div>
    </div>
</div>
@push('scripts')
    <script>
        let postImagesGlobal = [];
        let currentSlideIndex = 0;
        let postSliderInterval;

        // Fallback universal
        const DEFAULT_IMAGE = "/storage/default.png";

        // Reemplaza imágenes rotas automáticamente
        function applyImageFallback($img) {
            $img.on("error", function() {
                $(this).attr("src", DEFAULT_IMAGE);
            });
        }

        // Construye la lista de imágenes con fallback
        function sanitizeImages(images) {
            if (!Array.isArray(images)) return [];

            return images
                .map(img => img?.path?.trim())
                .filter(path => path && path.length > 3) // Debe tener ruta real
                .map(path => ({
                    path
                }));
        }


        function renderPostImagesSlider(imagesRaw) {
            postImagesGlobal = sanitizeImages(imagesRaw);

            const mainImg = $("#sliderActiveImage");
            const thumbContainer = $("#sliderThumbnails");
            const shimmer = $("#shimmerPlaceholder");
            const slider = $("#post-image-slider");
            const placeholder = $("#post-no-image");

            thumbContainer.html("");

            // 🔵 SI NO HAY NINGUNA IMAGEN → MOSTRAR PLACEHOLDER
            if (postImagesGlobal.length === 0) {
                slider.addClass("hidden");
                thumbContainer.addClass("hidden");

                placeholder.removeClass("hidden"); // Mostrar bloque "no hay imágenes"

                mainImg.attr("src", DEFAULT_IMAGE);
                shimmer.hide();
                return;
            }

            // 🔵 SI HAY IMÁGENES → Apagar placeholder y mostrar slider
            placeholder.addClass("hidden");
            slider.removeClass("hidden");
            thumbContainer.removeClass("hidden");

            shimmer.show();
            mainImg.hide();

            currentSlideIndex = 0;

            // Thumbnails
            postImagesGlobal.forEach((img, index) => {
                let thumb;

                const path = img.path?.trim();

                if (!path) {
                    // 🔵 Si la imagen NO existe
                    thumb = $(`<div class="thumbnail-placeholder"><i class="ri-image-line"></i></div>`);
                } else {
                    // Imagen normal
                    thumb = $(`<img src="/storage/${path}" class="thumbnail-img">`);

                    // Fallback automático si la miniatura no carga
                    thumb.on("error", function() {
                        $(this).replaceWith(
                            `<div class="thumbnail-placeholder"><i class="ri-image-line"></i></div>`
                        );
                    });
                }

                // Resaltar la primera miniatura
                if (index === 0) thumb.addClass("active-thumb");

                // Al hacer clic en una miniatura → cambiar de slide
                thumb.on("click", () => {
                    currentSlideIndex = index;
                    showSlide();
                });

                thumbContainer.append(thumb);
            });


            showSlide();

            clearInterval(postSliderInterval);
            postSliderInterval = setInterval(() => {
                currentSlideIndex = (currentSlideIndex + 1) % postImagesGlobal.length;
                showSlide();
            }, 5000);
        }

        function showSlide() {
            const mainImg = $("#sliderActiveImage");
            const shimmer = $("#shimmerPlaceholder");
            const thumbs = $("#sliderThumbnails .thumbnail-img");

            if (postImagesGlobal.length === 0) {
                mainImg.attr("src", DEFAULT_IMAGE);
                shimmer.hide();
                return;
            }

            let imgPath = postImagesGlobal[currentSlideIndex]?.path || DEFAULT_IMAGE;

            mainImg.attr("src", `/storage/${imgPath}`);
            applyImageFallback(mainImg);

            mainImg.show();
            shimmer.hide();

            thumbs.removeClass("active-thumb");
            $(thumbs[currentSlideIndex]).addClass("active-thumb");
            mainImg.on("error", function() {
                $(this).attr("src", DEFAULT_IMAGE);
            });

        }

        // Botones slider
        $(".prev-btn").on("click", () => {
            if (postImagesGlobal.length) {
                currentSlideIndex = (currentSlideIndex - 1 + postImagesGlobal.length) % postImagesGlobal.length;
                showSlide();
            }
        });

        $(".next-btn").on("click", () => {
            if (postImagesGlobal.length) {
                currentSlideIndex = (currentSlideIndex + 1) % postImagesGlobal.length;
                showSlide();
            }
        });

        // ========== MODAL FUNCTIONS ==========
        function openPostModal() {
            $("#modalShow").removeClass("hidden");
            $(".modal-content").removeClass("animate-out").addClass("animate-in");
            $('#modalShow').appendTo('body');

            // 🔵 IMPORTANTE: ocultar botones desde ya
            $("#approveForm").hide();
            $("#rejectForm").hide();

            document.addEventListener("keydown", escPostListener);
            document.addEventListener("mousedown", clickOutsideShowListener);
        }


        function closeModal() {
            $(".modal-content").removeClass("animate-in").addClass("animate-out");
            setTimeout(() => {
                $("#modalShow").addClass("hidden");
                setLoadingPostFields();
                document.removeEventListener("keydown", escPostListener);
                document.removeEventListener("mousedown", clickOutsideShowListener);
            }, 250);
        }

        function setLoadingPostFields() {
            const fields = [
                "#post-id", "#post-slug", "#post-title", "#post-title-cell", "#post-content",
                "#post-status", "#post-visibility", "#post-views", "#post-allow-comments",
                "#post-published-at", "#post-tags", "#post-created-by", "#post-updated-by", "#post-reviewed-by"
            ];

            fields.forEach(f => $(f).html('<div class="shimmer shimmer-cell"></div>'));

            $("#sliderActiveImage").attr("src", DEFAULT_IMAGE);
            $("#sliderThumbnails").html("");
        }

        function renderPostModal(data) {

            function getStatusBadge(status) {
                const map = {
                    draft: {
                        text: '<i class="ri-pencil-line"></i> Borrador',
                        class: 'badge-gray'
                    },
                    pending: {
                        text: '<i class="ri-time-line"></i> Pendiente',
                        class: 'badge-warning'
                    },
                    published: {
                        text: '<i class="ri-check-line"></i> Publicado',
                        class: 'badge-success'
                    },
                    rejected: {
                        text: '<i class="ri-close-line"></i> Rechazado',
                        class: 'badge-danger'
                    }
                };

                const item = map[status] || {
                    text: status ? status : "Desconocido",
                    class: "badge-secondary"
                };

                return `<span class="badge ${item.class}">${item.text}</span>`;
            }

            $("#post-id").text(data.id);
            $("#post-slug").html(`<span class="badge badge-primary">${data.slug}</span>`);
            $("#post-title").text(data.title);
            $("#post-title-cell").text(data.title);
            $("#post-content").html(data.content ?? "Sin contenido");

            $("#post-status").html(getStatusBadge(data.status));

            $("#post-visibility").text(data.visibility);
            $("#post-views").text(data.views);

            $("#post-allow-comments").html(
                data.allow_comments ?
                `<span class="badge boton-success"><i class="ri-checkbox-circle-line"></i> Sí</span>` :
                `<span class="badge boton-danger"><i class="ri-close-circle-line"></i> No</span>`
            );

            $("#post-published-at").text(data.published_at ?? "—");

            // Tags
            $("#post-tags").html(
                data.tags.length ?
                data.tags.map(t => `<span class="badge badge-warning">${t}</span>`).join(" ") :
                "—"
            );

            // Construcción de imágenes principal + adicionales
            let images = [];

            if (data.image && data.image.trim() !== "") {
                images.push({
                    path: data.image
                });
            }

            if (Array.isArray(data.images)) {
                images = images.concat(sanitizeImages(data.images));
            }

            $("#post-created-by").html(`
                <div class="show-cell-content">
                    <span class="font-bold">${data.created_by_name}</span>
                    <span class="show-date"><i class="ri-time-fill"></i> ${data.created_at}</span>
                </div>
                `);
            $("#post-updated-by").html(`
                <div class="show-cell-content">
                    <span class="font-bold">${data.updated_by_name}</span>
                    <span class="show-date"><i class="ri-time-fill"></i> ${data.updated_at}</span>
                </div>
                `);
            $("#post-reviewed-by").html(`
                <div class="show-cell-content">
                    <span class="font-bold">${data.reviewed_by_name}</span>
                    <span class="show-date"><i class="ri-time-fill"></i> ${data.reviewed_at}</span>
                </div>
                `);

            $("#modalPostEditBtn").attr("href", `/admin/posts/${data.slug}/edit`);
            $("#modalDeleteForm").attr("action", `/admin/posts/${data.slug}`);

            $("#approveForm").attr("action", `/admin/posts/${data.slug}/approve`);
            $("#rejectForm").attr("action", `/admin/posts/${data.slug}/reject`);

            if (data.status === "pending") {
                $("#approveForm").show();
                $("#rejectForm").show();
            } else {
                $("#approveForm").hide();
                $("#rejectForm").hide();
            }

            renderPostImagesSlider(images);
        }

        function loadPostModal(slug) {
            setLoadingPostFields();
            openPostModal();

            $.ajax({
                url: `/admin/posts/${slug}/show`,
                method: "GET",
                success: data => renderPostModal(data),
                error: () => $("#post-title").text("Error al cargar datos")
            });
        }

        $(document).on("click", ".btn-ver-post", function() {
            loadPostModal($(this).data("slug"));
        });

        $("#closeModal, #cancelPostButton").on("click", closeModal);

        function escPostListener(e) {
            if (e.key === "Escape") closeModal();
        }

        // Cerrar modal haciendo clic fuera
        function clickOutsideShowListener(e) {
            const overlay = document.getElementById('modalShow');
            const content = document.querySelector('#modalShow .modal-content');

            // Clic directo en el overlay (no en modal-content)
            if (e.target === overlay) {
                closeModal();
            }
        }

        $(document).on("submit", "#rejectForm", function(e) {
            e.preventDefault();
            const form = this;

            // Desactiva el cierre por clic afuera temporalmente
            document.removeEventListener("click", clickOutsideShowListener);

            window.showConfirm({
                type: "danger",
                header: "Rechazar Post",
                title: "¿Estás seguro?",
                message: 'Vas a rechazar el post <strong>' + $('#post-title').text() + '</strong>.',
                confirmText: "Sí, rechazar",
                cancelText: "No, cancelar",
                onConfirm: function() {
                    document.addEventListener('click', clickOutsideShowListener);
                    form.submit();
                },
                onCancel: function() {
                    // Restablecer cierre por clic afuera
                    restoreOutsideClick();
                }
            });
        });


        $(document).on("submit", "#approveForm", function(e) {
            e.preventDefault();
            const form = this;

            // Desactiva cierre por clic afuera
            document.removeEventListener("click", clickOutsideShowListener);

            window.showConfirm({
                type: "success",
                header: "Aprobar Post",
                title: "¿Confirmar aprobación?",
                message: 'Vas a aprobar el post <strong>' + $('#post-title').text() + '</strong>.',
                confirmText: "Sí, aprobar",
                cancelText: "Cancelar",
                onConfirm: function() {
                    document.addEventListener('click', clickOutsideShowListener);
                    form.submit();
                },
                onCancel: function() {
                    // Restablecer cierre por clic afuera
                    restoreOutsideClick();
                }
            });
        });


        // Confirmación antes de eliminar
        $(document).on("submit", "#modalDeleteForm", function(e) {
            e.preventDefault();
            const form = this;

            // Desactivar cierre por clic afuera mientras se muestra la confirmación
            document.removeEventListener('click', clickOutsideShowListener);

            window.showConfirm({
                type: "danger",
                header: "Eliminar Post",
                title: "¿Estás seguro?",
                message: 'Esta acción no se puede deshacer.<br>Se eliminará el post <strong>' +
                    $('#post-title').text() + '</strong> del sistema.',
                confirmText: "Sí, eliminar",
                cancelText: "No, cancelar",
                onConfirm: function() {
                    // Restablecer cierre por clic afuera
                    document.addEventListener('click', clickOutsideShowListener);
                    form.submit();
                },
                onCancel: function() {
                    // Restablecer cierre por clic afuera
                    restoreOutsideClick();
                }
            });
        });

        function restoreOutsideClick() {
            document.addEventListener('click', clickOutsideShowListener);
        }
    </script>
@endpush
