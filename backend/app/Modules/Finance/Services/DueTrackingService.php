<?php

declare(strict_types=1);

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\Adjustment;
use App\Modules\Finance\Models\StudentFee;
use App\Modules\Finance\Models\StudentFeeItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

/**
 * Live due tracking — current due, overdue, fine and outstanding are all
 * CALCULATED (never stored as snapshots). Active adjustments (credit/waiver
 * reduce; debit increases) are factored into the outstanding balance.
 */
class DueTrackingService
{
    public function __construct(private readonly FineService $fines) {}

    /**
     * @return array{
     *     net_amount:float, paid_amount:float, current_due:float, overdue:float,
     *     fine:float, adjustments:float, outstanding:float
     * }
     */
    public function forStudent(int $studentId, ?string $asOf = null): array
    {
        $today = $asOf !== null ? Carbon::parse($asOf) : Carbon::now();

        $fees = StudentFee::query()->where('student_id', $studentId)->get();
        $net = (float) $fees->sum('net_amount');
        $paid = (float) $fees->sum('paid_amount');

        /** @var Collection<int, StudentFeeItem> $items */
        $items = StudentFeeItem::query()
            ->whereIn('student_fee_id', $fees->pluck('id'))
            ->whereColumn('paid_amount', '<', 'amount')
            ->get();

        $currentDue = 0.0;
        $overdue = 0.0;
        $fine = 0.0;
        foreach ($items as $item) {
            $outstanding = round($item->amount - $item->paid_amount, 2);
            if ($item->due_date === null || Carbon::parse($item->due_date)->lessThanOrEqualTo($today)) {
                $currentDue += $outstanding;
            }
            if ($item->due_date !== null && Carbon::parse($item->due_date)->lessThan($today)) {
                $overdue += $outstanding;
                $fine += $this->fines->forItem($item, $today->toDateString());
            }
        }

        $adjust = $this->adjustmentBalance($studentId);
        $outstanding = round(max(0.0, $net - $paid - $adjust), 2);

        return [
            'net_amount' => round($net, 2),
            'paid_amount' => round($paid, 2),
            'current_due' => round($currentDue, 2),
            'overdue' => round($overdue, 2),
            'fine' => round($fine, 2),
            'adjustments' => round($adjust, 2),
            'outstanding' => round($outstanding + $fine, 2),
        ];
    }

    /** Net effect of active adjustments (positive reduces what the student owes). */
    private function adjustmentBalance(int $studentId): float
    {
        $balance = 0.0;
        $adjustments = Adjustment::query()->where('student_id', $studentId)->where('status', 'active')->get();
        foreach ($adjustments as $adj) {
            $balance += $adj->type->reducesDue() ? $adj->amount : -$adj->amount;
        }

        return round($balance, 2);
    }
}
