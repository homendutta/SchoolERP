<?php

declare(strict_types=1);

namespace App\Modules\Examination\Services;

use App\Modules\Examination\Models\ExamResult;
use App\Modules\Examination\Models\ExamSession;
use App\Modules\Examination\Models\ExamSubject;

/**
 * Class-wise tabulation sheet (export ready). Each student row lists marks for
 * their assigned subjects only, with totals, percentage, grade and rank.
 */
class TabulationService
{
    public function __construct(private readonly ResultProcessingService $results) {}

    /**
     * @return array{
     *     subjects:array<int, array{exam_subject_id:int, subject:string|null, code:string|null, max_marks:float}>,
     *     rows:array<int, array<string, mixed>>
     * }
     */
    public function build(int $sessionId, int $classId, ?int $sectionId = null): array
    {
        ExamSession::query()->findOrFail($sessionId);

        // Column set = all subjects mapped for the class/section in the session.
        $subjectCols = ExamSubject::query()
            ->where('exam_session_id', $sessionId)
            ->where('class_id', $classId)
            ->when($sectionId !== null, fn ($q) => $q->where('section_id', $sectionId))
            ->with('subject:id,name,code')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (ExamSubject $s) => [
                'exam_subject_id' => $s->id,
                'subject' => $s->subject?->name,
                'code' => $s->subject?->code,
                'max_marks' => $s->max_marks,
            ])
            ->values()
            ->all();

        $results = ExamResult::query()
            ->with('student:id,name,admission_number')
            ->where('exam_session_id', $sessionId)
            ->where('class_id', $classId)
            ->when($sectionId !== null, fn ($q) => $q->where('section_id', $sectionId))
            ->orderBy('rank')
            ->get();

        $rows = [];
        foreach ($results as $result) {
            $perSubject = [];
            foreach ($this->results->subjectResults($sessionId, (int) $result->student_id) as $sr) {
                $perSubject[$sr['exam_subject_id']] = [
                    'obtained' => $sr['obtained'],
                    'is_absent' => $sr['is_absent'],
                    'passed' => $sr['passed'],
                ];
            }

            $rows[] = [
                'student_id' => $result->student_id,
                'student' => $result->student?->name,
                'admission_number' => $result->student?->admission_number,
                'marks' => $perSubject, // keyed by exam_subject_id; unassigned subjects simply absent
                'total_obtained' => $result->total_obtained,
                'total_max' => $result->total_max,
                'percentage' => $result->percentage,
                'result_status' => $result->result_status->value,
                'rank' => $result->rank,
            ];
        }

        return ['subjects' => $subjectCols, 'rows' => $rows];
    }
}
