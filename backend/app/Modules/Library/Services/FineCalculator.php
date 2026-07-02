<?php

declare(strict_types=1);

namespace App\Modules\Library\Services;

use App\Modules\Library\Models\Borrowing;
use App\Modules\Library\Models\FineRule;
use Illuminate\Support\Carbon;

/**
 * Library fine calculation. Library CALCULATES the fine; Finance collects the
 * payment (no payment logic here). Uses configurable fine rules with grace
 * period and maximum fine.
 */
class FineCalculator
{
    /**
     * @return array{late_days:int, fine:float}
     */
    public function forReturn(Borrowing $borrowing, string $returnDate): array
    {
        $due = Carbon::parse($borrowing->due_date);
        $returned = Carbon::parse($returnDate);

        if ($returned->lessThanOrEqualTo($due)) {
            return ['late_days' => 0, 'fine' => 0.0];
        }

        $lateDays = (int) $due->diffInDays($returned);
        $rule = $this->ruleFor((int) $borrowing->school_id, $borrowing->owner_type);

        if ($rule === null) {
            return ['late_days' => $lateDays, 'fine' => 0.0];
        }

        $chargeableDays = max(0, $lateDays - $rule->grace_period_days);
        $fine = $rule->mode->compute($chargeableDays, $rule->amount);

        if ($rule->max_fine !== null) {
            $fine = min($fine, $rule->max_fine);
        }

        return ['late_days' => $lateDays, 'fine' => round($fine, 2)];
    }

    private function ruleFor(int $schoolId, string $ownerType): ?FineRule
    {
        return FineRule::query()
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->where(fn ($q) => $q->where('borrower_type', $ownerType)->orWhereNull('borrower_type'))
            ->orderByRaw('borrower_type IS NULL')
            ->first();
    }
}
