<div>
    <header class="site-header" id="siteHeader">
        <div class="site-header-container">
            <div class="header-actions">
                <!-- Menú hamburguesa -->
                <button id="siteMenuToggle" class="menu-toggle nav-icon" aria-label="Abrir menú">
                    <i class="ri-menu-fill"></i>
                </button>

                <!-- Logo -->
                <a class="site-logo" href="{{ route('site.home') }}" aria-label="Ir a la página principal">
                    @include('partials.admin.company-brand')
                </a>
            </div>
            <div class="header-actions">
                <!-- Navegación principal (solo desktop) -->
                <nav class="site-nav-desktop">
                    <a href="{{ route('about.index') }}" class="site-nav-link">Nosotros</a>
                    <a href="{{ route('site.blog.index') }}" class="site-nav-link">Blog</a>
                    <a href="{{ route('contact.index') }}" class="site-nav-link">Contacto</a>
                </nav>

            </div>

            <!-- Acciones del header -->
            <div class="header-actions">
                <!-- Buscador desktop como icono -->

                <button type="button" class="nav-icon" data-site-search-open aria-label="Buscar productos"
                    title="Buscar productos">
                    <i class="ri-search-2-line"></i>
                </button>

                <!-- Icono carrito de compras -->
                <a href="{{ route('carts.show') }}" class="nav-icon" aria-label="Carrito de compras"
                    title="Ver carrito de compras">
                    <i class="ri-shopping-cart-2-line"></i>
                    @if ($cartCount > 0)
                        <span class="nav-icon-badge">{{ $cartCount }}</span>
                    @endif
                </a>
                <!-- Icono whishlist (solo desktop) -->
                <div class="button-desktop">
                    <a href="{{ route('wishlists.index') }}" class="nav-icon" aria-label="Lista de deseos"
                        title="Ver lista de deseos">
                        <i class="ri-heart-3-line"></i>
                        @if ($wishlistCount > 0)
                            <span class="nav-icon-badge">{{ $wishlistCount }}</span>
                        @endif
                    </a>
                </div>
                <!-- Botón de inicio de sesión / usuario -->
                @guest
                    <a href="{{ route('login') }}" class="nav-login-button" title="Iniciar sesión">
                        <span class="boton-icon">
                            <i class="ri-user-line"></i>
                        </span>

                        <span class="boton-text">Iniciar Sesión</span>
                    </a>
                @else
                    <div class="nav-user-dropdown">
                        <button class="nav-user-button" id="userMenuBtn" aria-label="Mi cuenta" aria-haspopup="true">
                            @if ($hasAvatarImage)
                                <img class="nav-avatar" src="{{ asset('storage/' . $user->image) }}"
                                    alt="{{ $user->name }}">
                            @else
                                <div class="nav-avatar"
                                    style="background-color: {{ $user->avatar_colors['background'] }};
                                        color: {{ $user->avatar_colors['color'] }};">
                                    {{ $user->initials }}
                                </div>
                            @endif
                            <i class="ri-arrow-down-s-line dropdown-arrow"></i>
                        </button>
                        <div class="nav-user-menu" id="userMenu">
                            <a href="{{ route('site.profile.index') }}" class="nav-user-menu-item">
                                <i class="ri-user-line"></i>
                                <span>Mi Cuenta</span>
                            </a>
                            <div class="button-mobile">
                                <a href="{{ route('wishlists.index') }}" class="nav-user-menu-item">
                                    <i class="ri-heart-3-line"></i>
                                    <span>Mi Lista de Deseos</span>
                                </a>
                            </div>
                            <a href="{{ route('site.profile.orders') }}" class="nav-user-menu-item">
                                <i class="ri-shopping-bag-line"></i>
                                <span>Mis Pedidos</span>
                            </a>
                            <a href="{{ route('site.profile.addresses') }}" class="nav-user-menu-item">
                                <i class="ri-map-pin-line"></i>
                                <span>Mis Direcciones</span>
                            </a>
                            <a href="{{ route('site.profile.security') }}" class="nav-user-menu-item">
                                <i class="ri-settings-3-line"></i>
                                <span>Configuración</span>
                            </a>
                            <form method="POST" action="{{ route('logout') }}" style="display: none;" id="logoutForm">
                                @csrf
                            </form>
                            <a href="{{ route('logout') }}" class="nav-user-menu-item logout"
                                onclick="event.preventDefault(); document.getElementById('logoutForm').submit();">
                                <i class="ri-logout-box-line"></i>
                                <span>Cerrar Sesión</span>
                            </a>
                        </div>
                    </div>
                @endguest
            </div>
        </div>
    </header>

    <!-- Overlay del menú - tapa header y contenido -->
    <div id="siteOverlay" class="site-overlay"></div>

    <!-- Menú lateral izquierdo -->
    <aside id="siteSidebar" class="site-sidebar">
        <!-- Header del sidebar -->
        <div class="site-sidebar-header">
            <span>Menú</span>
            <button id="siteMenuClose" aria-label="Cerrar menú">
                <i class="ri-close-line"></i>
            </button>
        </div>
        <!-- Contenido del sidebar -->
        <div class="site-sidebar-content">
            <!-- Navegación principal -->
            <nav class="site-nav-menu">
                <a href="" class="site-nav-menu-item">
                    <i class="ri-home-line"></i>
                    <span>Inicio</span>
                </a>
                <a href="" class="site-nav-menu-item">
                    <i class="ri-shopping-bag-line"></i>
                    <span>Productos</span>
                </a>
            </nav>
            <hr class="site-nav-divider">

            <!-- Familias (sin subniveles en el panel izquierdo) -->
            <nav class="site-nav-menu">
                <div class="site-nav-section-title">Categorías</div>
                @foreach ($families as $family)
                    <button type="button" class="site-nav-family-link" data-family-id="{{ $family->id }}">
                        <div class="site-nav-family-content">
                            <i class="ri-shopping-basket-2-line"></i>
                            <span>{{ $family->name }}</span>
                        </div>
                        <div class="site-arrow-family-content">
                            <i class="ri-arrow-right-s-line family-arrow"></i>
                        </div>
                    </button>

                    <!-- Panel de categorías (acordeón solo en móvil) -->
                    <div class="site-flyout-panel-mobile" data-family-panel-mobile="{{ $family->id }}">
                        <div class="site-flyout-content">
                            @forelse($family->categories as $category)
                                @include('livewire.site.category-flyout', [
                                    'category' => $category,
                                    'level' => 0,
                                ])
                            @empty
                                <div class="site-flyout-empty">Sin categorías disponibles</div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </nav>
        </div>

        <!-- Panel derecho de categorías por familia (solo desktop/tablet) -->
        <div class="site-sidebar-flyout">
            @foreach ($families as $family)
                <div class="site-flyout-panel" data-family-panel="{{ $family->id }}">
                    <div class="site-flyout-header">
                        <span class="site-flyout-header-title">{{ $family->name }}</span>
                        <a href="{{ route('families.show', $family) }}" class="site-btn site-btn-primary">
                            <span class="boton-form-icon">
                                <i class="ri-eye-line"></i>
                            </span>
                            <span class="boton-form-text">Ver Todo</span>
                        </a>
                    </div>
                    <div class="site-flyout-content">
                        @forelse($family->categories as $category)
                            @include('livewire.site.category-flyout', [
                                'category' => $category,
                                'level' => 0,
                            ])
                        @empty
                            <div class="site-flyout-empty">Sin categorías disponibles</div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </aside>

    @include('partials.site.search-modal')
</div>
