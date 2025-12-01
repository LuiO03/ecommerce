<!-- Modal para mostrar datos completos de la categoría -->
<div id="modalCategoryShow" class="modal-show hidden modal-horizontal">
    <div class="modal-content">
        <div class="modal-show-header" id="modalHeader">
            <h6>Detalles de la Categoría</h6>
            <button type="button" id="closeCategoryModal" class="confirm-close ripple-btn">
                <i class="ri-close-line"></i>
            </button>
        </div>

        <h3 class="modal-title-body" id="category-name-title">Sin nombre</h3>
        <div class="modal-show-body">
            <div class="modal-img-container">
                <div id="category-image">-</div>
                <div class="modal-show-actions">
                    <a href="#" id="modalCategoryEditBtn" class="boton boton-warning" title="Editar categoría">
                        <span class="boton-icon"><i class="ri-edit-circle-fill"></i></span>
                        <span class="boton-text">Editar</span>
                    </a>

                    <form id="modalCategoryDeleteForm" action="#" method="POST" title="Eliminar categoría">
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
                    <td id="category-id">-</td>
                </tr>
                <tr>
                    <th>Slug</th>
                    <td id="category-slug">-</td>
                </tr>
                <tr>
                    <th>Nombre</th>
                    <td id="category-name">-</td>
                </tr>
                <tr>
                    <th>Descripción</th>
                    <td id="category-description">-</td>
                </tr>
                <tr>
                    <th>Familia</th>
                    <td id="category-family">-</td>
                </tr>
                <tr>
                    <th>Padre</th>
                    <td id="category-parent">-</td>
                </tr>
                <tr>
                    <th>Estado</th>
                    <td id="category-status">-</td>
                </tr>
                <tr>
                    <th>Creado por</th>
                    <td id="category-created-by-fecha">-</td>
                </tr>
                <tr>
                    <th>Actualizado por</th>
                    <td id="category-updated-by-fecha">-</td>
                </tr>
            </table>
        </div>
        <div class="modal-show-footer">
            <button type="button" class="boton boton-modal-close" id="cancelUserButton">
                <span class="boton-icon text-base"><i class="ri-close-line"></i></span>
                <span class="boton-text">Cerrar</span>
            </button>
        </div>
    </div>
