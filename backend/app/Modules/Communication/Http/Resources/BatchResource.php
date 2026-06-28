<?php

declare(strict_types=1);

namespace App\Modules\Communication\Http\Resources;

use App\Modules\Communication\Models\CommunicationBatch;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin CommunicationBatch
 */
class BatchResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'template_id' => $this->template_id,
            'source' => $this->source,
            'event' => $this->event,
            'channel' => $this->channel->value,
            'subject' => $this->subject,
            'body' => $this->body,
            'audience_type' => $this->audience_type->value,
            'is_mandatory' => $this->is_mandatory,
            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'status' => $this->status,
            'total_recipients' => $this->total_recipients,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
