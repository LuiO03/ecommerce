<x-site-layout>
    @section('title', 'Inicio')

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">Bienvenido a Geckommerce</h1>
            <p class="hero-subtitle">Encuentra los mejores productos al mejor precio</p>
            <a href="{{ route('products') }}" class="bg-white text-purple-600 px-8 py-3 rounded-full font-semibold text-lg hover:bg-gray-100 transition">
                Explorar Productos
            </a>
        </div>
    </section>

    <!-- Categorías Destacadas -->
    <section class="categories-section">
        <div class="site-container">
            <h2 class="section-title">Categorías Destacadas</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="category-card">
                    <i class="category-icon ri-shirt-line"></i>
                    <p class="category-name">Ropa</p>
                </div>
                <div class="category-card">
                    <i class="category-icon ri-smartphone-line"></i>
                    <p class="category-name">Electrónica</p>
                </div>
                <div class="category-card">
                    <i class="category-icon ri-home-4-line"></i>
                    <p class="category-name">Hogar</p>
                </div>
                <div class="category-card">
                    <i class="category-icon ri-book-line"></i>
                    <p class="category-name">Libros</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Productos Destacados -->
    <section class="featured-section">
        <div class="site-container">
            <h2 class="section-title">Productos Destacados</h2>
            <div class="products-grid">
                @for($i = 1; $i <= 8; $i++)
                <div class="product-card relative">
                    @if($i % 3 == 0)
                    <span class="product-card-badge">-20%</span>
                    @endif
                    <img src="https://via.placeholder.com/300x200" alt="Producto {{ $i }}" class="product-card-image">
                    <div class="product-card-body">
                        <h3 class="product-card-title">Producto de Ejemplo {{ $i }}</h3>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="product-card-price">${{ rand(100, 999) }}</span>
                            @if($i % 3 == 0)
                            <span class="product-card-old-price">${{ rand(1000, 1299) }}</span>
                            @endif
                        </div>
                        <button class="w-full mt-4 bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700 transition">
                            Agregar al Carrito
                        </button>
                    </div>
                </div>
                @endfor
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-16 bg-gradient-to-r from-purple-600 to-pink-500 text-white">
        <div class="site-container text-center">
            <h2 class="text-4xl font-bold mb-4">¿Listo para comenzar?</h2>
            <p class="text-xl mb-8">Regístrate y obtén un 10% de descuento en tu primera compra</p>
            <a href="{{ route('register') }}" class="bg-white text-purple-600 px-8 py-3 rounded-full font-semibold text-lg hover:bg-gray-100 transition inline-block">
                Crear Cuenta
            </a>
        </div>
    </section>
</x-site-layout>
