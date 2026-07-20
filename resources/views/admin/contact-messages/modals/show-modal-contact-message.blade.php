<div id="modalShowContactMessage" class="modal-show hidden">
    <div class="modal-content">
        <div class="modal-show-header" id="modalHeaderContactMessage">
            <h6>Detalles del mensaje</h6>
            <button type="button" id="closeModalContactMessage" class="confirm-close ripple-btn">
                <i class="ri-close-line"></i>
            </button>
        </div>
        <div class="modal-show-body">
            <h3 class="modal-title-body" id="msg-name">Sin remitente</h3>
            <table class="modal-show-table">
                <tr>
                    <th>ID</th>
                    <td id="msg-id">-</td>
                </tr>
                <tr>
                    <th>Correo</th>
                    <td id="msg-email">-</td>
                </tr>
                <tr>
                    <th>Tema</th>
                    <td id="msg-topic">-</td>
                </tr>
                <tr>
                    <th>Nro. pedido</th>
                    <td id="msg-order-number">-</td>
                </tr>
                <tr>
                    <th>Estado</th>
                    <td id="msg-status">-</td>
                </tr>
                <tr>
                    <th>Creado</th>
                    <td id="msg-created-at">-</td>
                </tr>
                <tr>
                    <th>Leido</th>
                    <td id="msg-read-at">-</td>
                </tr>
                <tr>
                    <th>Respondido</th>
                    <td id="msg-replied-at">-</td>
                </tr>
            </table>
            <div class="modal-content-section">
                <span class="modal-content-title">Mensaje del cliente:</span>
                <div id="msg-content" class="post-content">Sin contenido</div>
            </div>
            <div class="modal-content-section">
                <span class="modal-content-title">Respuesta:</span>
                <div id="msg-response-view" class="post-content">Pendiente</div>
            </div>
            <div class="modal-show-actions">
                <button type="button" id="markReadBtn" class="boton boton-info" title="Marcar como leido">
                    <span class="boton-icon"><i class="ri-eye-fill"></i></span>
                    <span class="boton-text">Marcar leido</span>
                </button>
            </div>
        </div>
        <div class="modal-show-footer">

            <button type="button" class="boton boton-modal-close" id="cancelButtonContactMessage"
                title="Cerrar Ventana">
                <span class="boton-icon text-base"><i class="ri-close-line"></i></span>
                <span class="boton-text">Cerrar</span>
            </button>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function setLoadingContactMessageFields() {
            $('#msg-id').html('<div class="shimmer shimmer-cell"></div>');
            $('#msg-name').html('<div class="shimmer shimmer-cell shimmer-title" style="width:140px;"></div>');
            $('#msg-email').html('<div class="shimmer shimmer-cell" style="width:160px;"></div>');
            $('#msg-topic').html('<div class="shimmer shimmer-cell" style="width:120px;"></div>');
            $('#msg-order-number').html('<div class="shimmer shimmer-cell" style="width:100px;"></div>');
            $('#msg-status').html('<div class="shimmer shimmer-cell" style="width:90px;"></div>');
            $('#msg-created-at').html('<div class="shimmer shimmer-cell" style="width:120px;"></div>');
            $('#msg-read-at').html('<div class="shimmer shimmer-cell" style="width:120px;"></div>');
            $('#msg-replied-at').html('<div class="shimmer shimmer-cell" style="width:120px;"></div>');
            $('#msg-content').html('<div class="shimmer shimmer-cell" style="width:100%; height:60px;"></div>');
            $('#msg-response-view').html('<div class="shimmer shimmer-cell" style="width:100%; height:60px;"></div>');

            $('#reply-msg-id').html('<div class="shimmer shimmer-cell"></div>');
            $('#reply-msg-name').html('<div class="shimmer shimmer-cell shimmer-title" style="width:140px;"></div>');
            $('#reply-msg-email').html('<div class="shimmer shimmer-cell" style="width:160px;"></div>');
            $('#reply-msg-topic').html('<div class="shimmer shimmer-cell" style="width:120px;"></div>');
            $('#reply-msg-order-number').html('<div class="shimmer shimmer-cell" style="width:100px;"></div>');
            $('#reply-msg-created-at').html('<div class="shimmer shimmer-cell" style="width:120px;"></div>');
            $('#reply-msg-content').html('<div class="shimmer shimmer-cell" style="width:100%; height:60px;"></div>');
            $('#reply-msg-response').val('');
        }

        function openModal(selector) {
            $(selector).removeClass('hidden');
            $(selector + ' .modal-content')
                .removeClass('animate-out')
                .addClass('animate-in');

            $(selector).appendTo('body');

            document.addEventListener('keydown', escContactListener);
            document.addEventListener('mousedown', clickOutsideContactListener);
        }

        function closeModal(selector) {
            $(selector + ' .modal-content')
                .removeClass('animate-in')
                .addClass('animate-out');

            setTimeout(function() {
                $(selector).addClass('hidden');
                setLoadingContactMessageFields();

                document.removeEventListener('keydown', escContactListener);
                document.removeEventListener('mousedown', clickOutsideContactListener);
            }, 250);
        }

        function renderStatusBadge(status) {
            if (status === 'new') {
                return '<span class="badge badge-warning"><i class="ri-error-warning-fill"></i> Nuevo</span>';
            }
            if (status === 'read') {
                return '<span class="badge badge-info"><i class="ri-eye-fill"></i> Leido</span>';
            }
            return '<span class="badge badge-success"><i class="ri-checkbox-circle-fill"></i> Respondido</span>';
        }

        function updateMessageStatus(messageId, status) {
            $.ajax({
                url: `/admin/contact-messages/${messageId}/status`,
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

        function saveMessageResponse(messageId, response) {
            $.ajax({
                url: `/admin/contact-messages/${messageId}/response`,
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

        function escContactListener(e) {
            if (e.key === "Escape") {
                closeModal('#modalShowContactMessage');
                closeModal('#modalReplyContactMessage');
            }
        }

        function clickOutsideContactListener(e) {
            const showOverlay = document.getElementById('modalShowContactMessage');
            if (e.target === showOverlay) {
                closeModal('#modalShowContactMessage');
            }

            const replyOverlay = document.getElementById('modalReplyContactMessage');
            if (e.target === replyOverlay) {
                closeModal('#modalReplyContactMessage');
            }
        }

        function isReplyLocked(data) {
            return !!(data?.replied_at || data?.status === 'replied' || (data?.response && String(data.response).trim() !== ''));
        }

        function loadContactMessage(id, mode = 'show') {
            setLoadingContactMessageFields();
            openModal(mode === 'reply' ? '#modalReplyContactMessage' : '#modalShowContactMessage');

            $.ajax({
                url: `/admin/contact-messages/${id}/show`,
                method: 'GET',
                success: function(data) {
                    if (mode === 'show') {
                        $('#msg-id').text(data.id ?? '-');
                        $('#msg-name').text(data.name ?? '-');
                        $('#msg-email').text(data.email ?? '-');
                        $('#msg-topic').text(data.topic ?? '-');
                        $('#msg-order-number').text(data.order_number ?? 'No aplica');
                        $('#msg-status').html(renderStatusBadge(data.status));
                        $('#msg-created-at').text(data.created_at ?? '-');
                        $('#msg-read-at').text(data.read_at ?? 'Pendiente');
                        $('#msg-replied-at').text(data.replied_at ?? 'Pendiente');
                        $('#msg-content').text(data.message ?? '-');
                        $('#msg-response-view').text(data.response ?? 'Sin respuesta');

                        $('#markReadBtn').off('click').on('click', function() {
                            updateMessageStatus(data.id, 'read');
                        });
                        return;
                    }

                    // mode === 'reply'
                    $('#reply-msg-id').text(data.id ?? '-');
                    $('#reply-msg-name').text(data.name ?? '-');
                    $('#reply-msg-email').text(data.email ?? '-');
                    $('#reply-msg-topic').text(data.topic ?? '-');
                    $('#reply-msg-order-number').text(data.order_number ?? 'No aplica');
                    $('#reply-msg-created-at').text(data.created_at ?? '-');
                    $('#reply-msg-content').text(data.message ?? '-');
                    $('#reply-msg-response').val('');

                    const locked = isReplyLocked(data);
                    $('#reply-msg-response').prop('disabled', locked);
                    $('#saveReplyResponseBtn').prop('disabled', locked);

                    if (locked) {
                        if (typeof window.showToast === 'function') {
                            window.showToast({
                                type: 'warning',
                                title: 'No disponible',
                                message: 'Este mensaje ya fue respondido.'
                            });
                        }
                    }

                    $('#saveReplyResponseBtn').off('click').on('click', function() {
                        if (locked) return;
                        saveMessageResponse(data.id, $('#reply-msg-response').val());
                    });

                    setTimeout(function() {
                        $('#reply-msg-response').trigger('focus');
                    }, 100);
                },
                error: function() {
                    $('#msg-id').text('Error');
                    $('#msg-name').text('Error al cargar');
                    $('#msg-response-view').text('');
                    $('#reply-msg-id').text('Error');
                    $('#reply-msg-name').text('Error al cargar');
                    $('#reply-msg-response').val('');
                }
            });
        }

        $(document).on('click', '.btn-ver-contact-message', function() {
            loadContactMessage($(this).data('id'), 'show');
        });

        $(document).on('click', '.btn-reply-contact-message', function() {
            loadContactMessage($(this).data('id'), 'reply');
        });

        $('#cancelButtonContactMessage').on('click', function() {
            closeModal('#modalShowContactMessage');
        });
        $('#closeModalContactMessage').on('click', function() {
            closeModal('#modalShowContactMessage');
        });

        $('#cancelButtonReplyContactMessage').on('click', function() {
            closeModal('#modalReplyContactMessage');
        });
        $('#closeModalReplyContactMessage').on('click', function() {
            closeModal('#modalReplyContactMessage');
        });
    </script>
@endpush
