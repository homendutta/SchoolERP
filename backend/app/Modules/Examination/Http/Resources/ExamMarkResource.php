<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Resources;

use App\Modules\Examination\Models\ExamMark;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin ExamMark
 */
class ExamMarkResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'exam_subject_id' => $this->exam_subject_id,
            'student_id' => $this->student_id,
            'student' => $this->whenLoaded('student', fn () => $this->student?->name),
            'component_id' => $this->component_id,
            'marks_obtained' => $this->marks_obtained,
            'max_marks' => $this->max_marks,
            'is_absent' => $this->is_absent,
            'remarks' => $this->remarks,
        ];
    }
}
