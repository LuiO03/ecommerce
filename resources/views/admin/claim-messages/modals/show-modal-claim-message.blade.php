<div id="modalShowClaimMessage" class="modal-show hidden">
    <div class="modal-content">
        <div class="modal-show-header" id="modalHeaderClaimMessage">
            <h6>Detalles del reclamo</h6>
            <button type="button" id="closeModalClaimMessage" class="confirm-close ripple-btn">
                <i class="ri-close-line"></i>
            </button>
        </div>
        <div class="modal-show-body">
            <h3 class="modal-title-body" id="claim-name">Sin remitente</h3>
            <table class="modal-show-table">
                <tr>
                    <th>ID</th>
                    <td id="claim-id">-</td>
                </tr>
                <tr>
                    <th>Correo</th>
                    <td id="claim-email">-</td>
                </tr>
                <tr>
                    <th>Teléfono</th>
                    <td id="claim-phone">-</td>
                </tr>
                <tr>
                    <th>Tipo de reclamo</th>
                    <td id="claim-type">-</td>
                </tr>
                <tr>
                    <th>Estado</th>
                    <td id="claim-status">-</td>
                </tr>
                <tr>
                    <th>Creado</th>
                    <td id="claim-created-at">-</td>
                </tr>
                <tr>
                    <th>Leído</th>
                    <td id="claim-read-at">-</td>
                </tr>
                <tr>
                    <th>Respondido</th>
                    <td id="claim-replied-at">-</td>
                </tr>
                <tr>
                    <th>Detalles del reclamo</th>
                    <td id="claim-content">-</td>
                </tr>
                <tr>
                    <th>Respuesta guardada</th>
                    <td>
                        <div id="claim-response-view" class="post-content">Sin respuesta</div>
                    </td>
                </tr>
            </table>
            <div class="modal-show-actions">
                <button type="button" id="markReadBtn" class="boton boton-info" title="Marcar como leído">
                    <span class="boton-icon"><i class="ri-eye-fill"></i></span>
                    <span class="boton-text">Marcar leído</span>
                </button>
            </div>
        </div>
        <div class="modal-show-footer">
            <button type="button" class="boton boton-modal-close" id="cancelButtonClaimMessage"
                title="Cerrar Ventana">
                <span class="boton-icon text-base"><i class="ri-close-line"></i></span>
                <span class="boton-text">Cerrar</span>
            </button>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function setLoadingClaimMessageFields() {
            $('#claim-id').html('<div class="shimmer shimmer-cell"></div>');
            $('#claim-name').html('<div class="shimmer shimmer-cell shimmer-title" style="width:140px;"></div>');
            $('#claim-email').html('<div class="shimmer shimmer-cell" style="width:160px;"></div>');
            $('#claim-phone').html('<div class="shimmer shimmer-cell" style="width:120px;"></div>');
            $('#claim-type').html('<div class="shimmer shimmer-cell" style="width:100px;"></div>');
            $('#claim-status').html('<div class="shimmer shimmer-cell" style="width:90px;"></div>');
            $('#claim-created-at').html('<div class="shimmer shimmer-cell" style="width:120px;"></div>');
            $('#claim-read-at').html('<div class="shimmer shimmer-cell" style="width:120px;"></div>');
            $('#claim-replied-at').html('<div class="shimmer shimmer-cell" style="width:120px;"></div>');
            $('#claim-content').html('<div class="shimmer shimmer-cell" style="width:100%; height:60px;"></div>');
            $('#claim-response-view').html('<div class="shimmer shimmer-cell" style="width:100%; height:60px;"></div>');

            $('#reply-claim-id').html('<div class="shimmer shimmer-cell"></div>');
            $('#reply-claim-name').html('<div class="shimmer shimmer-cell shimmer-title" style="width:140px;"></div>');
            $('#reply-claim-email').html('<div class="shimmer shimmer-cell" style="width:160px;"></div>');
            $('#reply-claim-phone').html('<div class="shimmer shimmer-cell" style="width:120px;"></div>');
            $('#reply-claim-type').html('<div class="shimmer shimmer-cell" style="width:100px;"></div>');
            $('#reply-claim-created-at').html('<div class="shimmer shimmer-cell" style="width:120px;"></div>');
            $('#reply-claim-content').html('<div class="shimmer shimmer-cell" style="width:100%; height:60px;"></div>');
            $('#reply-claim-response').val('');
        }

        function openModal(selector) {
            $(selector).removeClass('hidden');
            $(selector + ' .modal-content')
                .removeClass('animate-out')
                .addClass('animate-in');

            $(selector).appendTo('body');

            document.addEventListener('keydown', escClaimListener);
            document.addEventListener('mousedown', clickOutsideClaimListener);
        }

        function closeModal(selector) {
            $(selector + ' .modal-content')
                .removeClass('animate-in')
                .addClass('animate-out');

            setTimeout(function() {
                $(selector).addClass('hidden');
                setLoadingClaimMessageFields();

                document.removeEventListener('keydown', escClaimListener);
                document.removeEventListener('mousedown', clickOutsideClaimListener);
            }, 250);
        }

        function renderClaimStatusBadge(status) {
            if (status === 'new') {
                return '<span class="badge badge-warning"><i class="ri-error-warning-fill"></i> Nuevo</span>';
            }
            if (status === 'read') {
                return '<span class="badge badge-info"><i class="ri-eye-fill"></i> Leído</span>';
            }
            return '<span class="badge badge-success"><i class="ri-checkbox-circle-fill"></i> Respondido</span>';
        }

        function renderClaimTypeBadge(type) {
            if (type === 'reclamo') {
                return '<span class="badge badge-danger"><i class="ri-alert-line"></i> Reclamo</span>';
            }
            return '<span class="badge badge-warning"><i class="ri-emotion-sad-fill"></i> Queja</span>';
        }

        function updateClaimStatus(claimId, status) {
            $.ajax({
                url: `/admin/claim-messages/${claimId}/status`,
                method: 'PATCH',
                data: {
                    _token: '{{ csrf_token() }}',
                    status
                },
                success: function() {
                    location.reload();
                }
            });
        }

        function saveClaimResponse(claimId, response) {
            $.ajax({
                url: `/admin/claim-messages/${claimId}/response`,
                method: 'PATCH',
                data: {
                    _token: '{{ csrf_token() }}',
                    response
                },
                success: function() {
                    location.reload();
                },
                error: function(xhr) {
                    const fallback = 'No se pudo guardar la respuesta.';
                    if (typeof window.showToast === 'function') {
                        window.showToast({
                            type: 'danger',
                            title: 'Error',
                            message: xhr?.responseJSON?.message || fallback
                        });
                    }
                }
            });
        }

        function escClaimListener(e) {
            if (e.key === "Escape") {
                closeModal('#modalShowClaimMessage');
                closeModal('#modalReplyClaimMessage');
            }
        }

        function clickOutsideClaimListener(e) {
            const overlay = document.getElementById('modalShowClaimMessage');
            if (e.target === overlay) {
                closeModal('#modalShowClaimMessage');
            }

            const replyOverlay = document.getElementById('modalReplyClaimMessage');
            if (e.target === replyOverlay) {
                closeModal('#modalReplyClaimMessage');
            }
        }

        function isReplyLocked(data) {
            return !!(data?.replied_at || data?.status === 'replied' || (data?.response && String(data.response).trim() !== ''));
        }

        function loadClaimMessage(id, mode = 'show') {
            setLoadingClaimMessageFields();
            openModal(mode === 'reply' ? '#modalReplyClaimMessage' : '#modalShowClaimMessage');

            $.ajax({
                url: `/admin/claim-messages/${id}/show`,
                method: 'GET',
                success: function(data) {
                    if (mode === 'show') {
                        $('#claim-id').text(data.id ?? '-');
                        $('#claim-name').text(data.name ?? '-');
                        $('#claim-email').text(data.email ?? '-');
                        $('#claim-phone').text(data.phone ?? 'No especificado');
                        $('#claim-type').html(renderClaimTypeBadge(data.claim_type));
                        $('#claim-status').html(renderClaimStatusBadge(data.status));
                        $('#claim-created-at').text(data.created_at ?? '-');
                        $('#claim-read-at').text(data.read_at ?? 'Pendiente');
                        $('#claim-replied-at').text(data.replied_at ?? 'Pendiente');
                        $('#claim-content').text(data.message ?? '-');
                        $('#claim-response-view').text(data.response ?? 'Sin respuesta');

                        $('#markReadBtn').off('click').on('click', function() {
                            updateClaimStatus(data.id, 'read');
                        });
                        return;
                    }

                    // mode === 'reply'
                    $('#reply-claim-id').text(data.id ?? '-');
                    $('#reply-claim-name').text(data.name ?? '-');
                    $('#reply-claim-email').text(data.email ?? '-');
                    $('#reply-claim-phone').text(data.phone ?? 'No especificado');
                    $('#reply-claim-type').html(renderClaimTypeBadge(data.claim_type));
                    $('#reply-claim-created-at').text(data.created_at ?? '-');
                    $('#reply-claim-content').text(data.message ?? '-');
                    $('#reply-claim-response').val('');

                    const locked = isReplyLocked(data);
                    $('#reply-claim-response').prop('disabled', locked);
                    $('#saveReplyClaimResponseBtn').prop('disabled', locked);

                    if (locked) {
                        if (typeof window.showToast === 'function') {
                            window.showToast({
                                type: 'warning',
                                title: 'No disponible',
                                message: 'Este reclamo ya fue respondido.'
                            });
                        }
                    }

                    $('#saveReplyClaimResponseBtn').off('click').on('click', function() {
                        if (locked) return;
                        saveClaimResponse(data.id, $('#reply-claim-response').val());
                    });

                    setTimeout(function() {
                        $('#reply-claim-response').trigger('focus');
                    }, 100);
                },
                error: function() {
                    $('#claim-id').text('Error');
                    $('#claim-name').text('Error al cargar');
                    $('#claim-response-view').text('');
                    $('#reply-claim-id').text('Error');
                    $('#reply-claim-name').text('Error al cargar');
                    $('#reply-claim-response').val('');
                }
            });
        }

        $(document).on('click', '.btn-ver-claim-message', function() {
            loadClaimMessage($(this).data('id'), 'show');
        });

        $(document).on('click', '.btn-reply-claim-message', function() {
            loadClaimMessage($(this).data('id'), 'reply');
        });

        $('#cancelButtonClaimMessage').on('click', function() {
            closeModal('#modalShowClaimMessage');
        });
        $('#closeModalClaimMessage').on('click', function() {
            closeModal('#modalShowClaimMessage');
        });

        $('#cancelButtonReplyClaimMessage').on('click', function() {
            closeModal('#modalReplyClaimMessage');
        });
        $('#closeModalReplyClaimMessage').on('click', function() {
            closeModal('#modalReplyClaimMessage');
        });
    </script>
@endpush
