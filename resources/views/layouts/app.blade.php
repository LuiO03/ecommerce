<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    @stack('css')
    <!-- Carga combinada de Lato y Poppins -->
    <link
        href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Remix Icon -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.7.0/fonts/remixicon.css" rel="stylesheet" />
    <!-- Script de Google -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <!-- Scripts -->
    @vite(['resources/css/site/layout.css'])
    @vite(['resources/css/app.css', 'resources/js/site.js'])
    <!-- Styles -->
    @livewireStyles
</head>

<body class="antialiased site-body">
    <x-banner />

    {{-- @livewire('navigation-menu') --}}
    @livewire('site.navigation')

    <!-- Page Heading -->
    @if (isset($header))
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
    @endif

    <!-- Page Content -->
    <main class="site-main">
        {{ $slot }}

        <div class="site-button-go-top" data-go-top>
            <i class="ri-arrow-up-s-line"></i>
            Volver arriba
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const btn = document.querySelector('[data-go-top]');

                btn.addEventListener('click', () => {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
            });
        </script>
    </main>


    @livewire('site.footer')

    @include('partials.admin.modal-info')
    @include('partials.admin.modal-confirm')
    @include('partials.admin.modal-toast')
    @include('partials.site.auth-wishlist-modal')

    @if (Session::has('info'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const info = @json(Session::get('info'));
                console.log('Modal info debug:', info);
                window.showInfoModal(info);
            });
        </script>
    @endif

    @if (Session::has('toast'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const navEntries = (typeof performance !== 'undefined' && typeof performance.getEntriesByType ===
                        'function') ?
                    performance.getEntriesByType('navigation') : [];
                const legacyNav = (typeof performance !== 'undefined' && performance.navigation) ?
                    performance.navigation.type :
                    null;
                const navType = navEntries.length ? navEntries[0].type : legacyNav;
                const isBackNavigation = navType === 'back_forward' || navType === 2;

                if (isBackNavigation) {
                    return;
                }

                const toast = @json(Session::get('toast'));
                window.showToast(toast);
            });
        </script>
    @endif
    @livewireScripts

    @stack('js')
</body>

</html>
