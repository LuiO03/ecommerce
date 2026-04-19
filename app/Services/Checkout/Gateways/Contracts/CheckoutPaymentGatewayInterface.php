<?php

namespace App\Services\Checkout\Gateways\Contracts;

use App\Models\Cart;
use App\Services\Checkout\Gateways\DTO\GatewayAuthorizationResult;
use App\Services\Checkout\Gateways\DTO\GatewaySessionResult;

interface CheckoutPaymentGatewayInterface
{
    public function code(): string;

    public function createSessionToken(float $amount, ?Cart $cart = null): GatewaySessionResult;

    public function authorize(array $payload): GatewayAuthorizationResult;
}
