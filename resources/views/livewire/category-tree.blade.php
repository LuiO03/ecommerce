<!-- Vista recursiva para categorías anidadas -->
<div class="site-nav-category-item">
    <a href="" class="site-nav-category-link" data-category-id="{{ $category->id }}">
        <div class="site-nav-category-content">
            •
            <span>{{ $category->name }}</span>
        </div>
        @if($category->children->count() > 0)
            <i class="ri-arrow-down-s-line category-arrow"></i>
        @endif
    </a>

    @if($category->children->count() > 0)
        <div class="site-nav-subcategories">
            @foreach($category->children as $child)
                @include('livewire.category-tree', ['category' => $child])
            @endforeach
        </div>
    @endif
</div>
