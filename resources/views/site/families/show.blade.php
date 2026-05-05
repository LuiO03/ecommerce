<x-app-layout>
    @section('title', $family->name)
    @include('partials.site.breadcrumb', [
        'items' => [
            ['label' => 'Tienda', 'url' => route('site.shop.index'), 'icon' => 'ri-store-2-fill'],
            ['label' => $family->name, 'icon' => 'ri-price-tag-3-fill'],
        ],
    ])
    @livewire('site.filter', [
        'family_id' => $family->id,
    ])

</x-app-layout>
