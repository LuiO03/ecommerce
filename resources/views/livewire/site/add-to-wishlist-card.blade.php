<button
    wire:click="addToWishlist"
    class="wishlist-btn {{ $isInWishlist ? 'is-active' : '' }}"
    aria-label="Agregar a lista de deseos"
    title="Agregar a lista de deseos"
>
    <i class="{{ $isInWishlist ? 'ri-heart-fill' : 'ri-heart-line' }}"></i>
</button>
