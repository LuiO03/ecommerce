<?php

namespace App\Services\Checkout;

use App\Services\Checkout\Gateways\Contracts\CheckoutPaymentGatewayInterface;
use App\Services\Checkout\Gateways\NiubizGatewayService;

class PaymentGatewayManager
{
    /**
     * @var array<string, CheckoutPaymentGatewayInterface>
     */
    private array $gateways;

    public function __construct(NiubizGatewayService $niubizGatewayService)
    {
        $this->gateways = [
            $niubizGatewayService->code() => $niubizGatewayService,
        ];
    }

    public function resolve(string $method): ?CheckoutPaymentGatewayInterface
    {
        $code = mb_strtolower(trim($method));

        return $this->gateways[$code] ?? null;
    }
}
