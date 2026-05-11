<x-app-layout>
    @section('title', 'Tienda')

    @include('partials.site.breadcrumb', [
        'items' => [['label' => 'Tienda']],
    ])
    <section class="section-container pb-0">
        @include('partials.site.category-card')
    </section>
    @livewire('site.filter')
</x-app-layout>
