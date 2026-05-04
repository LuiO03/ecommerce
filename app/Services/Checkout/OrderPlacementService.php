<?php

namespace App\Services\Checkout;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Cart\CartService;
use Illuminate\Support\Facades\DB;

class OrderPlacementService
{
    public function __construct(
        private readonly CartService $cartService
    ) {}

    public function placePaidOrder(User $user, Cart $cart, array $data): Order
    {
        return DB::transaction(function () use ($user, $cart, $data) {
            $lockedCart = Cart::whereKey($cart->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (!$lockedCart || !$lockedCart->is_active) {
                throw new \RuntimeException('El carrito ya fue procesado por otra operación.');
            }

            $lockedCart->load(['items.product', 'items.variant']);

            if ($lockedCart->items->isEmpty()) {
                throw new \RuntimeException('No se encontraron ítems activos en el carrito para procesar.');
            }

            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => (string) ($data['purchase_number'] ?? ''),
                'total' => (float) ($data['total'] ?? 0),
                'subtotal' => (float) ($data['subtotal'] ?? 0),
                'shipping_cost' => (float) ($data['shipping_cost'] ?? 0),
                'delivery_type' => ($data['delivery_type'] ?? 'delivery') === 'pickup' ? 'pickup' : 'delivery',
                'address_id' => ($data['delivery_type'] ?? 'delivery') === 'delivery' ? ($data['address_id'] ?? null) : null,
                'pickup_store_code' => ($data['delivery_type'] ?? 'delivery') === 'pickup' ? ($data['pickup_store_code'] ?? null) : null,
                'status' => 'pending',
                'shipping_address' => (string) ($data['shipping_address'] ?? 'Sin dirección registrada'),
                'shipping_city' => $data['shipping_city'] ?? null,
                'shipping_phone' => $data['shipping_phone'] ?? null,
            ]);

            $payment = Payment::create([
                'order_id' => $order->id,
                'provider' => (string) ($data['payment_method'] ?? 'niubiz'),
                'transaction_id' => $data['payment_id'] ?? null,
                'amount' => (float) ($data['total'] ?? 0),
                'fee' => 0,
                'net_amount' => (float) ($data['total'] ?? 0),
                'status' => 'paid',
                'paid_at' => now(),
                'response' => $data['payment_response'] ?? null,
            ]);

            if ((float) $payment->fee > 0) {
                Transaction::create([
                    'payment_id' => $payment->id,
                    'type' => 'fee',
                    'amount' => -(float) $payment->fee,
                    'description' => 'Comisión del proveedor de pago',
                ]);
            }

            foreach ($lockedCart->items as $item) {
                $product = $item->product;

                if (!$product) {
                    continue;
                }

                $variant = $item->variant;

                // Usar CartService para obtener precio unitario consistente
                $unitPrice = $this->cartService->getItemDiscountedPrice($item);
                $lineTotal = $this->cartService->getItemLineTotal($item);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'variant_id' => $variant?->id,
                    'quantity' => $item->quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ]);

                if ($variant) {
                    $currentStock = (int) ($variant->stock ?? 0);

                    if ($currentStock > 0) {
                        $newStock = max($currentStock - (int) $item->quantity, 0);

                        $variant->update([
                            'stock' => $newStock,
                            'updated_by' => $user->id,
                        ]);
                    }
                }
            }

            $lockedCart->update(['is_active' => false]);

            return $order;
        });
    }
}
