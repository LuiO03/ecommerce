<div>
    <header class="site-header site-header-sticky" id="siteHeader">
        <div class="site-header-container">
            <div class="site-header-top">
                <!-- Menú hamburguesa -->
                <button id="siteMenuToggle" @click="$dispatch('toggle-sidebar')" class="menu-toggle nav-icon" aria-label="Abrir menú">
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
                        <input
                            type="search"
                            name="q"
                            class="site-search-input"
                            placeholder="Buscar productos, marcas o categorías..."
                            autocomplete="off"
                        />
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
                    <input
                        type="search"
                        name="q"
                        class="site-search-input"
                        placeholder="Buscar en la tienda..."
                        autocomplete="off"
                    />
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
            <button id="siteMenuClose" @click="$dispatch('toggle-sidebar')" aria-label="Cerrar menú">
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

            <!-- Familias/Categorías -->
            <nav class="site-nav-menu">
                <div class="site-nav-section-title">Categorías</div>
                @foreach($families as $family)
                    <div class="site-nav-family">
                        <a href="" class="site-nav-family-link">
                            <div class="site-nav-family-content">
                                <i class="ri-shopping-basket-2-line"></i>
                                <span>{{ $family->name }}</span>
                            </div>
                            @if($family->categories->count() > 0)
                                <i class="ri-arrow-down-s-line family-arrow"></i>
                            @endif
                        </a>

                        @if($family->categories->count() > 0)
                            <div class="site-nav-categories">
                                @foreach($family->categories as $category)
                                    @include('livewire.category-tree', ['category' => $category])
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </nav>
        </div>
    </aside>
</div>
