<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Services;

use App\Modules\Admissions\Enums\ApplicationStatus;
use App\Modules\Admissions\Enums\EnquiryStatus;
use App\Modules\Admissions\Models\AdmissionApplication;
use App\Modules\Admissions\Models\AdmissionEnquiry;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Read model for the Admission Dashboard: headline widgets + chart series.
 */
class AdmissionDashboardService
{
    /** @return array<string, mixed> */
    public function overview(?int $schoolId = null): array
    {
        $enquiries = fn () => AdmissionEnquiry::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId));
        $applications = fn () => AdmissionApplication::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId));

        $totalEnquiries = (clone $enquiries())->count();
        $converted = (clone $enquiries())->where('status', EnquiryStatus::Converted->value)->count();

        return [
            'widgets' => [
                'today_enquiries' => (clone $enquiries())->whereDate('created_at', Carbon::today())->count(),
                'pending_applications' => (clone $applications())->whereIn('status', [
                    ApplicationStatus::Submitted->value,
                    ApplicationStatus::UnderReview->value,
                    ApplicationStatus::Verified->value,
                ])->count(),
                'approved' => (clone $applications())->where('status', ApplicationStatus::Approved->value)->count(),
                'rejected' => (clone $applications())->where('status', ApplicationStatus::Rejected->value)->count(),
                'month_admissions' => (clone $applications())
                    ->where('status', ApplicationStatus::Enrolled->value)
                    ->whereBetween('approved_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                    ->count(),
                'conversion_rate' => $totalEnquiries > 0 ? round(($converted / $totalEnquiries) * 100, 1) : 0.0,
            ],
            'charts' => [
                'monthly_admissions' => $this->monthlyAdmissions($schoolId),
                'enquiry_sources' => $this->enquirySources($schoolId),
                'status_distribution' => $this->statusDistribution($schoolId),
            ],
        ];
    }

    /** @return array<int, array{month:string, count:int}> */
    private function monthlyAdmissions(?int $schoolId): array
    {
        $rows = AdmissionApplication::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('status', ApplicationStatus::Enrolled->value)
            ->where('approved_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->get(['approved_at']);

        return $rows->groupBy(fn ($r) => Carbon::parse($r->approved_at)->format('Y-m'))
            ->map(fn ($g, $month) => ['month' => $month, 'count' => $g->count()])
            ->sortKeys()
            ->values()
            ->all();
    }

    /** @return array<int, array{label:string, count:int}> */
    private function enquirySources(?int $schoolId): array
    {
        return AdmissionEnquiry::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->with('source:id,label')
            ->select('source_id', DB::raw('count(*) as aggregate'))
            ->groupBy('source_id')
            ->get()
            ->map(fn ($r) => ['label' => $r->source?->label ?? 'Unspecified', 'count' => (int) $r->aggregate])
            ->all();
    }

    /** @return array<int, array{label:string, count:int}> */
    private function statusDistribution(?int $schoolId): array
    {
        return AdmissionApplication::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->select('status', DB::raw('count(*) as aggregate'))
            ->groupBy('status')
            ->get()
            ->map(fn ($r) => [
                'label' => ($r->status instanceof ApplicationStatus ? $r->status->label() : (string) $r->status),
                'count' => (int) $r->aggregate,
            ])
            ->all();
    }
}
