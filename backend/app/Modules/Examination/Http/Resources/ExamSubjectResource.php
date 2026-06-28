<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Resources;

use App\Modules\Examination\Models\ExamSubject;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin ExamSubject
 */
class ExamSubjectResource extends BaseResource
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
            'class_id' => $this->class_id,
            'class' => $this->whenLoaded('schoolClass', fn () => $this->schoolClass?->name),
            'section_id' => $this->section_id,
            'section' => $this->whenLoaded('section', fn () => $this->section?->name),
            'subject_id' => $this->subject_id,
            'subject' => $this->whenLoaded('subject', fn () => $this->subject?->name),
            'subject_type_id' => $this->subject_type_id,
            'subject_type' => $this->whenLoaded('subjectType', fn () => $this->subjectType?->label),
            'is_elective' => $this->is_elective,
            'max_marks' => $this->max_marks,
            'passing_marks' => $this->passing_marks,
            'has_components' => $this->has_components,
            'sort_order' => $this->sort_order,
            'status' => $this->status->value,
        ];
    }
}
