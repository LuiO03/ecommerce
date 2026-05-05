<x-app-layout>
    @section('title', 'Búsqueda: "' . $query . '"')
    @include('partials.site.breadcrumb', [
        'items' => [['label' => 'Tienda', 'icon' => 'ri-store-2-fill']],
    ])
    @livewire('site.filter', ['search' => $query])
</x-app-layout>
