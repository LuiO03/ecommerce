<x-app-layout>

    @include('partials.site.breadcrumb', [
        'items' => [
            ['label' => 'Familias', 'url' => route('families.show', $family), 'icon' => 'ri-team-line'],
            ['label' => $family->name, 'icon' => 'ri-price-tag-3-line'],
        ],
    ])
    @livewire('filter', [
        'family_id' => $family->id,
    ])

</x-app-layout>
