<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Optimización Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Carga combinada de Lato y Poppins -->
    <link
        href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Remix Icon -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.dataTables.min.css">


    <!-- CSS base del dashboard -->
    @vite(['resources/css/main.css'])
    <!-- CSS de Tailwind y JS global -->
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/index.js'])
    @stack('styles')
    @livewireStyles
</head>
<script>
    // Evitar flash blanco: aplicar tema antes del renderizado
    (function() {
        const theme = localStorage.getItem("color-theme");
        const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
        if (theme === "dark" || (!theme && prefersDark)) {
            document.documentElement.classList.add("dark");
        } else {
            document.documentElement.classList.remove("dark");
        }
    })();
</script>

<body>
    <!-- OVERLAY -->
    <div id="overlay"
        class="fixed inset-0 bg-black/50 hidden opacity-0 transition-opacity duration-300 ease-in-out z-50">
    </div>

    @include('partials.admin.navigation')
    @include('partials.admin.sidebar-left')
    @include('partials.admin.sidebar-right')
    @include('partials.admin.mobile-dropdown')
    <main id="mainContent" class="main-container">
        <div class="slot-container">
            <div class="page-header">
                <div class="page-title">
                    {{ $title ?? 'Sin título' }}
                </div>
                <div class="page-nav">
                    @include('partials.admin.breadcrumb')
                    @isset($action)
                        <div>
                            {{ $action }}
                        </div>
                    @endisset
                </div>
            </div>
        </div>
        <div class="slot-container">
            {{ $slot }}
        </div>
    </main>

    @include('partials.admin.modal-info')
    @include('partials.admin.modal-confirm')

    <!-- ✅ DataTables v2.3.4 -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.min.js"></script>
    @stack('scripts')
    @livewireScripts
</body>

</html>
