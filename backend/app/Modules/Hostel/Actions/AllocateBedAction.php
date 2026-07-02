<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Actions;

use App\Modules\Hostel\Models\Allocation;
use App\Modules\Hostel\Services\AllocationEngine;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;

/** Allocate a student to a bed (single-occupant, history-preserving). */
class AllocateBedAction implements Action
{
    use AsAction;

    public function __construct(private readonly AllocationEngine $engine) {}

    /**
     * @param  array{student_id:int, bed_id:int, academic_year_id?:int|null}  $payload
     */
    public function handle(array $payload): Allocation
    {
        return $this->engine->allocate(
            (int) $payload['student_id'],
            (int) $payload['bed_id'],
            ['academic_year_id' => $payload['academic_year_id'] ?? null],
        );
    }
}
