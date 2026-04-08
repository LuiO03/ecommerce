<x-app-layout>
    @section('title', 'Búsqueda: "' . $query . '"')
    @livewire('site.filter', ['search' => $query])
</x-app-layout>
