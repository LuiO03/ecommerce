<div>
    <header class="site-header site-header-sticky" id="siteHeader">
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
                    <a href="" class="nav-icon" aria-label="Carrito de compras">
                        <i class="ri-shopping-cart-2-line"></i>
                        <span class="nav-icon-badge">3</span>
                    </a>
                    <a href="" class="nav-icon" aria-label="Notificaciones">
                        <i class="ri-notification-3-line"></i>
                        <span class="nav-icon-badge">5</span>
                    </a>
                    <a href="{{ route('login') }}" class="nav-icon" aria-label="Mi cuenta">
                        <i class="ri-user-line"></i>
                    </a>
                </div>
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
                @endforeach
            </nav>
        </div>

        <!-- Panel derecho de categorías por familia (aparece al hover) -->
        <div class="site-sidebar-flyout">
            @foreach ($families as $family)
                <div class="site-flyout-panel" data-family-panel="{{ $family->id }}">
                    <div class="site-flyout-header">
                        <span class="site-flyout-header-title">{{ $family->name }}</span>
                        <button class="boton-form boton-accent" type="submit" id="submitBtn">
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
