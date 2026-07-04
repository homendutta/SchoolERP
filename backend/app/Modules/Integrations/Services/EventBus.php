<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Services;

use App\Modules\Integrations\Enums\WebhookDirection;
use App\Modules\Integrations\Jobs\DeliverWebhookJob;
use App\Modules\Integrations\Models\IntegrationEvent;
use App\Modules\Integrations\Models\Webhook;
use App\Modules\Integrations\Models\WebhookDelivery;

/**
 * The integration event bus. Business modules PUBLISH domain events here (never
 * poll integrations); the platform records each event IMMUTABLY and fans it out to
 * any outgoing webhook subscribed to it (queued, signed delivery).
 *
 * Examples: student.created, fee.paid, attendance.marked, result.published,
 * payroll.processed, certificate.generated.
 */
class EventBus
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function publish(int $schoolId, string $event, array $payload, string $source = 'erp'): IntegrationEvent
    {
        $record = IntegrationEvent::query()->create([
            'school_id' => $schoolId,
            'event' => $event,
            'source' => $source,
            'payload' => $payload,
            'dispatched_at' => now(),
        ]);

        $webhooks = Webhook::query()->where('school_id', $schoolId)
            ->where('direction', WebhookDirection::Outgoing->value)
            ->where('status', 'active')->get()
            ->filter(fn (Webhook $w) => in_array($event, $w->events ?? [], true) || in_array('*', $w->events ?? [], true));

        foreach ($webhooks as $webhook) {
            $signature = hash_hmac('sha256', json_encode($payload) ?: '', (string) ($webhook->secret ?? ''));
            $delivery = WebhookDelivery::query()->create([
                'school_id' => $schoolId,
                'webhook_id' => $webhook->id,
                'event' => $event,
                'payload' => $payload,
                'signature' => $signature,
            ]);

            DeliverWebhookJob::dispatch($delivery->id);
        }

        return $record;
    }

    /**
     * Verify an INCOMING webhook's HMAC signature against its secret.
     */
    public function verifySignature(Webhook $webhook, string $rawBody, string $signature): bool
    {
        // The `secret` cast already returns the decrypted signing key.
        $expected = hash_hmac('sha256', $rawBody, (string) ($webhook->secret ?? ''));

        return hash_equals($expected, $signature);
    }
}
