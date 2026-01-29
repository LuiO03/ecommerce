<div>
    <header class="site-header site-header-sticky">
        <div class="site-header-container">
            <div class="site-header-top">
                <!-- Menú hamburguesa -->
                <div>
                    <button @click="$dispatch('toggle-sidebar')" class="menu-toggle" aria-label="Abrir menú">
                        <i class="ri-menu-fill"></i>
                    </button>
                </div>

                <!-- Logo -->
                <div>
                    <a href="" class="site-logo">
                        <span class="site-logo-title">Geckommerce</span>
                        <span class="site-logo-subtitle">Tienda Virtual</span>
                    </a>
                </div>

                <!-- Buscador desktop -->
                <div class="search-desktop">
                    <x-input class="w-full" placeholder="Buscar producto..." />
                </div>

                <!-- Acciones del header -->
                <div class="header-actions">
                    <a href="" class="nav-icon" aria-label="Carrito de compras">
                        <i class="ri-shopping-cart-2-fill"></i>
                        <span class="nav-icon-badge">3</span>
                    </a>
                    <a href="" class="nav-icon" aria-label="Notificaciones">
                        <i class="ri-notification-3-fill"></i>
                        <span class="nav-icon-badge">5</span>
                    </a>
                    <a href="{{ route('login') }}" class="nav-icon" aria-label="Mi cuenta">
                        <i class="ri-user-fill"></i>
                    </a>
                </div>
            </div>

            <!-- Buscador mobile -->
            <div class="search-mobile">
                <x-input class="w-full" placeholder="Buscar por producto, tienda o marca..." />
            </div>
        </div>
    </header>

    <!-- Overlay del menú -->
    <div
        x-data="{ open: false }"
        @toggle-sidebar.window="open = !open"
        @click="open = false"
        class="nav-overlay"
        :class="{ 'active': open }"
    ></div>

    <!-- Menú lateral -->
    <div
        x-data="{ open: false }"
        @toggle-sidebar.window="open = !open"
        class="nav-sidebar"
        :class="{ 'active': open }"
    >
        <!-- Header del sidebar -->
        <div class="nav-sidebar-header">
            <span>Menú</span>
            <button @click="open = false" aria-label="Cerrar menú">
                <i class="ri-close-line"></i>
            </button>
        </div>

        <!-- Contenido del sidebar -->
        <div class="nav-sidebar-content">
            <nav class="nav-menu">
                <a href="" class="nav-menu-item">
                    <i class="ri-home-line"></i> Inicio
                </a>
                <a href="" class="nav-menu-item">
                    <i class="ri-shopping-bag-line"></i> Productos
                </a>
                <a href="" class="nav-menu-item">
                    <i class="ri-list-check"></i> Categorías
                </a>

                <hr class="nav-menu-divider">

                <a href="" class="nav-menu-item">
                    <i class="ri-truck-line"></i> Mis Pedidos
                </a>
                <a href="" class="nav-menu-item">
                    <i class="ri-user-settings-line"></i> Mi Cuenta
                </a>
                <a href="" class="nav-menu-item">
                    <i class="ri-logout-box-line"></i> Cerrar Sesión
                </a>
            </nav>
        </div>
    </div>
</div>
