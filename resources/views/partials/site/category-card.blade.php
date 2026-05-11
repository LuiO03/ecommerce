<!-- Sección de Categorías -->

    <div class="section-header">
        <h2 class="section-title">Categorías populares</h2>
        <p class="section-subtitle">
            Explora nuestras categorías más populares y encuentra lo que buscas
        </p>
    </div>
    <div class="categories-slider swiper">
        <div class="swiper-wrapper">
            @foreach ($categories as $category)
                <div class="swiper-slide categories-slide">
                    <!-- colocalr imagen css url a la etiqueta "a" y eliminar la etiqueta "img" -->
                    <a href="{{ route('categories.show', $category) }}" class="category-card">
                        <div class="category-image">
                            @if ($category->image)
                                <img src="{{ $category->image ? asset('storage/' . $category->image) : asset('images/default-category.png') }}"
                                    alt="{{ $category->name }} imagen"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="product-card-image-fallback" style="display: none;">
                                    <i class="ri-image-fill"></i>
                                    <span>Imagen no disponible</span>
                                </div>
                            @endif
                        </div>
                        <div class="category-name">{{ $category->name }}</div>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>
        <div class="swiper-pagination"></div>
    </div>


