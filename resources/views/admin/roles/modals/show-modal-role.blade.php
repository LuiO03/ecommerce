<!-- Modal para mostrar datos completos del rol -->
<div id="modalShow" class="modal-show hidden">
    <div class="modal-content">
        <div class="modal-show-header" id="modalHeader">
            <h6>Detalles del Rol</h6>

            <button type="button" id="closeModal" class="confirm-close ripple-btn">
                <i class="ri-close-line"></i>
            </button>
        </div>

        <div class="modal-show-body">

            <h3 class="modal-title-body" id="role-name">Sin título</h3>

            <table class="modal-show-table">
                <tr>
                    <th>ID</th>
                    <td id="role-id">-</td>
                </tr>

                <tr>
                    <th>Descripción</th>
                    <td id="role-description">-</td>
                </tr>

                <tr>
                    <th>N° Usuarios</th>
                    <td id="role-users-count">-</td>
                </tr>

                <tr>
                    <th>Usuarios con este rol</th>
                    <td>
                        <div id="role-users-list" class="role-users-list">-</div>
                    </td>
                </tr>

                <tr>
                    <th>Creado por</th>
                    <td id="role-created-by">-</td>
                </tr>

                <tr>
                    <th>Actualizado por</th>
                    <td id="role-updated-by">-</td>
                </tr>
            </table>

            <div class="modal-show-actions">

                <a href="#" id="modalEditBtn" class="boton boton-warning" title="Editar rol">
                    <span class="boton-icon">
                        <i class="ri-edit-circle-fill"></i>
                    </span>
                    <span class="boton-text">Editar</span>
                </a>

                <form id="modalDeleteForm" action="#" method="POST" title="Eliminar rol">
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="boton boton-danger">
                        <span class="boton-icon">
                            <i class="ri-delete-bin-2-fill"></i>
                        </span>
                        <span class="boton-text">Eliminar</span>
                    </button>
                </form>

            </div>
        </div>

        <div class="modal-show-footer">
            <button type="button" class="boton boton-modal-close" id="cancelButton">
                <span class="boton-icon text-base">
                    <i class="ri-close-line"></i>
                </span>
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

        document.addEventListener('keydown', escRoleListener);
        document.addEventListener('mousedown', clickOutsideShowListener);
    }

    function closeModal() {
        $('.modal-content').removeClass('animate-in').addClass('animate-out');

        setTimeout(function () {
            $('#modalShow').addClass('hidden');
            setLoadingModalFields();

            document.removeEventListener('keydown', escRoleListener);
            document.removeEventListener('mousedown', clickOutsideShowListener);
        }, 250);
    }

    function setLoadingModalFields() {
        $('#role-id').html('<div class="shimmer shimmer-cell"></div>');
        $('#role-name').html('<div class="shimmer shimmer-cell shimmer-title" style="width:150px;"></div>');
        $('#role-description').html('<div class="shimmer shimmer-cell" style="width:180px;"></div>');
        $('#role-users-count').html('<div class="shimmer shimmer-cell" style="width:80px;"></div>');
        $('#role-users-list').html('<div class="shimmer shimmer-cell" style="width:160px;"></div>');
        $('#role-created-by').html('<div class="shimmer shimmer-cell" style="width:160px;"></div>');
        $('#role-updated-by').html('<div class="shimmer shimmer-cell" style="width:160px;"></div>');
    }

    $(document).on('click', '.btn-ver-rol', function () {

        setLoadingModalFields();
        openmodalShow();

        const id = $(this).data('id');

        $.ajax({
            url: `/admin/roles/${id}/show`,
            method: 'GET',

            success: function (data) {

                $('#role-id').text(data.id ?? '-');
                $('#role-name').text(data.name ?? '-');

                if (!data.description) {
                    $('#role-description').html(
                        '<span class="text-muted-null">Sin descripción registrada</span>'
                    );
                } else {
                    $('#role-description').text(data.description);
                }
                $('#role-users-count').text(data.users_count ?? '-');

                $('#role-users-list').html(`
                    ${data.users && data.users.length > 0
                        ? data.users.map(u => `<span class="badge badge-success"><i class="ri-user-3-fill"></i> ${u}</span>`).join(' ')
                        : '<span class="text-muted-null">No hay usuarios con este rol</span>'
                    }
                `);

                $('#role-created-by').html(`
                    <div class="show-cell-content">
                        <span class="font-bold">${data.created_by_name}</span>
                        <span class="show-date">
                            <i class="ri-time-fill"></i>
                            ${data.created_at}
                        </span>
                    </div>
                `);

                $('#role-updated-by').html(`
                    <div class="show-cell-content">
                        <span class="font-bold">${data.updated_by_name}</span>
                        <span class="show-date">
                            <i class="ri-time-fill"></i>
                            ${data.updated_at_human}
                        </span>
                    </div>
                `);

                $('#modalEditBtn').attr('href', `/admin/roles/${id}/edit`);
                $('#modalDeleteForm').attr('action', `/admin/roles/${id}`);
            },

            error: function () {
                $('#role-id').text('Error');
                $('#role-name').text('Error');
                $('#role-description').text('Error');
                $('#role-users-count').text('Error');
                $('#role-created-by').text('Error');
                $('#role-updated-by').text('Error');
            }
        });
    });

    $('#cancelButton').on('click', closeModal);
    $('#closeModal').on('click', closeModal);

    function escRoleListener(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    }

    function clickOutsideShowListener(e) {
        const overlay = document.getElementById('modalShow');

        if (e.target === overlay) {
            closeModal();
        }
    }

    $(document).on('submit', '#modalDeleteForm', function (e) {
        e.preventDefault();

        const form = this;

        document.removeEventListener('mousedown', clickOutsideShowListener);

        window.showConfirm({
            type: 'danger',
            header: 'Eliminar rol',
            title: '¿Estás seguro?',
            message:
                'Esta acción no se puede deshacer.<br>Se eliminará el rol <strong>' +
                $('#role-name').text() +
                '</strong> del sistema.',

            confirmText: 'Sí, eliminar',
            cancelText: 'No, cancelar',

            onConfirm: function () {
                document.addEventListener('mousedown', clickOutsideShowListener);
                form.submit();
            },

            onCancel: function () {
                document.addEventListener('mousedown', clickOutsideShowListener);
            }
        });
    });
</script>
@endpush
