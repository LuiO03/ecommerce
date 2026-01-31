<div>
    <header class="site-header site-header-sticky">
        <div class="site-header-container">
            <div class="site-header-top">
                <!-- Menú hamburguesa -->
                <button @click="$dispatch('toggle-sidebar')" class="menu-toggle nav-icon" aria-label="Abrir menú">
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

    <!-- Overlay del menú -->
    <div x-data="{ open: false }" @toggle-sidebar.window="open = !open" @click="open = false" class="nav-overlay"
        :class="{ 'active': open }"></div>

    <!-- Menú lateral -->
    <div x-data="{ open: false }" @toggle-sidebar.window="open = !open" class="nav-sidebar" :class="{ 'active': open }">
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
                <hr class="nav-menu-divider">
            </nav>
            <nav class="nav-menu">
                <ul class="nav-menu-list">
                    @foreach($families as $family)
                        <li class="nav-menu-item">
                            <a href="}}">
                                <i class="ri-drop-line"></i> {{ $family->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </div>
    </div>
</div>
