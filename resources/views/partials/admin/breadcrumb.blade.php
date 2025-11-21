@php
    use Illuminate\Support\Facades\Route;

    $routeName = Route::currentRouteName(); // Ej: admin.families.create
    $segments = explode('.', $routeName); // ["admin", "families", "create"]

    // Configuración de módulos (puedes agregar más)
    $modules = [
        'families' => ['label' => 'Familias', 'icon' => 'ri-team-fill'],
        'users' => ['label' => 'Usuarios', 'icon' => 'ri-user-3-fill'],
        'categories' => ['label' => 'Categorías', 'icon' => 'ri-price-tag-3-fill'],
        'brands' => ['label' => 'Marcas', 'icon' => 'ri-store-2-fill'],
    ];

    // Traducciones de acciones
    $actions = [
        'create' => ['label' => 'Crear', 'icon' => 'ri-add-box-fill'],
        'edit' => ['label' => 'Editar', 'icon' => 'ri-edit-circle-fill'],
        'show' => ['label' => 'Ver', 'icon' => 'ri-eye-fill'],
    ];

    // Detectar módulo y acción actual
    $module = $segments[1] ?? null;
    $action = $segments[2] ?? null;

    $moduleLabel = $modules[$module]['label'] ?? ucfirst($module ?? '');
    $moduleIcon = $modules[$module]['icon'] ?? 'ri-folder-3-fill';
    $actionLabel = $actions[$action]['label'] ?? null;
    $actionIcon = $actions[$action]['icon'] ?? 'ri-question-fill';
@endphp

<nav class="breadcrumb-nav">
    <ol class="breadcrumb-list">
        <i class="ri-arrow-right-s-line breadcrumb-separator"></i>
        {{-- Inicio --}}
        <li class="breadcrumb">
            <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link ripple-btn">
                <i class="ri-home-heart-fill breadcrumb-icon"></i>
                <span>Inicio</span>
            </a>
        </li>
        {{-- Módulo principal --}}
        @if ($module && $module !== 'dashboard')
            <i class="ri-arrow-right-s-line breadcrumb-separator"></i>
            <li class="breadcrumb">
                @if ($action === null || $action === 'index')
                    {{-- Si ya estamos en la vista principal, no mostrar enlace --}}
                    <span class="breadcrumb-current ripple-btn">
                        <i class="{{ $moduleIcon }} breadcrumb-icon"></i>
                        {{ $moduleLabel }}
                    </span>
                @else
                    {{-- Si estamos en otra acción (create, edit, etc.), mostrar enlace --}}
                    <a href="{{ route('admin.' . $module . '.index') }}" class="breadcrumb-link ripple-btn">
                        <i class="{{ $moduleIcon }} breadcrumb-icon"></i>
                        <span>{{ $moduleLabel }}</span>
                    </a>
                @endif
            </li>
        @endif
        {{-- Acción (crear, editar, ver) --}}
        @if ($actionLabel)
            <i class="ri-arrow-right-s-line breadcrumb-separator"></i>
            <li class="breadcrumb">
                <span class="breadcrumb-current ripple-btn">
                    <i class="{{ $actionIcon }} breadcrumb-icon"></i>
                    <span>{{ $actionLabel }}</span>
                </span>
            </li>
        @endif
    </ol>
</nav>
