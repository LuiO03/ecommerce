<!-- Modal para mostrar datos completos de la auditoría -->
<div id="modalShowAudit" class="modal-show hidden modal-horizontal">
    <div class="modal-content">
        <div class="modal-show-header" id="modalAuditHeader">
            <h6>Detalles de la Auditoría</h6>
            <button type="button" id="closeModalAudit" class="confirm-close ripple-btn">
                <i class="ri-close-line"></i>
            </button>
        </div>
        <div class="modal-show-body">
            <div class="modal-show-row">
                <div class="modal-json-container">
                    <h4 class="mb-2 modal-section-title" id="audit-main-title">Detalle del evento</h4>
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
                </div>

                <div class="modal-json-container">
                    <h4 class="mb-2 modal-section-title" id="audit-changes-title">Cambios del registro</h4>
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

            // Shimmer por celda en la tabla de detalle
            $('#audit-id').html(shimmer);
            $('#audit-user').html(shimmer);
            $('#audit-event').html('<div class="shimmer shimmer-cell shimmer-title" style="width:140px;"></div>');
            $('#audit-model').html(shimmer);
            $('#audit-model-id').html(shimmer);
            $('#audit-ip').html(shimmer);
            $('#audit-user-agent').html('<div class="shimmer shimmer-cell" style="width:180px;"></div>');
            $('#audit-created-at').html('<div class="shimmer shimmer-cell" style="width:120px;"></div>');
            $('#audit-main-title').text('Detalle del evento');
            $('#audit-changes-title').text('Cambios del registro');
            $('#audit-col-field').text('Campo');
            $('#audit-col-prev').text('Valor anterior');
            $('#audit-col-new').text('Valor nuevo');

            // Shimmer por celda en la tabla de cambios (3 filas de ejemplo)
            let placeholderRows = '';
            for (let i = 0; i < 3; i++) {
                placeholderRows += `
                    <tr>
                        <td><div class="shimmer shimmer-cell" style="width:110px;"></div></td>
                        <td><div class="shimmer shimmer-cell" style="width:160px;"></div></td>
                        <td><div class="shimmer shimmer-cell" style="width:160px;"></div></td>
                    </tr>
                `;
            }
            $('#audit-changes-body').html(placeholderRows);
        }

        function mapAuditFieldLabel(key) {
            if (!key) {
                return '';
            }

            const raw = key.toString();

            const labels = {
                // Generales comunes
                name: 'Nombre',
                nombre: 'Nombre',
                title: 'Título',
                titulo: 'Título',
                slug: 'Slug',
                description: 'Descripción',
                status: 'Estado',
                image: 'Imagen',

                // Metadatos de auditoría
                created_at: 'Creado el',
                updated_at: 'Actualizado el',
                deleted_at: 'Eliminado el',
                created_by: 'Creado por',
                updated_by: 'Actualizado por',
                deleted_by: 'Eliminado por',

                // Usuario
                last_name: 'Apellidos',
                address: 'Dirección',
                dni: 'DNI',
                phone: 'Teléfono',
                background_style: 'Fondo de perfil',

                // Producto / catálogo
                sku: 'SKU',
                price: 'Precio',
                discount: 'Descuento',
                min_stock: 'Stock mínimo',
                category_id: 'Categoría',
                family_id: 'Familia',
                parent_id: 'Categoría padre',
                option_id: 'Opción',
                variant_id: 'Variante',
                product_id: 'Producto',
                stock: 'Stock',
                image_path: 'Imagen de variante',
                value: 'Valor',

                // Posts / Blog
                content: 'Contenido',
                views: 'Vistas',
                published_at: 'Fecha de publicación',
                visibility: 'Visibilidad',
                allow_comments: 'Permitir comentarios',
                reviewed_by: 'Revisado por',
                reviewed_at: 'Fecha de revisión',

                // Permisos / Seguridad
                modulo: 'Módulo',
                guard_name: 'Guard',
                permissions: 'Permisos',

                // Access logs / auditoría
                user_id: 'Usuario',
                email: 'Correo electrónico',
                action: 'Acción',
                ip_address: 'IP',
                user_agent: 'Agente de usuario',

                // CompanySetting
                legal_name: 'Razón social',
                ruc: 'RUC',
                slogan: 'Eslogan',
                about: 'Acerca de la empresa',
                primary_color: 'Color primario',
                secondary_color: 'Color secundario',
                logo_path: 'Logotipo',
                support_email: 'Correo de soporte',
                support_phone: 'Teléfono de soporte',
                website: 'Sitio web',
                social_links: 'Redes sociales',
                facebook_enabled: 'Facebook habilitado',
                instagram_enabled: 'Instagram habilitado',
                twitter_enabled: 'Twitter habilitado',
                youtube_enabled: 'YouTube habilitado',
                tiktok_enabled: 'TikTok habilitado',
                linkedin_enabled: 'LinkedIn habilitado',
                terms_conditions: 'Términos y condiciones',
                privacy_policy: 'Política de privacidad',
                claims_book_information: 'Información del libro de reclamaciones',
            };

            if (Object.prototype.hasOwnProperty.call(labels, raw)) {
                return labels[raw];
            }

            return raw.replace(/_/g, ' ');
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

                    if (data.user_name) {
                        $('#audit-user').html(`<span class="badge badge-primary"><i class="ri-user-3-line"></i> ${data.user_name}</span>`);
                    } else {
                        $('#audit-user').html('<span class="badge badge-gray"><i class="ri-user-unfollow-line"></i> Sistema / Invitado</span>');
                    }

                    const eventLabel = data.event_label ?? data.event ?? '-';
                    $('#audit-event').text(eventLabel);
                    $('#audit-main-title').text(eventLabel);
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

                    const hasOldValues = Object.keys(oldValues).length > 0;
                    const hasNewValues = Object.keys(newValues).length > 0;

                    let titleText = 'Cambios del registro';
                    if (eventType === 'created') {
                        titleText = 'Datos del registro creado';
                    } else if (eventType === 'deleted') {
                        titleText = 'Datos del registro eliminado';
                    } else if (eventType === 'permissions_updated') {
                        titleText = 'Permisos del rol (antes y después)';
                    } else if (eventType === 'company_social_updated') {
                        titleText = 'Redes sociales de la empresa (antes y después)';
                    }
                    $('#audit-changes-title').text(titleText);

                    // Encabezados por tipo de evento
                    if (eventType === 'permissions_updated') {
                        $('#audit-col-field').text('Tipo');
                        $('#audit-col-prev').text('Permisos quitados');
                        $('#audit-col-new').text('Permisos agregados');
                    } else if (hasOldValues && hasNewValues) {
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

                        // Campo de estado (booleanos y estados múltiples como posts)
                        if (lowerKey === 'status' || lowerKey === 'estado' || lowerKey.endsWith('_status')) {
                            // Estados textuales (posts: draft, pending, published, rejected)
                            if (typeof val === 'string') {
                                const statusStr = val.toString().toLowerCase();

                                if (statusStr === 'draft' || statusStr === 'borrador') {
                                    return '<span class="badge badge-gray"><i class="ri-file-list-2-line"></i> Borrador</span>';
                                }

                                if (statusStr === 'pending' || statusStr === 'pendiente') {
                                    return '<span class="badge badge-warning"><i class="ri-time-line"></i> Pendiente</span>';
                                }

                                if (statusStr === 'published' || statusStr === 'publicado' || statusStr === 'publicada') {
                                    return '<span class="badge boton-success"><i class="ri-check-line"></i> Publicado</span>';
                                }

                                if (statusStr === 'rejected' || statusStr === 'rechazado' || statusStr === 'rechazada') {
                                    return '<span class="badge boton-danger"><i class="ri-close-line"></i> Rechazado</span>';
                                }
                            }

                            // Estados booleanos (módulos con activo/inactivo)
                            const active = val === true || val === 1 || val === '1' || val === 'true' || val === 'activo' || val === 'active';
                            if (active) {
                                return '<span class="badge boton-success"><i class="ri-check-line"></i> Activo</span>';
                            }
                            return '<span class="badge boton-danger"><i class="ri-close-line"></i> Inactivo</span>';
                        }

                        // Listado de permisos (evento permissions_updated)
                        if (lowerKey === 'permissions' || lowerKey === 'permisos' || lowerKey.includes('permission')) {
                            const escapeHtmlSimple = function(str) {
                                return String(str)
                                    .replace(/&/g, '&amp;')
                                    .replace(/</g, '&lt;')
                                    .replace(/>/g, '&gt;')
                                    .replace(/"/g, '&quot;')
                                    .replace(/'/g, '&#039;');
                            };

                            let items = [];
                            if (Array.isArray(val)) {
                                items = val;
                            } else {
                                items = String(val)
                                    .split(',')
                                    .map(v => v.trim())
                                    .filter(v => v.length > 0);
                            }

                            if (!items.length) {
                                return '—';
                            }

                            const badges = items.map(function(item) {
                                return '<span class="badge badge-purple"><i class="ri-shield-check-fill"></i> ' + escapeHtmlSimple(item) + '</span>';
                            }).join(' ');

                            return '<div class="permissions-badge-list">' + badges + '</div>';
                        }

                        // Booleans tipo *_enabled, allow_comments, toggles varios
                        if (lowerKey.endsWith('_enabled') || lowerKey === 'allow_comments') {
                            const truthy = val === true || val === 1 || val === '1' || val === 'true';

                            // Para flags *_enabled (ej. Facebook habilitado): Activo / Inactivo
                            if (lowerKey.endsWith('_enabled')) {
                                if (truthy) {
                                    return '<span class="badge boton-success"><i class="ri-check-line"></i> Activo</span>';
                                }
                                return '<span class="badge boton-danger"><i class="ri-close-line"></i> Inactivo</span>';
                            }

                            // Para allow_comments: Permitidos / No permitidos
                            if (lowerKey === 'allow_comments') {
                                if (truthy) {
                                    return '<span class="badge boton-success"><i class="ri-check-line"></i> Permitidos</span>';
                                }
                                return '<span class="badge boton-danger"><i class="ri-close-line"></i> No permitidos</span>';
                            }
                        }

                        // IDs de relación (mostrar con #id)
                        if (lowerKey === 'id' || lowerKey.endsWith('_id')) {
                            return '#' + String(val);
                        }

                        // Campos de fecha (terminan en _at, _on, date, fecha)
                        if (/(?:_at|_on|date|fecha)$/.test(lowerKey)) {
                            return formatDate(val);
                        }

                        if (typeof val === 'object') return JSON.stringify(val);
                        return String(val);
                    };

                    // Evento especial: cambio de permisos de rol
                    if (eventType === 'permissions_updated') {
                        const removed = oldValues['removed_permissions'] || [];
                        const added = newValues['added_permissions'] || [];

                        const removedHtml = removed.length ? formatVal('permissions', removed) : '—';
                        const addedHtml = added.length ? formatVal('permissions', added) : '—';

                        const rowHtml = `
                            <tr>
                                <td><code>${mapAuditFieldLabel('permissions')}</code></td>
                                <td>${removedHtml}</td>
                                <td>${addedHtml}</td>
                            </tr>
                        `;

                        $('#audit-changes-body').html(rowHtml);
                        return;
                    }

                    // Eventos con old_values y new_values: mostrar comparación old/new
                    if (hasOldValues && hasNewValues) {
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

                            // Caso especial: redes sociales de la empresa
                            if (eventType === 'company_social_updated' && key === 'social_links') {
                                const oldLinks = oldVal || {};
                                const newLinks = newVal || {};
                                const platforms = ['facebook', 'instagram', 'twitter', 'youtube', 'tiktok', 'linkedin'];

                                const formatLink = function(url) {
                                    if (!url) return '—';
                                    const safe = String(url).replace(/"/g, '&quot;');
                                    return `<a href="${safe}" target="_blank" rel="noopener noreferrer">${safe}</a>`;
                                };

                                platforms.forEach(function(platform) {
                                    const prevUrl = oldLinks[platform] || '';
                                    const nextUrl = newLinks[platform] || '';

                                    if (prevUrl === nextUrl) {
                                        return;
                                    }

                                    const label = 'Red social: ' + platform.charAt(0).toUpperCase() + platform.slice(1);

                                    rowsHtml += `
                                        <tr>
                                            <td><code>${label}</code></td>
                                            <td>${formatLink(prevUrl)}</td>
                                            <td>${formatLink(nextUrl)}</td>
                                        </tr>
                                    `;
                                });

                                return;
                            }

                            // Saltar campos cuyo valor no cambió
                            try {
                                const same = JSON.stringify(oldVal) === JSON.stringify(newVal);
                                if (same) {
                                    return;
                                }
                            } catch (e) {
                                if (oldVal === newVal) {
                                    return;
                                }
                            }

                            rowsHtml += `
                                <tr>
                                    <td><code>${mapAuditFieldLabel(key)}</code></td>
                                    <td>${formatVal(key, oldVal)}</td>
                                    <td>${formatVal(key, newVal)}</td>
                                </tr>
                            `;
                        });

                        if (!rowsHtml) {
                            $('#audit-changes-body').html('<tr><td colspan="3" class="text-muted-null">Sin datos de cambios para este evento</td></tr>');
                        } else {
                            $('#audit-changes-body').html(rowsHtml);
                        }
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
                                    <td><code>${mapAuditFieldLabel(key)}</code></td>
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
                                    <td><code>${mapAuditFieldLabel(key)}</code></td>
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
