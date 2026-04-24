<nav class="navbar">
    <button id="toggleSidebarWidth" class="navbar-boton-desktop ripple-btn">
        <i class="ri-arrow-left-double-fill"></i>
    </button>
    <button id="openLeftSidebarBtn" aria-controls="logo-sidebar" type="button" class="navbar-boton-mobile ripple-btn"
        title="Abrir menú lateral">
        <span class="sr-only">Open sidebar tablet</span>
        <i class="ri-menu-2-line"></i>
    </button>
    <div class="topbar-center">
        <!-- Logo visible solo en tablet/móvil -->
        <a href="#" class="navbar-logo sm:hidden flex items-center gap-2">
            <div class="navbar-logo-texto"><strong>Gecko</strong><span>merce</span></div>
        </a>

        <!-- Fecha visible solo en escritorio -->
        <span class="current-date hidden sm:inline">
            Hoy es {{ fecha_hoy() }}
        </span>
    </div>

    <div class="topbar-right">
        @php
            $user = auth()->user();
            $unreadCount = $user ? $user->unreadNotifications()->count() : 0;
        @endphp

        <!-- boton para ir al sitio web -->
        <a class="topbar-icon-btn" href="{{ route('site.home') }}" target="_blank" title="Ir al sitio web">
            <i class="ri-global-line"></i>
        </a>
        <!-- boton para ir a las notificaciones -->
        <button id="notificationSidebarToggle" class="topbar-icon-btn" title="Ver notificaciones">
            <i class="ri-notification-2-line"></i>
            @if ($unreadCount > 0)
                <span class="notification-badge">{{ $unreadCount }}</span>
            @endif
        </button>
        <!-- boton para ir al perfil de usuario -->
        @if ($user)
            <div class="flex items-center ms-2">
                <a href="{{ route('admin.profile.index') }}" title="Perfil de usuario">
                    @php
                        $hasAvatarImage = $user->image && Storage::disk('public')->exists($user->image);
                    @endphp

                    @if ($hasAvatarImage)
                        <img class="topbar-avatar" src="{{ asset('storage/' . $user->image) }}"
                            alt="{{ $user->name }}">
                    @else
                        <div class="topbar-avatar"
                            style="background-color: {{ $user->avatar_colors['background'] }};
                           color: {{ $user->avatar_colors['color'] }};">
                            {{ $user->initials }}
                        </div>
                    @endif
                </a>
            </div>
        @endif

        <div class="topbar-user-menu">
            <button id="userSidebarToggle" class="hamburger-btn z-[70]" aria-label="Abrir menú de usuario">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
        </div>
    </div>
</nav>
