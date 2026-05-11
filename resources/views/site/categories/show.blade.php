<x-app-layout>
    @section('title', $category->name)
    @include('partials.site.breadcrumb', [
        'items' => $breadcrumbItems,
    ])


    @livewire('site.filter', [
        'category_id' => $category->id,
    ])

    @include('partials.site.why-us')
</x-app-layout>
