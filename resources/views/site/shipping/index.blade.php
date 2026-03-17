<x-app-layout>
    @include('partials.site.breadcrumb', [
        'items' => $breadcrumbItems ?? [
            ['label' => 'Carrito de compras', 'icon' => 'ri-shopping-cart-fill'],
            ['label' => 'Envío', 'icon' => 'ri-truck-fill'],
        ],
    ])

    <section class="site-container">
        <div class="section-header">
            <h1>Direcciones de envío</h1>
            <p>
                Aquí puedes gestionar tus direcciones de envío.
            </p>
        </div>
        <div>
            @livewire('site.shipping-addresses')
        </div>
    </section>
</x-app-layout>
