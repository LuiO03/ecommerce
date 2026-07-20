<div id="modalReplyClaimMessage" class="modal-show hidden">
    <div class="modal-content">
        <div class="modal-show-header" id="modalHeaderReplyClaimMessage">
            <h6>Responder reclamo</h6>
            <button type="button" id="closeModalReplyClaimMessage" class="confirm-close ripple-btn">
                <i class="ri-close-line"></i>
            </button>
        </div>
        <div class="modal-show-body">
            <h3 class="modal-title-body" id="reply-claim-name">Sin remitente</h3>

            <table class="modal-show-table">
                <tr>
                    <th>ID</th>
                    <td id="reply-claim-id">-</td>
                </tr>
                <tr>
                    <th>Correo</th>
                    <td id="reply-claim-email">-</td>
                </tr>
                <tr>
                    <th>Teléfono</th>
                    <td id="reply-claim-phone">-</td>
                </tr>
                <tr>
                    <th>Tipo de reclamo</th>
                    <td id="reply-claim-type">-</td>
                </tr>
                <tr>
                    <th>Creado</th>
                    <td id="reply-claim-created-at">-</td>
                </tr>
            </table>

            <div class="modal-content-section">
                <span class="modal-content-title">Detalles del reclamo:</span>
                <div id="reply-claim-content" class="post-content">Sin contenido</div>
            </div>

            <div class="modal-content-section">
                <span class="modal-content-title">Tu respuesta:</span>
                <div class="input-group">
                    <div class="input-icon-container">
                        <textarea name="reply-claim-response" id="reply-claim-response" class="textarea-form"
                            placeholder="Escribe aquí la respuesta al cliente" rows="4" data-validate="min:10|max:500"></textarea>
                        <i class="ri-file-text-line input-icon"></i>
                    </div>
                </div>
            </div>

            <div class="modal-show-actions">
                <button type="button" id="saveReplyClaimResponseBtn" class="boton boton-success" title="Guardar respuesta">
                    <span class="boton-icon"><i class="ri-mail-send-fill"></i></span>
                    <span class="boton-text">Guardar respuesta</span>
                </button>
            </div>
        </div>
        <div class="modal-show-footer">
            <button type="button" class="boton boton-modal-close" id="cancelButtonReplyClaimMessage" title="Cerrar Ventana">
                <span class="boton-icon text-base"><i class="ri-close-line"></i></span>
                <span class="boton-text">Cerrar</span>
            </button>
        </div>
    </div>
</div>
