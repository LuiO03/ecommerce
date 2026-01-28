<nav class="navbar">
    <button id="toggleSidebarWidth" class="hidden sm:flex navbar-boton ripple-btn">
        <i class="ri-arrow-left-double-fill"></i>
    </button>
    <div class="sm:hidden">
        <button id="openLeftSidebarBtn" aria-controls="logo-sidebar" type="button" class="navbar-boton">
            <span class="sr-only">Open sidebar tablet</span>
            <i class="ri-menu-2-line"></i>
        </button>
    </div>
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
            $unreadCount = auth()->user()->unreadNotifications()->count();
        @endphp
        <button id="notificationSidebarToggle" class="topbar-icon-btn" aria-label="Ver notificaciones">
            <i class="ri-notification-2-line"></i>
            @if ($unreadCount > 0)
                <span class="notification-badge">{{ $unreadCount }}</span>
            @endif
        </button>
        <div class="flex items-center ms-2">
            <a href="{{ route('admin.profile.index') }}" title="Perfil de usuario">
                @php
                    $user = auth()->user();
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

        <div class="topbar-user-menu">
            <button id="userSidebarToggle" class="hamburger-btn z-[70]" aria-label="Abrir menú de usuario">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
        </div>
    </div>
</nav>
