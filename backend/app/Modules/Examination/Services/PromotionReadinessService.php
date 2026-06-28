<?php

declare(strict_types=1);

namespace App\Modules\Examination\Services;

use App\Modules\Examination\Models\ExamResult;
use App\Modules\Examination\Models\ExamSession;

/**
 * Promotion readiness (NOT automatic promotion). Surfaces, per student, whether
 * they are eligible, which assigned subjects they failed, and which marks are
 * missing — reusing the Student Promotion architecture (Sprint 5) downstream.
 * Eligibility is computed against ONLY the student's assigned subjects.
 */
class PromotionReadinessService
{
    public function __construct(private readonly ResultProcessingService $results) {}

    /**
     * @return array{
     *     summary:array{eligible:int, not_eligible:int, pending:int, total:int},
     *     students:array<int, array<string, mixed>>
     * }
     */
    public function forSession(int $sessionId, ?int $classId = null, ?int $sectionId = null): array
    {
        ExamSession::query()->findOrFail($sessionId);

        $results = ExamResult::query()
            ->with('student:id,name,admission_number')
            ->where('exam_session_id', $sessionId)
            ->when($classId !== null, fn ($q) => $q->where('class_id', $classId))
            ->when($sectionId !== null, fn ($q) => $q->where('section_id', $sectionId))
            ->get();

        $students = [];
        $eligible = 0;
        $notEligible = 0;
        $pending = 0;

        foreach ($results as $result) {
            $subjects = $this->results->subjectResults($sessionId, (int) $result->student_id);
            $failedSubjects = array_values(array_filter($subjects, fn ($s) => ! $s['passed'] && ! $s['is_absent']));
            $missing = array_values(array_filter($subjects, fn ($s) => $s['obtained'] === 0.0 && ! $s['is_absent']));

            $status = $result->result_status->value;
            $isEligible = $status === 'pass';
            if ($status === 'pending' || $subjects === []) {
                $pending++;
                $eligibility = 'pending';
            } elseif ($isEligible) {
                $eligible++;
                $eligibility = 'eligible';
            } else {
                $notEligible++;
                $eligibility = 'not_eligible';
            }

            $students[] = [
                'student_id' => $result->student_id,
                'student' => $result->student?->name,
                'admission_number' => $result->student?->admission_number,
                'eligibility' => $eligibility,
                'percentage' => $result->percentage,
                'failed_subjects' => array_column($failedSubjects, 'subject'),
                'missing_marks' => array_column($missing, 'subject'),
            ];
        }

        return [
            'summary' => [
                'eligible' => $eligible,
                'not_eligible' => $notEligible,
                'pending' => $pending,
                'total' => count($students),
            ],
            'students' => $students,
        ];
    }
}
