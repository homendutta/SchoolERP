<?php

declare(strict_types=1);

namespace App\Modules\Transport\Actions;

use App\Modules\Transport\Models\StudentAssignment;
use App\Modules\Transport\Services\StudentAssignmentEngine;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;

/** Assign a student to a route + stop (capacity-checked, history-preserving). */
class AssignStudentAction implements Action
{
    use AsAction;

    public function __construct(private readonly StudentAssignmentEngine $engine) {}

    /**
     * @param  array{student_id:int, route_id:int, stop_id:int, academic_year_id?:int|null, shift?:string|null}  $payload
     */
    public function handle(array $payload): StudentAssignment
    {
        return $this->engine->assign(
            (int) $payload['student_id'],
            (int) $payload['route_id'],
            (int) $payload['stop_id'],
            ['academic_year_id' => $payload['academic_year_id'] ?? null, 'shift' => $payload['shift'] ?? null],
        );
    }
}
