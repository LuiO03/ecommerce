@if ($category->children->isNotEmpty())
    <section class="site-sidebar-page" data-sidebar-page="category-{{ $category->id }}" aria-hidden="true">
        <div class="site-sidebar-page-nav" aria-label="Navegación de categorías">
            <button type="button" class="site-sidebar-nav-btn" data-sidebar-back aria-label="Regresar">
                <i class="ri-arrow-left-s-line"></i>
                <span>Regresar</span>
            </button>

            <div class="site-sidebar-page-title" title="{{ $category->name }}">{{ $category->name }}</div>

            <div class="site-sidebar-page-actions">
                <a href="{{ route('categories.show', $category) }}" class="site-btn site-btn-primary site-sidebar-cta" data-sidebar-close>
                    <span class="boton-form-text">Ver todo</span>
                </a>
                <button type="button" class="site-sidebar-nav-btn" data-sidebar-home aria-label="Menú principal">
                    <i class="ri-home-4-line"></i>
                    <span>Menú</span>
                </button>
            </div>
        </div>

        <div class="site-sidebar-content">
            <nav class="site-nav-menu" aria-label="Subcategorías">
                @foreach ($category->children as $child)
                    @if ($child->children->isNotEmpty())
                        <button type="button" class="site-sidebar-page-link" data-sidebar-nav-to="category-{{ $child->id }}"
                            aria-label="Ver subcategorías de {{ $child->name }}">
                            <span class="site-sidebar-page-link-text">{{ $child->name }}</span>
                            <i class="ri-arrow-right-s-line" aria-hidden="true"></i>
                        </button>
                    @else
                        <a href="{{ route('categories.show', $child) }}" class="site-sidebar-leaf-link" data-sidebar-close>
                            <span class="site-sidebar-page-link-text">{{ $child->name }}</span>
                        </a>
                    @endif
                @endforeach
            </nav>
        </div>
    </section>

    @foreach ($category->children as $child)
        @include('livewire.site.sidebar-category-page', ['category' => $child])
    @endforeach
@endif
