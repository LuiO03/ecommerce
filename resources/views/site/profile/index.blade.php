<x-app-layout>
    @section('title', 'Mi cuenta')
    @include('partials.site.breadcrumb', [
        'items' => $breadcrumbItems ?? [['label' => 'Mi cuenta', 'icon' => 'ri-user-3-fill']],
    ])
    <section class="site-container profile-page">
        <header
            class="profile-header {{ $user->background_style && $user->background_style !== '' ? $user->background_style : 'fondo-estilo-2' }}">
            <div class="profile-header-main">
                <div class="profile-avatar">
                    @php
                        $profileHasImage = $user->image && Storage::disk('public')->exists($user->image);
                    @endphp
                    @if ($profileHasImage)
                        <img src="{{ asset('storage/' . $user->image) }}" alt="Foto de perfil"
                            class="profile-avatar-circle">
                    @else
                        <div class="profile-avatar-circle"
                            style="background-color: {{ $user->avatar_colors['background'] }};
                                            color: {{ $user->avatar_colors['color'] }}; border-color: {{ $user->avatar_colors['color'] }};">
                            {{ $user->initials }}
                        </div>
                    @endif
                    <div class="profile-header-text">
                        <h1 class="profile-title">Hola, {{ $user->name }}</h1>
                        <p class="profile-subtitle">Administra tus datos personales, pedidos y direcciones desde un
                            mismo lugar.</p>
                    </div>

                    @include('site.profile.partials.address-modal')
                </div>
            </div>
        </header>

        <div class="profile-layout">
            <aside class="profile-sidebar">
                <nav class="profile-nav">
                    <a href="{{ route('site.profile.index', ['section' => 'overview']) }}"
                        class="profile-nav-item {{ $activeSection === 'overview' ? 'is-active' : '' }}"
                        data-section="overview">
                        <i class="ri-user-3-line" data-icon-line="ri-user-3-line" data-icon-fill="ri-user-3-fill"></i>
                        <span>Resumen</span>
                    </a>
                    <a href="{{ route('site.profile.index', ['section' => 'details']) }}"
                        class="profile-nav-item {{ $activeSection === 'details' ? 'is-active' : '' }}"
                        data-section="details">
                        <i class="ri-id-card-line" data-icon-line="ri-id-card-line" data-icon-fill="ri-id-card-fill"></i>
                        <span>Detalles de la cuenta</span>
                    </a>
                    <a href="{{ route('site.profile.index', ['section' => 'orders']) }}"
                        class="profile-nav-item {{ $activeSection === 'orders' ? 'is-active' : '' }}"
                        data-section="orders">
                        <i class="ri-shopping-bag-3-line" data-icon-line="ri-shopping-bag-3-line" data-icon-fill="ri-shopping-bag-3-fill"></i>
                        <span>Mis pedidos</span>
                    </a>
                    <a href="{{ route('site.profile.index', ['section' => 'wishlist']) }}"
                        class="profile-nav-item {{ $activeSection === 'wishlist' ? 'is-active' : '' }}"
                        data-section="wishlist">
                        <i class="ri-heart-3-line" data-icon-line="ri-heart-3-line" data-icon-fill="ri-heart-3-fill"></i>
                        <span>Mis favoritos</span>
                    </a>
                    <a href="{{ route('site.profile.index', ['section' => 'addresses']) }}"
                        class="profile-nav-item {{ $activeSection === 'addresses' ? 'is-active' : '' }}"
                        data-section="addresses">
                        <i class="ri-map-pin-line" data-icon-line="ri-map-pin-line" data-icon-fill="ri-map-pin-fill"></i>
                        <span>Direcciones</span>
                    </a>
                    <a href="{{ route('site.profile.index', ['section' => 'security']) }}"
                        class="profile-nav-item {{ $activeSection === 'security' ? 'is-active' : '' }}"
                        data-section="security">
                        <i class="ri-shield-keyhole-line" data-icon-line="ri-shield-keyhole-line" data-icon-fill="ri-shield-keyhole-fill"></i>
                        <span>Seguridad</span>
                    </a>
                </nav>

                <div class="profile-sidebar-footer">
                    <form method="POST" action="{{ route('logout') }}" id="profileLogoutForm">
                        @csrf
                        <button type="submit" class="profile-logout-btn">
                            <i class="ri-shut-down-line"></i>
                            <span>Cerrar sesión</span>
                        </button>
                    </form>
                </div>
            </aside>

            <section class="profile-content">
                <div class="profile-section-wrapper {{ $activeSection === 'overview' ? 'is-active' : 'hidden' }}"
                    data-section-content="overview">
                    @include('site.profile.partials.overview')
                </div>
                <div class="profile-section-wrapper {{ $activeSection === 'details' ? 'is-active' : 'hidden' }}"
                    data-section-content="details">
                    @include('site.profile.partials.details')
                </div>
                <div class="profile-section-wrapper {{ $activeSection === 'orders' ? 'is-active' : 'hidden' }}"
                    data-section-content="orders">
                    @include('site.profile.partials.orders')
                </div>
                <div class="profile-section-wrapper {{ $activeSection === 'wishlist' ? 'is-active' : 'hidden' }}"
                    data-section-content="wishlist">
                    @include('site.profile.partials.wishlist')
                </div>
                <div class="profile-section-wrapper {{ $activeSection === 'addresses' ? 'is-active' : 'hidden' }}"
                    data-section-content="addresses">
                    @include('site.profile.partials.addresses')
                </div>
                <div class="profile-section-wrapper {{ $activeSection === 'security' ? 'is-active' : 'hidden' }}"
                    data-section-content="security">
                    @include('site.profile.partials.security')
                </div>
            </section>
        </div>
    </section>
    @push('js')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const navItems = document.querySelectorAll('.profile-nav-item[data-section]');
                const sections = document.querySelectorAll('.profile-section-wrapper[data-section-content]');

                const validSections = ['overview', 'details', 'orders', 'wishlist', 'addresses', 'security'];

                function activateSection(section) {
                    if (!validSections.includes(section)) {
                        section = 'overview';
                    }

                    sections.forEach(function(container) {
                        const isTarget = container.getAttribute('data-section-content') === section;
                        if (isTarget) {
                            container.classList.remove('hidden');
                            // forzar reflujo para que la transición se aplique al agregar is-active
                            void container.offsetWidth;
                            container.classList.add('is-active');
                        } else {
                            container.classList.remove('is-active');
                            container.classList.add('hidden');
                        }
                    });

                    navItems.forEach(function(item) {
                        if (item.getAttribute('data-section') === section) {
                            item.classList.add('is-active');
                            const icon = item.querySelector('i[data-icon-line][data-icon-fill]');
                            if (icon) {
                                const lineClass = icon.getAttribute('data-icon-line');
                                const fillClass = icon.getAttribute('data-icon-fill');
                                icon.classList.remove(lineClass);
                                icon.classList.add(fillClass);
                            }
                        } else {
                            item.classList.remove('is-active');
                            const icon = item.querySelector('i[data-icon-line][data-icon-fill]');
                            if (icon) {
                                const lineClass = icon.getAttribute('data-icon-line');
                                const fillClass = icon.getAttribute('data-icon-fill');
                                icon.classList.remove(fillClass);
                                icon.classList.add(lineClass);
                            }
                        }
                    });

                    try {
                        localStorage.setItem('profileActiveSection', section);
                    } catch (e) {}
                }

                function getInitialSection() {
                    const urlParams = new URLSearchParams(window.location.search);
                    const paramSection = urlParams.get('section');
                    const hash = window.location.hash ? window.location.hash.replace('#', '') : '';

                    if (paramSection && validSections.includes(paramSection)) {
                        return paramSection;
                    }

                    if (hash && validSections.includes(hash)) {
                        return hash;
                    }

                    try {
                        const saved = localStorage.getItem('profileActiveSection');
                        if (saved && validSections.includes(saved)) {
                            return saved;
                        }
                    } catch (e) {}

                    return '{{ $activeSection ?? 'overview' }}';
                }

                const initialSection = getInitialSection();
                activateSection(initialSection);

                navItems.forEach(function(item) {
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        const targetSection = this.getAttribute('data-section');
                        if (!targetSection) return;

                        const url = new URL(window.location.href);
                        url.searchParams.set('section', targetSection);
                        history.replaceState(null, '', url.toString());

                        activateSection(targetSection);
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>
