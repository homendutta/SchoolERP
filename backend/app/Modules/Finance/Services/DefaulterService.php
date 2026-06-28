<?php

declare(strict_types=1);

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\StudentFee;
use App\Modules\Finance\Models\StudentFeeItem;
use Illuminate\Support\Carbon;

/**
 * Defaulter lists are generated DYNAMICALLY (never stored as snapshots). Filter
 * by class, section, date and fee category.
 */
class DefaulterService
{
    /**
     * @param  array{class_id?:int|null, section_id?:int|null, as_of?:string|null, fee_category_id?:int|null}  $filters
     * @return array{as_of:string, count:int, total_outstanding:float, students:array<int, array<string, mixed>>}
     */
    public function list(int $schoolId, array $filters = []): array
    {
        $asOf = isset($filters['as_of']) ? Carbon::parse((string) $filters['as_of']) : Carbon::now();

        $fees = StudentFee::query()
            ->where('school_id', $schoolId)
            ->when($filters['class_id'] ?? null, fn ($q, $c) => $q->where('class_id', $c))
            ->when($filters['section_id'] ?? null, fn ($q, $s) => $q->where('section_id', $s))
            ->with('student:id,name,admission_number')
            ->get();

        $students = [];
        $totalOutstanding = 0.0;

        foreach ($fees->groupBy('student_id') as $studentId => $studentFees) {
            $items = StudentFeeItem::query()
                ->whereIn('student_fee_id', $studentFees->pluck('id'))
                ->whereColumn('paid_amount', '<', 'amount')
                ->when($filters['fee_category_id'] ?? null, fn ($q, $cat) => $q->where('fee_category_id', $cat))
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<', $asOf->toDateString())
                ->get();

            if ($items->isEmpty()) {
                continue;
            }

            $outstanding = round((float) $items->sum(fn (StudentFeeItem $i) => $i->amount - $i->paid_amount), 2);
            $totalOutstanding += $outstanding;
            $student = $studentFees->first()?->student;

            $students[] = [
                'student_id' => (int) $studentId,
                'student' => (string) ($student?->name ?? ''),
                'admission_number' => (string) ($student?->admission_number ?? ''),
                'overdue_items' => $items->count(),
                'outstanding' => $outstanding,
            ];
        }

        usort($students, static fn (array $a, array $b): int => $b['outstanding'] <=> $a['outstanding']);

        return [
            'as_of' => $asOf->toDateString(),
            'count' => count($students),
            'total_outstanding' => round($totalOutstanding, 2),
            'students' => $students,
        ];
    }
}
