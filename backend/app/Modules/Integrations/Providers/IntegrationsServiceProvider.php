<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Providers;

use App\Modules\Integrations\Adapters\ManualPaymentAdapter;
use App\Modules\Integrations\Adapters\RestConnectorAdapter;
use App\Modules\Integrations\Support\ProviderRegistry;
use App\Platform\Core\Providers\ModuleServiceProvider;

/**
 * Integrations Platform module (Sprint 22).
 *
 * The single gateway between the ERP and every third-party system. Modules never
 * call providers directly — they resolve a provider by CATEGORY through the
 * platform, which selects the enabled provider and its registered adapter. Provider
 * credentials are stored ENCRYPTED; every request/failure is logged; the reusable
 * REST connector handles retries/timeouts; webhooks verify HMAC signatures and
 * retry on the queue; the event bus records IMMUTABLE domain events and fans them
 * out; config changes are audited + timelined. Health/monitoring is exposed.
 *
 * The ProviderRegistry is a singleton so adapters registered here (and by other
 * modules) persist. Representative adapters ship — Manual payment (REUSING the
 * Finance gateway abstraction) and a generic REST connector. Real provider SDKs
 * (Google/Microsoft OAuth, SES/Mailgun/SendGrid, MSG91/Twilio, Firebase, WhatsApp,
 * Razorpay/PhonePe/Cashfree/Stripe/PayPal, eSSL/ZKTeco, S3/R2/GCS/Azure, Zoom/Meet/
 * Teams/Jitsi/BBB, OpenAI/Anthropic/Gemini, DigiLocker/Aadhaar/UDISE+/AISHE …) plug
 * in as adapters with NO structural change.
 */
class IntegrationsServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Integrations';

    protected function registerBindings(): void
    {
        $this->app->singleton(ProviderRegistry::class, function ($app): ProviderRegistry {
            $registry = new ProviderRegistry;
            $registry->register($app->make(ManualPaymentAdapter::class));
            $registry->register($app->make(RestConnectorAdapter::class));

            return $registry;
        });
    }
}
