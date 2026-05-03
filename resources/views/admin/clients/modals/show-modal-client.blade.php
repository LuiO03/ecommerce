<!-- Modal para mostrar datos completos del usuario -->
<div id="modalShow" class="modal-show hidden modal-horizontal">
    <div class="modal-content">
        <div class="modal-show-header" id="modalHeader">
            <h6>Detalles del Cliente</h6>
            <button type="button" id="closeModal" class="confirm-close ripple-btn">
                <i class="ri-close-line"></i>
            </button>
        </div>

        <h3 class="modal-title-body" id="client-name-title">Sin nombre</h3>
        <div class="modal-show-body">
            <div class="modal-show-row">
                <div class="modal-img-container">
                    <div id="client-image">-</div>
                    <div class="modal-show-actions">
                        <a href="#" id="modalUserEditBtn" class="boton boton-warning" title="Editar usuario">
                            <span class="boton-icon"><i class="ri-edit-circle-fill"></i></span>
                            <span class="boton-text">Editar</span>
                        </a>
                        <form id="modalDeleteForm" action="#" method="POST" title="Eliminar usuario">
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
                        <td id="client-id">-</td>
                    </tr>
                    <tr>
                        <th>Slug</th>
                        <td id="client-slug">-</td>
                    </tr>
                    <tr>
                        <th>Nombre completo</th>
                        <td id="client-fullname">-</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td id="client-email">-</td>
                    </tr>
                    <tr>
                        <th>Rol</th>
                        <td id="client-role">-</td>
                    </tr>
                    <tr>
                        <th>Tipo de documento</th>
                        <td id="client-document-type">-</td>
                    </tr>
                    <tr>
                        <th>N° documento</th>
                        <td id="client-document-number">-</td>
                    </tr>
                    <tr>
                        <th>Teléfono</th>
                        <td id="client-phone">-</td>
                    </tr>
                    <tr>
                        <th>Direcciones</th>
                        <td id="client-addresses">-</td>
                    </tr>

                    <tr>
                        <th>Órdenes</th>
                        <td id="client-orders">-</td>
                    </tr>

                    <tr>
                        <th>Total gastado</th>
                        <td id="client-spent">-</td>
                    </tr>

                    <tr>
                        <th>Última compra</th>
                        <td id="client-last-order">-</td>
                    </tr>
                    <tr>
                        <th>Estado</th>
                        <td id="client-status">-</td>
                    </tr>
                    <tr>
                        <th>Email verificado</th>
                        <td id="client-email-verif">-</td>
                    </tr>
                    <tr>
                        <th>Última sesión</th>
                        <td id="client-last-login-at">-</td>
                    </tr>
                    <tr>
                        <th>Creado por</th>
                        <td id="client-created-by-fecha">-</td>
                    </tr>
                    <tr>
                        <th>Actualizado por</th>
                        <td id="client-updated-by-fecha">-</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="modal-show-footer">
            <button type="button" class="boton boton-modal-close" id="cancelUserButton" title="Cerrar Ventana">
                <span class="boton-icon text-base"><i class="ri-close-line"></i></span>
                <span class="boton-text">Cerrar</span>
            </button>
        </div>
    </div>
