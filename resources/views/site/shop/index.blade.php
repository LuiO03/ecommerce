<x-app-layout>
    @section('title', 'Tienda')

    @include('partials.site.breadcrumb', [
        'items' => [['label' => 'Tienda']],
    ])
    @include('partials.site.category-list')
    @livewire('site.filter')
</x-app-layout>
