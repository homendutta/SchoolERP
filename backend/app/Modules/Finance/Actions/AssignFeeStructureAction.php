<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Modules\Finance\Models\FeeStructure;
use App\Modules\Finance\Models\StudentFee;
use App\Modules\Finance\Models\StudentFeeItem;
use App\Modules\Finance\Services\StudentFeeService;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentAcademicRecord;
use App\Modules\Students\Services\StudentTimelineService;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;
use Illuminate\Support\Facades\DB;

/**
 * Assign a Fee Structure to a student. Line items are COPIED from the structure
 * (denormalized) so a later payment never touches a Fee Master. Reuses the
 * student's current placement from Student Management.
 */
class AssignFeeStructureAction implements Action
{
    use AsAction;

    public function __construct(
        private readonly StudentFeeService $studentFees,
        private readonly StudentTimelineService $timeline,
        private readonly ActivityLogger $activity,
    ) {}

    public function handle(Student $student, FeeStructure $structure): StudentFee
    {
        return DB::transaction(function () use ($student, $structure): StudentFee {
            $placement = StudentAcademicRecord::query()
                ->where('student_id', $student->id)
                ->where('is_current', true)
                ->latest('id')
                ->first();

            $fee = StudentFee::query()->create([
                'school_id' => $student->school_id,
                'student_id' => $student->id,
                'fee_structure_id' => $structure->id,
                'academic_year_id' => $structure->academic_year_id ?? $placement?->academic_year_id,
                'class_id' => $placement?->class_id,
                'section_id' => $placement?->section_id,
            ]);

            $structure->loadMissing('items.feeMaster');
            foreach ($structure->items as $item) {
                $master = $item->feeMaster;
                StudentFeeItem::query()->create([
                    'school_id' => $student->school_id,
                    'student_fee_id' => $fee->id,
                    'fee_master_id' => $master?->id,
                    'fee_category_id' => $master?->fee_category_id,
                    'name' => $master?->name ?? 'Fee',
                    'amount' => $item->amount ?? ($master?->amount ?? 0),
                    'due_date' => $master?->due_date,
                ]);
            }

            $fee = $this->studentFees->recompute($fee);

            $this->timeline->record($student->id, 'finance.fee_assigned', "Fee structure '{$structure->name}' assigned", null, [
                'student_fee_id' => $fee->id, 'net_amount' => $fee->net_amount,
            ]);
            $this->activity->record('finance.fee_assigned', "Assigned {$structure->name}", $fee, ['net_amount' => $fee->net_amount], $student->school_id, 'finance');

            return $fee;
        });
    }
}
