<div>
    <header class="site-header site-header-sticky">
        <div class="flex flex-col gap-2 justify-between items-center px-5 py-2">
            <div class="flex items-center gap-6 justify-between w-full">
                <!-- Menú hamburguesa -->
                <div>
                    <button @click="$dispatch('toggle-sidebar')" class="menu-toggle" aria-label="Abrir menú">
                        <i class="ri-menu-fill"></i>
                    </button>
                </div>

                <!-- Logo -->
                <div>
                    <a href="{{ route('home') }}" class="site-logo">
                        <span class="site-logo-title">Geckommerce</span>
                        <span class="site-logo-subtitle">Tienda Virtual</span>
                    </a>
                </div>

                <!-- Buscador desktop -->
                <div class="flex-1 hidden md:block">
                    <x-input class="w-full" placeholder="Buscar producto..." />
                </div>

                <!-- Acciones del header -->
                <div class="flex gap-2">
                    <a href="{{ route('cart') }}" class="nav-icon relative" aria-label="Carrito de compras">
                        <i class="ri-shopping-cart-2-fill"></i>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">3</span>
                    </a>
                    <a href="{{ route('notifications') }}" class="nav-icon relative" aria-label="Notificaciones">
                        <i class="ri-notification-3-fill"></i>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">5</span>
                    </a>
                    <a href="{{ route('login') }}" class="nav-icon" aria-label="Mi cuenta">
                        <i class="ri-user-fill"></i>
                    </a>
                </div>
            </div>

            <!-- Buscador mobile -->
            <div class="md:hidden w-full">
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
        <div class="nav-sidebar-header flex justify-between items-center">
            <span>Menú</span>
            <button @click="open = false" class="text-white text-2xl" aria-label="Cerrar menú">
                <i class="ri-close-line"></i>
            </button>
        </div>

        <!-- Contenido del sidebar -->
        <div class="p-4">
            <nav class="space-y-2">
                <a href="{{ route('home') }}" class="block px-4 py-2 text-gray-700 hover:bg-purple-50 hover:text-purple-600 rounded transition-colors">
                    <i class="ri-home-line mr-2"></i> Inicio
                </a>
                <a href="{{ route('products') }}" class="block px-4 py-2 text-gray-700 hover:bg-purple-50 hover:text-purple-600 rounded transition-colors">
                    <i class="ri-shopping-bag-line mr-2"></i> Productos
                </a>
                <a href="{{ route('categories') }}" class="block px-4 py-2 text-gray-700 hover:bg-purple-50 hover:text-purple-600 rounded transition-colors">
                    <i class="ri-list-check mr-2"></i> Categorías
                </a>

                <hr class="my-4">

                <a href="{{ route('orders') }}" class="block px-4 py-2 text-gray-700 hover:bg-purple-50 hover:text-purple-600 rounded transition-colors">
                    <i class="ri-truck-line mr-2"></i> Mis Pedidos
                </a>
                <a href="{{ route('profile') }}" class="block px-4 py-2 text-gray-700 hover:bg-purple-50 hover:text-purple-600 rounded transition-colors">
                    <i class="ri-user-settings-line mr-2"></i> Mi Cuenta
                </a>
                <a href="{{ route('logout') }}" class="block px-4 py-2 text-gray-700 hover:bg-purple-50 hover:text-purple-600 rounded transition-colors">
                    <i class="ri-logout-box-line mr-2"></i> Cerrar Sesión
                </a>
            </nav>
        </div>
    </div>
</div>
