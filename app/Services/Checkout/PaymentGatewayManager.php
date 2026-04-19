<?php

namespace App\Services\Checkout;

use App\Services\Checkout\Gateways\Contracts\CheckoutPaymentGatewayInterface;
use App\Services\Checkout\Gateways\CulqiGatewayService;
use App\Services\Checkout\Gateways\MercadoPagoGatewayService;
use App\Services\Checkout\Gateways\NiubizGatewayService;

class PaymentGatewayManager
{
    /**
     * @var array<string, CheckoutPaymentGatewayInterface>
     */
    private array $gateways;

    public function __construct(
        NiubizGatewayService $niubizGatewayService,
        CulqiGatewayService $culqiGatewayService,
        MercadoPagoGatewayService $mercadoPagoGatewayService,
    )
    {
        $this->gateways = [
            $niubizGatewayService->code() => $niubizGatewayService,
            $culqiGatewayService->code() => $culqiGatewayService,
            $mercadoPagoGatewayService->code() => $mercadoPagoGatewayService,
        ];
    }

    public function resolve(string $method): ?CheckoutPaymentGatewayInterface
    {
        $code = mb_strtolower(trim($method));

        return $this->gateways[$code] ?? null;
    }
}
