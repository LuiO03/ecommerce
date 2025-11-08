@push('styles')
    @vite('resources/css/modules/dashboard.css')
@endpush
<x-admin-layout>
    <x-slot name="title">
        Panel de Administración
    </x-slot>
    <div class="grid sm:grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="targeta">
            <div class="targeta-usuario">
                @if (auth()->user()->has_local_photo)
                    <img src="{{ asset('storage/' . auth()->user()->profile_photo_path) }}"
                        alt="{{ auth()->user()->name }}" class="dashboard-user-avatar">
                @else
                    <div class="dashboard-user-avatar"
                        style="background-color: {{ auth()->user()->avatar_colors['background'] }};
                                   color: {{ auth()->user()->avatar_colors['color'] }};
                                   border-color: {{ auth()->user()->avatar_colors['color'] }};">
                        {{ auth()->user()->initials }}
                    </div>
                @endif

                <div class="dashboard-user-info">
                    <h2 class="dashboard-name">Buen día, {{ auth()->user()->name }}</h2>
                    <p>
                        Eres <strong>{{ auth()->user()->roles->pluck('name')->join(', ') }}.</strong> <br>
                        Puedes gestionar el sitio desde este panel de administración.
                    </p>
                </div>
            </div>
        </div>

        <div class="targeta">
            <h2>Constantine</h2>
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
        <a href="" class="dashboard-card">
            <div class="card-icon card-danger">
                <i class="ri-box-3-line"></i>
            </div>
            <div class="card-content">
                <h1 class="card-count">24</h1>
                <p class="card-label">Productos</p>
            </div>
        </a>

        <!-- Tarjeta: Categorías -->
        <a href="" class="dashboard-card">
            <div class="card-icon card-info">
                <i class="ri-price-tag-3-line"></i>
            </div>
            <div class="card-content">
                <h1 class="card-count">10</h1>
                <p class="card-label">Categorías</p>
            </div>
        </a>

        <!-- Tarjeta: Familias -->
        <a href="{{ route('admin.families.index') }}" class="dashboard-card">
            <div class="card-icon card-success">
                <i class="ri-apps-line"></i>
            </div>
            <div class="card-content">
                <h1 class="card-count">{{ $totalFamilies }}</h1>
                <p class="card-label">Familias</p>
            </div>
        </a>

        <!-- Tarjeta: Marcas -->
        <a href="" class="dashboard-card">
            <div class="card-icon card-warning">
                <i class="ri-award-line"></i>
            </div>
            <div class="card-content">
                <h1 class="card-count">15</h1>
                <p class="card-label">Marcas</p>
            </div>
        </a>

        <!-- Tarjeta: Usuarios -->
        <a href="" class="dashboard-card">
            <div class="card-icon card-purple">
                <i class="ri-admin-line"></i>
            </div>
            <div class="card-content">
                <h1 class="card-count">23</h1>
                <p class="card-label">Usuarios</p>
            </div>
        </a>
    </div>
</x-admin-layout>
