<div class="form-profile-column">
    <div class="card-header">
        <span class="card-title">Dispositivos y sesiones activas</span>
        <p class="card-description">Administra tus dispositivos y sesiones activas para mantener la seguridad de tu
            cuenta.</p>
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
                            if (
                                preg_match('/Android|iPhone|iPad|Mobile|iPod|webOS|BlackBerry|Windows Phone/i', $agent)
                            ) {
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
                                    // Extraer navegador y SO + iconos y punto separador
                                    $browser = 'Desconocido';
                                    $os = 'Desconocido';
                                    $browserIcon = 'ri-question-line';
                                    $osIcon = 'ri-question-line';
                                    if (preg_match('/Chrome\//i', $agent)) {
                                        $browser = 'Chrome';
                                        $browserIcon = 'ri-chrome-line';
                                    } elseif (preg_match('/Firefox\//i', $agent)) {
                                        $browser = 'Firefox';
                                        $browserIcon = 'ri-firefox-line';
                                    } elseif (preg_match('/Edg\//i', $agent)) {
                                        $browser = 'Edge';
                                        $browserIcon = 'ri-edge-line';
                                    } elseif (preg_match('/Safari\//i', $agent) && !preg_match('/Chrome\//i', $agent)) {
                                        $browser = 'Safari';
                                        $browserIcon = 'ri-safari-line';
                                    } elseif (preg_match('/Opera|OPR\//i', $agent)) {
                                        $browser = 'Opera';
                                        $browserIcon = 'ri-opera-line';
                                    } elseif (preg_match('/MSIE|Trident/i', $agent)) {
                                        $browser = 'IE';
                                        $browserIcon = 'ri-ie-line';
                                    }
                                    if (preg_match('/Windows/i', $agent)) {
                                        $os = 'Windows';
                                        $osIcon = 'ri-windows-line';
                                    } elseif (preg_match('/Macintosh|Mac OS/i', $agent)) {
                                        $os = 'MacOS';
                                        $osIcon = 'ri-mac-line';
                                    } elseif (preg_match('/Linux/i', $agent)) {
                                        $os = 'Linux';
                                        $osIcon = 'ri-linux-line';
                                    } elseif (preg_match('/Android/i', $agent)) {
                                        $os = 'Android';
                                        $osIcon = 'ri-android-line';
                                    } elseif (preg_match('/iPhone|iPad|iOS/i', $agent)) {
                                        $os = 'iOS';
                                        $osIcon = 'ri-apple-line';
                                    }
                                @endphp
                                <span class="agent-info" title="{{ $agent }}">
                                    <span class="agent-browser">
                                        <i class="{{ $browserIcon }}"></i> {{ $browser }}
                                    </span>
                                    <span class="agent-dot">•</span>
                                    <span class="agent-os">
                                        <i class="{{ $osIcon }}"></i> {{ $os }}
                                    </span>
                                </span>
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
                            <span class="badge badge-success"><i class="ri-checkbox-circle-fill"></i> Sesión
                                actual</span>
                        @else
                            <form method="POST" action="{{ route('admin.profile.logout-session') }}"
                                style="display:inline;" class="logout-session-form">
                                @csrf
                                <input type="hidden" name="session_id" value="{{ $session->id }}">
                                <button type="button" class="boton-form boton-danger logout-session-btn"
                                    title="Cerrar sesión">
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
