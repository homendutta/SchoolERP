<?php

declare(strict_types=1);

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\Discount;
use App\Modules\Finance\Models\Scholarship;
use App\Modules\Finance\Models\StudentDiscount;
use App\Modules\Finance\Models\StudentFee;
use App\Modules\Finance\Models\StudentScholarship;
use App\Modules\Students\Models\Student;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Exceptions\BusinessRuleException;
use App\Platform\Shared\Services\BaseService;

/**
 * Applies discounts, scholarships and sibling concessions to a student fee, then
 * recomputes the fee. Scholarships are independent of discounts and their full
 * history is preserved.
 */
class ConcessionService extends BaseService
{
    public function __construct(
        private readonly StudentFeeService $studentFees,
        private readonly SiblingDiscountService $siblings,
        private readonly ActivityLogger $activity,
    ) {}

    public function applyDiscount(StudentFee $fee, Discount $discount, ?string $reason = null): StudentDiscount
    {
        return $this->transaction(function () use ($fee, $discount, $reason): StudentDiscount {
            $base = $this->discountBase($fee);
            $amount = $discount->method->compute($discount->value, $base);

            $record = StudentDiscount::query()->create([
                'school_id' => $fee->school_id,
                'student_fee_id' => $fee->id,
                'student_id' => $fee->student_id,
                'discount_id' => $discount->id,
                'source' => 'discount',
                'amount' => $amount,
                'reason' => $reason ?? $discount->name,
            ]);

            $this->studentFees->recompute($fee);
            $this->activity->record('finance.discount_applied', "Discount {$discount->name} applied", $record, ['amount' => $amount], $fee->school_id, 'finance');

            return $record;
        });
    }

    public function applyScholarship(StudentFee $fee, Scholarship $scholarship, ?string $notes = null): StudentScholarship
    {
        return $this->transaction(function () use ($fee, $scholarship, $notes): StudentScholarship {
            $base = $this->discountBase($fee);
            $amount = $scholarship->type->value === 'full' ? $base : $scholarship->method->compute($scholarship->value, $base);

            $record = StudentScholarship::query()->create([
                'school_id' => $fee->school_id,
                'student_id' => $fee->student_id,
                'scholarship_id' => $scholarship->id,
                'student_fee_id' => $fee->id,
                'academic_year_id' => $fee->academic_year_id,
                'amount' => $amount,
                'awarded_on' => now()->toDateString(),
                'notes' => $notes,
            ]);

            $this->studentFees->recompute($fee);
            $this->activity->record('finance.scholarship_awarded', "Scholarship {$scholarship->name} awarded", $record, ['amount' => $amount], $fee->school_id, 'finance');

            return $record;
        });
    }

    /** Apply the configurable sibling concession for the fee's student. */
    public function applySibling(StudentFee $fee): StudentDiscount
    {
        $student = Student::query()->findOrFail($fee->student_id);
        $rule = $this->siblings->ruleFor($student);
        if ($rule === null) {
            throw BusinessRuleException::make('No sibling concession applies to this student.', 'NO_SIBLING_RULE');
        }

        return $this->transaction(function () use ($fee, $rule): StudentDiscount {
            $base = $this->discountBase($fee);
            $amount = $rule->method->compute($rule->value, $base);

            $record = StudentDiscount::query()->create([
                'school_id' => $fee->school_id,
                'student_fee_id' => $fee->id,
                'student_id' => $fee->student_id,
                'sibling_rule_id' => $rule->id,
                'source' => 'sibling',
                'amount' => $amount,
                'reason' => $rule->name,
            ]);

            $this->studentFees->recompute($fee);
            $this->activity->record('finance.sibling_discount_applied', "Sibling concession {$rule->name}", $record, ['amount' => $amount], $fee->school_id, 'finance');

            return $record;
        });
    }

    /** Concessions apply to the gross fee. */
    private function discountBase(StudentFee $fee): float
    {
        $fee->loadMissing('items');

        return (float) $fee->items->sum('amount');
    }
}
