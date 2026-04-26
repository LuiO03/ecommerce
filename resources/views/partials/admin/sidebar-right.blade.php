<!-- SIDEBAR DERECHO (Perfil de usuario) -->
@php
    $user = auth()->user();
@endphp

@if ($user)
    <aside id="userSidebar" class="sidebar-usuario z-[60] translate-x-full">

        @php
            $hasAvatarImage = $user->image && Storage::disk('public')->exists($user->image);
            $unreadCount = $user->unreadNotifications()->count();
            $notifications = $user->notifications()->latest()->limit(15)->get();
        @endphp

        <div class="sidebar-user-contenido">
            <div class="sidebar-tabs">
                <button type="button" class="sidebar-tab active" data-sidebar-tab="profile">
                    <i class="ri-user-line"></i>
                    <span>Perfil</span>
                </button>
                <button type="button" class="sidebar-tab" data-sidebar-tab="notifications">
                    <div>
                        <i class="ri-notification-3-line"></i>
                        @if ($unreadCount > 0)
                            x
                            <span id="sidebarNotificationCount">
                                {{ $unreadCount }}
                            </span>
                        @endif
                    </div>
                    <span>Notificaciones</span>

                </button>
            </div>

            <div class="sidebar-sections-wrapper">
                <div class="sidebar-section active" data-sidebar-section="profile">
                    <div class="sidebar-cuerpo">
                        <div
                            class="fondo-usuario w-full {{ $user->background_style && $user->background_style !== '' ? $user->background_style : 'fondo-estilo-2' }}">
                            @if ($hasAvatarImage)
                                <img src="{{ asset('storage/' . $user->image) }}" alt="{{ $user->name }}"
                                    class="sidebar-avatar">
                            @else
                                <div class="sidebar-avatar"
                                    style="background-color: {{ $user->avatar_colors['background'] }};
                                            color: {{ $user->avatar_colors['color'] }}; border-color: {{ $user->avatar_colors['color'] }};">
                                    {{ $user->initials }}
                                </div>
                            @endif
                        </div>
                        <div class="info-usuario">
                            <!-- nombre y apellido -->
                            <span class="avatar-nombre">{{ $user->name }} {{ $user->last_name }}</span>
                            <p class="avatar-rol">{{ $user->role_list ?? 'Sin rol' }}(a)</p>
                        </div>
                    </div>

                    <hr class="w-full my-0 border-default">

                    <div class="flex flex-col items-center">
                        <ul class="w-full text-left space-y-1">
                            <li>
                                <a href="{{ route('admin.profile.index') }}" class="menu-item">
                                    <i class="ri-user-line sidebar-icon"></i>
                                    <span>Ver perfil</span>
                                </a>
                            </li>
                            <!-- ver sitio web -->
                            <li>
                                <a href="{{ route('site.home') }}" target="_blank" class="menu-item">
                                    <i class="ri-global-line sidebar-icon"></i>
                                    <span>Ver sitio web</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="menu-item">
                                    <i class="ri-settings-3-line sidebar-icon"></i>
                                    <span>Configuración</span>
                                </a>
                            </li>
                            <li>
                                <form id="logoutFormRight" action="{{ route('logout') }}" method="POST"
                                    onsubmit="return false;">
                                    @csrf
                                    <button type="button" id="logoutBtnRight" class="menu-item-logout"
                                        title="Cerrar sesión" aria-label="Cerrar sesión">
                                        <i class="ri-shut-down-line sidebar-icon"></i>
                                        <span>Cerrar sesión</span>
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="sidebar-section" data-sidebar-section="notifications">
                    <span class="sidebar-user-titulo">Notificaciones</span>

                    @if ($notifications->isEmpty())
                        <div class="data-empty">
                            <i class="ri-notification-off-line"></i>
                            <span>
                                No tienes notificaciones por
                                ahora.
                            </span>
                        </div>
                    @else
                        <ul class="sidebar-notifications space-y-1 mt-1">
                            @foreach ($notifications as $notification)
                                @php
                                    $data = $notification->data ?? [];
                                @endphp
                                <li>
                                    <a href="{{ route('admin.notifications.redirect', $notification) }}"
                                        class="menu-item sidebar-notification-item {{ is_null($notification->read_at) ? 'is-unread' : '' }}">
                                        <i class="{{ $data['icon'] ?? 'ri-notification-3-line' }} sidebar-icon"></i>
                                        <div class="sidebar-notification-text">
                                            <span
                                                class="sidebar-notification-title">{{ $data['title'] ?? 'Notificación' }}</span>

                                            @if (!empty($data['body'] ?? null))
                                                <span class="sidebar-notification-body">{{ $data['body'] }}</span>
                                            @endif

                                            <span class="sidebar-notification-meta">
                                                {{ $notification->created_at?->diffForHumans() }}
                                            </span>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        @if ($unreadCount > 0)
                            <form method="POST" action="{{ route('admin.notifications.mark-all-as-read') }}"
                                class="mt-2 text-right">
                                @csrf
                                <button type="submit"
                                    class="sidebar-mark-all-btn text-xs font-medium text-accent hover:underline">
                                    Marcar todas como leídas
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </aside>
@endif
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const logoutBtnRight = document.getElementById('logoutBtnRight');
        const logoutFormRight = document.getElementById('logoutFormRight');
        if (logoutBtnRight && logoutFormRight) {
            logoutBtnRight.addEventListener('click', function(e) {
                e.preventDefault();
                if (typeof window.showConfirm === 'function') {
                    window.showConfirm({
                        type: 'danger',
                        header: 'Cerrar sesión',
                        title: '¿Deseas salir de tu cuenta?',
                        message: 'Se cerrará tu sesión actual.',
                        confirmText: 'Sí, salir',
                        cancelText: 'Cancelar',
                        onConfirm: function() {
                            logoutFormRight.submit();
                        }
                    });
                } else {
                    logoutFormRight.submit();
                }
            });
        }

        const sidebar = document.getElementById('userSidebar');
        const tabs = sidebar?.querySelectorAll('[data-sidebar-tab]');
        const sections = sidebar?.querySelectorAll('[data-sidebar-section]');

        if (tabs && sections) {
            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    const target = tab.getAttribute('data-sidebar-tab');
                    if (!target) return;

                    tabs.forEach(t => t.classList.toggle('active', t === tab));
                    sections.forEach(section => {
                        const isActive = section.getAttribute(
                            'data-sidebar-section') === target;
                        section.classList.toggle('active', isActive);
                    });
                });
            });
        }
    });
</script>
