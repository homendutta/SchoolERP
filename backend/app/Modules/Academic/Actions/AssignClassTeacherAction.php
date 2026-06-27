<?php

declare(strict_types=1);

namespace App\Modules\Academic\Actions;

use App\Modules\Academic\Models\ClassTeacher;
use App\Modules\Academic\Services\ClassTeacherService;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;

class AssignClassTeacherAction implements Action
{
    use AsAction;

    public function __construct(private readonly ClassTeacherService $service) {}

    /** @param array{academic_year_id:int, class_id:int, section_id:int, teacher_id:int} $data */
    public function handle(array $data): ClassTeacher
    {
        return $this->service->assign($data);
    }
}
