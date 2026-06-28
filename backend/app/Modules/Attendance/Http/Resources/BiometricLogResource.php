<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Http\Resources;

use App\Modules\Attendance\Models\BiometricLog;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin BiometricLog
 */
class BiometricLogResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'device_id' => $this->device_id,
            'device' => $this->whenLoaded('device', fn () => $this->device?->only(['id', 'name', 'device_identifier'])),
            'identity_number' => $this->identity_number,
            'event_time' => $this->event_time?->toIso8601String(),
            'direction' => $this->direction,
            'processing_status' => $this->processing_status?->value,
            'attendance_id' => $this->attendance_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
