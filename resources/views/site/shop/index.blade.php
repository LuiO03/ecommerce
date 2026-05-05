<x-app-layout>
    @section('title', 'Tienda')

    @include('partials.site.breadcrumb', [
        'items' => [
            ['label' => 'Tienda', 'icon' => 'ri-store-2-fill'],
        ],
    ])

    @livewire('site.filter')
</x-app-layout>
