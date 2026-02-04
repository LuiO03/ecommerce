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
        <!-- Scripts -->
        @vite(['resources/css/site/layout.css'])
        @vite(['resources/css/app.css', 'resources/js/site.js'])
        <!-- Styles -->
        @livewireStyles
    </head>
    <body class="antialiased site-body">
        <x-banner />

            {{-- @livewire('navigation-menu') --}}
            @livewire('navigation')
            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>

        @stack('modals')

        @livewireScripts

        @stack('js')
    </body>
</html>
