<?php

namespace App\Services\Cart;

use Illuminate\Database\Eloquent\Collection;

/**
 * CartSummary
 *
 * Value Object que encapsula el resumen completo de un carrito.
 * Inmutable y tipado para uso seguro en vistas y controllers.
 *
 * @package App\Services\Cart
 */
final class CartSummary
{
    public function __construct(
        public readonly Collection $items,
        public readonly int $itemsCount,
        public readonly int $itemsQuantity,
        public readonly float $subtotal,
    ) {}

    /**
     * Obtiene el JSON serializable del resumen.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'itemsCount' => $this->itemsCount,
            'itemsQuantity' => $this->itemsQuantity,
            'subtotal' => $this->subtotal,
        ];
    }
}
