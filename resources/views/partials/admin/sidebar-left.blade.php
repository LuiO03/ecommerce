<!-- SIDEBAR IZQUIERDO -->
<aside id="logo-sidebar" class="sidebar-principal z-40 w-64 -translate-x-full sm:translate-x-0" aria-label="Sidebar">

    <div class="sidebar-header">
        <a href="{{ request()->routeIs('admin.dashboard') ? '#' : route('admin.dashboard') }}"
            class="flex items-center gap-2">
            @include('partials.admin.company-brand')
        </a>
    </div>

    <div class="sidebar-contenido">
        <ul class="space-y-1">
            <li>
                <a href="{{ request()->routeIs('admin.dashboard') ? '#' : route('admin.dashboard') }}"
                    class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                    data-tooltip="Dashboard">
                    <i class="ri-dashboard-line sidebar-icon"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- Portadas -->
            @can('portadas.index')
                <li>
                    <a href="{{ request()->routeIs('admin.covers.*') ? '#' : route('admin.covers.index') }}"
                        class="sidebar-link {{ request()->routeIs('admin.covers.*') ? 'active' : '' }}"
                        data-tooltip="Portadas">
                        <i class="ri-image-2-line sidebar-icon"></i>
                        <span>Portadas</span>
                    </a>
                </li>
            @endcan
            <!-- Submenú Finanzas -->
            <li class="submenu-container">
                <button type="button" class="sidebar-link w-full submenu-btn flex items-center"
                    data-tooltip="Finanzas">
                    <i class="ri-money-dollar-circle-line sidebar-icon"></i>
                    <span class="flex-1 text-left">Finanzas</span>
                    <i class="ri-arrow-down-s-line submenu-arrow transition-transform duration-300"></i>
                </button>

                <ul id="dropdown-tienda" class="sidebar-submenu space-y-1">
                    <li>
                        <a href="{{ request()->routeIs('admin.payments.*') ? '#' : route('admin.payments.index') }}"
                            class="sidebar-sublink {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}"
                            data-tooltip="Pagos">
                            <i class="ri-bank-card-line sidebar-icon"></i>
                            <span>Pagos</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ request()->routeIs('admin.transactions.*') ? '#' : route('admin.transactions.index') }}"
                            class="sidebar-sublink {{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}"
                            data-tooltip="Transacciones">
                            <i class="ri-exchange-dollar-line sidebar-icon"></i>
                            <span>Transacciones</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Pedidos -->
            @can('ordenes.index')
                <li>
                    <a href="{{ request()->routeIs('admin.orders.*') ? '#' : route('admin.orders.index') }}"
                        class="sidebar-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"
                        data-tooltip="Pedidos">
                        <i class="ri-shopping-bag-3-line sidebar-icon"></i>
                        <span>Pedidos</span>
                    </a>
                </li>
            @endcan

            <!-- Conductores -->
            @can('conductores.index')
                <li>
                    <a href="{{ request()->routeIs('admin.drivers.*') ? '#' : route('admin.drivers.index') }}"
                        class="sidebar-link {{ request()->routeIs('admin.drivers.*') ? 'active' : '' }}"
                        data-tooltip="Conductores">
                        <i class="ri-user-2-line sidebar-icon"></i>
                        <span>Conductores</span>
                    </a>
                </li>
            @endcan

            <!-- Submenú Tienda -->
            @canany(['familias.index', 'categorias.index', 'productos.index'])
                <li class="submenu-container">
                    <button type="button" class="sidebar-link w-full submenu-btn flex items-center" data-tooltip="Tienda">
                        <i class="ri-store-line sidebar-icon"></i>
                        <span class="flex-1 text-left">Tienda</span>
                        <i class="ri-arrow-down-s-line submenu-arrow transition-transform duration-300"></i>
                    </button>

                    <ul id="dropdown-tienda" class="sidebar-submenu space-y-1">

                        @can('familias.index')
                            <li>
                                <a href="{{ request()->routeIs('admin.families.*') ? '#' : route('admin.families.index') }}"
                                    class="sidebar-sublink {{ request()->routeIs('admin.families.*') ? 'active' : '' }}"
                                    data-tooltip="Familias">
                                    <i class="ri-apps-line sidebar-icon"></i>
                                    <span>Familias</span>
                                </a>
                            </li>
                        @endcan

                        @can('categorias.index')
                            <li>
                                <a href="{{ request()->routeIs('admin.categories.*') ? '#' : route('admin.categories.index') }}"
                                    class="sidebar-sublink {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"
                                    data-tooltip="Categorías">
                                    <i aria-busy=""class="ri-price-tag-3-line sidebar-icon"></i>
                                    <span>Categorías</span>
                                </a>
                            </li>
                        @endcan
                        @can('productos.index')
                            <li>
                                <a href="{{ request()->routeIs('admin.products.*') ? '#' : route('admin.products.index') }}"
                                    class="sidebar-sublink {{ request()->routeIs('admin.products.*') ? 'active' : '' }}"
                                    data-tooltip="Productos">
                                    <i class="ri-box-3-line sidebar-icon"></i>
                                    <span>Productos</span>
                                </a>
                            </li>
                        @endcan

                    </ul>
                </li>
            @endcanany

            <!-- Posts -->
            @can('posts.index')
                <li>
                    <a href="{{ request()->routeIs('admin.posts.*') ? '#' : route('admin.posts.index') }}"
                        class="sidebar-link {{ request()->routeIs('admin.posts.*') ? 'active' : '' }}"
                        data-tooltip="Posts">
                        <i class="ri-article-line sidebar-icon"></i>
                        <span>Posts</span>
                    </a>
                </li>
            @endcan

            @can('opciones.index')
                <li>
                    <a href="{{ request()->routeIs('admin.options.*') ? '#' : route('admin.options.index') }}"
                        class="sidebar-link {{ request()->routeIs('admin.options.*') ? 'active' : '' }}"
                        data-tooltip="Opciones y Valores">
                        <i class="ri-settings-3-line sidebar-icon"></i>
                        <span>Opciones y Valores</span>
                    </a>
                </li>
            @endcan
            @can('configuracion.index')
                <li>
                    <a href="{{ request()->routeIs('admin.company-settings.*') ? '#' : route('admin.company-settings.index') }}"
                        class="sidebar-link {{ request()->routeIs('admin.company-settings.*') ? 'active' : '' }}"
                        data-tooltip="Configuración">
                        <i class="ri-building-4-line sidebar-icon"></i>
                        <span>Empresa</span>
                    </a>
                </li>
            @endcan
            <!-- Submenú Gestion de Acceso -->
            @canany(['usuarios.index', 'clientes.index', 'roles.index', 'accesos.index', 'auditorias.index'])
                <li class="submenu-container">
                    <button type="button" class="sidebar-link w-full submenu-btn flex items-center"
                        data-tooltip="Gestión de Acceso">
                        <i class="ri-shield-user-line sidebar-icon"></i>
                        <span class="flex-1 text-left">Gestión de Acceso</span>
                        <i class="ri-arrow-down-s-line submenu-arrow transition-transform duration-300"></i>
                    </button>

                    <ul id="dropdown-acceso" class="sidebar-submenu space-y-1">

                        @can('usuarios.index')
                            <li>
                                <a href="{{ request()->routeIs('admin.users.*') ? '#' : route('admin.users.index') }}"
                                    class="sidebar-sublink {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                                    data-tooltip="Users">
                                    <i class="ri-id-card-line sidebar-icon"></i>
                                    <span>Usuarios</span>
                                </a>
                            </li>
                        @endcan
                        @can('clientes.index')
                            <li>
                                <a href="{{ request()->routeIs('admin.clients.*') ? '#' : route('admin.clients.index') }}"
                                    class="sidebar-sublink {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}"
                                    data-tooltip="Clientes">
                                    <i class="ri-user-5-line sidebar-icon"></i>
                                    <span>Clientes</span>
                                </a>
                            </li>
                        @endcan

                        @can('roles.index')
                            <li>
                                <a href="{{ request()->routeIs('admin.roles.*') ? '#' : route('admin.roles.index') }}"
                                    class="sidebar-sublink {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}"
                                    data-tooltip="Roles y Permisos">
                                    <i class="ri-shield-user-line sidebar-icon"></i>
                                    <span>Roles y Permisos</span>
                                </a>
                            </li>
                        @endcan
                        @can('accesos.index')
                            <li>
                                <a href="{{ request()->routeIs('admin.access-logs.*') ? '#' : route('admin.access-logs.index') }}"
                                    class="sidebar-sublink {{ request()->routeIs('admin.access-logs.*') ? 'active' : '' }}"
                                    data-tooltip="Registros de acceso">
                                    <i class="ri-login-circle-line sidebar-icon"></i>
                                    <span>Accesos</span>
                                </a>
                            </li>
                        @endcan
                        @can('auditorias.index')
                            <li>
                                <a href="{{ request()->routeIs('admin.audits.*') ? '#' : route('admin.audits.index') }}"
                                    class="sidebar-sublink {{ request()->routeIs('admin.audits.*') ? 'active' : '' }}"
                                    data-tooltip="Auditoría">
                                    <i class="ri-file-list-3-line sidebar-icon"></i>
                                    <span>Auditoría</span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany
        </ul>
    </div>

    <!-- switch tema -->
    <div class="sidebar-footer">
        <div id="theme-toggle" class="sidebar-theme-toggle" data-tooltip="Cambiar tema">
            <div class="theme-text flex items-center">
                <i id="theme-toggle-dark-icon" class="ri-moon-fill hidden text-xl mr-2"></i>
                <i id="theme-toggle-light-icon" class="ri-sun-fill hidden text-xl mr-2"></i>
                <span>Cambiar tema</span>
            </div>
            <div id="theme-switch" class="sidebar-switch">
                <div id="switch-handle" class="sidebar-switch-handle"></div>
            </div>
        </div>
    </div>

</aside>
