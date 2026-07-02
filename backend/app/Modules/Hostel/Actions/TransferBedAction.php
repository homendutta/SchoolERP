<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Actions;

use App\Modules\Hostel\Models\Allocation;
use App\Modules\Hostel\Services\AllocationEngine;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;

/** Transfer a student to a new bed (room/bed/building/hostel change). */
class TransferBedAction implements Action
{
    use AsAction;

    public function __construct(private readonly AllocationEngine $engine) {}

    /**
     * @param  array{student_id:int, to_bed_id:int, reason?:string|null, transfer_type?:string|null}  $payload
     */
    public function handle(array $payload): Allocation
    {
        return $this->engine->transfer(
            (int) $payload['student_id'],
            (int) $payload['to_bed_id'],
            $payload['reason'] ?? null,
            $payload['transfer_type'] ?? null,
        );
    }
}
