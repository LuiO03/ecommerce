<?php

namespace App\Services\Checkout\Gateways\Contracts;

use App\Models\Cart;

interface CheckoutPaymentGatewayInterface
{
    public function code(): string;

    public function createSessionToken(float $amount, ?Cart $cart = null): array;

    public function authorize(array $payload): array;
}
