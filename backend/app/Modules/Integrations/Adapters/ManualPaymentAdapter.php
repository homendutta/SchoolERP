<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Adapters;

use App\Modules\Finance\Support\Gateway\GatewayRegistry;
use App\Modules\Integrations\Support\IntegrationProvider;

/**
 * Payment provider adapter that REUSES the Finance gateway abstraction (never
 * duplicating payment logic). It exposes the Finance-registered gateways
 * (Manual today; Razorpay/PhonePe/Cashfree/Stripe/PayPal as they are added) to the
 * Integration Platform. A representative adapter — no real provider SDK ships.
 */
class ManualPaymentAdapter implements IntegrationProvider
{
    public function __construct(private readonly GatewayRegistry $gateways) {}

    public function code(): string
    {
        return 'manual';
    }

    public function category(): string
    {
        return 'payment';
    }

    public function name(): string
    {
        return 'Manual Payment (Finance gateway)';
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array{status:string, detail?:string}
     */
    public function healthCheck(array $config): array
    {
        return $this->gateways->has('manual')
            ? ['status' => 'healthy', 'detail' => 'Finance manual gateway registered.']
            : ['status' => 'down', 'detail' => 'Finance manual gateway missing.'];
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    public function test(array $config): array
    {
        return [
            'ok' => $this->gateways->has('manual'),
            'available_gateways' => $this->gateways->providers(),
        ];
    }
}
