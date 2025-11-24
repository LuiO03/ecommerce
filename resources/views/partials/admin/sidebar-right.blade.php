<!-- SIDEBAR DERECHO (Perfil de usuario) -->
<aside id="userSidebar"
    class="sidebar-usuario z-[60] translate-x-full">

    <div class="sidebar-user-contenido">
        <div class="sidebar-cuerpo">
            <div class="fondo-usuario w-full">
                @if (auth()->user()->has_local_photo)
                    <img src="{{ asset('storage/' . auth()->user()->profile_photo_path) }}" alt="{{ auth()->user()->name }}"
                        class="sidebar-avatar">
                @else
                    <div class="sidebar-avatar"
                        style="background-color: {{ auth()->user()->avatar_colors['background'] }};
                                    color: {{ auth()->user()->avatar_colors['color'] }}; border-color: {{ auth()->user()->avatar_colors['color'] }};">
                        {{ auth()->user()->initials }}
                    </div>
                @endif
            </div>
            <div class="info-usuario">
                <span class="avatar-nombre">{{ auth()->user()->name }}</span>
                <p class="avatar-rol">{{ auth()->user()->role_list ?? 'Sin rol' }}</p>
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
                <li>
                    <a href="#" class="menu-item">
                        <i class="ri-settings-3-line sidebar-icon"></i>
                        <span>Configuración</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="menu-item-logout">
                        <i class="ri-shut-down-line sidebar-icon"></i>
                        <span>Cerrar sesión</span>
                    </a>
                </li>
            </ul>
        </div>

        <span class="sidebar-user-titulo">Notificaciones</span>
        <ul class="sidebar-notifications space-y-1">
            <li>
                <a href="#" class="menu-item">
                    <i class="ri-notification-3-line sidebar-icon"></i> Tienes 3 tareas pendientes
                </a>
            </li>
            <li>
                <a href="#" class="menu-item">
                    <i class="ri-mail-line sidebar-icon"></i> Nuevo mensaje de soporte
                </a>
            </li>
            <li>
                <a href="#" class="menu-item">
                    <i class="ri-calendar-event-line sidebar-icon"></i> Reunión hoy a las 3 PM
                </a>
            </li>
        </ul>
    </div>
</aside>
