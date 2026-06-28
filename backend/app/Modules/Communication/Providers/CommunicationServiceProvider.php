<?php

declare(strict_types=1);

namespace App\Modules\Communication\Providers;

use App\Modules\Communication\Models\CommunicationMessage;
use App\Modules\Communication\Policies\CommunicationPolicy;
use App\Modules\Communication\Support\Channels\EmailProvider;
use App\Modules\Communication\Support\Channels\InAppProvider;
use App\Modules\Communication\Support\Channels\ProviderRegistry;
use App\Modules\Communication\Support\Channels\PushProvider;
use App\Modules\Communication\Support\Channels\SmsProvider;
use App\Platform\Core\Providers\ModuleServiceProvider;
use App\Platform\Foundation\Notifications\NotificationService;
use Illuminate\Support\Facades\Gate;

/**
 * Communication module — the central communication hub.
 *
 * Every business module publishes here; none sends Email/SMS/Push/In-App
 * directly. Channels are vendor-independent through the provider registry
 * (Email/SMS reuse the Platform Notification Engine; real providers — SMTP/SES/
 * Mailgun/SendGrid, Twilio/MSG91/Textlocal/Fast2SMS, FCM/APNs, WhatsApp … — bind
 * later with no structural change). Attachments use the Media Platform.
 */
class CommunicationServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Communication';

    protected function registerBindings(): void
    {
        $this->app->singleton(ProviderRegistry::class, function ($app): ProviderRegistry {
            $registry = new ProviderRegistry;
            $notifications = $app->make(NotificationService::class);
            $registry->register(new EmailProvider($notifications));
            $registry->register(new SmsProvider($notifications));
            $registry->register(new PushProvider);
            $registry->register(new InAppProvider);

            return $registry;
        });
    }

    protected function registerPolicies(): void
    {
        Gate::policy(CommunicationMessage::class, CommunicationPolicy::class);
    }
}
