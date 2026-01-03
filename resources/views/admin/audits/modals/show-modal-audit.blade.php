<!-- Modal para mostrar datos completos de la auditoría -->
<div id="modalShowAudit" class="modal-show hidden modal-horizontal">
    <div class="modal-content">
        <div class="modal-show-header" id="modalAuditHeader">
            <h6>Detalles de la Auditoría</h6>
            <button type="button" id="closeModalAudit" class="confirm-close ripple-btn">
                <i class="ri-close-line"></i>
            </button>
        </div>
        <h3 class="modal-title-body" id="audit-description">Sin descripción</h3>
        <div class="modal-show-body">
            <div class="modal-show-row">
                <table class="modal-show-table">
                    <tr>
                        <th>ID</th>
                        <td id="audit-id">-</td>
                    </tr>
                    <tr>
                        <th>Usuario</th>
                        <td id="audit-user">-</td>
                    </tr>
                    <tr>
                        <th>Evento</th>
                        <td id="audit-event">-</td>
                    </tr>
                    <tr>
                        <th>Modelo</th>
                        <td id="audit-model">-</td>
                    </tr>
                    <tr>
                        <th>ID Modelo</th>
                        <td id="audit-model-id">-</td>
                    </tr>
                    <tr>
                        <th>IP</th>
                        <td id="audit-ip">-</td>
                    </tr>
                    <tr>
                        <th>User Agent</th>
                        <td id="audit-user-agent">-</td>
                    </tr>
                    <tr>
                        <th>Fecha</th>
                        <td id="audit-created-at">-</td>
                    </tr>
                </table>

                <div class="modal-json-container">
                    <h4 class="font-semibold mb-2" id="audit-changes-title">Cambios del registro</h4>
                    <table class="modal-show-table modal-changes-table">
                        <thead>
                            <tr>
                                <th id="audit-col-field">Campo</th>
                                <th id="audit-col-prev">Valor anterior</th>
                                <th id="audit-col-new">Valor nuevo</th>
                            </tr>
                        </thead>
                        <tbody id="audit-changes-body">
                            <tr>
                                <td colspan="3" class="text-muted-null">Sin datos de cambios</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="modal-show-footer">
            <button type="button" class="boton boton-modal-close" id="cancelAuditButton" title="Cerrar Ventana">
                <span class="boton-icon text-base"><i class="ri-close-line"></i></span>
                <span class="boton-text">Cerrar</span>
            </button>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function openModalAudit() {
            $('#modalShowAudit').removeClass('hidden');
            $('#modalShowAudit .modal-content').removeClass('animate-out').addClass('animate-in');
            $('#modalShowAudit').appendTo('body');
            document.addEventListener('keydown', escAuditListener);
            document.addEventListener('mousedown', clickOutsideAuditListener);
        }

        function closeModalAudit() {
            $('#modalShowAudit .modal-content').removeClass('animate-in').addClass('animate-out');
            setTimeout(function() {
                $('#modalShowAudit').addClass('hidden');
                setLoadingAuditModalFields();
                document.removeEventListener('keydown', escAuditListener);
                document.removeEventListener('mousedown', clickOutsideAuditListener);
            }, 250);
        }

        function setLoadingAuditModalFields() {
            const shimmer = '<div class="shimmer shimmer-cell"></div>';
            $('#audit-id').html(shimmer);
            $('#audit-user').html(shimmer);
            $('#audit-event').html(shimmer);
            $('#audit-model').html(shimmer);
            $('#audit-model-id').html(shimmer);
            $('#audit-ip').html(shimmer);
            $('#audit-user-agent').html(shimmer);
            $('#audit-created-at').html(shimmer);
            $('#audit-description').html('<div class="shimmer shimmer-cell shimmer-title" style="width:200px;"></div>');
            $('#audit-changes-title').text('Cambios del registro');
            $('#audit-col-field').text('Campo');
            $('#audit-col-prev').text('Valor anterior');
            $('#audit-col-new').text('Valor nuevo');
            $('#audit-changes-body').html('<tr><td colspan="3"><div class="shimmer shimmer-cell"></div></td></tr>');
        }

        $(document).on('click', '.btn-ver-audit', function() {
            setLoadingAuditModalFields();
            openModalAudit();

            const id = $(this).data('id');

            $.ajax({
                url: `/admin/audits/${id}/show`,
                method: 'GET',
                success: function(data) {
                    $('#audit-id').text(data.id ?? '-');
                    $('#audit-description').text(data.description ?? '-');

                    if (data.user_name) {
                        $('#audit-user').html(`<span class="badge badge-primary"><i class="ri-user-3-line"></i> ${data.user_name}</span>`);
                    } else {
                        $('#audit-user').html('<span class="badge badge-gray"><i class="ri-user-unfollow-line"></i> Sistema / Invitado</span>');
                    }

                    $('#audit-event').text(data.event_label ?? data.event ?? '-');
                    $('#audit-model').text(data.auditable_type_name ?? '-');
                    $('#audit-model-id').text(data.auditable_id ?? '—');
                    $('#audit-ip').text(data.ip_address ?? '—');
                    // Formatear User Agent similar a profile-sessions
                    (function() {
                        const agent = data.user_agent || '';
                        if (!agent) {
                            $('#audit-user-agent').text('Desconocido');
                            return;
                        }

                        const escapeHtml = function(str) {
                            return String(str)
                                .replace(/&/g, '&amp;')
                                .replace(/</g, '&lt;')
                                .replace(/>/g, '&gt;')
                                .replace(/"/g, '&quot;')
                                .replace(/'/g, '&#039;');
                        };

                        let browser = 'Desconocido';
                        let os = 'Desconocido';
                        let browserIcon = 'ri-question-line';
                        let osIcon = 'ri-question-line';

                        if (/Chrome\//i.test(agent)) {
                            browser = 'Chrome';
                            browserIcon = 'ri-chrome-line';
                        } else if (/Firefox\//i.test(agent)) {
                            browser = 'Firefox';
                            browserIcon = 'ri-firefox-line';
                        } else if (/Edg\//i.test(agent)) {
                            browser = 'Edge';
                            browserIcon = 'ri-edge-line';
                        } else if (/Safari\//i.test(agent) && !/Chrome\//i.test(agent)) {
                            browser = 'Safari';
                            browserIcon = 'ri-safari-line';
                        } else if (/Opera|OPR\//i.test(agent)) {
                            browser = 'Opera';
                            browserIcon = 'ri-opera-line';
                        } else if (/MSIE|Trident/i.test(agent)) {
                            browser = 'IE';
                            browserIcon = 'ri-ie-line';
                        }

                        if (/Windows/i.test(agent)) {
                            os = 'Windows';
                            osIcon = 'ri-windows-line';
                        } else if (/Macintosh|Mac OS/i.test(agent)) {
                            os = 'MacOS';
                            osIcon = 'ri-mac-line';
                        } else if (/Linux/i.test(agent)) {
                            os = 'Linux';
                            osIcon = 'ri-linux-line';
                        } else if (/Android/i.test(agent)) {
                            os = 'Android';
                            osIcon = 'ri-android-line';
                        } else if (/iPhone|iPad|iOS/i.test(agent)) {
                            os = 'iOS';
                            osIcon = 'ri-apple-line';
                        }

                        const html = `
                            <span class="agent-info" title="${escapeHtml(agent)}">
                                <span class="agent-browser">
                                    <i class="${browserIcon}"></i> ${browser}
                                </span>
                                <span class="agent-dot">•</span>
                                <span class="agent-os">
                                    <i class="${osIcon}"></i> ${os}
                                </span>
                            </span>
                        `;

                        $('#audit-user-agent').html(html);
                    })();
                    $('#audit-created-at').text(data.created_at ?? '-');

                    const oldValues = data.old_values || {};
                    const newValues = data.new_values || {};

                    const eventType = data.event || '';

                    let titleText = 'Cambios del registro';
                    if (eventType === 'created') {
                        titleText = 'Datos del registro creado';
                    } else if (eventType === 'deleted') {
                        titleText = 'Datos del registro eliminado';
                    }
                    $('#audit-changes-title').text(titleText);

                    // Encabezados por tipo de evento
                    if (eventType === 'updated' || eventType === 'status_updated') {
                        $('#audit-col-field').text('Campo');
                        $('#audit-col-prev').text('Valor anterior');
                        $('#audit-col-new').text('Valor nuevo');
                    } else if (eventType === 'created') {
                        $('#audit-col-field').text('Campo');
                        $('#audit-col-prev').text('Valor');
                        $('#audit-col-new').text('');
                    } else if (eventType === 'deleted') {
                        $('#audit-col-field').text('Campo');
                        $('#audit-col-prev').text('Valor al eliminarse');
                        $('#audit-col-new').text('');
                    } else {
                        $('#audit-col-field').text('Campo');
                        $('#audit-col-prev').text('Valor');
                        $('#audit-col-new').text('');
                    }

                    const formatDate = function(raw) {
                        if (!raw) return '—';
                        const value = String(raw).trim();

                        // Formato "YYYY-MM-DD HH:MM:SS"
                        const sqlMatch = value.match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})(?::(\d{2}))?$/);
                        let dateObj = null;

                        if (sqlMatch) {
                            const year = parseInt(sqlMatch[1], 10);
                            const month = parseInt(sqlMatch[2], 10) - 1;
                            const day = parseInt(sqlMatch[3], 10);
                            const hour = parseInt(sqlMatch[4], 10);
                            const minute = parseInt(sqlMatch[5], 10);
                            const second = sqlMatch[6] ? parseInt(sqlMatch[6], 10) : 0;
                            dateObj = new Date(year, month, day, hour, minute, second);
                        } else {
                            const parsed = Date.parse(value);
                            if (!Number.isNaN(parsed)) {
                                dateObj = new Date(parsed);
                            }
                        }

                        if (!dateObj || Number.isNaN(dateObj.getTime())) {
                            return value;
                        }

                        const pad = (n) => (n < 10 ? '0' + n : '' + n);
                        const d = pad(dateObj.getDate());
                        const m = pad(dateObj.getMonth() + 1);
                        const y = dateObj.getFullYear();
                        const hh = pad(dateObj.getHours());
                        const mm = pad(dateObj.getMinutes());

                        return `${d}/${m}/${y} ${hh}:${mm}`;
                    };

                    const formatVal = function(key, val) {
                        if (val === null || typeof val === 'undefined') return '—';

                        const lowerKey = (key || '').toString().toLowerCase();

                        // Campo de estado
                        if (lowerKey === 'status' || lowerKey === 'estado' || lowerKey.endsWith('_status')) {
                            const active = val === true || val === 1 || val === '1' || val === 'true' || val === 'activo' || val === 'active';
                            if (active) {
                                return '<span class="badge boton-success"><i class="ri-check-line"></i> Activo</span>';
                            }
                            return '<span class="badge boton-danger"><i class="ri-close-line"></i> Inactivo</span>';
                        }

                        // Campos de fecha (terminan en _at, _on, date, fecha)
                        if (/(?:_at|_on|date|fecha)$/.test(lowerKey)) {
                            return formatDate(val);
                        }

                        if (typeof val === 'object') return JSON.stringify(val);
                        return String(val);
                    };

                    // Eventos de actualización (incluye cambio de estado): mostrar comparación old/new
                    if (eventType === 'updated' || eventType === 'status_updated') {
                        const keys = Array.from(new Set([
                            ...Object.keys(oldValues),
                            ...Object.keys(newValues)
                        ])).sort();

                        if (!keys.length) {
                            $('#audit-changes-body').html('<tr><td colspan="3" class="text-muted-null">Sin datos de cambios para este evento</td></tr>');
                            return;
                        }

                        let rowsHtml = '';
                        keys.forEach(function(key) {
                            const oldVal = oldValues[key];
                            const newVal = newValues[key];

                            rowsHtml += `
                                <tr>
                                    <td><code>${key}</code></td>
                                    <td>${formatVal(key, oldVal)}</td>
                                    <td>${formatVal(key, newVal)}</td>
                                </tr>
                            `;
                        });

                        $('#audit-changes-body').html(rowsHtml);
                        return;
                    }

                    // Otros eventos relacionados con creación/eliminación: mostrar datos "normales" del registro
                    if (eventType === 'created' || eventType === 'deleted') {
                        const source = Object.keys(newValues).length ? newValues : oldValues;
                        const keys = Object.keys(source).sort();

                        if (!keys.length) {
                            $('#audit-changes-body').html('<tr><td colspan="3" class="text-muted-null">Sin datos para este registro</td></tr>');
                            return;
                        }

                        let rowsHtml = '';
                        keys.forEach(function(key) {
                            const val = source[key];
                            rowsHtml += `
                                <tr>
                                    <td><code>${key}</code></td>
                                    <td colspan="2">${formatVal(key, val)}</td>
                                </tr>
                            `;
                        });

                        $('#audit-changes-body').html(rowsHtml);
                        return;
                    }

                    // Eventos personalizados con datos (bulk_deleted, exportaciones, etc.)
                    if (eventType === 'bulk_deleted' || eventType === 'pdf_exported' || eventType === 'excel_exported' || eventType === 'csv_exported') {
                        const source = Object.keys(newValues).length ? newValues : oldValues;
                        const keys = Object.keys(source).sort();

                        if (!keys.length) {
                            $('#audit-changes-body').html('<tr><td colspan="3" class="text-muted-null">Sin datos para este evento</td></tr>');
                            return;
                        }

                        let rowsHtml = '';
                        keys.forEach(function(key) {
                            const val = source[key];
                            rowsHtml += `
                                <tr>
                                    <td><code>${key}</code></td>
                                    <td colspan="2">${formatVal(key, val)}</td>
                                </tr>
                            `;
                        });

                        $('#audit-changes-body').html(rowsHtml);
                        return;
                    }

                    // Otros eventos sin cambios de campos ni datos relevantes
                    $('#audit-changes-body').html('<tr><td colspan="3" class="text-muted-null">Sin datos de cambios para este evento</td></tr>');
                },
                error: function() {
                    $('#audit-id').text('Error');
                    $('#audit-user').text('Error');
                    $('#audit-event').text('Error');
                    $('#audit-model').text('Error');
                    $('#audit-model-id').text('Error');
                    $('#audit-ip').text('Error');
                    $('#audit-user-agent').text('Error');
                    $('#audit-created-at').text('Error');
                    $('#audit-description').text('Error al cargar la auditoría');
                    $('#audit-changes-body').html('<tr><td colspan="3" class="text-red-500">Error al cargar los cambios</td></tr>');
                }
            });
        });

        $('#cancelAuditButton').on('click', closeModalAudit);
        $('#closeModalAudit').on('click', closeModalAudit);

        function escAuditListener(e) {
            if (e.key === 'Escape') {
                closeModalAudit();
            }
        }

        function clickOutsideAuditListener(e) {
            const overlay = document.getElementById('modalShowAudit');
            const content = document.querySelector('#modalShowAudit .modal-content');

            if (e.target === overlay) {
                closeModalAudit();
            }
        }
    </script>
@endpush
