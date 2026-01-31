<div class="site-flyout-item" data-level="{{ $level }}">
    <a href="" class="site-flyout-link @if($level === 0)site-flyout-link-primary @endif">
        <span>{{ $category->name }}</span>
    </a>

    @if($category->children->count() > 0)
        <div class="site-flyout-children">
            @foreach($category->children as $child)
                @include('livewire.category-flyout', ['category' => $child, 'level' => $level + 1])
            @endforeach
        </div>
    @endif
</div>
