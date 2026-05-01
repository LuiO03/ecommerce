<div id="modalShowBrand" class="modal-show hidden modal-horizontal">
    <div class="modal-content">
        <div class="modal-show-header" id="modalHeaderBrand">
            <h6>Detalles de la Marca</h6>
            <button type="button" id="closeModalBrand" class="confirm-close ripple-btn">
                <i class="ri-close-line"></i>
            </button>
        </div>

        <h3 class="modal-title-body" id="brand-name-title">Sin nombre</h3>

        <div class="modal-show-body">
            <div class="modal-show-row">
                <div class="modal-img-container">
                    <div id="brand-image">-</div>
                    <div class="modal-show-actions">
                        <a href="#" id="modalBrandEditBtn" class="boton boton-warning" title="Editar marca">
                            <span class="boton-icon"><i class="ri-edit-circle-fill"></i></span>
                            <span class="boton-text">Editar</span>
                        </a>

                        <form id="modalBrandDeleteForm" action="#" method="POST" title="Eliminar marca">
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
                        <td id="brand-id">-</td>
                    </tr>
                    <tr>
                        <th>Slug</th>
                        <td id="brand-slug">-</td>
                    </tr>
                    <tr>
                        <th>Nombre</th>
                        <td id="brand-name">-</td>
                    </tr>
                    <tr>
                        <th>Descripción</th>
                        <td id="brand-description">-</td>
                    </tr>
                    <tr>
                        <th>Productos</th>
                        <td id="brand-products">-</td>
                    </tr>
                    <tr>
                        <th>Estado</th>
                        <td id="brand-status">-</td>
                    </tr>
                    <tr>
                        <th>Creado por</th>
                        <td id="brand-created-by-fecha">-</td>
                    </tr>
                    <tr>
                        <th>Actualizado por</th>
                        <td id="brand-updated-by-fecha">-</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="modal-show-footer">
            <button type="button" class="boton boton-modal-close" id="cancelBrandButton" title="Cerrar Ventana">
                <span class="boton-icon text-base"><i class="ri-close-line"></i></span>
                <span class="boton-text">Cerrar</span>
            </button>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function openBrandModal() {
            $('#modalShowBrand').removeClass('hidden');
            $('.modal-content').removeClass('animate-out').addClass('animate-in');
            $('#modalShowBrand').appendTo('body');
            document.addEventListener('keydown', escBrandListener);
            document.addEventListener('mousedown', clickOutsideBrandListener);
        }

        function closeBrandModal() {
            $('.modal-content').removeClass('animate-in').addClass('animate-out');
            setTimeout(function() {
                $('#modalShowBrand').addClass('hidden');
                setLoadingBrandFields();
                document.removeEventListener('keydown', escBrandListener);
                document.removeEventListener('mousedown', clickOutsideBrandListener);
            }, 250);
        }

        function escBrandListener(e) {
            if (e.key === 'Escape') {
                closeBrandModal();
            }
        }

        function clickOutsideBrandListener(e) {
            const modal = document.querySelector('#modalShowBrand .modal-content');
            if (!modal) return;
            if (!modal.contains(e.target) && !$(e.target).closest('.btn-ver-marca').length) {
                closeBrandModal();
            }
        }

        function setLoadingBrandFields() {
            $('#brand-id').html('<div class="shimmer shimmer-cell"></div>');
            $('#brand-slug').html('<div class="shimmer shimmer-cell" style="width:80px;"></div>');
            $('#brand-name-title').html('<div class="shimmer shimmer-cell shimmer-title" style="width:150px;"></div>');
            $('#brand-name').html('<div class="shimmer shimmer-cell" style="width:120px;"></div>');
            $('#brand-description').html('<div class="shimmer shimmer-cell" style="width:160px;"></div>');
            $('#brand-products').html('<div class="shimmer shimmer-cell" style="width:80px;"></div>');
            $('#brand-status').html('<div class="shimmer shimmer-cell" style="width:80px;"></div>');
            $('#brand-image').html('<div class="shimmer shimmer-img"></div>');
            $('#brand-created-by-fecha').html('<div class="shimmer shimmer-cell" style="width:120px;"></div>');
            $('#brand-updated-by-fecha').html('<div class="shimmer shimmer-cell" style="width:120px;"></div>');
        }

        function renderBrandModal(data) {
            $('#brand-id').text(data.id ?? '-');
            $('#brand-slug').html(`<span class='slug-mono'>${data.slug}</span>`);
            $('#brand-name-title').text(data.name);
            $('#brand-name').text(data.name);
            $('#brand-description').text(data.description ?? 'Sin descripción');
            $('#brand-products').text((data.products_count ?? 0) + ' productos');

            if (data.status) {
                $('#brand-status').html('<span class="badge boton-success"><i class="ri-eye-fill"></i> Activa</span>');
            } else {
                $('#brand-status').html('<span class="badge boton-danger"><i class="ri-eye-off-fill"></i> Inactiva</span>');
            }

            if (data.image) {
                const img = new Image();
                img.src = `/storage/${data.image}`;
                img.className = 'modal-img';
                img.alt = 'Imagen de la marca';
                img.onerror = function() {
                    $('#brand-image').html(`<div class="modal-img-placeholder"><i class="ri-file-close-fill"></i> Imagen no disponible</div>`);
                };
                img.onload = function() {
                    $('#brand-image').html(img);
                };
            } else {
                $('#brand-image').html(`<div class="modal-img-placeholder"><i class="ri-image-add-fill"></i> Sin imagen</div>`);
            }

            $('#brand-created-by-fecha').html(`
                <div class="show-cell-content">
                    <span class="font-bold">${data.created_by_name}</span>
                    <span class="show-date"><i class="ri-time-fill"></i> ${data.created_at}</span>
                </div>
            `);

            const updatedLabel = data.updated_at_human ?? data.updated_at ?? '—';
            $('#brand-updated-by-fecha').html(`
                <div class="show-cell-content">
                    <span class="font-bold">${data.updated_by_name}</span>
                    <span class="show-date"><i class="ri-time-fill"></i> ${updatedLabel}</span>
                </div>
            `);

            $('#modalBrandEditBtn').attr('href', `/admin/brands/${data.slug}/edit`);
            $('#modalBrandDeleteForm').attr('action', `/admin/brands/${data.slug}`);
        }

        function loadBrandModal(slug) {
            setLoadingBrandFields();
            openBrandModal();

            $.ajax({
                url: `/admin/brands/${slug}/show`,
                method: 'GET',
                success: function(data) {
                    renderBrandModal(data);
                },
                error: function() {
                    closeBrandModal();
                }
            });
        }

        $(document).on('click', '#closeModalBrand, #cancelBrandButton', function() {
            closeBrandModal();
        });
    </script>
@endpush
