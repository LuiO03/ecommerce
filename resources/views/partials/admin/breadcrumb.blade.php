@php
    use Illuminate\Support\Facades\Route;

    $routeName = Route::currentRouteName(); // Ej: admin.families.create
    $segments = explode('.', $routeName);   // ["admin", "families", "create"]

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
        'edit' => ['label' => 'Editar', 'icon' => 'ri-edit-2-fill'],
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
        {{-- Inicio --}}
        <li class="breadcrumb">
            <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">
                <i class="ri-home-heart-fill breadcrumb-icon"></i>
                <span>Dashboard</span>
            </a>
        </li>

        {{-- Módulo principal --}}
        @if ($module)
            <li class="breadcrumb">
                <i class="ri-arrow-right-s-line breadcrumb-separator"></i>
                <a href="{{ route('admin.' . $module . '.index') }}" class="breadcrumb-link">
                    <i class="{{ $moduleIcon }} breadcrumb-icon"></i>
                    <span>{{ $moduleLabel }}</span>
                </a>
            </li>
        @endif

        {{-- Acción (crear, editar, ver) --}}
        @if ($actionLabel)
            <li class="breadcrumb">
                <i class="ri-arrow-right-s-line breadcrumb-separator"></i>
                <span class="breadcrumb-current"><i class="{{ $actionIcon }} breadcrumb-icon"></i> {{ $actionLabel }}</span>
            </li>
        @endif
    </ol>
</nav>
