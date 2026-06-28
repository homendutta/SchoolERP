<?php

declare(strict_types=1);

namespace App\Modules\Students\Http\Resources;

use App\Modules\Students\Models\StudentAcademicRecord;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin StudentAcademicRecord
 */
class AcademicRecordResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'academic_year' => $this->whenLoaded('academicYear', fn () => $this->academicYear?->only(['id', 'name'])),
            'class' => $this->whenLoaded('schoolClass', fn () => $this->schoolClass?->only(['id', 'name'])),
            'section' => $this->whenLoaded('section', fn () => $this->section?->only(['id', 'name'])),
            'roll_number' => $this->roll_number,
            'admission_number' => $this->admission_number,
            'status' => $this->status,
            // "Current" is derived (latest open record); falls back to the column
            // for single-record reads where no marking is supplied.
            'is_current' => (bool) ($this->is_current_marked ?? $this->is_current),
            'promoted_from_record_id' => $this->promoted_from_record_id,
            'started_on' => $this->started_on?->toDateString(),
            'ended_on' => $this->ended_on?->toDateString(),
        ];
    }
}
