<x-app-layout>
    @section('title', 'Tienda')

    @include('partials.site.breadcrumb', [
        'items' => [['label' => 'Tienda']],
    ])
    <livewire:site.category-list
        section-title="Categorias populares"
        section-subtitle="Explora nuestras categorias mas populares y encuentra lo que buscas"
    />
    @livewire('site.filter')
</x-app-layout>
