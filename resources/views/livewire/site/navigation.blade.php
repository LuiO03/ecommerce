<aside class="site-section-nav">
    <header class="site-nav-container" id="siteHeader">
        <div class="site-nav">
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
                    <a href="{{ route('login') }}" class="nav-icon nav-login-button" title="Iniciar sesión">
                        <span class="boton-icon">
                            <i class="ri-user-line"></i>
                        </span>

                        <span class="boton-text">Iniciar Sesión</span>
                    </a>
                @else
                    <div class="nav-user-dropdown">
                        <button class="nav-icon" id="userMenuBtn" aria-label="Mi cuenta" aria-haspopup="true">
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
        <div class="site-sidebar-pages" data-sidebar-pages>
            <!-- Página raíz: menú completo -->
            <section class="site-sidebar-page active" data-sidebar-page="root" aria-hidden="false">
                <!-- Contenido del sidebar -->
                <div class="site-sidebar-content">
                    <!-- Navegación principal -->
                    <nav class="site-nav-menu" aria-label="Navegación principal">
                        <a href="{{ route('about.index') }}" class="site-nav-menu-item" data-sidebar-close>
                            <i class="ri-information-line"></i>
                            <span>Nosotros</span>
                        </a>
                        <a href="{{ route('site.blog.index') }}" class="site-nav-menu-item" data-sidebar-close>
                            <i class="ri-newspaper-line"></i>
                            <span>Blog</span>
                        </a>
                        <a href="{{ route('contact.index') }}" class="site-nav-menu-item" data-sidebar-close>
                            <i class="ri-mail-line"></i>
                            <span>Contacto</span>
                        </a>
                    </nav>

                    <hr class="w-full my-0 border-default">

                    <!-- Familias → Categorías (navegación por páginas) -->
                    <nav class="site-nav-menu" aria-label="Categorías">
                        <div class="site-nav-section-title">Categorías</div>
                        @foreach ($families as $family)
                            <button type="button" class="site-sidebar-page-link" data-sidebar-nav-to="family-{{ $family->id }}"
                                aria-label="Ver categorías de {{ $family->name }}">
                                <span class="site-sidebar-page-link-leading">
                                    <i class="ri-shopping-basket-2-line" aria-hidden="true"></i>
                                    <span class="site-sidebar-page-link-text">{{ $family->name }}</span>
                                </span>
                                <i class="ri-arrow-right-s-line" aria-hidden="true"></i>
                            </button>
                        @endforeach
                    </nav>

                    <hr class="w-full my-0 border-default">

                    <nav class="site-nav-menu" aria-label="Información">
                        <div class="site-nav-section-title">Información</div>
                        <a href="{{ route('site.legal.terms') }}" class="site-nav-menu-item" data-sidebar-close>
                            <i class="ri-file-text-line"></i>
                            <span>Términos y condiciones</span>
                        </a>
                        <a href="{{ route('site.legal.privacy') }}" class="site-nav-menu-item" data-sidebar-close>
                            <i class="ri-shield-line"></i>
                            <span>Política de privacidad</span>
                        </a>
                        <a href="{{ route('site.legal.claims') }}" class="site-nav-menu-item" data-sidebar-close>
                            <i class="ri-book-line"></i>
                            <span>Libro de reclamaciones</span>
                        </a>
                        <a href="#" class="site-nav-menu-item" data-sidebar-close>
                            <i class="ri-question-line"></i>
                            <span>Preguntas frecuentes</span>
                        </a>
                    </nav>
                </div>
            </section>

            <!-- Páginas por familia -->
            @foreach ($families as $family)
                <section class="site-sidebar-page" data-sidebar-page="family-{{ $family->id }}" aria-hidden="true">
                    <div class="site-sidebar-page-nav" aria-label="Navegación de categorías">
                        <button type="button" class="site-sidebar-nav-btn" data-sidebar-back aria-label="Regresar">
                            <i class="ri-arrow-left-s-line"></i>
                            <span>Regresar</span>
                        </button>

                        <div class="site-sidebar-page-title" title="{{ $family->name }}">{{ $family->name }}</div>

                        <div class="site-sidebar-page-actions">
                            <a href="{{ route('families.show', $family) }}" class="site-btn site-btn-primary site-sidebar-cta" data-sidebar-close>
                                <span class="boton-form-text">Ver todo</span>
                            </a>
                            <button type="button" class="site-sidebar-nav-btn" data-sidebar-home aria-label="Menú principal">
                                <i class="ri-home-4-line"></i>
                                <span>Menú</span>
                            </button>
                        </div>
                    </div>

                    <div class="site-sidebar-content">
                        <nav class="site-nav-menu" aria-label="Categorías de {{ $family->name }}">
                            @forelse ($family->categories as $category)
                                @if ($category->children->isNotEmpty())
                                    <button type="button" class="site-sidebar-page-link" data-sidebar-nav-to="category-{{ $category->id }}"
                                        aria-label="Ver subcategorías de {{ $category->name }}">
                                        <span class="site-sidebar-page-link-text">{{ $category->name }}</span>
                                        <i class="ri-arrow-right-s-line" aria-hidden="true"></i>
                                    </button>
                                @else
                                    <a href="{{ route('categories.show', $category) }}" class="site-sidebar-leaf-link" data-sidebar-close>
                                        <span class="site-sidebar-page-link-text">{{ $category->name }}</span>
                                    </a>
                                @endif
                            @empty
                                <div class="site-sidebar-empty">Sin categorías disponibles</div>
                            @endforelse
                        </nav>
                    </div>
                </section>

                @foreach ($family->categories as $category)
                    @include('livewire.site.sidebar-category-page', ['category' => $category])
                @endforeach
            @endforeach
        </div>
    </aside>

    @include('partials.site.search-modal')
</aside>
