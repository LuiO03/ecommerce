<!-- Modal para mostrar datos completos de la familia -->
<div id="modalShow" class="modal-show hidden">
    <div class="modal-content">
        <div class="modal-show-header" id="modalHeader">
            <h6>Detalles de la Familia</h6>
            <button type="button" id="closeModal" class="confirm-close ripple-btn">
                <i class="ri-close-line"></i>
            </button>
        </div>
        <div class="modal-show-body">
            <h3 class="modal-title-body" id="fam-name">Sin título</h3>
            <table class="modal-show-table">
                <tr>
                    <th>ID</th>
                    <td id="fam-id">-</td>
                </tr>
                <tr>
                    <th>Slug</th>
                    <td id="fam-slug">-</td>
                </tr>
                <tr>
                    <th>Descripción</th>
                    <td id="fam-description">-</td>
                </tr>
                <tr>
                    <th>Estado</th>
                    <td id="fam-status">-</td>
                </tr>
                <tr>
                    <th>Creado por</th>
                    <td id="fam-created-by-fecha">-</td>
                </tr>
                <tr>
                    <th>Actualizado por</th>
                    <td id="fam-updated-by-fecha">-</td>
                </tr>
            </table>
            <div class="modal-img-container">
                <div id="fam-image">-</div>
            </div>
            <div class="modal-show-actions">
                <a href="#" id="modalEditBtn" class="boton boton-warning" title="Editar familia">
                    <span class="boton-icon"><i class="ri-edit-circle-fill"></i></span>
                    <span class="boton-text">Editar</span>
                </a>
                <form id="modalDeleteForm" action="#" method="POST" title="Eliminar familia">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="boton boton-danger">
                        <span class="boton-icon"><i class="ri-delete-bin-2-fill"></i></span>
                        <span class="boton-text">Eliminar</span>
                    </button>
                </form>
            </div>
        </div>
        <div class="modal-show-footer">
            <button type="button" class="boton boton-modal-close" id="cancelButton" title="Cerrar Ventana">
                <span class="boton-icon text-base"><i class="ri-close-line"></i></span>
                <span class="boton-text">Cerrar</span>
            </button>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function openmodalShow() {
            $('#modalShow').removeClass('hidden');
            $('.modal-content').removeClass('animate-out').addClass('animate-in');
            $('#modalShow').appendTo('body');
            // Listener ESC solo para esta modal
            document.addEventListener('keydown', escFamiliaListener);
            // Listener click fuera solo para esta modal
            document.addEventListener('mousedown', clickOutsideShowListener);
        }

        function closeModal() {
            $('.modal-content').removeClass('animate-in').addClass('animate-out');
            setTimeout(function() {
                $('#modalShow').addClass('hidden');
                setLoadingModalFields();
                // Remover listeners al cerrar
                document.removeEventListener('keydown', escFamiliaListener);
                document.removeEventListener('mousedown', clickOutsideShowListener);
            }, 250);
        }

        function setLoadingModalFields() {
            // Shimmer para cada celda
            $('#fam-id').html('<div class="shimmer shimmer-cell"></div>');
            $('#fam-slug').html('<div class="shimmer shimmer-cell" style="width:80px;"></div>');
            $('#fam-name').html('<div class="shimmer shimmer-cell shimmer-title" style="width:120px;"></div>');
            $('#fam-description').html('<div class="shimmer shimmer-cell" style="width:160px;"></div>');
            $('#fam-status').html('<div class="shimmer shimmer-cell" style="width:90px;"></div>');
            $('#fam-image').html('<div class="shimmer shimmer-img"></div>');
            $('#fam-created-by-fecha').html('<div class="shimmer shimmer-cell" style="width:120px;"></div>');
            $('#fam-updated-by-fecha').html('<div class="shimmer shimmer-cell" style="width:120px;"></div>');
        }
        $(document).on('click', '.btn-ver-familia', function() {
            setLoadingModalFields();
            openmodalShow();
            const slug = $(this).data('slug');
            $.ajax({
                url: `/admin/families/${slug}/show`,
                method: 'GET',
                success: function(data) {
                    $('#fam-id').text(data.id ?? '-');
                    // Slug tipografía especial
                    $('#fam-slug').html(
                        `<span class='badge badge-primary slug-mono'>${data.slug ?? '-'}</span>`);
                    $('#fam-name').text(data.name ?? '-');
                    // Descripción
                    if (!data.description) {
                        $('#fam-description').html(
                            '<span class="text-muted-null">Sin descripción registrada</span>');
                    } else {
                        $('#fam-description').text(data.description);
                    }
                    // Estado badge
                    if (data.status) {
                        $('#fam-status').html(
                            '<span class="badge boton-success"><i class="ri-eye-fill"></i> Activo</span>'
                        );
                    } else {
                        $('#fam-status').html(
                            '<span class="badge boton-danger"><i class="ri-eye-off-fill"></i> Inactivo</span>'
                        );
                    }
                    // Imagen fuera de la tabla
                    if (data.image) {
                        // Intentar cargar la imagen, si falla mostrar placeholder
                        const img = new Image();
                        img.src = `/storage/${data.image}`;
                        img.className = 'modal-img';
                        img.alt = 'Imagen de la familia';
                        img.onerror = function() {
                            $('#fam-image').html(
                                `<div class='modal-img-placeholder'>
                                    <i class="ri-file-close-fill"></i>
                                    Parece que la imagen ya no existe.
                                </div>`
                            );
                        };
                        img.onload = function() {
                            $('#fam-image').html(img);
                        };
                    } else {
                        $('#fam-image').html(
                            `<div class='modal-img-placeholder'>
                                <i class="ri-image-add-fill"></i>
                                Aún no se ha subido una imagen.
                            </div>`
                        );
                    }

                    // Creado por
                    $('#fam-created-by-fecha').html(`
                        <div class="show-cell-content">
                            <span class="font-bold">${data.created_by_name}</span>
                            <span class="show-date"><i class="ri-time-fill"></i> ${data.created_at}</span>
                        </div>
                    `);

                    // Actualizado por
                    $('#fam-updated-by-fecha').html(`
                        <div class="show-cell-content">
                            <span class="font-bold">${data.updated_by_name}</span>
                            <span class="show-date"><i class="ri-time-fill"></i> ${data.updated_at}</span>
                        </div>
                    `);

                    // Actualizar enlace de Editar
                    $('#modalEditBtn').attr('href', `/admin/families/${data.slug}/edit`);
                    // Eliminar: la acción debe ser /admin/families/{slug} (destroy individual por slug)
                    let famSlug = (data.slug ?? '-');
                    $('#modalDeleteForm').attr('action', `/admin/families/${famSlug}`);
                },
                error: function() {
                    $('#fam-id').text('Error');
                    $('#fam-slug').text('Error');
                    $('#fam-name').text('Error');
                    $('#fam-description').text('Error');
                    $('#fam-status').text('Error');
                    $('#fam-image').html('<span class="text-red-500">Error</span>');
                    $('#fam-created-by-fecha').text('Error');
                    $('#fam-updated-by-fecha').text('Error');
                }
            });
        });
        $('#cancelButton').on('click', closeModal);
        $('#closeModal').on('click', closeModal);

        // Cerrar modal con ESC
        function escFamiliaListener(e) {
            if (e.key === "Escape") {
                closeModal();
            }
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

        // === Eliminar desde la modal ===
        // Eliminar desde la modal: usar la modal de confirmación personalizada
        $(document).on('submit', '#modalDeleteForm', function(e) {
            e.preventDefault();
            const form = this;

            // Desactivar cierre por clic afuera mientras se muestra la confirmación
            document.removeEventListener('click', clickOutsideShowListener);

            window.showConfirm({
                type: 'danger',
                header: 'Eliminar familia',
                title: '¿Estás seguro?',
                message: 'Esta acción no se puede deshacer.<br>Se eliminará la familia <strong>' +
                    $('#fam-name').text() + '</strong> del sistema.',
                confirmText: 'Sí, eliminar',
                cancelText: 'No, cancelar',

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
