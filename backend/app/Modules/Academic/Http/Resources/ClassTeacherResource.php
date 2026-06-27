<?php

declare(strict_types=1);

namespace App\Modules\Academic\Http\Resources;

use App\Modules\Academic\Models\ClassTeacher;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin ClassTeacher
 */
class ClassTeacherResource extends BaseResource
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
            'teacher_id' => $this->teacher_id,
            'teacher' => $this->whenLoaded('teacher', fn () => $this->teacher?->only(['id', 'name'])),
            'is_active' => (bool) $this->is_active,
            'assigned_on' => $this->assigned_on?->toDateString(),
            'ended_on' => $this->ended_on?->toDateString(),
        ];
    }
}
