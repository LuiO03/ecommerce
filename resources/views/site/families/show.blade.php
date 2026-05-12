<x-app-layout>
    @section('title', $family->name)
    @include('partials.site.breadcrumb', [
        'items' => [['label' => 'Tienda', 'url' => route('site.shop.index')], ['label' => $family->name]],
    ])

    @livewire('site.filter', [
        'family_id' => $family->id,
    ])

    @include('partials.site.why-us')
</x-app-layout>
