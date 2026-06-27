<?php

declare(strict_types=1);

namespace App\Modules\Academic\Services;

use App\Modules\Academic\Models\TeacherSubjectAssignment;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

class TeacherSubjectAssignmentService extends BaseCrudService
{
    protected function model(): string
    {
        return TeacherSubjectAssignment::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with([
            'teacher:id,name',
            'subject:id,code,name',
            'schoolClass:id,name',
            'section:id,name',
        ]);
    }

    protected function filterable(): array
    {
        return ['academic_year_id', 'class_id', 'section_id', 'subject_id', 'teacher_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'created_at'];
    }
}
