<div>
    <button wire:click="addToWishlist" class="product-action-wishlist {{ $isInWishlist ? ' is-active' : '' }}"
        type="button" aria-label="Agregar a favoritos" title="Agregar a favoritos">
        <i class="{{ $isInWishlist ? 'ri-heart-fill' : 'ri-heart-line' }}"></i>
    </button>
</div>
