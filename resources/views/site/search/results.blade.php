<x-app-layout>
    @section('title', 'Búsqueda: "' . $query . '"')
    @include('partials.site.breadcrumb', [
        'items' => [['label' => 'Tienda']],
    ])
    @livewire('site.filter', ['search' => $query])
</x-app-layout>
