<nav class="navbar">
    <button id="toggleSidebarWidth" class="hidden sm:flex navbar-boton">
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
        <div class="flex items-center ms-3">
            <div>
                @if (auth()->user()->has_local_photo)
                    <img class="topbar-avatar" src="{{ asset('storage/' . auth()->user()->profile_photo_path) }}"
                        alt="{{ auth()->user()->name }}">
                @else
                    <div class="topbar-avatar"
                        style="background-color: {{ auth()->user()->avatar_colors['background'] }};
                       color: {{ auth()->user()->avatar_colors['color'] }};">
                        {{ auth()->user()->initials }}
                    </div>
                @endif
            </div>

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
