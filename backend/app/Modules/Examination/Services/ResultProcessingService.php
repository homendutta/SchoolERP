<?php

declare(strict_types=1);

namespace App\Modules\Examination\Services;

use App\Modules\Examination\Models\ExamMark;
use App\Modules\Examination\Models\ExamResult;
use App\Modules\Examination\Models\ExamSession;
use App\Modules\Examination\Models\ExamStudentSubject;
use App\Modules\Students\Models\StudentAcademicRecord;
use App\Platform\Shared\Services\BaseService;
use Illuminate\Support\Collection;

/**
 * Result processing. Subject totals, percentage, grade, GPA and pass/fail are
 * computed using ONLY the student's assigned subjects — optional subjects the
 * student did not take are never counted and can never cause a fail.
 */
class ResultProcessingService extends BaseService
{
    public function __construct(
        private readonly StudentSubjectService $assignments,
        private readonly GradeResolver $grades,
        private readonly RankingService $ranking,
    ) {}

    /**
     * Per-subject breakdown for a student (assigned subjects only). Reused by the
     * report card and tabulation sheet.
     *
     * @return array<int, array{exam_subject_id:int, subject_id:int, subject:string|null, code:string|null, is_elective:bool, max_marks:float, obtained:float, passing_marks:float, is_absent:bool, passed:bool, grade:string|null}>
     */
    public function subjectResults(int $sessionId, int $studentId): array
    {
        $schoolId = (int) (ExamSession::query()->where('id', $sessionId)->value('school_id') ?? 0);
        $out = [];

        foreach ($this->assignments->assignedSubjects($sessionId, $studentId) as $examSubject) {
            /** @var Collection<int, ExamMark> $marks */
            $marks = ExamMark::query()
                ->where('exam_subject_id', $examSubject->id)
                ->where('student_id', $studentId)
                ->get();

            $isAbsent = $marks->isNotEmpty() && $marks->every(fn (ExamMark $m) => $m->is_absent);
            $obtained = (float) $marks->sum(fn (ExamMark $m) => (float) ($m->marks_obtained ?? 0));
            $max = $marks->isNotEmpty()
                ? (float) $marks->sum(fn (ExamMark $m) => (float) $m->max_marks)
                : $examSubject->max_marks;
            // For single-component subjects the row max equals the subject max.
            if ($marks->count() <= 1) {
                $max = $examSubject->max_marks;
            }

            $passed = ! $isAbsent && $obtained >= $examSubject->passing_marks;
            $pct = $max > 0 ? round($obtained / $max * 100, 2) : 0.0;

            $out[] = [
                'exam_subject_id' => $examSubject->id,
                'subject_id' => $examSubject->subject_id,
                'subject' => $examSubject->subject?->name,
                'code' => $examSubject->subject?->code,
                'is_elective' => $examSubject->is_elective,
                'max_marks' => $max,
                'obtained' => $obtained,
                'passing_marks' => $examSubject->passing_marks,
                'is_absent' => $isAbsent,
                'passed' => $passed,
                'grade' => $this->grades->resolve($schoolId, $pct)?->code,
            ];
        }

        return $out;
    }

    /**
     * Process (or re-process) every student's aggregate result for a session,
     * then apply ranking.
     *
     * @return array{processed:int}
     */
    public function process(ExamSession $session): array
    {
        return $this->transaction(function () use ($session): array {
            $studentIds = ExamStudentSubject::query()
                ->where('exam_session_id', $session->id)
                ->distinct()
                ->pluck('student_id');

            $processed = 0;
            foreach ($studentIds as $studentId) {
                $this->processStudent($session, (int) $studentId);
                $processed++;
            }

            $this->ranking->apply($session->id, $session->ranking_method);

            return ['processed' => $processed];
        });
    }

    private function processStudent(ExamSession $session, int $studentId): void
    {
        $subjects = $this->subjectResults($session->id, $studentId);

        $totalObtained = array_sum(array_column($subjects, 'obtained'));
        $totalMax = array_sum(array_column($subjects, 'max_marks'));
        $failed = count(array_filter($subjects, fn ($s) => ! $s['passed']));
        $count = count($subjects);
        $percentage = $totalMax > 0 ? round($totalObtained / $totalMax * 100, 2) : 0.0;
        $grade = $this->grades->resolve($session->school_id, $percentage);

        $placement = StudentAcademicRecord::query()
            ->where('student_id', $studentId)
            ->where('academic_year_id', $session->academic_year_id)
            ->where('is_current', true)
            ->first();

        ExamResult::query()->updateOrCreate(
            ['exam_session_id' => $session->id, 'student_id' => $studentId],
            [
                'school_id' => $session->school_id,
                'class_id' => $placement?->class_id,
                'section_id' => $placement?->section_id,
                'total_obtained' => $totalObtained,
                'total_max' => $totalMax,
                'percentage' => $percentage,
                'grade_id' => $grade?->id,
                'gpa' => $grade?->grade_point,
                'result_status' => $count === 0 ? 'pending' : ($failed > 0 ? 'fail' : 'pass'),
                'subjects_count' => $count,
                'failed_count' => $failed,
            ],
        );
    }
}
