@php
    $links = [
        [
            'label' => 'Dashboard',
            'icon' => 'ri-dashboard-line',
            'route' => 'admin.dashboard',
        ],
        [
            'label' => 'Kanban',
            'icon' => 'ri-stack-line',
            'route' => 'admin.kanban',
            'badge' => 'Pro',
        ],
        [
            'label' => 'Inbox',
            'icon' => 'ri-mail-line',
            'route' => 'admin.inbox',
            'badge' => '3',
        ],
        [
            'label' => 'Gestión',
            'icon' => 'ri-settings-3-line',
            'submenu' => [
                [
                    'label' => 'Categorías',
                    'icon' => 'ri-apps-line',
                    'route' => 'admin.categorias',
                ],
                [
                    'label' => 'Marcas',
                    'icon' => 'ri-price-tag-3-line',
                    'route' => 'admin.marcas',
                ],
                [
                    'label' => 'Productos',
                    'icon' => 'ri-box-3-line',
                    'route' => 'admin.productos',
                ],
            ],
        ],
        [
            'label' => 'Usuarios',
            'icon' => 'ri-id-card-line',
            'route' => 'admin.usuarios',
        ],
        [
            'label' => 'Productos',
            'icon' => 'ri-shopping-bag-3-line',
            'route' => 'admin.products',
        ],
    ];

    // Función helper para manejar rutas inexistentes
    function safe_route($name) {
        try {
            return route($name);
        } catch (\Exception $e) {
            return '#';
        }
    }
@endphp

<!-- SIDEBAR IZQUIERDO -->
<aside id="logo-sidebar" class="sidebar-principal z-40 w-64 -translate-x-full sm:translate-x-0" aria-label="Sidebar">

    <div class="sidebar-header">
        <a href="#" class="flex items-center gap-2">
            <img src="https://flowbite.com/docs/images/logo.svg" class="h-8" alt="Logo">
            <div class="sidebar-logo-texto"><strong>Gecko</strong><span>merce</span></div>
        </a>
    </div>

    <div class="sidebar-contenido">
        <ul class="space-y-1">
            @foreach ($links as $link)
            
            @endforeach
            <li>
                <a href="#" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" data-tooltip="Dashboard">
                    <i class="ri-dashboard-line sidebar-icon"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="#" class="sidebar-link" data-tooltip="Kanban">
                    <i class="ri-stack-line sidebar-icon"></i>
                    <span>Kanban</span>
                    <span class="sidebar-badge">Pro</span>
                </a>
            </li>
            <li>
                <a href="#" class="sidebar-link" data-tooltip="Inbox">
                    <i class="ri-mail-line sidebar-icon"></i>
                    <span>Inbox</span>
                    <span class="sidebar-badge">3</span>
                </a>
            </li>
            <li>
                <a href="admin/users" class="sidebar-link {{ request()->routeIs('admin.users') ? 'active' : '' }}" data-tooltip="Users">
                    <i class="ri-id-card-line sidebar-icon"></i>
                    <span>Users</span>
                </a>
            </li>

            <!-- Submenú Gestión -->
            <li class="submenu-container">
                <button type="button" class="sidebar-link w-full submenu-btn flex items-center" data-tooltip="Gestión">
                    <i class="ri-settings-3-line sidebar-icon"></i>
                    <span class="flex-1 text-left">Gestión</span>
                    <i class="ri-arrow-down-s-line submenu-arrow transition-transform duration-300"></i>
                </button>

                <ul id="dropdown-gestion" class="sidebar-submenu space-y-1">
                    <li>
                        <a href="#" class="sidebar-sublink active" data-tooltip="Categorías">
                            <i aria-busy=""class="ri-apps-line sidebar-icon"></i>
                            <span>Categorías</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="sidebar-sublink" data-tooltip="Marcas">
                            <i class="ri-price-tag-3-line sidebar-icon"></i>
                            <span>Marcas</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="sidebar-sublink" data-tooltip="Productos">
                            <i class="ri-box-3-line sidebar-icon"></i>
                            <span>Productos</span>
                        </a>
                    </li>
                </ul>
            </li>

            <li>
                <a href="#" class="sidebar-link" data-tooltip="Products">
                    <i class="ri-shopping-bag-3-line sidebar-icon"></i>
                    <span>Products</span>
                </a>
            </li>
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
