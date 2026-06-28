<?php

declare(strict_types=1);

namespace App\Modules\Communication\Http\Resources;

use App\Modules\Communication\Models\CommunicationMessage;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin CommunicationMessage
 */
class MessageResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'batch_id' => $this->batch_id,
            'channel' => $this->channel->value,
            'recipient_name' => $this->recipient_name,
            'recipient_type' => $this->recipient_type ? class_basename((string) $this->recipient_type) : null,
            'address' => $this->address,
            'subject' => $this->subject,
            'body' => $this->body,
            'status' => $this->status->value,
            'is_mandatory' => $this->is_mandatory,
            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'attempts' => $this->attempts,
            'max_attempts' => $this->max_attempts,
            'sent_at' => $this->sent_at?->toIso8601String(),
            'delivered_at' => $this->delivered_at?->toIso8601String(),
            'read_at' => $this->read_at?->toIso8601String(),
            'error' => $this->error,
            'logs' => $this->whenLoaded('logs', fn () => $this->logs->map(fn ($l) => [
                'event' => $l->event,
                'detail' => $l->detail,
                'at' => $l->created_at?->toIso8601String(),
            ])->values()),
        ];
    }
}
