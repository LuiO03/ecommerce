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
                <tr>
                    <th>Mensaje</th>
                    <td id="msg-content">-</td>
                </tr>
                <tr>
                    <th>Respuesta</th>
                    <td>
                        <div class="input-group">
                            <label for="description" class="label-form label-textarea">
                                Respuesta al cliente
                            </label>
                            <div class="input-icon-container">
                                <textarea name="msg-response" id="msg-response" class="textarea-form" placeholder="Escribe aquí la respuesta al cliente"
                                    rows="4" data-validate="min:10|max:500">{{ old('description') }}</textarea>
                                <i class="ri-file-text-line input-icon"></i>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
            <div class="modal-show-actions">
                <button type="button" id="markReadBtn" class="boton boton-info" title="Marcar como leido">
                    <span class="boton-icon"><i class="ri-eye-fill"></i></span>
                    <span class="boton-text">Marcar leido</span>
                </button>
                <button type="button" id="saveResponseBtn" class="boton boton-success" title="Guardar respuesta">
                    <span class="boton-icon"><i class="ri-mail-send-fill"></i></span>
                    <span class="boton-text">Guardar respuesta</span>
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
        function openContactMessageModal() {
            $('#modalShowContactMessage').removeClass('hidden');
            $('#modalShowContactMessage .modal-content').removeClass('animate-out').addClass('animate-in');
            $('#modalShowContactMessage').appendTo('body');
        }

        function closeContactMessageModal() {
            $('#modalShowContactMessage .modal-content').removeClass('animate-in').addClass('animate-out');
            setTimeout(function() {
                $('#modalShowContactMessage').addClass('hidden');
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

        function loadContactMessage(id, focusResponse = false) {
            openContactMessageModal();

            $.ajax({
                url: `/admin/contact-messages/${id}/show`,
                method: 'GET',
                success: function(data) {
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
                    $('#msg-response').val(data.response ?? '');

                    $('#markReadBtn').off('click').on('click', function() {
                        updateMessageStatus(data.id, 'read');
                    });

                    $('#saveResponseBtn').off('click').on('click', function() {
                        saveMessageResponse(data.id, $('#msg-response').val());
                    });

                    if (focusResponse) {
                        setTimeout(function() {
                            $('#msg-response').trigger('focus');
                        }, 100);
                    }
                },
                error: function() {
                    $('#msg-id').text('Error');
                    $('#msg-name').text('Error al cargar');
                    $('#msg-response').val('');
                }
            });
        }

        $(document).on('click', '.btn-ver-contact-message', function() {
            loadContactMessage($(this).data('id'), false);
        });

        $(document).on('click', '.btn-reply-contact-message', function() {
            loadContactMessage($(this).data('id'), true);
        });

        $('#cancelButtonContactMessage').on('click', closeContactMessageModal);
        $('#closeModalContactMessage').on('click', closeContactMessageModal);
    </script>
@endpush
