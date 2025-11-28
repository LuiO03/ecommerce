<!-- Modal para mostrar datos completos de la familia -->
<div id="modalFamilia" class="modal-familia hidden">
    <div class="modal-content">
        <div class="show-modal-header" id="modalHeader">
            <h6>Detalles de la Familia</h6>
            <button type="button" id="closeModal" class="confirm-close ripple-btn">
                <i class="ri-close-line"></i>
            </button>
        </div>
        <span class="card-title" id="fam-titulo">Fondo de perfil</span>
        <div class="show-modal-body">
            <table class="show-modal-table">
                <tr><th>ID</th><td id="fam-id">-</td></tr>
                <tr><th>Slug</th><td id="fam-slug">-</td></tr>
                <tr><th>Nombre</th><td id="fam-name">-</td></tr>
                <tr><th>Descripción</th><td id="fam-description">-</td></tr>
                <tr><th>Estado</th><td id="fam-status">-</td></tr>
                <tr><th>Imagen</th><td id="fam-image">-</td></tr>
                <tr><th>Creado por</th><td id="fam-created-by-fecha">-</td></tr>
                <tr><th>Actualizado por</th><td id="fam-updated-by-fecha">-</td></tr>
            </table>
        </div>
        <div class="show-modal-footer">
            <button type="button" class="boton boton-modal-close" id="cancelButton">
                <span class="boton-icon text-base"><i class="ri-close-line"></i></span>
                <span class="boton-text">Cerrar</span>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openModalFamilia() {
        $('#modalFamilia').removeClass('hidden');
        $('.modal-content').removeClass('animate-out').addClass('animate-in');
        $('#modalFamilia').appendTo('body');
    }
    function closeModal() {
        $('.modal-content').removeClass('animate-in').addClass('animate-out');
        setTimeout(function() {
            $('#modalFamilia').addClass('hidden');
            setLoadingModalFields();
        }, 250);
    }
    function setLoadingModalFields() {
        $('#fam-titulo').html('<span class="loader-modal">Cargando...</span>');
        $('#fam-id').html('<span class="loader-modal">Cargando...</span>');
        $('#fam-slug').html('<span class="loader-modal">Cargando...</span>');
        $('#fam-name').html('<span class="loader-modal">Cargando...</span>');
        $('#fam-description').html('<span class="loader-modal">Cargando...</span>');
        $('#fam-status').html('<span class="loader-modal">Cargando...</span>');
        $('#fam-image').html('<span class="loader-modal">Cargando...</span>');
        $('#fam-created-by-fecha').html('<span class="loader-modal">Cargando...</span>');
        $('#fam-updated-by-fecha').html('<span class="loader-modal">Cargando...</span>');
    }
    $(document).on('click', '.btn-ver-familia', function() {
        setLoadingModalFields();
        openModalFamilia();
        const slug = $(this).data('slug');
        $.ajax({
            url: `/admin/families/${slug}/show-full`,
            method: 'GET',
            success: function(data) {
                $('#fam-titulo').text(data.name ?? '-');
                $('#fam-id').text(data.id ?? '-');
                $('#fam-slug').text(data.slug ?? '-');
                $('#fam-name').text(data.name ?? '-');
                $('#fam-description').text(data.description ?? 'Sin descripción');
                $('#fam-status').text(data.status ? 'Activo' : 'Inactivo');
                if (data.image) {
                    $('#fam-image').html(`<img src='/storage/${data.image}' class='modal-img' />`);
                } else {
                    $('#fam-image').html('Sin imagen');
                }
                // Creado por + fecha
                let creadoPor = (data.created_by_name ?? data.created_by ?? '-');
                let creadoEn = (data.created_at ?? '-');
                $('#fam-created-by-fecha').html(`
                    <div class="show-cell-content">
                        <span class='font-bold'>${creadoPor}</span>
                        <span class="show-date">
                            <i class="ri-time-fill"></i>
                            ${creadoEn}
                        </span>
                    </div>
                `);
                // Actualizado por + fecha
                let actualizadoPor = (data.updated_by_name ?? data.updated_by ?? '-');
                let actualizadoEn = (data.updated_at ?? '-');
                $('#fam-updated-by-fecha').html(`
                    <div class="show-cell-content">
                        <span class='font-bold'>${actualizadoPor}</span>
                        <span class="show-date">
                            <i class="ri-time-fill"></i>
                            ${actualizadoEn}
                        </span>
                    </div>
                `);
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
    $(document).on('mousedown', function(e) {
        const modal = $('#modalFamilia');
        const content = $('.modal-content');
        if (!modal.hasClass('hidden') && !content.is(e.target) && content.has(e.target).length === 0) {
            closeModal();
        }
    });
</script>
@endpush
