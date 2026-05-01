<x-app-layout>
    @section('title', $brand->name)
    @include('partials.site.breadcrumb', [
        'items' => $breadcrumbItems,
    ])
    @livewire('site.filter', [
        'brand_id' => $brand->id,
    ])

</x-app-layout>
