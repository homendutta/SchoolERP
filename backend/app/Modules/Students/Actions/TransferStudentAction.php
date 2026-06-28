<?php

declare(strict_types=1);

namespace App\Modules\Students\Actions;

use App\Modules\Students\Enums\StudentStatus;
use App\Modules\Students\Enums\TransferType;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentAcademicRecord;
use App\Modules\Students\Models\StudentTransfer;
use App\Modules\Students\Services\StudentTimelineService;
use App\Modules\Students\Support\TimelineEvent;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;
use App\Platform\Shared\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Student Transfer — internal (class/section move, history preserved via a new
 * academic record) or external (to another school, status → Transferred). The
 * previous record is closed, never overwritten.
 */
class TransferStudentAction implements Action
{
    use AsAction;

    public function __construct(
        private readonly StudentTimelineService $timeline,
        private readonly ActivityLogger $activity,
    ) {}

    /**
     * @param  array{type:string, to_class_id?:int|null, to_section_id?:int|null, transfer_date:string, reason?:string|null, destination_school?:string|null, notes?:string|null}  $data
     */
    public function handle(Student $student, array $data): StudentTransfer
    {
        $type = TransferType::from($data['type']);
        $current = $student->currentRecord()->first();

        if ($type === TransferType::Internal && empty($data['to_class_id'])) {
            throw BusinessRuleException::make('An internal transfer needs a destination class.', 'TRANSFER_NEEDS_CLASS');
        }

        return DB::transaction(function () use ($student, $current, $type, $data): StudentTransfer {
            $transfer = StudentTransfer::create([
                'school_id' => $student->school_id,
                'student_id' => $student->id,
                'type' => $type->value,
                'academic_year_id' => $current?->academic_year_id,
                'from_class_id' => $current?->class_id,
                'from_section_id' => $current?->section_id,
                'to_class_id' => $data['to_class_id'] ?? null,
                'to_section_id' => $data['to_section_id'] ?? null,
                'transfer_date' => $data['transfer_date'],
                'reason' => $data['reason'] ?? null,
                'destination_school' => $data['destination_school'] ?? null,
                'notes' => $data['notes'] ?? null,
                'performed_by' => Auth::id(),
            ]);

            if ($type === TransferType::Internal && $current !== null) {
                // Preserve history immutably: the old record is NOT updated; a new
                // record is inserted and becomes the latest (current) placement.
                StudentAcademicRecord::create([
                    'school_id' => $student->school_id,
                    'student_id' => $student->id,
                    'academic_year_id' => $current->academic_year_id,
                    'class_id' => $data['to_class_id'],
                    'section_id' => $data['to_section_id'] ?? null,
                    'admission_number' => $student->admission_number,
                    'status' => 'active',
                    'is_current' => true,
                    'started_on' => now()->toDateString(),
                ]);
            }

            if ($type === TransferType::External) {
                // External transfer CLOSES the current record and changes status.
                $current?->forceFill(['is_current' => false, 'ended_on' => now()->toDateString(), 'status' => 'transferred'])->save();
                $student->forceFill(['status' => StudentStatus::Transferred->value])->save();
            }

            $this->timeline->record($student, TimelineEvent::Transferred, $type->label().' transfer', $data['reason'] ?? null, [
                'type' => $type->value,
            ]);
            $this->activity->record('student.transferred', "{$type->label()} transfer for {$student->name}", $student, [
                'type' => $type->value,
            ], $student->school_id, 'students');

            return $transfer;
        });
    }
}
