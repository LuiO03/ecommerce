<?php

namespace App\Services\Checkout\Gateways\DTO;

class GatewaySessionResult
{
    public function __construct(
        public readonly ?string $token,
        public readonly int $status,
        public readonly ?string $message,
    ) {
    }

    public function isSuccess(): bool
    {
        return $this->token !== null && $this->token !== '';
    }
}
