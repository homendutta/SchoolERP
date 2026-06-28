<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Resources;

use App\Modules\Examination\Models\ExamResult;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin ExamResult
 */
class ExamResultResource extends BaseResource
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
            'student_id' => $this->student_id,
            'student' => $this->whenLoaded('student', fn () => $this->student?->name),
            'admission_number' => $this->whenLoaded('student', fn () => $this->student?->admission_number),
            'class_id' => $this->class_id,
            'class' => $this->whenLoaded('schoolClass', fn () => $this->schoolClass?->name),
            'section_id' => $this->section_id,
            'total_obtained' => $this->total_obtained,
            'total_max' => $this->total_max,
            'percentage' => $this->percentage,
            'grade_id' => $this->grade_id,
            'grade' => $this->whenLoaded('grade', fn () => $this->grade?->code),
            'gpa' => $this->gpa,
            'result_status' => $this->result_status->value,
            'rank' => $this->rank,
            'subjects_count' => $this->subjects_count,
            'failed_count' => $this->failed_count,
            'is_published' => $this->is_published,
        ];
    }
}
