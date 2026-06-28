<?php

declare(strict_types=1);

namespace App\Modules\Examination\Services;

use App\Modules\Examination\Models\ExamMark;
use App\Modules\Examination\Models\ExamResult;
use App\Modules\Examination\Models\ExamSession;
use Illuminate\Database\Eloquent\Builder;

class ExamDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function overview(?int $schoolId, ?int $sessionId = null): array
    {
        $sessions = fn (): Builder => ExamSession::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId));

        $widgets = [
            'active_exams' => (clone $sessions())->where('status', 'ongoing')->count(),
            'scheduled_exams' => (clone $sessions())->where('status', 'scheduled')->count(),
            'completed_exams' => (clone $sessions())->where('status', 'completed')->count(),
            'published_results' => (clone $sessions())->where('status', 'published')->count(),
            'pending_marks_entry' => $this->pendingMarksSessions($schoolId),
        ];

        $results = fn (): Builder => ExamResult::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->when($sessionId, fn ($q) => $q->where('exam_session_id', $sessionId))
            ->where('result_status', '!=', 'pending');

        $total = (clone $results())->count();
        $passed = (clone $results())->where('result_status', 'pass')->count();

        $gradeDistribution = (clone $results())
            ->whereNotNull('grade_id')
            ->with('grade:id,code')
            ->get(['grade_id'])
            ->groupBy(fn ($r) => $r->grade?->code ?? '—')
            ->map(fn ($g, $code) => ['label' => $code, 'count' => $g->count()])
            ->values()->all();

        $classPerformance = (clone $results())
            ->with('schoolClass:id,name')
            ->get(['class_id', 'percentage'])
            ->groupBy('class_id')
            ->map(fn ($g) => [
                'label' => $g->first()->schoolClass?->name ?? 'Class',
                'count' => round($g->avg('percentage'), 1),
            ])
            ->values()->all();

        return [
            'widgets' => $widgets,
            'charts' => [
                'pass_percentage' => [
                    ['label' => 'Pass', 'count' => $passed],
                    ['label' => 'Fail', 'count' => $total - $passed],
                ],
                'grade_distribution' => $gradeDistribution,
                'subject_performance' => $this->subjectPerformance($schoolId, $sessionId),
                'class_performance' => $classPerformance,
            ],
        ];
    }

    private function pendingMarksSessions(?int $schoolId): int
    {
        return ExamSession::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->whereIn('status', ['scheduled', 'ongoing'])
            ->count();
    }

    /**
     * @return array<int, array{label:string, count:float}>
     */
    private function subjectPerformance(?int $schoolId, ?int $sessionId): array
    {
        return ExamMark::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->whereNotNull('marks_obtained')
            ->when($sessionId, function ($q) use ($sessionId): void {
                $q->whereIn('exam_subject_id', function ($sub) use ($sessionId): void {
                    $sub->select('id')->from('exam_subjects')->where('exam_session_id', $sessionId);
                });
            })
            ->with('examSubject.subject:id,name')
            ->get(['exam_subject_id', 'marks_obtained', 'max_marks'])
            ->groupBy('exam_subject_id')
            ->map(function ($g) {
                $label = $g->first()->examSubject?->subject?->name ?? 'Subject';
                $maxSum = (float) $g->sum('max_marks');
                $obtainedSum = (float) $g->sum('marks_obtained');

                return ['label' => $label, 'count' => $maxSum > 0 ? round($obtainedSum / $maxSum * 100, 1) : 0.0];
            })
            ->values()->all();
    }
}
