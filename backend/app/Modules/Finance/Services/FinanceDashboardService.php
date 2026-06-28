<?php

declare(strict_types=1);

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\Payment;
use App\Modules\Finance\Models\PaymentAllocation;
use App\Modules\Finance\Models\Refund;
use App\Modules\Finance\Models\StudentDiscount;
use App\Modules\Finance\Models\StudentFee;
use App\Modules\Finance\Models\StudentFeeItem;
use App\Modules\Finance\Models\StudentScholarship;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class FinanceDashboardService
{
    public function __construct(private readonly DefaulterService $defaulters) {}

    /**
     * @return array<string, mixed>
     */
    public function overview(?int $schoolId): array
    {
        $payments = fn (): Builder => Payment::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('status', '!=', 'cancelled');

        $today = Carbon::now()->toDateString();
        $monthStart = Carbon::now()->startOfMonth()->toDateString();

        $fees = StudentFee::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->get(['net_amount', 'paid_amount']);
        $outstanding = round((float) $fees->sum('net_amount') - (float) $fees->sum('paid_amount'), 2);

        $widgets = [
            'total_collection' => round((float) (clone $payments())->sum('amount'), 2),
            'outstanding_amount' => max(0.0, $outstanding),
            'todays_collection' => round((float) (clone $payments())->whereDate('paid_on', $today)->sum('amount'), 2),
            'monthly_collection' => round((float) (clone $payments())->whereDate('paid_on', '>=', $monthStart)->sum('amount'), 2),
            'refunds' => round((float) Refund::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->sum('amount'), 2),
            'discounts' => round((float) StudentDiscount::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->where('status', 'active')->sum('amount'), 2),
            'scholarships' => round((float) StudentScholarship::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->where('status', 'active')->sum('amount'), 2),
            'defaulters' => $schoolId !== null ? $this->defaulters->list($schoolId)['count'] : 0,
        ];

        return [
            'widgets' => $widgets,
            'charts' => [
                'daily_collection' => $this->collectionSeries(clone $payments(), 'Y-m-d', 14),
                'monthly_collection' => $this->collectionSeries(clone $payments(), 'Y-m', 6),
                'category_collection' => $this->categoryCollection($schoolId),
                'outstanding_trend' => $this->outstandingTrend($schoolId),
            ],
        ];
    }

    /**
     * @return array<int, array{label:string, count:float}>
     */
    private function collectionSeries(Builder $query, string $format, int $limit): array
    {
        return $query->get(['paid_on', 'amount'])
            ->groupBy(fn ($p) => $p->paid_on !== null ? Carbon::parse($p->paid_on)->format($format) : '—')
            ->map(fn ($g, $period) => ['label' => $period, 'count' => round((float) $g->sum('amount'), 2)])
            ->sortKeys()
            ->values()
            ->take(-$limit)
            ->all();
    }

    /**
     * @return array<int, array{label:string, count:float}>
     */
    private function categoryCollection(?int $schoolId): array
    {
        return PaymentAllocation::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->whereNotNull('student_fee_item_id')
            ->with('item.category:id,name')
            ->get(['student_fee_item_id', 'amount'])
            ->groupBy(fn ($a) => $a->item?->category?->name ?? 'Uncategorised')
            ->map(fn ($g, $name) => ['label' => $name, 'count' => round((float) $g->sum('amount'), 2)])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{label:string, count:float}>
     */
    private function outstandingTrend(?int $schoolId): array
    {
        return StudentFeeItem::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->whereColumn('paid_amount', '<', 'amount')
            ->whereNotNull('due_date')
            ->get(['due_date', 'amount', 'paid_amount'])
            ->groupBy(fn ($i) => Carbon::parse($i->due_date)->format('Y-m'))
            ->map(fn ($g, $period) => ['label' => $period, 'count' => round((float) $g->sum(fn ($i) => $i->amount - $i->paid_amount), 2)])
            ->sortKeys()
            ->values()
            ->all();
    }
}
