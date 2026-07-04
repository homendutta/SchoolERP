<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Http\Controllers;

use App\Modules\Integrations\Enums\DeliveryStatus;
use App\Modules\Integrations\Enums\WebhookDirection;
use App\Modules\Integrations\Models\Webhook;
use App\Modules\Integrations\Models\WebhookDelivery;
use App\Modules\Integrations\Services\EventBus;
use App\Platform\Shared\Http\Controllers\BaseController;
use App\Platform\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Receives an INCOMING webhook (no auth). The HMAC signature is verified against
 * the webhook's stored secret before the payload is accepted; every receipt is
 * logged as a delivery. Invalid signatures are rejected.
 */
class IncomingWebhookController extends BaseController
{
    public function __construct(private readonly EventBus $bus) {}

    public function receive(Request $request, int|string $id): JsonResponse
    {
        $webhook = Webhook::query()->where('direction', WebhookDirection::Incoming->value)->find($id);
        if ($webhook === null) {
            return ApiResponse::error('Webhook not found.', 404, 'NOT_FOUND');
        }

        $raw = $request->getContent();
        $signature = (string) $request->header('X-Signature', '');
        $valid = $this->bus->verifySignature($webhook, $raw, $signature);

        WebhookDelivery::query()->create([
            'school_id' => $webhook->school_id,
            'webhook_id' => $webhook->id,
            'event' => (string) $request->header('X-Event', 'incoming'),
            'payload' => $request->all(),
            'signature' => $signature,
            'status' => $valid ? DeliveryStatus::Delivered->value : DeliveryStatus::Failed->value,
            'attempts' => 1,
            'response_code' => $valid ? 200 : 401,
            'delivered_at' => $valid ? now() : null,
        ]);

        if (! $valid) {
            return ApiResponse::error('Invalid signature.', 401, 'INVALID_SIGNATURE');
        }

        return $this->ok(['accepted' => true], 'Webhook accepted.');
    }
}
