<?php

declare(strict_types=1);

namespace App\Modules\Finance\Providers;

use App\Modules\Finance\Models\StudentFee;
use App\Modules\Finance\Policies\FinancePolicy;
use App\Modules\Finance\Support\Gateway\GatewayRegistry;
use App\Modules\Finance\Support\Gateway\ManualGateway;
use App\Platform\Core\Providers\ModuleServiceProvider;
use Illuminate\Support\Facades\Gate;

/**
 * Finance module.
 *
 * Keeps Fees (what is owed), Payments (what was paid) and the Ledger (the
 * accounting impact) as separate concepts. Receipt + transaction numbers come
 * from the Number Generator; payment methods from Master Data; the receipt QR
 * from the Identity Platform. Online gateways are vendor-independent through the
 * GatewayRegistry (abstraction only — the Manual gateway ships by default).
 */
class FinanceServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Finance';

    protected function registerBindings(): void
    {
        // Vendor-independent payment gateways. New providers (Razorpay, PhonePe,
        // Cashfree, Stripe…) register here — the rest of Finance never changes.
        $this->app->singleton(GatewayRegistry::class, function (): GatewayRegistry {
            $registry = new GatewayRegistry;
            $registry->register(new ManualGateway);

            return $registry;
        });
    }

    protected function registerPolicies(): void
    {
        Gate::policy(StudentFee::class, FinancePolicy::class);
    }
}
