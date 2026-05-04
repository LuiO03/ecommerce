<?php

namespace App\Models;

use App\Services\Cart\CartService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CartItem extends Model
{
    use SoftDeletes;

    protected $table = 'cart_items';

    protected $fillable = [
        'cart_id',
        'product_id',
        'variant_id',
        'quantity',
        'notes',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }

    /**
     * Obtiene el precio unitario descuentado del producto.
     *
     * @return float
     */
    public function getDiscountedPrice(): float
    {
        return app(CartService::class)->getItemDiscountedPrice($this);
    }

    /**
     * Obtiene el precio base sin descuento.
     *
     * @return float
     */
    public function getBasePrice(): float
    {
        return app(CartService::class)->getItemBasePrice($this);
    }

    /**
     * Obtiene el total de esta línea (precio descuentado × cantidad).
     *
     * @return float
     */
    public function getLineTotal(): float
    {
        return app(CartService::class)->getItemLineTotal($this);
    }

    /**
     * Obtiene la cantidad máxima permitida basada en stock.
     *
     * @return int
     */
    public function getMaxQuantity(): int
    {
        return app(CartService::class)->getItemMaxQuantity($this);
    }

    /**
     * Obtiene el porcentaje de descuento del producto.
     *
     * @return float
     */
    public function getDiscountPercent(): float
    {
        $product = $this->product;
        if (!$product) {
            return 0.0;
        }
        return app(CartService::class)->getProductDiscountPercent($product);
    }

    /**
     * Determina si el producto tiene descuento.
     *
     * @return bool
     */
    public function hasDiscount(): bool
    {
        $product = $this->product;
        if (!$product) {
            return false;
        }
        return app(CartService::class)->hasDiscount($product);
    }

    /**
     * Obtiene la imagen del item (prioriza variante).
     *
     * @return mixed
     */
    public function getImage()
    {
        return app(CartService::class)->getItemImage($this);
    }

    /**
     * Obtiene las etiquetas de la variante (opciones como "Talla: M").
     *
     * @return array
     */
    public function getVariantLabels(): array
    {
        $variant = $this->variant;
        if (!$variant || $variant->features->isEmpty()) {
            return [];
        }

        $labels = [];
        foreach ($variant->features as $feature) {
            $option = $feature->option;

            // Saltar opciones de color (se muestran como círculos)
            if ($option && method_exists($option, 'isColor') && $option->isColor()) {
                continue;
            }

            $optionName = $option->name ?? ($option->slug ?? null);
            $label = $optionName ? $optionName . ': ' . $feature->value : $feature->value;
            $labels[] = $label;
        }

        return $labels;
    }

    /**
     * Obtiene las características de color de la variante.
     *
     * @return array
     */
    public function getColorFeatures(): array
    {
        $variant = $this->variant;
        if (!$variant || $variant->features->isEmpty()) {
            return [];
        }

        $colorFeatures = [];
        foreach ($variant->features as $feature) {
            $option = $feature->option;

            // Solo incluir opciones de color
            if ($option && method_exists($option, 'isColor') && $option->isColor()) {
                $colorFeatures[] = $feature;
            }
        }

        return $colorFeatures;
    }
}
