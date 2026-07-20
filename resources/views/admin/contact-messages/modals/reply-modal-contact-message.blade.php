<div id="modalReplyContactMessage" class="modal-show hidden">
    <div class="modal-content">
        <div class="modal-show-header" id="modalHeaderReplyContactMessage">
            <h6>Responder mensaje</h6>
            <button type="button" id="closeModalReplyContactMessage" class="confirm-close ripple-btn">
                <i class="ri-close-line"></i>
            </button>
        </div>
        <div class="modal-show-body">
            <h3 class="modal-title-body" id="reply-msg-name">Sin remitente</h3>

            <table class="modal-show-table">
                <tr>
                    <th>ID</th>
                    <td id="reply-msg-id">-</td>
                </tr>
                <tr>
                    <th>Correo</th>
                    <td id="reply-msg-email">-</td>
                </tr>
                <tr>
                    <th>Tema</th>
                    <td id="reply-msg-topic">-</td>
                </tr>
                <tr>
                    <th>Nro. pedido</th>
                    <td id="reply-msg-order-number">-</td>
                </tr>
                <tr>
                    <th>Creado</th>
                    <td id="reply-msg-created-at">-</td>
                </tr>
            </table>

            <div class="modal-content-section">
                <span class="modal-content-title">Mensaje del cliente:</span>
                <div id="reply-msg-content" class="post-content">Sin contenido</div>
            </div>

            <div class="modal-content-section">
                <span class="modal-content-title">Tu respuesta:</span>
                <div class="input-group">
                    <div class="input-icon-container">
                        <textarea name="reply-msg-response" id="reply-msg-response" class="textarea-form"
                            placeholder="Escribe aquí la respuesta al cliente" rows="4" data-validate="min:10|max:500"></textarea>
                        <i class="ri-file-text-line input-icon"></i>
                    </div>
                </div>
            </div>

            <div class="modal-show-actions">
                <button type="button" id="saveReplyResponseBtn" class="boton boton-success" title="Guardar respuesta">
                    <span class="boton-icon"><i class="ri-mail-send-fill"></i></span>
                    <span class="boton-text">Guardar respuesta</span>
                </button>
            </div>
        </div>
        <div class="modal-show-footer">
            <button type="button" class="boton boton-modal-close" id="cancelButtonReplyContactMessage" title="Cerrar Ventana">
                <span class="boton-icon text-base"><i class="ri-close-line"></i></span>
                <span class="boton-text">Cerrar</span>
            </button>
        </div>
    </div>
</div>
