<?php

declare(strict_types=1);

namespace App\Modules\Examination\Services;

use App\Modules\Examination\Models\ExamSession;
use App\Modules\Examination\Models\ExamStudentSubject;
use App\Modules\Examination\Models\ExamSubject;
use App\Modules\Students\Models\StudentAcademicRecord;
use App\Platform\Shared\Services\BaseService;
use Illuminate\Support\Collection;

/**
 * The optional/elective engine — the heart of the mandatory requirement.
 *
 * A student only ever interacts with subjects recorded in exam_student_subjects.
 * Core subjects are auto-assigned to every current student of the class; elective
 * subjects must be assigned explicitly. Every downstream calculation (marks,
 * results, report cards, promotion) reads ONLY this assignment — so a subject a
 * student did not take has no row and can never show up as failed.
 */
class StudentSubjectService extends BaseService
{
    /**
     * Current students of the exam-subject's class/section (immutable per-year
     * placement, reused from Student Management).
     *
     * @return Collection<int, StudentAcademicRecord>
     */
    public function currentStudents(ExamSubject $examSubject): Collection
    {
        $session = ExamSession::query()->find($examSubject->exam_session_id);

        return StudentAcademicRecord::query()
            ->where('academic_year_id', $session?->academic_year_id)
            ->where('class_id', $examSubject->class_id)
            ->when($examSubject->section_id !== null, fn ($q) => $q->where('section_id', $examSubject->section_id))
            ->where('is_current', true)
            ->get();
    }

    /** Assign one student to an exam subject (idempotent). */
    public function assign(ExamSubject $examSubject, int $studentId): ExamStudentSubject
    {
        return ExamStudentSubject::query()->updateOrCreate(
            ['exam_subject_id' => $examSubject->id, 'student_id' => $studentId],
            [
                'school_id' => $examSubject->school_id,
                'exam_session_id' => $examSubject->exam_session_id,
                'status' => 'active',
            ],
        );
    }

    public function unassign(ExamSubject $examSubject, int $studentId): void
    {
        ExamStudentSubject::query()
            ->where('exam_subject_id', $examSubject->id)
            ->where('student_id', $studentId)
            ->delete();
    }

    /**
     * Auto-assign a CORE subject to every current student of its class/section.
     * Electives are never auto-assigned — they are opt-in only.
     *
     * @return int number of students assigned
     */
    public function autoAssignCore(ExamSubject $examSubject): int
    {
        if ($examSubject->is_elective) {
            return 0;
        }

        return $this->transaction(function () use ($examSubject): int {
            $count = 0;
            foreach ($this->currentStudents($examSubject) as $record) {
                $this->assign($examSubject, (int) $record->student_id);
                $count++;
            }

            return $count;
        });
    }

    /**
     * The exam subjects a student is assigned for a session — the single source
     * of truth used everywhere downstream.
     *
     * @return Collection<int, ExamSubject>
     */
    public function assignedSubjects(int $sessionId, int $studentId): Collection
    {
        $ids = ExamStudentSubject::query()
            ->where('exam_session_id', $sessionId)
            ->where('student_id', $studentId)
            ->pluck('exam_subject_id');

        return ExamSubject::query()
            ->whereIn('id', $ids)
            ->with(['subject:id,name,code', 'subjectType:id,label,value'])
            ->orderBy('sort_order')
            ->get();
    }

    /** Whether a student is assigned a specific exam subject. */
    public function isAssigned(int $examSubjectId, int $studentId): bool
    {
        return ExamStudentSubject::query()
            ->where('exam_subject_id', $examSubjectId)
            ->where('student_id', $studentId)
            ->exists();
    }
}
