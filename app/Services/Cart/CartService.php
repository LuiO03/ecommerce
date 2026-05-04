<?php

namespace App\Services\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;

/**
 * CartService
 *
 * Centraliza toda la lógica de negocio del carrito:
 * - Cálculos de precios, descuentos y totales
 * - Validaciones de stock y cantidad
 * - Recuperación optimizada de datos
 *
 * @package App\Services\Cart
 */
class CartService
{
    /**
     * Obtiene el precio unitario descuentado de un item del carrito.
     *
     * @param CartItem $item
     * @return float
     */
    public function getItemDiscountedPrice(CartItem $item): float
    {
        $product = $item->product;
        if (!$product) {
            return 0.0;
        }

        $variant = $item->variant;
        $basePrice = ($variant && $variant->price && $variant->price > 0)
            ? (float) $variant->price
            : (float) $product->price;

        $discountPercent = $this->getProductDiscountPercent($product);

        if ($discountPercent <= 0) {
            return max($basePrice, 0);
        }

        return max($basePrice * (1 - $discountPercent / 100), 0);
    }

    /**
     * Obtiene el precio base de un item sin descuento.
     *
     * @param CartItem $item
     * @return float
     */
    public function getItemBasePrice(CartItem $item): float
    {
        $product = $item->product;
        if (!$product) {
            return 0.0;
        }

        $variant = $item->variant;
        return ($variant && $variant->price && $variant->price > 0)
            ? (float) $variant->price
            : (float) $product->price;
    }

    /**
     * Obtiene el total de línea de un item (precio descuentado × cantidad).
     *
     * @param CartItem $item
     * @return float
     */
    public function getItemLineTotal(CartItem $item): float
    {
        $discountedPrice = $this->getItemDiscountedPrice($item);
        $quantity = (int) $item->quantity;
        return $discountedPrice * $quantity;
    }

    /**
     * Obtiene el porcentaje de descuento de un producto.
     *
     * @param Product $product
     * @return float
     */
    public function getProductDiscountPercent(Product $product): float
    {
        if (is_null($product->discount)) {
            return 0.0;
        }

        return min(max((float) $product->discount, 0), 100);
    }

    /**
     * Determina si un producto tiene descuento.
     *
     * @param Product $product
     * @return bool
     */
    public function hasDiscount(Product $product): bool
    {
        return $this->getProductDiscountPercent($product) > 0;
    }

    /**
     * Obtiene la cantidad máxima permitida para un item.
     *
     * @param CartItem $item
     * @return int
     */
    public function getItemMaxQuantity(CartItem $item): int
    {
        $variant = $item->variant;

        // Si no hay variante, permitir cantidad amplia
        if (!$variant) {
            return 99;
        }

        $stock = (int) $variant->stock;
        return $stock > 0 ? $stock : 0;
    }

    /**
     * Obtiene el subtotal del carrito (suma de todos los líneas).
     *
     * @param Cart $cart
     * @return float
     */
    public function getCartSubtotal(Cart $cart): float
    {
        $cart->loadMissing(['items.product', 'items.variant']);

        return $cart->items
            ->sum(fn (CartItem $item) => $this->getItemLineTotal($item));
    }

    /**
     * Obtiene un resumen completo del carrito con todos los cálculos.
     *
     * @param Cart $cart
     * @return CartSummary
     */
    public function getCartSummary(Cart $cart): CartSummary
    {
        $cart->loadMissing(['items.product', 'items.variant.features.option', 'items.product.images']);

        $items = $cart->items;
        $itemsCount = $items->count();
        $itemsQuantity = $items->sum('quantity');
        $subtotal = $items->sum(fn (CartItem $item) => $this->getItemLineTotal($item));

        return new CartSummary(
            items: $items,
            itemsCount: $itemsCount,
            itemsQuantity: $itemsQuantity,
            subtotal: $subtotal,
        );
    }

    /**
     * Valida que el carrito pertenece al usuario.
     *
     * @param Cart $cart
     * @param int $userId
     * @return bool
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function validateOwnership(Cart $cart, int $userId): bool
    {
        if ($cart->user_id !== $userId) {
            throw new \Illuminate\Auth\Access\AuthorizationException(
                'No autorizado para acceder a este carrito.'
            );
        }

        return true;
    }

    /**
     * Obtiene la imagen de un item (prioriza variante sobre producto).
     *
     * @param CartItem $item
     * @return mixed
     */
    public function getItemImage(CartItem $item)
    {
        $variant = $item->variant;

        if ($variant && $variant->images->isNotEmpty()) {
            return $variant->images->first();
        }

        if ($item->product && $item->product->images->isNotEmpty()) {
            return $item->product->images->sortBy('order')->first();
        }

        return null;
    }
}
