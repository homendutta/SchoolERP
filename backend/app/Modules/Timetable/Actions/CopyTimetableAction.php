<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Actions;

use App\Modules\Timetable\Models\ClassTimetable;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;
use Illuminate\Support\Facades\DB;

/**
 * Copy a timetable from one academic year / template into another. Supports
 * selective copying by class. The source is never modified.
 */
class CopyTimetableAction implements Action
{
    use AsAction;

    public function __construct(private readonly ActivityLogger $activity) {}

    /**
     * @param  array{
     *     school_id:int,
     *     from_academic_year_id:int,
     *     to_academic_year_id:int,
     *     from_template_id?:int|null,
     *     to_template_id?:int|null,
     *     class_ids?:array<int, int>|null
     * }  $payload
     * @return array{copied:int}
     */
    public function handle(array $payload): array
    {
        return DB::transaction(function () use ($payload): array {
            $source = ClassTimetable::query()
                ->where('school_id', $payload['school_id'])
                ->where('academic_year_id', $payload['from_academic_year_id'])
                ->when(
                    ($payload['from_template_id'] ?? null) === null,
                    fn ($q) => $q->whereNull('template_id'),
                    fn ($q) => $q->where('template_id', $payload['from_template_id']),
                )
                ->when(
                    ! empty($payload['class_ids']),
                    fn ($q) => $q->whereIn('class_id', $payload['class_ids']),
                )
                ->get();

            $copied = 0;
            foreach ($source as $slot) {
                ClassTimetable::query()->create([
                    'school_id' => $slot->school_id,
                    'template_id' => $payload['to_template_id'] ?? null,
                    'academic_year_id' => $payload['to_academic_year_id'],
                    'class_id' => $slot->class_id,
                    'section_id' => $slot->section_id,
                    'weekday' => $slot->weekday->value,
                    'period_id' => $slot->period_id,
                    'subject_id' => $slot->subject_id,
                    'teacher_id' => $slot->teacher_id,
                    'room_id' => $slot->room_id,
                    'status' => 'active',
                ]);
                $copied++;
            }

            $this->activity->record('timetable.copied', "Copied {$copied} slots between academic years", null, [
                'from' => $payload['from_academic_year_id'],
                'to' => $payload['to_academic_year_id'],
                'copied' => $copied,
            ], $payload['school_id'], 'timetable');

            return ['copied' => $copied];
        });
    }
}
