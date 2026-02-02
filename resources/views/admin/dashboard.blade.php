@section('title', 'Dashboard')

@push('styles')
    @vite('resources/css/admin/modules/dashboard.css')
@endpush
<x-admin-layout :useSlotContainer="false">
    <div class="grid sm:grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="targeta ripple-card">
            <div class="targeta-usuario">
                @php
                    $dashboardUser = auth()->user();
                    $dashboardHasImage = $dashboardUser->image && Storage::disk('public')->exists($dashboardUser->image);
                @endphp
                @if ($dashboardHasImage)
                    <img src="{{ asset('storage/' . $dashboardUser->image) }}"
                        alt="{{ $dashboardUser->name }}" class="dashboard-user-avatar">
                @else
                        <div class="dashboard-user-avatar"
                            style="background-color: {{ $dashboardUser->avatar_colors['background'] ?? '#cccccc' }}; color: {{ $dashboardUser->avatar_colors['color'] ?? '#333333' }}; border-color: {{ $dashboardUser->avatar_colors['color'] ?? '#333333' }};">
                            {{ $dashboardUser->initials }}
                        </div>
                @endif

                <div class="dashboard-user-info">
                    <h2 class="dashboard-name">Buen día, {{ $dashboardUser->name }}</h2>
                    <p>
                        Eres <strong>{{ $dashboardUser->roles->pluck('name')->join(', ') }}(a).</strong> <br>
                        Puedes gestionar el sitio desde este panel de administración.
                    </p>
                </div>
            </div>
        </div>

        <div class="targeta">
            <!-- Nombre de la empresa y botón de cerrar sesión -->
            <h1 class="dashboard-company-name">{{ $companyName }}</h1>
            <form action="{{ route('logout') }}" method="post">
                @csrf

            </form>
            <button class="boton-cerrar-sesion ripple-btn">
                <i class="ri-logout-box-line mr-2"></i>
                <span>Cerrar Sesión</span>
            </button>
        </div>
    </div>
    <div class="grid grid-cols-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 dashboard-cards">
        <!-- Tarjeta: Productos -->
        @can('productos.index')
        <a href="{{ route('admin.products.index') }}" class="dashboard-card ripple-card">
            <div class="card-icon card-danger">
                <i class="ri-box-3-line"></i>
            </div>
            <div class="card-content">
                <h1 class="card-count">{{ $totalProducts }}</h1>
                <p class="card-label">Productos</p>
            </div>
        </a>
        @endcan
        <!-- Tarjeta: Categorías -->
        @can('categorias.index')
        <a href="{{ route('admin.categories.index') }}" class="dashboard-card ripple-card">
            <div class="card-icon card-info">
                <i class="ri-price-tag-3-line"></i>
            </div>
            <div class="card-content">
                <h1 class="card-count">{{ $totalCategories }}</h1>
                <p class="card-label">Categorías</p>
            </div>
        </a>
        @endcan

        <!-- Tarjeta: Familias -->
        @can('familias.index')
        <a href="{{ route('admin.families.index') }}" class="dashboard-card ripple-card">
            <div class="card-icon card-success">
                <i class="ri-apps-line"></i>
            </div>
            <div class="card-content">
                <h1 class="card-count">{{ $totalFamilies }}</h1>
                <p class="card-label">Familias</p>
            </div>
        </a>
        @endcan

        <!-- Tarjeta: Portadas -->
        @can('portadas.index')
        <a href="{{ route('admin.covers.index') }}" class="dashboard-card ripple-card">
            <div class="card-icon card-info">
                <i class="ri-image-2-line"></i>
            </div>
            <div class="card-content">
                <h1 class="card-count">{{ $totalCovers }}</h1>
                <p class="card-label">Portadas</p>
            </div>
        </a>
        @endcan

        <!-- Tarjeta: Marcas -->
        <a href="" class="dashboard-card ripple-card">
            <div class="card-icon card-warning">
                <i class="ri-award-line"></i>
            </div>
            <div class="card-content">
                <h1 class="card-count">15</h1>
                <p class="card-label">Marcas</p>
            </div>
        </a>

        <!-- Tarjeta: Roles -->
        @can('roles.index')
        <a href="{{ route('admin.roles.index') }}" class="dashboard-card ripple-card">
            <div class="card-icon card-primary">
                <i class="ri-shield-user-line"></i>
            </div>
            <div class="card-content">
                <h1 class="card-count">{{ $totalRoles }}</h1>
                <p class="card-label">Roles</p>
            </div>
        </a>
        @endcan

        <!-- Tarjeta: Usuarios -->
        @can('usuarios.index')
        <a href="{{ route('admin.users.index') }}" class="dashboard-card ripple-card">
            <div class="card-icon card-purple">
                <i class="ri-admin-line"></i>
            </div>
            <div class="card-content">
                <h1 class="card-count">{{ $totalUsers }}</h1>
                <p class="card-label">Usuarios</p>
            </div>
        </a>
        @endcan

        <!-- Tarjeta: Posts -->
        @can('posts.index')
        <a href="{{ route('admin.posts.index') }}" class="dashboard-card ripple-card">
            <div class="card-icon card-orange">
                <i class="ri-article-line"></i>
            </div>
            <div class="card-content">
                <h1 class="card-count">{{ $totalPosts }}</h1>
                <p class="card-label">Posts</p>
            </div>
        </a>
        @endcan

        <!-- Tarjeta: Opciones -->
        @can('opciones.index')
        <a href="{{ route('admin.options.index') }}" class="dashboard-card ripple-card">
            <div class="card-icon card-teal">
                <i class="ri-settings-3-line"></i>
            </div>
            <div class="card-content">
                <h1 class="card-count">{{ $totalOptions }}</h1>
                <p class="card-label">Opciones</p>
            </div>
        </a>
        @endcan

        <!-- Tarjeta: Accesos -->
        @can('accesos.index')
        <a href="{{ route('admin.access-logs.index') }}" class="dashboard-card ripple-card">
            <div class="card-icon card-pink">
                <i class="ri-login-circle-line"></i>
            </div>
            <div class="card-content">
                <h1 class="card-count">{{ $totalAccessLogs }}</h1>
                <p class="card-label">Accesos</p>
            </div>
        </a>
        @endcan
    </div>
</x-admin-layout>
