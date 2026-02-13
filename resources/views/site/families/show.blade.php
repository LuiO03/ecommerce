<x-app-layout>

    @include('partials.site.breadcrumb', [
        'items' => [
            ['label' => $family->name, 'icon' => 'ri-price-tag-3-fill'],
        ],
    ])
    @livewire('site.filter', [
        'family_id' => $family->id,
    ])

</x-app-layout>
