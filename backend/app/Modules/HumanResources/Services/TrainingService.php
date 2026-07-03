<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Services;

use App\Modules\HumanResources\Enums\TrainingStatus;
use App\Modules\HumanResources\Models\Training;
use App\Modules\HumanResources\Models\TrainingParticipant;
use App\Modules\Staff\Services\StaffTimelineService;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

/** Training programmes + participant enrolment. Records remain historical. */
class TrainingService extends BaseCrudService
{
    public function __construct(
        private readonly ActivityLogger $activity,
        private readonly StaffTimelineService $timeline,
        private readonly HrHooks $hooks,
    ) {}

    protected function model(): string
    {
        return Training::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['participants:id,training_id,staff_id,status,completed_on']);
    }

    protected function searchable(): array
    {
        return ['name', 'provider'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'start_date', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'status' => ['type' => 'enum', 'enum' => TrainingStatus::class],
        ];
    }

    /** Enrol an employee on a training programme (publishes a Communication event). */
    public function assignParticipant(Training $training, int $staffId): TrainingParticipant
    {
        return $this->transaction(function () use ($training, $staffId): TrainingParticipant {
            $participant = TrainingParticipant::query()->firstOrCreate(
                ['training_id' => $training->id, 'staff_id' => $staffId],
                ['status' => 'assigned'],
            );

            $this->timeline->record($staffId, 'hr.training_assigned', 'Training assigned', $training->name, [
                'training_id' => $training->id,
            ]);
            $this->activity->record('hr.training_assigned', 'Training assigned', $participant, [
                'training_id' => $training->id, 'staff_id' => $staffId,
            ], (int) $training->school_id, 'hr');
            $this->hooks->trainingAssigned((int) $training->school_id, "Training '{$training->name}' assigned to employee #{$staffId}.");

            return $participant;
        });
    }
}
