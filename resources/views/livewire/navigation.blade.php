<div>
    <header class="site-header" id="siteHeader">
        <div class="site-header-container">
            <div class="site-header-top">
                <!-- Menú hamburguesa -->
                <button id="siteMenuToggle" class="menu-toggle nav-icon" aria-label="Abrir menú">
                    <i class="ri-menu-fill"></i>
                </button>

                <!-- Logo -->
                <a href="" class="site-logo">
                    <span class="site-logo-title">Geckommerce</span>
                    <span class="site-logo-subtitle">Tienda Virtual</span>
                </a>

                <!-- Buscador desktop -->
                <div class="search-desktop">
                    <form class="site-search" role="search" aria-label="Buscar productos">
                        <input type="search" name="q" class="site-search-input"
                            placeholder="Buscar productos, marcas o categorías..." autocomplete="off" />
                        <button type="submit" class="site-search-btn" aria-label="Buscar">
                            <i class="ri-search-2-line"></i>
                            <span>Buscar</span>
                        </button>
                    </form>
                </div>

                <!-- Acciones del header -->
                <div class="header-actions">
                    <!-- Icono carrito de compras -->
                    <a href="" class="nav-icon" aria-label="Carrito de compras" title="Ver carrito de compras">
                        <i class="ri-shopping-cart-2-line"></i>
                        <span class="nav-icon-badge">3</span>
                    </a>
                    <!-- Icono whishlist (solo desktop) -->
                    <a href="" class="nav-icon" aria-label="Lista de deseos" title="Ver lista de deseos">
                        <i class="ri-heart-line"></i>
                        <span class="nav-icon-badge">5</span>
                    </a>
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
                                <span class="boton-icon">
                                    <i class="ri-user-line"></i>
                                </span>
                                <span class="boton-text">{{ Auth::user()->name }}</span>
                                <i class="ri-arrow-down-s-line dropdown-arrow"></i>
                            </button>
                            <div class="nav-user-menu" id="userMenu">
                                <a href="{{ route('profile.show') }}" class="nav-user-menu-item">
                                    <i class="ri-user-line"></i>
                                    <span>Mi Perfil</span>
                                </a>
                                <a href="" class="nav-user-menu-item">
                                    <i class="ri-shopping-bag-line"></i>
                                    <span>Mis Pedidos</span>
                                </a>
                                <a href="" class="nav-user-menu-item">
                                    <i class="ri-map-pin-line"></i>
                                    <span>Mis Direcciones</span>
                                </a>
                                <a href="" class="nav-user-menu-item">
                                    <i class="ri-settings-3-line"></i>
                                    <span>Configuración</span>
                                </a>
                                <hr class="nav-user-divider">
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

        <!-- Buscador mobile -->
        <div class="search-mobile">
            <form class="site-search site-search-mobile" role="search" aria-label="Buscar productos">
                <input type="search" name="q" class="site-search-input" placeholder="Buscar en la tienda..."
                    autocomplete="off" />
                <button type="submit" class="site-search-btn" aria-label="Buscar">
                    <i class="ri-search-2-line"></i>
                    <span>Buscar</span>
                </button>
            </form>
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
                <a href="" class="site-nav-menu-item">
                    <i class="ri-list-check"></i>
                    <span>Categorías</span>
                </a>

                <hr class="site-nav-divider">

                <a href="" class="site-nav-menu-item">
                    <i class="ri-truck-line"></i>
                    <span>Mis Pedidos</span>
                </a>
                <a href="" class="site-nav-menu-item">
                    <i class="ri-user-settings-line"></i>
                    <span>Mi Cuenta</span>
                </a>
                <a href="" class="site-nav-menu-item">
                    <i class="ri-logout-box-line"></i>
                    <span>Cerrar Sesión</span>
                </a>

                <hr class="site-nav-divider">
            </nav>

            <!-- Familias (sin subniveles en el panel izquierdo) -->
            <nav class="site-nav-menu">
                <div class="site-nav-section-title">Categorías</div>
                @foreach ($families as $family)
                    <button type="button" class="site-nav-family-link" data-family-id="{{ $family->id }}">
                        <div class="site-nav-family-content">
                            <i class="ri-shopping-basket-2-line"></i>
                            <span>{{ $family->name }}</span>
                        </div>
                        <i class="ri-arrow-right-s-line family-arrow"></i>
                    </button>

                    <!-- Panel de categorías (acordeón solo en móvil) -->
                    <div class="site-flyout-panel-mobile" data-family-panel-mobile="{{ $family->id }}">
                        <div class="site-flyout-content">
                            @forelse($family->categories as $category)
                                @include('livewire.category-flyout', [
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
                        <button class="boton-form boton-accent" type="submit">
                            <span class="boton-form-icon">
                                <i class="ri-eye-line"></i>
                            </span>
                            <span class="boton-form-text">Ver Todo</span>
                        </button>
                    </div>
                    <div class="site-flyout-content">
                        @forelse($family->categories as $category)
                            @include('livewire.category-flyout', ['category' => $category, 'level' => 0])
                        @empty
                            <div class="site-flyout-empty">Sin categorías disponibles</div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </aside>
</div>
