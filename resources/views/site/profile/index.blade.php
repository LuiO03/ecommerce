<x-app-layout>
    @section('title', 'Mi cuenta')

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
                </div>
            </div>
        </header>

        <div class="profile-layout">
            <aside class="profile-sidebar">
                <nav class="profile-nav">
                    <a href="{{ route('site.profile.index') }}"
                        class="profile-nav-item {{ $activeSection === 'overview' ? 'is-active' : '' }}">
                        <i class="ri-user-3-line"></i>
                        <span>Resumen</span>
                    </a>
                    <a href="{{ route('site.profile.orders') }}"
                        class="profile-nav-item {{ $activeSection === 'orders' ? 'is-active' : '' }}">
                        <i class="ri-shopping-bag-3-line"></i>
                        <span>Mis pedidos</span>
                    </a>
                    <a href="{{ route('site.profile.wishlist') }}"
                        class="profile-nav-item {{ $activeSection === 'wishlist' ? 'is-active' : '' }}">
                        <i class="ri-heart-3-line"></i>
                        <span>Mis favoritos</span>
                    </a>
                    <a href="{{ route('site.profile.addresses') }}"
                        class="profile-nav-item {{ $activeSection === 'addresses' ? 'is-active' : '' }}">
                        <i class="ri-map-pin-line"></i>
                        <span>Direcciones</span>
                    </a>
                    <a href="{{ route('site.profile.security') }}"
                        class="profile-nav-item {{ $activeSection === 'security' ? 'is-active' : '' }}">
                        <i class="ri-shield-keyhole-line"></i>
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
                @if ($activeSection === 'overview')
                    @include('site.profile.partials.overview')
                @elseif ($activeSection === 'orders')
                    @include('site.profile.partials.orders')
                @elseif ($activeSection === 'wishlist')
                    @include('site.profile.partials.wishlist')
                @elseif ($activeSection === 'addresses')
                    @include('site.profile.partials.addresses')
                @elseif ($activeSection === 'security')
                    @include('site.profile.partials.security')
                @endif
            </section>
        </div>
    </section>
</x-app-layout>
