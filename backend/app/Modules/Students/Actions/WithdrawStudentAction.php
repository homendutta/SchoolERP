<?php

declare(strict_types=1);

namespace App\Modules\Students\Actions;

use App\Modules\Students\Enums\StudentStatus;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentWithdrawal;
use App\Modules\Students\Services\StudentTimelineService;
use App\Modules\Students\Support\TimelineEvent;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;
use App\Platform\Shared\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;

/**
 * Student Withdrawal. Never deletes a student — records the withdrawal, closes
 * the current academic placement, and moves the lifecycle status to Withdrawn.
 */
class WithdrawStudentAction implements Action
{
    use AsAction;

    public function __construct(
        private readonly StudentTimelineService $timeline,
        private readonly ActivityLogger $activity,
    ) {}

    /**
     * @param  array{withdraw_date:string, reason?:string|null, approved_by?:int|null, remarks?:string|null}  $data
     */
    public function handle(Student $student, array $data): StudentWithdrawal
    {
        if ($student->status === StudentStatus::Withdrawn) {
            throw BusinessRuleException::make('Student is already withdrawn.', 'ALREADY_WITHDRAWN');
        }

        return DB::transaction(function () use ($student, $data): StudentWithdrawal {
            $withdrawal = StudentWithdrawal::create([
                'school_id' => $student->school_id,
                'student_id' => $student->id,
                'withdraw_date' => $data['withdraw_date'],
                'reason' => $data['reason'] ?? null,
                'approved_by' => $data['approved_by'] ?? null,
                'remarks' => $data['remarks'] ?? null,
            ]);

            $student->currentRecord()->first()?->forceFill([
                'is_current' => false,
                'ended_on' => now()->toDateString(),
            ])->save();

            $student->forceFill(['status' => StudentStatus::Withdrawn->value])->save();

            $this->timeline->record($student, TimelineEvent::Withdrawn, 'Student withdrawn', $data['reason'] ?? null);
            $this->activity->record('student.withdrawn', "Withdrew {$student->name}", $student, [], $student->school_id, 'students');

            return $withdrawal;
        });
    }
}
