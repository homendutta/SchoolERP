<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Http\Resources;

use App\Modules\Attendance\Models\AttendanceRecord;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin AttendanceRecord
 */
class AttendanceRecordResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'school_id' => $this->school_id,
            'identity_id' => $this->identity_id,
            'identity_number' => $this->whenLoaded('identity', fn () => $this->identity?->identity_number),
            'owner' => $this->whenLoaded('owner', fn () => $this->owner ? [
                'id' => $this->owner->getKey(),
                'name' => $this->owner->getAttribute('name'),
                'type' => class_basename($this->owner_type),
            ] : null),
            'owner_type' => class_basename($this->owner_type),
            'academic_year_id' => $this->academic_year_id,
            'class_id' => $this->class_id,
            'section_id' => $this->section_id,
            'department_id' => $this->department_id,
            'designation_id' => $this->designation_id,
            'session_id' => $this->session_id,
            'session' => $this->whenLoaded('session', fn () => $this->session?->only(['id', 'label', 'value'])),
            'shift' => $this->shift,
            'attendance_date' => $this->attendance_date?->toDateString(),
            'status' => $this->status?->value,
            'source' => $this->source?->value,
            'check_in_time' => $this->check_in_time,
            'check_out_time' => $this->check_out_time,
            'is_late' => (bool) $this->is_late,
            'remarks' => $this->remarks,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