</div>
</div>
@push('scripts')
    <script>
        function openCategoryModal() {
            $('#modalCategoryShow').removeClass('hidden');
            $('.modal-content').removeClass('animate-out').addClass('animate-in');
            $('#modalCategoryShow').appendTo('body');
            document.addEventListener('keydown', escCategoryListener);
            document.addEventListener('mousedown', clickOutsideCategoryListener);
        }

        function closeCategoryModal() {
            $('.modal-content').removeClass('animate-in').addClass('animate-out');
            setTimeout(function() {
                $('#modalCategoryShow').addClass('hidden');
                setLoadingCategoryFields();
                document.removeEventListener('keydown', escCategoryListener);
                document.removeEventListener('mousedown', clickOutsideCategoryListener);
            }, 250);
        }

        function setLoadingCategoryFields() {
            $('#category-id').html('<div class="shimmer shimmer-cell"></div>');
            $('#category-slug').html('<div class="shimmer shimmer-cell" style="width:80px;"></div>');
            $('#category-name-title').html('<div class="shimmer shimmer-cell shimmer-title" style="width:150px;"></div>');
            $('#category-name').html('<div class="shimmer shimmer-cell" style="width:120px;"></div>');
            $('#category-description').html('<div class="shimmer shimmer-cell" style="width:160px;"></div>');
            $('#category-family').html('<div class="shimmer shimmer-cell" style="width:90px;"></div>');
            $('#category-parent').html('<div class="shimmer shimmer-cell" style="width:90px;"></div>');
            $('#category-status').html('<div class="shimmer shimmer-cell" style="width:80px;"></div>');
            $('#category-image').html('<div class="shimmer shimmer-img"></div>');
            $('#category-created-by-fecha').html('<div class="shimmer shimmer-cell" style="width:120px;"></div>');
            $('#category-updated-by-fecha').html('<div class="shimmer shimmer-cell" style="width:120px;"></div>');

        }

        $(document).on('click', '.btn-ver-categoria', function() {
            setLoadingCategoryFields();
            openCategoryModal();
            const slug = $(this).data('slug');
            $.ajax({
                url: `/admin/categories/${slug}/show`,
                method: 'GET',
                success: function(data) {
                    $('#category-id').text(data.id ?? '-');
                    $('#category-slug').html(
                        `<span class='badge badge-primary slug-mono'>${data.slug}</span>`);
                    $('#category-name-title').text(data.name);
                    $('#category-name').text(data.name);
                    $('#category-description').text(data.description ?? 'Sin descripción');
                    $('#category-family').text(data.family ?? 'Sin familia');
                    $('#category-parent').text(data.parent ?? 'Sin padre');
                    if (data.status) {
                        $('#category-status').html(
                            '<span class="badge boton-success"><i class="ri-eye-fill"></i> Activa</span>'
                            );
                    } else {
                        $('#category-status').html(
                            '<span class="badge boton-danger"><i class="ri-eye-off-fill"></i> Inactiva</span>'
                            );
                    }
                    if (data.image) {
                        const img = new Image();
                        img.src = `/storage/${data.image}`;
                        img.className = 'modal-img';
                        img.alt = 'Imagen de la categoría';
                        img.onerror = function() {
                            $('#category-image').html(
                                `<div class="modal-img-placeholder"><i class="ri-file-close-fill"></i> Imagen no disponible</div>`
                                );
                        };
                        img.onload = function() {
                            $('#category-image').html(img);
                        };
                    } else {
                        $('#category-image').html(
                            `<div class="modal-img-placeholder"><i class="ri-image-add-fill"></i> Sin imagen</div>`
                            );
                    }
                    // Creado por
                    $('#category-created-by-fecha').html(`
                        <div class="show-cell-content">
                            <span class="font-bold">${data.created_by_name}</span>
                            <span class="show-date"><i class="ri-time-fill"></i> ${data.created_at}</span>
                        </div>
                    `);

                    // Actualizado por
                    $('#category-updated-by-fecha').html(`
                        <div class="show-cell-content">
                            <span class="font-bold">${data.updated_by_name}</span>
                            <span class="show-date"><i class="ri-time-fill"></i> ${data.updated_at}</span>
                        </div>
                    `);
                    // Botón editar
                    $('#modalCategoryEditBtn').attr('href', `/admin/categories/${data.slug}/edit`);
                    // Botón eliminar
                    $('#modalCategoryDeleteForm').attr('action', `/admin/categories/${data.slug}`);
                },
                error: function() {
                    $('#category-name-title').text('Error');
                }
            });
        });

        $('#closeCategoryModal').on('click', closeCategoryModal);

        function escCategoryListener(e) {
            if (e.key === "Escape") closeCategoryModal();
        }

        function clickOutsideCategoryListener(e) {
            const modal = document.querySelector('#modalCategoryShow .modal-content');
            if (modal && !modal.contains(e.target)) {
                closeCategoryModal();
            }
        }

        // Confirmación eliminar
        $(document).on('submit', '#modalCategoryDeleteForm', function(e) {
            e.preventDefault();
            const form = this;
            window.showConfirm({
                type: 'danger',
                header: 'Eliminar categoría',
                title: '¿Estás seguro?',
                message: 'Esta acción no se puede deshacer.<br>Se eliminará la categoría <strong>' + $(
                    '#category-name').text() + '</strong>.',
                confirmText: 'Sí, eliminar',
                cancelText: 'No, cancelar',
                onConfirm: function() {
                    form.submit();
                }
            });
        });
    </script>
@endpush
