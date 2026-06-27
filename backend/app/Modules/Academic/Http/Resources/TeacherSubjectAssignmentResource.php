<?php

declare(strict_types=1);

namespace App\Modules\Academic\Http\Resources;

use App\Modules\Academic\Models\TeacherSubjectAssignment;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin TeacherSubjectAssignment
 */
class TeacherSubjectAssignmentResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'academic_year_id' => $this->academic_year_id,
            'class_id' => $this->class_id,
            'section_id' => $this->section_id,
            'subject_id' => $this->subject_id,
            'teacher_id' => $this->teacher_id,
            'is_primary' => (bool) $this->is_primary,
            'status' => $this->status?->value,
            'teacher' => $this->whenLoaded('teacher', fn () => $this->teacher?->only(['id', 'name'])),
            'subject' => $this->whenLoaded('subject', fn () => $this->subject?->only(['id', 'code', 'name'])),
            'class' => $this->whenLoaded('schoolClass', fn () => $this->schoolClass?->only(['id', 'name'])),
            'section' => $this->whenLoaded('section', fn () => $this->section?->only(['id', 'name'])),
            'archived' => $this->trashed(),
        ];
    }
}
