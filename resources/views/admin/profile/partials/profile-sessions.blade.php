

<div class="form-profile-column">
    <div class="card-header">
        <span class="card-title">Dispositivos y sesiones activas</span>
        <p class="card-description">Administra tus dispositivos y sesiones activas para mantener la seguridad de tu cuenta.</p>
    </div>
    @if (isset($sessions) && count($sessions) > 0)
        <ul class="sessions-list">
            @foreach ($sessions as $session)
                @php
                    $isCurrent = $session->id === session()->getId();
                @endphp
                <li class="sessions-item {{ $isCurrent ? 'sessions-item-active' : '' }}">
                    <div class="sessions-info">
                        @php
                            $agent = $session->user_agent ?? 'Desconocido';
                            $isMobile = false;
                            if (preg_match('/Android|iPhone|iPad|Mobile|iPod|webOS|BlackBerry|Windows Phone/i', $agent)) {
                                $isMobile = true;
                            }
                        @endphp
                        <div class="sessions-icon">
                            @if ($isMobile)
                                <i class="ri-smartphone-line"></i>
                            @else
                                <i class="ri-computer-line"></i>
                            @endif
                        </div>
                        <div class="sessions-details">
                            <div class="sessions-agent">
                                @php
                                    // Extraer navegador y SO de la cadena user_agent
                                    $browser = 'Navegador desconocido';
                                    $os = 'SO desconocido';
                                    if (preg_match('/(Chrome|Firefox|Safari|Edg|Opera|MSIE|Trident)\/([\d\.]+)/i', $agent, $matches)) {
                                        $browser = $matches[1] . ' ' . $matches[2];
                                    }
                                    if (preg_match('/Windows NT [\d\.]+|Mac OS X [\d_]+|Linux|Android|iPhone OS [\d_]+/i', $agent, $matches)) {
                                        $os = $matches[0];
                                    }
                                @endphp
                                <span class="sessions-browser"><i class="ri-window-line"></i> {{ $browser }}</span>
                                <span class="sessions-os"><i class="ri-computer-line"></i> {{ $os }}</span>
                            </div>
                            <div class="sessions-meta">
                                <span class="sessions-ip">
                                    <i class="ri-map-pin-line"></i>
                                    {{ $session->ip_address ?? 'IP desconocida' }}
                                </span>
                                <span class="sessions-last-active">
                                    <i class="ri-time-line"></i>
                                    {{ $session->last_activity ? \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans() : '' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="sessions-actions">
                        @if ($isCurrent)
                            <span class="badge badge-success"><i class="ri-checkbox-circle-fill"></i> Sesión actual</span>
                        @else
                            <form method="POST" action="{{ route('admin.profile.logout-session') }}" style="display:inline;" class="logout-session-form">
                                @csrf
                                <input type="hidden" name="session_id" value="{{ $session->id }}">
                                <button type="button" class="boton-form boton-danger logout-session-btn" title="Cerrar sesión">
                                    <span class="boton-form-icon"><i class="ri-git-repository-private-fill"></i></span>
                                    <span class="boton-form-text">Cerrar Sesión</span>
                                </button>
                            </form>
                        @push('scripts')
                        <script>
                        document.querySelectorAll('.logout-session-btn').forEach(function(btn) {
                            btn.addEventListener('click', function(e) {
                                e.preventDefault();
                                const form = this.closest('form.logout-session-form');
                                window.showConfirm({
                                    type: 'danger',
                                    title: '¿Cerrar esta sesión?',
                                    message: '¿Seguro que deseas cerrar la sesión de este dispositivo? Esta acción no se puede deshacer.',
                                    confirmText: 'Cerrar sesión',
                                    cancelText: 'Cancelar',
                                    onConfirm: function() {
                                        // Enviar el formulario
                                        form.submit();
                                    }
                                });
                            });
                        });
                        </script>
                        @endpush
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    @else
        <x-alert type="info" title="Sin sesiones activas">
            No hay otras sesiones abiertas en otros dispositivos.
        </x-alert>
    @endif
</div>
