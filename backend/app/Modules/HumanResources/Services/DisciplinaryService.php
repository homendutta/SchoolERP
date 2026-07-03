<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Services;

use App\Modules\HumanResources\Enums\DisciplinaryAction;
use App\Modules\HumanResources\Models\DisciplinaryRecord;
use App\Modules\Staff\Services\StaffTimelineService;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Disciplinary records. Complete history is maintained; documents are Media refs. */
class DisciplinaryService extends BaseCrudService
{
    public function __construct(
        private readonly ActivityLogger $activity,
        private readonly StaffTimelineService $timeline,
    ) {}

    protected function model(): string
    {
        return DisciplinaryRecord::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['employee:id,name,employee_number']);
    }

    protected function searchable(): array
    {
        return ['subject'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'staff_id', 'action_type', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'action_date', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'action_type' => ['type' => 'enum', 'enum' => DisciplinaryAction::class],
        ];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        return $this->transaction(function () use ($data): Model {
            $record = DisciplinaryRecord::query()->create($data);

            $this->timeline->record((int) $record->staff_id, 'hr.disciplinary_recorded', 'Disciplinary action recorded', $record->subject, [
                'disciplinary_id' => $record->id, 'action_type' => $record->action_type->value,
            ]);
            $this->activity->record('hr.disciplinary_recorded', 'Disciplinary action recorded', $record, [
                'action_type' => $record->action_type->value,
            ], (int) $record->school_id, 'hr');

            return $record->refresh();
        });
    }
}
