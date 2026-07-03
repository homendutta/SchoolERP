<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Services;

use App\Modules\HumanResources\Models\Separation;
use App\Modules\Staff\Services\StaffTimelineService;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Employee separation. The employee is NEVER deleted. A separation records the
 * exit AND creates a new "separated" employment state (through EmploymentService,
 * so prior history is preserved). Timeline + Audit + Communication are written.
 */
class SeparationService extends BaseCrudService
{
    public function __construct(
        private readonly ActivityLogger $activity,
        private readonly StaffTimelineService $timeline,
        private readonly EmploymentService $employment,
        private readonly HrHooks $hooks,
    ) {}

    protected function model(): string
    {
        return Separation::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['employee:id,name,employee_number']);
    }

    protected function filterable(): array
    {
        return ['school_id', 'staff_id', 'separation_type', 'clearance_status', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'last_working_day', 'created_at'];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        return $this->transaction(function () use ($data): Model {
            $separation = Separation::query()->create($data);

            // Creates a new "separated" employment state; prior history is preserved.
            $this->employment->recordSeparation(
                (int) $separation->staff_id,
                (int) $separation->school_id,
                $separation->reason,
            );

            $this->timeline->record((int) $separation->staff_id, 'hr.separation_initiated', 'Separation initiated', $separation->reason, [
                'separation_id' => $separation->id, 'type' => $separation->separation_type->value,
            ]);
            $this->activity->record('hr.separation_initiated', 'Separation initiated', $separation, [
                'separation_type' => $separation->separation_type->value,
            ], (int) $separation->school_id, 'hr');
            $this->hooks->separationInitiated((int) $separation->school_id, "Separation initiated for employee #{$separation->staff_id}.");

            return $separation->refresh();
        });
    }
}
