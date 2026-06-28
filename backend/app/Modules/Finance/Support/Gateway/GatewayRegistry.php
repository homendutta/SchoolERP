<?php

declare(strict_types=1);

namespace App\Modules\Finance\Support\Gateway;

use App\Platform\Shared\Exceptions\BusinessRuleException;

/**
 * Registry of payment gateways. New providers register here — the rest of the
 * Finance module never changes (mirrors the Attendance biometric connector
 * pattern). Vendor-independent by design.
 */
class GatewayRegistry
{
    /** @var array<string, PaymentGateway> */
    private array $gateways = [];

    public function register(PaymentGateway $gateway): void
    {
        $this->gateways[$gateway->provider()] = $gateway;
    }

    public function has(string $provider): bool
    {
        return isset($this->gateways[$provider]);
    }

    public function get(string $provider): PaymentGateway
    {
        return $this->gateways[$provider]
            ?? throw BusinessRuleException::make("Payment gateway '{$provider}' is not configured.", 'GATEWAY_NOT_FOUND');
    }

    /** @return array<int, string> */
    public function providers(): array
    {
        return array_keys($this->gateways);
    }
}
