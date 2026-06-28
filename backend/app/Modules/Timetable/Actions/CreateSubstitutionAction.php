<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Actions;

use App\Modules\Staff\Services\StaffTimelineService;
use App\Modules\Timetable\Models\TimetableSubstitution;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;
use Illuminate\Support\Facades\DB;

/**
 * Record a temporary substitution. This NEVER modifies the master timetable —
 * it is a separate record. It writes the Audit Log and a Timeline event on both
 * the original and substitute teachers.
 */
class CreateSubstitutionAction implements Action
{
    use AsAction;

    public function __construct(
        private readonly ActivityLogger $activity,
        private readonly StaffTimelineService $staffTimeline,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): TimetableSubstitution
    {
        return DB::transaction(function () use ($data): TimetableSubstitution {
            $substitution = TimetableSubstitution::query()->create($data);

            $title = 'Substitution on '.$substitution->date->toDateString();

            if ($substitution->original_teacher_id !== null) {
                $this->staffTimeline->record($substitution->original_teacher_id, 'timetable.substitution_out', $title, $data['reason'] ?? null, [
                    'substitution_id' => $substitution->id,
                ]);
            }
            $this->staffTimeline->record($substitution->substitute_teacher_id, 'timetable.substitution_in', $title, $data['reason'] ?? null, [
                'substitution_id' => $substitution->id,
            ]);

            $this->activity->record('timetable.substitution_created', $title, $substitution, [
                'original_teacher_id' => $substitution->original_teacher_id,
                'substitute_teacher_id' => $substitution->substitute_teacher_id,
                'date' => $substitution->date->toDateString(),
            ], $substitution->school_id, 'timetable');

            return $substitution->refresh();
        });
    }
}
