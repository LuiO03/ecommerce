<!-- Modal para mostrar datos completos del usuario -->
<div id="modalShow" class="modal-show hidden modal-horizontal">
    <div class="modal-content">
        <div class="modal-show-header" id="modalHeader">
            <h6>Detalles del Usuario</h6>
            <button type="button" id="closeModal" class="confirm-close ripple-btn">
                <i class="ri-close-line"></i>
            </button>
        </div>

        <h3 class="modal-title-body" id="user-name-title">Sin nombre</h3>
        <div class="modal-show-body">
            <div class="modal-show-row">
                <div class="modal-img-container">
                    <div id="user-image">-</div>
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
                        <td id="user-id">-</td>
                    </tr>
                    <tr>
                        <th>Slug</th>
                        <td id="user-slug">-</td>
                    </tr>
                    <tr>
                        <th>Nombre completo</th>
                        <td id="user-fullname">-</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td id="user-email">-</td>
                    </tr>
                    <tr>
                        <th>Rol</th>
                        <td id="user-role">-</td>
                    </tr>
                    <tr>
                        <th>DNI</th>
                        <td id="user-dni">-</td>
                    </tr>
                    <tr>
                        <th>Teléfono</th>
                        <td id="user-phone">-</td>
                    </tr>
                    <tr>
                        <th>Dirección</th>
                        <td id="user-address">-</td>
                    </tr>
                    <tr>
                        <th>Estado</th>
                        <td id="user-status">-</td>
                    </tr>
                    <tr>
                        <th>Email verificado</th>
                        <td id="user-email-verif">-</td>
                    </tr>
                    <tr>
                        <th>Última sesión</th>
                        <td id="user-last-login-at">-</td>
                    </tr>
                    <tr>
                        <th>Creado por</th>
                        <td id="user-created-by-fecha">-</td>
                    </tr>
                    <tr>
                        <th>Actualizado por</th>
                        <td id="user-updated-by-fecha">-</td>
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
            $('#user-id').html('<div class="shimmer shimmer-cell"></div>');
            $('#user-slug').html('<div class="shimmer shimmer-cell" style="width:80px;"></div>');
            $('#user-name-title').html('<div class="shimmer shimmer-cell shimmer-title" style="width:150px;"></div>');
            $('#user-fullname').html('<div class="shimmer shimmer-cell" style="width:120px;"></div>');
            $('#user-email').html('<div class="shimmer shimmer-cell" style="width:160px;"></div>');
            $('#user-role').html('<div class="shimmer shimmer-cell" style="width:90px;"></div>');
            $('#user-dni').html('<div class="shimmer shimmer-cell" style="width:70px;"></div>');
            $('#user-phone').html('<div class="shimmer shimmer-cell" style="width:80px;"></div>');
            $('#user-address').html('<div class="shimmer shimmer-cell" style="width:140px;"></div>');
            $('#user-status').html('<div class="shimmer shimmer-cell" style="width:80px;"></div>');
            $('#user-email-verif').html('<div class="shimmer shimmer-cell" style="width:100px;"></div>');
            $('#user-image').html('<div class="shimmer shimmer-img"></div>');
            $('#user-created-by-fecha').html('<div class="shimmer shimmer-cell" style="width:120px;"></div>');
            $('#user-updated-by-fecha').html('<div class="shimmer shimmer-cell" style="width:120px;"></div>');
        }

        $(document).on('click', '.btn-ver-usuario', function() {

            setLoadingUserFields();
            openUserModal();

            const slug = $(this).data('slug');

            $.ajax({
                url: `/admin/users/${slug}/show`,
                method: 'GET',
                success: function(data) {

                    $('#user-id').text(data.id ?? '-');
                    $('#user-slug').html(
                        `<span class='badge badge-primary slug-mono'>${data.slug}</span>`);

                    $('#user-name-title').text(data.name);
                    $('#user-fullname').text(`${data.name ?? ''} ${data.last_name ?? ''}`);

                    $('#user-role').text(data.role ?? '-');

                    // Email
                    if (!data.email) {
                        $('#user-email').html(
                            '<span class="text-muted-null">Sin email</span>');
                    } else {
                        $('#user-email').text(data.email);
                    }

                    // Última sesión
                    $('#user-last-login-at').text(data.last_login_at ?? '—');

                    // Descripción
                    if (!data.address) {
                        $('#user-address').html(
                            '<span class="text-muted-null">Sin dirección registrada</span>');
                    } else {
                        $('#user-address').text(data.address);
                    }

                    // Teléfono
                    if (!data.phone) {
                        $('#user-phone').html(
                            '<span class="text-muted-null">Sin teléfono registrado</span>');
                    } else {
                        $('#user-phone').text(data.phone);
                    }

                    // Dni
                    if (!data.dni) {
                        $('#user-dni').html(
                            '<span class="text-muted-null">Sin DNI registrado</span>');
                    } else {
                        $('#user-dni').text(data.dni);
                    }

                    if (data.status) {
                        $('#user-status').html(
                            '<span class="badge boton-success"><i class="ri-eye-fill"></i> Activo</span>'
                        );
                    } else {
                        $('#user-status').html(
                            '<span class="badge boton-danger"><i class="ri-eye-off-fill"></i> Inactivo</span>'
                        );
                    }

                    if (data.email_verified_at) {
                        $('#user-email-verif').html(
                            '<span class="badge badge-success"><i class="ri-checkbox-circle-fill"></i> Verificado</span>'
                            );
                    } else {
                        $('#user-email-verif').html(
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
                            $('#user-image').html(`
                            <div class="modal-foto-placeholder">
                                <i class="ri-file-close-fill"></i> Imagen no disponible
                            </div>
                        `);
                        };

                        img.onload = function() {
                            $('#user-image').html(img);
                        };

                    } else {
                        $('#user-image').html(`
                        <div class="modal-foto-placeholder">
                            <i class="ri-image-add-fill"></i> Sin imagen
                        </div>
                    `);
                    }

                    // Creado por
                    $('#user-created-by-fecha').html(`
                        <div class="show-cell-content">
                            <span class="font-bold">${data.created_by_name}</span>
                            <span class="show-date"><i class="ri-time-fill"></i> ${data.created_at}</span>
                        </div>
                    `);

                    // Actualizado por
                    const updatedLabel = data.updated_at_human ?? data.updated_at ?? '—';

                    $('#user-updated-by-fecha').html(`
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
                    const userNumericId = typeof data.raw_id !== 'undefined' ? Number(data.raw_id) : null;

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
                    $('#user-name-title').text('Error');
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
                    $('#user-fullname').text() + '</strong>.',
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
