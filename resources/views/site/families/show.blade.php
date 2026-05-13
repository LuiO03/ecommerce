<x-app-layout>
    @section('title', $family->name)
    @include('partials.site.breadcrumb', [
        'items' => [['label' => 'Tienda', 'url' => route('site.shop.index')], ['label' => $family->name]],
    ])

    <livewire:site.category-list
        :family-id="$family->id"
        section-title="Categorias de {{ $family->name }}"
        section-subtitle="{{ $family->description ?? 'Explora las categorias de esta familia y encuentra lo que buscas' }}"
    />

    @livewire('site.filter', [
        'family_id' => $family->id,
    ])

    @include('partials.site.why-us')
</x-app-layout>
