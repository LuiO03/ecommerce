@section('title', 'Dashboard')

<x-admin-layout :useSlotContainer="false">

    <div class="dashboard-cards">

        {{-- Bienvenida --}}
        <div class="targeta dashboard-card-user">
            <div class="targeta-usuario">

                @php
                    $dashboardUser = auth()->user();

                    $dashboardHasImage =
                        $dashboardUser->image && Storage::disk('public')->exists($dashboardUser->image);

                    $role = $dashboardUser->roles->first();
                @endphp

                @if ($dashboardHasImage)
                    <x-image-viewer :src="asset('storage/' . $dashboardUser->image)" gallery="profile" alt="Avatar de {{ $dashboardUser->name }}"
                        class="dashboard-user-avatar" title="{{ $dashboardUser->name }}"
                        description="Foto de perfil de {{ $dashboardUser->name }}" />
                @else
                    <div class="dashboard-user-avatar"
                        style="
                            background-color: {{ $dashboardUser->avatar_colors['background'] ?? '#cccccc' }};
                            color: {{ $dashboardUser->avatar_colors['color'] ?? '#333333' }};
                            border-color: {{ $dashboardUser->avatar_colors['color'] ?? '#333333' }};
                        ">
                        {{ $dashboardUser->initials }}
                    </div>
                @endif

                <div class="dashboard-user-info">
                    <h2 class="dashboard-name">
                        Buen día, {{ $dashboardUser->name }}
                    </h2>

                    <p>
                        Eres <strong>{{ $role->name ?? 'Sin rol' }}</strong>
                        <br>
                        {{ $role->description ?? 'No tienes permisos asignados.' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Últimos pedidos --}}
        @can('ordenes.index')
            <div class="targeta">
                <div class="dashboard-graphic-container">

                    <div class="flex items-center justify-between">
                        <span class="card-title">
                            Últimos pedidos
                        </span>

                        <a href="{{ route('admin.orders.index') }}" class="boton-single" title="Ver todos los pedidos">

                            <span class="boton-single-text">
                                Ver todos
                            </span>

                            <span class="boton-single-icon">
                                <i class="ri-arrow-right-line"></i>
                            </span>
                        </a>
                    </div>

                    <table class="tabla-normal">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Cliente</th>
                                <th>Estado</th>
                                <th>Monto</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse ($orders as $order)
                                <tr>
                                    <td class="text-center">
                                        {{ $order->id }}
                                    </td>

                                    <td>
                                        {{ trim(($order->user->name ?? '') . ' ' . ($order->user->last_name ?? '')) ?: '—' }}
                                    </td>

                                    <td>

                                        @switch($order->status)
                                            @case('pending')
                                                <span class="badge badge-warning">
                                                    <i class="ri-time-line"></i>
                                                    Pendiente
                                                </span>
                                            @break

                                            @case('processing')
                                                <span class="badge badge-orange">
                                                    <i class="ri-loader-4-line"></i>
                                                    En proceso
                                                </span>
                                            @break

                                            @case('shipped')
                                                <span class="badge badge-secondary">
                                                    <i class="ri-truck-line"></i>
                                                    Enviada
                                                </span>
                                            @break

                                            @case('delivered')
                                                <span class="badge badge-success">
                                                    <i class="ri-checkbox-circle-line"></i>
                                                    Entregada
                                                </span>
                                            @break

                                            @case('cancelled')
                                                <span class="badge badge-danger">
                                                    <i class="ri-close-circle-line"></i>
                                                    Cancelada
                                                </span>
                                            @break

                                            @default
                                                <span class="badge badge-secondary">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                        @endswitch

                                    </td>

                                    <td>
                                        S/. {{ number_format((float) $order->total, 2) }}
                                    </td>
                                </tr>

                                @empty

                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            <div class="tabla-no-data">
                                                <i class="ri-shopping-cart-line"></i>
                                                <span>No hay pedidos disponibles</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse

                            </tbody>
                        </table>

                    </div>
                </div>
            @endcan

            {{-- Ventas --}}
            @can('ordenes.index')
                <div class="dashboard-card ripple-card">
                    <div class="card-icon card-success">
                        <i class="ri-money-dollar-circle-line"></i>
                    </div>

                    <div class="card-content">
                        <h1 class="card-count">
                            S/. {{ number_format($totalSales, 2) }}
                        </h1>

                        <p class="card-label">
                            Ventas Totales
                        </p>
                    </div>
                </div>
            @endcan

            {{-- Pedidos hoy --}}
            @can('ordenes.index')
                <div class="dashboard-card ripple-card">
                    <div class="card-icon card-orange">
                        <i class="ri-shopping-bag-3-line"></i>
                    </div>

                    <div class="card-content">
                        <h1 class="card-count">
                            {{ $totalOrdersToday }}
                        </h1>

                        <p class="card-label">
                            Pedidos Hoy
                        </p>
                    </div>
                </div>
            @endcan


            {{-- Pedidos pendientes --}}
            {{--
        @can('ordenes.index')
            <div class="dashboard-card ripple-card">
                <div class="card-icon card-warning">
                    <i class="ri-loader-2-line"></i>
                </div>

                <div class="card-content">
                    <h1 class="card-count">
                        {{ $totalPendingOrders }}
                    </h1>

                    <p class="card-label">
                        Pendientes
                    </p>
                </div>
            </div>
        @endcan
        --}}
            {{-- Stock bajo --}}
            {{--
        @can('productos.index')
            <div class="dashboard-card ripple-card">
                <div class="card-icon card-danger">
                    <i class="ri-alert-line"></i>
                </div>

                <div class="card-content">
                    <h1 class="card-count">
                        {{ $totalLowStockProducts }}
                    </h1>

                    <p class="card-label">
                        Stock Bajo
                    </p>
                </div>
            </div>
        @endcan
        --}}
            {{-- Nuevos clientes --}}
            {{--
        @can('clientes.index')
            <div class="dashboard-card ripple-card">
                <div class="card-icon card-primary">
                    <i class="ri-user-add-line"></i>
                </div>

                <div class="card-content">
                    <h1 class="card-count">
                        {{ $newClientsThisMonth }}
                    </h1>

                    <p class="card-label">
                        Nuevos Clientes
                    </p>
                </div>
            </div>
        @endcan
        --}}


            {{-- Productos --}}
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

            {{-- Categorías --}}
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

            {{-- Familias --}}
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

            {{-- Marcas --}}
            @can('marcas.index')
                <a href="{{ route('admin.brands.index') }}" class="dashboard-card ripple-card">
                    <div class="card-icon card-warning">
                        <i class="ri-award-line"></i>
                    </div>

                    <div class="card-content">
                        <h1 class="card-count">{{ $totalBrands }}</h1>
                        <p class="card-label">Marcas</p>
                    </div>
                </a>
            @endcan

            {{-- Usuarios --}}
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

            {{-- Clientes --}}
            @can('clientes.index')
                <a href="{{ route('admin.clients.index') }}" class="dashboard-card ripple-card">
                    <div class="card-icon card-secondary">
                        <i class="ri-user-5-line"></i>
                    </div>

                    <div class="card-content">
                        <h1 class="card-count">{{ $totalClients }}</h1>
                        <p class="card-label">Clientes</p>
                    </div>
                </a>
            @endcan

        </div>
    </x-admin-layout>
