@php
    use Illuminate\Support\Facades\Route;
    $routeName = Route::currentRouteName();
    // Detectar si es la vista de error 403
    $is403 = $routeName === 'errors.403' || (isset($is403Breadcrumb) && $is403Breadcrumb);
@endphp

@if ($is403)
    <nav class="breadcrumb-nav">
        <ol class="breadcrumb-list">
            <i class="ri-arrow-right-s-line breadcrumb-separator"></i>
            <li class="breadcrumb">
                <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link ripple-btn">
                    <i class="ri-home-heart-fill breadcrumb-icon"></i>
                    <span>Inicio</span>
                </a>
            </li>
            <i class="ri-arrow-right-s-line breadcrumb-separator"></i>
            <li class="breadcrumb">
                <span class="breadcrumb-current ripple-btn text-danger">
                    <i class="ri-error-warning-fill breadcrumb-icon"></i>
                    Acceso denegado
                </span>
            </li>
        </ol>
    </nav>
@else
    @php
        $segments = explode('.', $routeName);
        $modules = [
            'families' => ['label' => 'Familias', 'icon' => 'ri-team-fill'],
            'covers' => ['label' => 'Portadas', 'icon' => 'ri-image-2-fill'],
            'users' => ['label' => 'Usuarios', 'icon' => 'ri-user-3-fill'],
            'profile' => ['label' => 'Perfil', 'icon' => 'ri-user-3-fill'],
            'categories' => ['label' => 'Categorías', 'icon' => 'ri-price-tag-3-fill'],
            'products' => ['label' => 'Productos', 'icon' => 'ri-box-3-fill'],
            'roles' => ['label' => 'Roles', 'icon' => 'ri-shield-user-fill'],
            'posts' => ['label' => 'Posts', 'icon' => 'ri-article-fill'],
            'brands' => ['label' => 'Marcas', 'icon' => 'ri-store-2-fill'],
            'options' => ['label' => 'Opciones', 'icon' => 'ri-settings-3-fill'],
            'company-settings' => ['label' => 'Empresa', 'icon' => 'ri-building-4-fill'],
            'access-logs' => ['label' => 'Accesos', 'icon' => 'ri-login-circle-fill'],
            'audits' => ['label' => 'Auditoría', 'icon' => 'ri-file-list-3-fill'],
        ];
        $actions = [
            'create' => ['label' => 'Crear', 'icon' => 'ri-add-box-fill'],
            'edit' => ['label' => 'Editar', 'icon' => 'ri-edit-circle-fill'],
            'show' => ['label' => 'Ver', 'icon' => 'ri-eye-fill'],
        ];
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
            <li class="breadcrumb">
                <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link ripple-btn">
                    <i class="ri-home-heart-fill breadcrumb-icon"></i>
                    <span>Inicio</span>
                </a>
            </li>
            @if ($module && $module !== 'dashboard')
                <i class="ri-arrow-right-s-line breadcrumb-separator"></i>
                <li class="breadcrumb">
                    @php
                        $moduleIndexRoute = 'admin.' . $module . '.index';
                        $hasIndexRoute = Route::has($moduleIndexRoute);
                    @endphp
                    @if ($action === null || $action === 'index' || ! $hasIndexRoute)
                        <span class="breadcrumb-current ripple-btn">
                            <i class="{{ $moduleIcon }} breadcrumb-icon"></i>
                            {{ $moduleLabel }}
                        </span>
                    @else
                        <a href="{{ route($moduleIndexRoute) }}" class="breadcrumb-link ripple-btn">
                            <i class="{{ $moduleIcon }} breadcrumb-icon"></i>
                            <span>{{ $moduleLabel }}</span>
                        </a>
                    @endif
                </li>
            @endif
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
@endif
