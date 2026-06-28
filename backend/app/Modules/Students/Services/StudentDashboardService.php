<?php

declare(strict_types=1);

namespace App\Modules\Students\Services;

use App\Modules\Students\Enums\StudentStatus;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentAcademicRecord;
use App\Modules\Students\Models\StudentTransfer;
use App\Modules\Students\Models\StudentWithdrawal;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StudentDashboardService
{
    /** @return array<string, mixed> */
    public function overview(?int $schoolId = null): array
    {
        $students = fn () => Student::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId));

        return [
            'widgets' => [
                'total_students' => (clone $students())->count(),
                'active' => (clone $students())->where('status', StudentStatus::Active->value)->count(),
                'withdrawn' => (clone $students())->where('status', StudentStatus::Withdrawn->value)->count(),
                'graduated' => (clone $students())->where('status', StudentStatus::Graduated->value)->count(),
                'promoted' => (clone $students())->where('status', StudentStatus::Promoted->value)->count(),
                'transfers' => StudentTransfer::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->count(),
                'new_admissions' => (clone $students())
                    ->whereBetween('enrolled_on', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                    ->count(),
            ],
            'charts' => [
                'monthly_admissions' => $this->monthly((clone $students())->whereNotNull('enrolled_on')->pluck('enrolled_on')),
                'promotions' => $this->monthly(
                    StudentAcademicRecord::query()
                        ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
                        ->whereNotNull('promoted_from_record_id')
                        ->pluck('created_at')
                ),
                'withdrawals' => $this->monthly(
                    StudentWithdrawal::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->pluck('withdraw_date')
                ),
                'gender_distribution' => (clone $students())
                    ->select('gender', DB::raw('count(*) as aggregate'))
                    ->groupBy('gender')->get()
                    ->map(fn ($r) => ['label' => $r->gender ?: 'Unspecified', 'count' => (int) $r->aggregate])->all(),
                'class_distribution' => StudentAcademicRecord::query()
                    ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
                    ->where('is_current', true)
                    ->with('schoolClass:id,name')
                    ->get()
                    ->groupBy(fn ($r) => $r->schoolClass?->name ?? 'Unassigned')
                    ->map(fn ($g, $name) => ['label' => $name, 'count' => $g->count()])
                    ->values()->all(),
            ],
        ];
    }

    /**
     * @param  iterable<int, mixed>  $dates
     * @return array<int, array{month:string, count:int}>
     */
    private function monthly(iterable $dates): array
    {
        $buckets = [];
        foreach ($dates as $date) {
            if ($date === null) {
                continue;
            }
            $month = Carbon::parse($date)->format('Y-m');
            $buckets[$month] = ($buckets[$month] ?? 0) + 1;
        }
        ksort($buckets);

        return array_map(static fn ($month, $count) => ['month' => $month, 'count' => $count], array_keys($buckets), $buckets);
    }
}
