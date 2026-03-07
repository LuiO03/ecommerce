<div class="add-to-cart-wrapper">
    <button wire:click="addToCart" wire:loading.attr="disabled" wire:loading.class="is-loading" wire:target="addToCart" class="product-action-cart" type="button" data-add-to-cart
        data-default-text="Agregar al carrito" data-prompt-text="Selecciona tus opciones"
        data-out-of-stock-text="Sin stock">
        <i class="ri-shopping-cart-line"></i>
        <span data-add-to-cart-label>Agregar al carrito</span>
    </button>

    <input type="hidden" wire:model="variantId" data-livewire-variant>
    <input type="hidden" wire:model="quantity" data-livewire-quantity>
</div>
