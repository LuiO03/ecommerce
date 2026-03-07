<?php

namespace App\Livewire\Site;

use Livewire\Component;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Variant;
use Illuminate\Support\Facades\Auth;

class AddToCart extends Component
{
    public int $productId;
    public bool $isInCart = false;
    public ?int $variantId = null;
    public int $quantity = 1;


    public function mount(int $productId)
    {
        $this->productId = $productId;
        $this->quantity = 1;
        if (Auth::check()) {
            $cart = Cart::where('user_id', Auth::id())->where('is_active', true)->first();

            if ($cart) {
                $this->isInCart = $cart->items()
                    ->where('product_id', $this->productId)
                    ->exists();
            }
        }
    }


    public function render()
    {
        return view('livewire.site.add-to-cart');
    }

    public function addToCart()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $userId = Auth::id();
        $cart = Cart::firstOrCreate(
            [
                'user_id' => $userId,
                'is_active' => true,
            ],
            [
                'items_count' => 0,
                'items_quantity' => 0,
            ]
        );

        $variantId = $this->variantId ?: null;
        $quantity = (int) $this->quantity;
        if ($quantity < 1) {
            $quantity = 1;
        }
        if ($quantity > 999) {
            $quantity = 999;
        }

        // Limitar por stock de la variante cuando aplica
        $maxAddable = $quantity;
        if ($variantId) {
            $variant = Variant::find($variantId);
            if ($variant) {
                $stock = (int) $variant->stock;

                // Cantidad actual en el carrito para este producto + variante
                $currentInCart = CartItem::where('cart_id', $cart->id)
                    ->where('product_id', $this->productId)
                    ->where('variant_id', $variantId)
                    ->whereNull('deleted_at')
                    ->sum('quantity');

                $remaining = max(0, $stock - $currentInCart);

                if ($remaining <= 0) {
                    $this->dispatch('toast', [
                        'type' => 'warning',
                        'title' => 'Sin stock adicional',
                        'message' => 'Ya alcanzaste el stock máximo disponible para esta variante.',
                    ]);

                    return;
                }

                if ($maxAddable > $remaining) {
                    $maxAddable = $remaining;
                }
            }
        }

        $quantity = $maxAddable;

        $cartItem = CartItem::withTrashed()
            ->where('cart_id', $cart->id)
            ->where('product_id', $this->productId)
            ->where('variant_id', $variantId)
            ->first();

        $title = 'Producto agregado al carrito';
        $message = 'El producto se ha agregado al carrito.';

        if ($cartItem && ! $cartItem->trashed()) {
            $cartItem->quantity = (int) $cartItem->quantity + $quantity;
            $cartItem->save();

            $cart->items_quantity = (int) $cart->items_quantity + $quantity;
            $cart->save();

            $title = 'Cantidad actualizada';
            $message = 'Se ha actualizado la cantidad en tu carrito.';
        } else {
            if ($cartItem && $cartItem->trashed()) {
                $cartItem->restore();
                $cartItem->quantity = $quantity;
                $cartItem->save();
            } else {
                $cartItem = CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $this->productId,
                    'variant_id' => $variantId,
                    'quantity' => $quantity,
                ]);
            }

            $cart->items_count = (int) $cart->items_count + 1;
            $cart->items_quantity = (int) $cart->items_quantity + $quantity;
            $cart->save();
        }

        $this->isInCart = true;

        $toastPayload = [
            'type' => 'success',
            'title' => $title,
            'message' => $message,
        ];

        $this->dispatch('toast', $toastPayload);
        $this->dispatch('cartUpdated', id: $cartItem->id);
    }
}
