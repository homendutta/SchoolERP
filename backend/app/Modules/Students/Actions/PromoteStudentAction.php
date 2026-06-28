<?php

declare(strict_types=1);

namespace App\Modules\Students\Actions;

use App\Modules\Students\Enums\StudentStatus;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentAcademicRecord;
use App\Modules\Students\Services\StudentTimelineService;
use App\Modules\Students\Support\TimelineEvent;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;
use App\Platform\Shared\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;

/**
 * Promotion Engine. Validates → creates a NEW academic record → updates status →
 * writes the timeline → audit log, all in one transaction. Previous academic
 * records are NEVER modified (history is immutable).
 */
class PromoteStudentAction implements Action
{
    use AsAction;

    public function __construct(
        private readonly StudentTimelineService $timeline,
        private readonly ActivityLogger $activity,
    ) {}

    /**
     * @param  array{academic_year_id:int, class_id:int, section_id?:int|null, roll_number?:string|null}  $data
     */
    public function handle(Student $student, array $data): Student
    {
        if (in_array($student->status, [StudentStatus::Withdrawn, StudentStatus::Graduated], true)) {
            throw BusinessRuleException::make('A withdrawn or graduated student cannot be promoted.', 'STUDENT_NOT_PROMOTABLE');
        }

        $current = $student->currentRecord()->first();
        if ($current === null) {
            throw BusinessRuleException::make('Student has no current academic record to promote from.', 'NO_CURRENT_RECORD');
        }

        return DB::transaction(function () use ($student, $current, $data): Student {
            // History is immutable: the previous record is NEVER updated. Promotion
            // only inserts a new record; "current" is derived as the latest one.
            StudentAcademicRecord::create([
                'school_id' => $student->school_id,
                'student_id' => $student->id,
                'academic_year_id' => $data['academic_year_id'],
                'class_id' => $data['class_id'],
                'section_id' => $data['section_id'] ?? null,
                'roll_number' => $data['roll_number'] ?? null,
                'admission_number' => $student->admission_number,
                'promoted_from_record_id' => $current->id,
                'status' => 'active',
                'is_current' => true,
                'started_on' => now()->toDateString(),
            ]);

            $student->forceFill(['status' => StudentStatus::Promoted->value])->save();

            $this->timeline->record($student, TimelineEvent::Promoted, 'Promoted to the next class', null, [
                'from_record_id' => $current->id,
                'to_academic_year_id' => $data['academic_year_id'],
            ]);
            $this->activity->record('student.promoted', "Promoted {$student->name}", $student, [
                'from_record_id' => $current->id,
            ], $student->school_id, 'students');

            return $student->refresh();
        });
    }
}
