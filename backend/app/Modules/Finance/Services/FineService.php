<?php

declare(strict_types=1);

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\FineRule;
use App\Modules\Finance\Models\StudentFeeItem;
use Illuminate\Support\Carbon;

/**
 * Automatic fine calculation. Fines are computed on the fly from configurable
 * fine rules (grace period + maximum) — they NEVER modify historical payments.
 */
class FineService
{
    /** Fine for a single overdue line item as of a date. */
    public function forItem(StudentFeeItem $item, ?string $asOf = null): float
    {
        $outstanding = $item->amount - $item->paid_amount;
        if ($outstanding <= 0 || $item->due_date === null) {
            return 0.0;
        }

        $rule = $this->ruleFor((int) $item->school_id, $item->fee_category_id);
        if ($rule === null) {
            return 0.0;
        }

        $today = $asOf !== null ? Carbon::parse($asOf) : Carbon::now();
        $dueWithGrace = Carbon::parse($item->due_date)->addDays($rule->grace_period_days);
        if ($today->lessThanOrEqualTo($dueWithGrace)) {
            return 0.0;
        }

        $overdueDays = (int) $dueWithGrace->diffInDays($today);
        $fine = $rule->mode->units($overdueDays) * $rule->amount;

        if ($rule->max_fine !== null) {
            $fine = min($fine, $rule->max_fine);
        }

        return round($fine, 2);
    }

    /** Most specific active fine rule: category-specific first, then general. */
    private function ruleFor(int $schoolId, ?int $categoryId): ?FineRule
    {
        return FineRule::query()
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->where(fn ($q) => $q->where('fee_category_id', $categoryId)->orWhereNull('fee_category_id'))
            ->orderByRaw('fee_category_id IS NULL') // category-specific (NULL last)
            ->first();
    }
}
