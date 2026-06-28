<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Resources;

use App\Modules\Examination\Models\ExamSchedule;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin ExamSchedule
 */
class ExamScheduleResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'exam_session_id' => $this->exam_session_id,
            'exam_subject_id' => $this->exam_subject_id,
            'subject' => $this->whenLoaded('examSubject', fn () => $this->examSubject?->subject?->name),
            'class' => $this->whenLoaded('examSubject', fn () => $this->examSubject?->schoolClass?->name),
            'exam_date' => $this->exam_date?->toDateString(),
            'period_id' => $this->period_id,
            'period' => $this->whenLoaded('period', fn () => $this->period?->name),
            'start_time' => $this->start_time,
            'room_id' => $this->room_id,
            'room' => $this->whenLoaded('room', fn () => $this->room?->name),
            'duration_minutes' => $this->duration_minutes,
            'status' => $this->status,
            'invigilators' => $this->whenLoaded('invigilators', fn () => $this->invigilators->map(fn ($i) => [
                'id' => $i->id,
                'staff_id' => $i->staff_id,
                'staff' => $i->staff?->name,
                'role' => $i->role->value,
            ])->values()),
        ];
    }
}
