<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Jobs;

use App\Modules\Integrations\Enums\DeliveryStatus;
use App\Modules\Integrations\Models\Webhook;
use App\Modules\Integrations\Models\WebhookDelivery;
use App\Modules\Integrations\Services\RestConnector;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Delivers an outgoing webhook (signed) via the REST connector and RETRIES on the
 * queue up to the webhook's max_retries. Each attempt updates the immutable-ish
 * delivery log; the platform never blocks a business request on webhook delivery.
 */
class DeliverWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly int $deliveryId) {}

    public function handle(RestConnector $connector): void
    {
        $delivery = WebhookDelivery::query()->find($this->deliveryId);
        if ($delivery === null || $delivery->status === DeliveryStatus::Delivered) {
            return;
        }
        $webhook = Webhook::query()->find($delivery->webhook_id);
        if ($webhook === null || $webhook->url === null) {
            $delivery->update(['status' => DeliveryStatus::Failed->value]);

            return;
        }

        $result = $connector->request('POST', $webhook->url, [
            'school_id' => $delivery->school_id,
            'provider_code' => 'webhook',
            'json' => $delivery->payload,
            'headers' => ['X-Signature' => (string) $delivery->signature, 'X-Event' => (string) $delivery->event],
            'retries' => 1,
        ]);

        $attempts = $delivery->attempts + 1;

        if ($result['ok']) {
            $delivery->update([
                'status' => DeliveryStatus::Delivered->value,
                'attempts' => $attempts,
                'response_code' => $result['status'],
                'delivered_at' => now(),
            ]);

            return;
        }

        if ($attempts < (int) $webhook->max_retries) {
            $delivery->update([
                'status' => DeliveryStatus::Pending->value,
                'attempts' => $attempts,
                'response_code' => $result['status'],
                'next_retry_at' => now()->addMinutes($attempts * 5),
            ]);
            self::dispatch($delivery->id)->delay(now()->addMinutes($attempts * 5));

            return;
        }

        $delivery->update([
            'status' => DeliveryStatus::Failed->value,
            'attempts' => $attempts,
            'response_code' => $result['status'],
        ]);
    }
}
