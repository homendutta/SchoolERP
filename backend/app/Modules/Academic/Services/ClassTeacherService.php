<?php

declare(strict_types=1);

namespace App\Modules\Academic\Services;

use App\Modules\Academic\Models\ClassTeacher;
use App\Platform\Shared\Services\BaseService;
use Illuminate\Support\Collection;

/**
 * Class Teacher assignment with history. Assigning a new class teacher
 * deactivates the current one (keeps the row) and inserts a fresh active row,
 * so only one is active per Academic Year / Class / Section at any time.
 */
class ClassTeacherService extends BaseService
{
    /**
     * @param  array{academic_year_id:int, class_id:int, section_id:int, teacher_id:int}  $data
     */
    public function assign(array $data): ClassTeacher
    {
        return $this->transaction(function () use ($data): ClassTeacher {
            ClassTeacher::query()
                ->where('academic_year_id', $data['academic_year_id'])
                ->where('class_id', $data['class_id'])
                ->where('section_id', $data['section_id'])
                ->where('is_active', true)
                ->update(['is_active' => false, 'ended_on' => now()->toDateString()]);

            return ClassTeacher::query()->create([
                ...$data,
                'is_active' => true,
                'assigned_on' => now()->toDateString(),
            ]);
        });
    }

    /** Active class teachers (optionally scoped), most recent first. */
    public function history(int $academicYearId, int $classId, int $sectionId): Collection
    {
        return ClassTeacher::query()
            ->with('teacher:id,name')
            ->where('academic_year_id', $academicYearId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->latest()
            ->get();
    }
}
