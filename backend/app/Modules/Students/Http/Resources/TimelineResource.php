<?php

declare(strict_types=1);

namespace App\Modules\Students\Http\Resources;

use App\Modules\Students\Models\StudentTimeline;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin StudentTimeline
 */
class TimelineResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'event_type' => $this->event_type,
            'title' => $this->title,
            'description' => $this->description,
            'performed_by' => $this->performed_by,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
