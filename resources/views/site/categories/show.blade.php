<x-app-layout>

    @include('partials.site.breadcrumb', [
        'items' => $breadcrumbItems,
    ])

    @livewire('filter', [
        'category_id' => $category->id,
    ])

</x-app-layout>
