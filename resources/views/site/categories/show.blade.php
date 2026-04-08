<x-app-layout>
    @section('title', $category->name)
    @include('partials.site.breadcrumb', [
        'items' => $breadcrumbItems,
    ])

    @livewire('site.filter', [
        'category_id' => $category->id,
    ])

</x-app-layout>
