<?php

namespace App\Services\Checkout\Gateways\DTO;

class GatewayAuthorizationResult
{
    /**
     * @param array<string, mixed> $response
     */
    public function __construct(
        public readonly bool $ok,
        public readonly array $response,
        public readonly int $status,
        public readonly ?string $message,
    ) {
    }

    public function hasResponse(): bool
    {
        return !empty($this->response);
    }
}
