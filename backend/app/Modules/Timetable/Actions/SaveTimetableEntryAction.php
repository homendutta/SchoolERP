<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Actions;

use App\Modules\Timetable\DTO\TimetableEntryData;
use App\Modules\Timetable\Models\ClassTimetable;
use App\Modules\Timetable\Services\ClashDetector;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;
use Illuminate\Support\Facades\DB;

/**
 * Create or update a master timetable slot. Clash detection runs first so an
 * invalid schedule can never be saved. Every change writes the Audit Log.
 */
class SaveTimetableEntryAction implements Action
{
    use AsAction;

    public function __construct(
        private readonly ClashDetector $clashes,
        private readonly ActivityLogger $activity,
    ) {}

    public function handle(TimetableEntryData $data, ?int $id = null): ClassTimetable
    {
        return DB::transaction(function () use ($data, $id): ClassTimetable {
            $this->clashes->assertNoClash($data, $id);

            if ($id !== null) {
                $entry = ClassTimetable::query()->findOrFail($id);
                $entry->update($data->toAttributes());
                $action = 'timetable.updated';
            } else {
                $entry = ClassTimetable::query()->create($data->toAttributes());
                $action = 'timetable.created';
            }

            $this->activity->record($action, "Timetable slot {$data->weekday->value} period {$data->periodId}", $entry, [
                'class_id' => $data->classId,
                'section_id' => $data->sectionId,
                'subject_id' => $data->subjectId,
                'teacher_id' => $data->teacherId,
                'room_id' => $data->roomId,
            ], $data->schoolId, 'timetable');

            return $entry->refresh();
        });
    }
}
