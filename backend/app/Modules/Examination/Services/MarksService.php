<?php

declare(strict_types=1);

namespace App\Modules\Examination\Services;

use App\Modules\Examination\Models\ExamMark;
use App\Modules\Examination\Models\ExamSubject;
use App\Platform\Shared\Exceptions\BusinessRuleException;
use App\Platform\Shared\Services\BaseService;
use Illuminate\Support\Facades\Auth;

/**
 * Marks entry. Validates max/passing marks and prevents duplicates. Marks are
 * ONLY accepted for subjects the student is assigned (optional/elective safety):
 * a student who did not take a subject can never be given marks for it.
 */
class MarksService extends BaseService
{
    public function __construct(private readonly StudentSubjectService $assignments) {}

    /**
     * Enter (or autosave) one mark. Upserts on exam_subject + student + component
     * (null-component safe), so re-entry overwrites rather than duplicates.
     *
     * @param  array{component_id?:int|null, marks_obtained?:float|null, max_marks?:float|null, is_absent?:bool, remarks?:string|null}  $data
     */
    public function enter(ExamSubject $examSubject, int $studentId, array $data): ExamMark
    {
        if (! $this->assignments->isAssigned($examSubject->id, $studentId)) {
            throw BusinessRuleException::make('This subject is not assigned to the student.', 'SUBJECT_NOT_ASSIGNED');
        }

        $componentId = $data['component_id'] ?? null;
        $maxMarks = $componentId === null ? $examSubject->max_marks : (float) ($data['max_marks'] ?? $examSubject->max_marks);
        $obtained = $data['is_absent'] ?? false ? null : ($data['marks_obtained'] ?? null);

        if ($obtained !== null && ($obtained < 0 || $obtained > $maxMarks)) {
            throw BusinessRuleException::make("Marks must be between 0 and {$maxMarks}.", 'MARKS_OUT_OF_RANGE');
        }

        return $this->transaction(function () use ($examSubject, $studentId, $componentId, $obtained, $maxMarks, $data): ExamMark {
            $existing = ExamMark::query()
                ->where('exam_subject_id', $examSubject->id)
                ->where('student_id', $studentId)
                ->when($componentId === null, fn ($q) => $q->whereNull('component_id'), fn ($q) => $q->where('component_id', $componentId))
                ->first();

            $payload = [
                'school_id' => $examSubject->school_id,
                'exam_subject_id' => $examSubject->id,
                'student_id' => $studentId,
                'component_id' => $componentId,
                'marks_obtained' => $obtained,
                'max_marks' => $maxMarks,
                'is_absent' => (bool) ($data['is_absent'] ?? false),
                'remarks' => $data['remarks'] ?? null,
                'entered_by' => Auth::id(),
            ];

            if ($existing !== null) {
                $existing->update($payload);

                return $existing->refresh();
            }

            return ExamMark::query()->create($payload);
        });
    }

    /**
     * Bulk entry. Skips rows for unassigned students (never errors the batch).
     *
     * @param  array<int, array{student_id:int, component_id?:int|null, marks_obtained?:float|null, is_absent?:bool, remarks?:string|null}>  $entries
     * @return array{saved:int, skipped:int}
     */
    public function enterMany(ExamSubject $examSubject, array $entries): array
    {
        $saved = 0;
        $skipped = 0;
        foreach ($entries as $entry) {
            try {
                $this->enter($examSubject, (int) $entry['student_id'], $entry);
                $saved++;
            } catch (BusinessRuleException) {
                $skipped++;
            }
        }

        return ['saved' => $saved, 'skipped' => $skipped];
    }
}
