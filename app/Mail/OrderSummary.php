<?php

namespace App\Mail;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderSummary extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public Cart $cart;
    public float $subtotal;
    public float $shipping;
    public float $amount;
    public ?array $paymentData;
    public ?string $purchaseNumber;

    /**
     * Create a new message instance.
     */
    public function __construct(
        User $user,
        Cart $cart,
        float $subtotal,
        float $shipping,
        float $amount,
        ?array $paymentData = null,
        ?string $purchaseNumber = null
    ) {
        $this->user = $user;
        $this->cart = $cart;
        $this->subtotal = $subtotal;
        $this->shipping = $shipping;
        $this->amount = $amount;
        $this->paymentData = $paymentData;
        $this->purchaseNumber = $purchaseNumber;
    }

    /**
     * Build the message.
     */
    public function build(): static
    {
        return $this
            ->subject('Resumen de tu compra en ' . config('app.name'))
            ->markdown('site.emails.orders.summary', [
                'user' => $this->user,
                'cart' => $this->cart,
                'subtotal' => $this->subtotal,
                'shipping' => $this->shipping,
                'amount' => $this->amount,
                'paymentData' => $this->paymentData,
                'purchaseNumber' => $this->purchaseNumber,
            ]);
    }
}