</div>
@push('scripts')
    <script>
        const AUTH_USER_ID = @json(Auth::id());

        function openUserModal() {
            $('#modalShow').removeClass('hidden');
            $('.modal-content').removeClass('animate-out').addClass('animate-in');
            $('#modalShow').appendTo('body');

            document.addEventListener('keydown', escUserListener);
            document.addEventListener('mousedown', clickOutsideShowListener);
        }

        function closeModal() {
            $('.modal-content').removeClass('animate-in').addClass('animate-out');
            setTimeout(function() {
                $('#modalShow').addClass('hidden');
                setLoadingUserFields();
                document.removeEventListener('keydown', escUserListener);
                document.removeEventListener('mousedown', clickOutsideShowListener);
            }, 250);
        }

        function setLoadingUserFields() {
            $('#client-id').html('<div class="shimmer shimmer-cell"></div>');
            $('#client-slug').html('<div class="shimmer shimmer-cell" style="width:80px;"></div>');
            $('#client-name-title').html('<div class="shimmer shimmer-cell shimmer-title" style="width:150px;"></div>');
            $('#client-fullname').html('<div class="shimmer shimmer-cell" style="width:120px;"></div>');
            $('#client-email').html('<div class="shimmer shimmer-cell" style="width:160px;"></div>');
            $('#client-role').html('<div class="shimmer shimmer-cell" style="width:90px;"></div>');
            $('#client-document-type').html('<div class="shimmer shimmer-cell" style="width:90px;"></div>');
            $('#client-document-number').html('<div class="shimmer shimmer-cell" style="width:110px;"></div>');
            $('#client-phone').html('<div class="shimmer shimmer-cell" style="width:80px;"></div>');
            $('#client-addresses').html('<div class="shimmer shimmer-cell" style="width:140px;"></div>');
            $('#client-orders').html('<div class="shimmer shimmer-cell" style="width:80px;"></div>');
            $('#client-spent').html('<div class="shimmer shimmer-cell" style="width:100px;"></div>');
            $('#client-last-order').html('<div class="shimmer shimmer-cell" style="width:120px;"></div>');
            $('#client-status').html('<div class="shimmer shimmer-cell" style="width:80px;"></div>');
            $('#client-email-verif').html('<div class="shimmer shimmer-cell" style="width:100px;"></div>');
            $('#client-image').html('<div class="shimmer shimmer-img"></div>');
            $('#client-created-by-fecha').html('<div class="shimmer shimmer-cell" style="width:120px;"></div>');
            $('#client-updated-by-fecha').html('<div class="shimmer shimmer-cell" style="width:120px;"></div>');
        }

        $(document).on('click', '.btn-ver-usuario', function() {

            setLoadingUserFields();
            openUserModal();

            const slug = $(this).data('slug');

            $.ajax({
                url: `/admin/clients/${slug}/show`,
                method: 'GET',
                success: function(data) {

                    $('#client-id').text(data.id ?? '-');
                    $('#client-slug').html(
                        `<span class='slug-mono'>${data.slug}</span>`);

                    $('#client-name-title').text(data.name);
                    $('#client-fullname').text(`${data.name ?? ''} ${data.last_name ?? ''}`);

                    $('#client-role').text(data.role ?? '-');

                    // Tipo de documento
                    if (!data.document_type) {
                        $('#client-document-type').html(
                            '<span class="text-muted-null">Sin tipo de documento</span>'
                        );
                    } else {
                        $('#client-document-type').text(data.document_type);
                    }

                    // Número de documento
                    if (!data.document_number) {
                        $('#client-document-number').html(
                            '<span class="text-muted-null">Sin número de documento</span>'
                        );
                    } else {
                        $('#client-document-number').text(data.document_number);
                    }

                    // Email
                    if (!data.email) {
                        $('#client-email').html(
                            '<span class="text-muted-null">Sin email</span>');
                    } else {
                        $('#client-email').text(data.email);
                    }

                    // Descripción
                    $('#client-orders').html(
                        `<span class="badge badge-info">${data.orders_count}</span>`
                    );

                    $('#client-addresses').html(
                        `<span class="badge badge-primary">${data.addresses_count}</span>`
                    );

                    $('#client-spent').text('S/ ' + data.total_spent);

                    $('#client-last-order').text(data.last_order_at ?? 'Sin compras');

                    // Teléfono
                    if (!data.phone) {
                        $('#client-phone').html(
                            '<span class="text-muted-null">Sin teléfono registrado</span>');
                    } else {
                        $('#client-phone').text(data.phone);
                    }

                    if (data.status) {
                        $('#client-status').html(
                            '<span class="badge boton-success"><i class="ri-eye-fill"></i> Activo</span>'
                        );
                    } else {
                        $('#client-status').html(
                            '<span class="badge boton-danger"><i class="ri-eye-off-fill"></i> Inactivo</span>'
                        );
                    }

                    if (data.email_verified_at) {
                        $('#client-email-verif').html(
                            '<span class="badge badge-success"><i class="ri-checkbox-circle-fill"></i> Verificado</span>'
                        );
                    } else {
                        $('#client-email-verif').html(
                            '<span class="badge badge-warning"><i class="ri-close-circle-fill"></i> No verificado</span>'
                        );
                    }

                    // Imagen
                    if (data.image) {
                        const img = new Image();
                        img.src = `/storage/${data.image}`;
                        img.className = 'modal-img';
                        img.alt = 'Foto del usuario';

                        img.onerror = function() {
                            $('#client-image').html(`
                            <div class="modal-foto-placeholder">
                                <i class="ri-file-close-fill"></i> Imagen no disponible
                            </div>
                        `);
                        };

                        img.onload = function() {
                            $('#client-image').html(img);
                        };

                    } else {
                        $('#client-image').html(`
                        <div class="modal-foto-placeholder">
                            <i class="ri-image-add-fill"></i> Sin imagen
                        </div>
                    `);
                    }

                    // Última sesión
                    $('#client-last-login-at').html(`
                        <span class="show-date"><i class="ri-time-fill"></i> ${data.last_login_at_human ?? '—'}</span>
                    `);

                    // Creado por
                    $('#client-created-by-fecha').html(`
                        <div class="show-cell-content">
                            <span class="font-bold">${data.created_by_name}</span>
                            <span class="show-date"><i class="ri-time-fill"></i> ${data.created_at}</span>
                        </div>
                    `);

                    // Actualizado por
                    const updatedLabel = data.updated_at_human ?? data.updated_at ?? '—';

                    $('#client-updated-by-fecha').html(`
                        <div class="show-cell-content">
                            <span class="font-bold">${data.updated_by_name}</span>
                            <span class="show-date"><i class="ri-time-fill"></i> ${updatedLabel}</span>
                        </div>
                    `);

                    // Botón editar
                    $('#modalUserEditBtn').attr('href', `/admin/users/${data.slug}/edit`);

                    // Botón eliminar
                    $('#modalDeleteForm').attr('action', `/admin/users/${data.slug}`);

                    const deleteBtn = $('#modalDeleteForm button[type="submit"]');
                    const deleteForm = $('#modalDeleteForm');
                    const userNumericId = typeof data.raw_id !== 'undefined' ? Number(data.raw_id) :
                        null;

                    if (userNumericId !== null && userNumericId === Number(AUTH_USER_ID)) {
                        deleteBtn.prop('disabled', true).addClass('disabled');
                        deleteBtn.attr('title', 'No puedes eliminar tu propia cuenta');
                        deleteForm.addClass('self-delete-disabled');
                    } else {
                        deleteBtn.prop('disabled', false).removeClass('disabled');
                        deleteBtn.attr('title', 'Eliminar usuario');
                        deleteForm.removeClass('self-delete-disabled');
                    }
                },

                error: function() {
                    $('#client-name-title').text('Error');
                }
            });
        });

        $('#cancelUserButton').on('click', closeModal);
        $('#closeModal').on('click', closeModal);

        function escUserListener(e) {
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

        // Confirmación eliminar
        $(document).on('submit', '#modalDeleteForm', function(e) {
            e.preventDefault();
            const form = this;

            if ($(form).hasClass('self-delete-disabled')) {
                return;
            }

            // Desactivar cierre por clic afuera mientras se muestra la confirmación
            document.removeEventListener('click', clickOutsideShowListener);

            window.showConfirm({
                type: 'danger',
                header: 'Eliminar usuario',
                title: '¿Estás seguro?',
                message: 'Esta acción no se puede deshacer.<br>Se eliminará el usuario <strong>' +
                    $('#client-fullname').text() + '</strong>.',
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
