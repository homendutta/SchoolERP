<?php

declare(strict_types=1);

namespace App\Modules\Academic\Actions;

use App\Modules\Academic\Models\TeacherSubjectAssignment;
use App\Modules\Academic\Services\TeacherSubjectAssignmentService;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;

class AssignTeacherToSubjectAction implements Action
{
    use AsAction;

    public function __construct(private readonly TeacherSubjectAssignmentService $service) {}

    /** @param array<string, mixed> $data validated (academic_year_id, class_id, section_id, subject_id, teacher_id) */
    public function handle(array $data): TeacherSubjectAssignment
    {
        /** @var TeacherSubjectAssignment $assignment */
        $assignment = $this->service->create($data);

        return $assignment;
    }
}
