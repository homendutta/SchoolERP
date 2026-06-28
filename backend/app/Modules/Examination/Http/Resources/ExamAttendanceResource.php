<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Resources;

use App\Modules\Examination\Models\ExamAttendance;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin ExamAttendance
 */
class ExamAttendanceResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'exam_schedule_id' => $this->exam_schedule_id,
            'student_id' => $this->student_id,
            'student' => $this->whenLoaded('student', fn () => $this->student?->name),
            'status' => $this->status->value,
            'remarks' => $this->remarks,
        ];
    }
}
