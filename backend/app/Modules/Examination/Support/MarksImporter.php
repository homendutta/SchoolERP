<?php

declare(strict_types=1);

namespace App\Modules\Examination\Support;

use App\Modules\Examination\Models\ExamSubject;
use App\Modules\Examination\Services\MarksService;
use App\Modules\Students\Models\Student;
use App\Platform\Shared\Contracts\Importer;

/**
 * Marks importer for the generic Import framework (Upload → Validate → Preview →
 * Import → Summary). Rows are written through the same MarksService as manual
 * entry, so optional-subject and max-marks rules apply identically.
 */
class MarksImporter implements Importer
{
    public function __construct(private readonly MarksService $marks) {}

    public function key(): string
    {
        return 'exam_marks';
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<int, string>>
     */
    public function validate(array $rows): array
    {
        $errors = [];
        foreach ($rows as $index => $row) {
            $rowErrors = [];
            if (empty($row['exam_subject_id'])) {
                $rowErrors[] = 'Missing exam_subject_id';
            }
            if (empty($row['student_id']) && empty($row['admission_number'])) {
                $rowErrors[] = 'Missing student_id or admission_number';
            }
            if (! isset($row['marks_obtained']) && empty($row['is_absent'])) {
                $rowErrors[] = 'Missing marks_obtained';
            }
            if (isset($row['marks_obtained']) && $row['marks_obtained'] !== '' && ! is_numeric((string) $row['marks_obtained'])) {
                $rowErrors[] = 'marks_obtained must be numeric';
            }
            if ($rowErrors !== []) {
                $errors[$index] = $rowErrors;
            }
        }

        return $errors;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, int>
     */
    public function execute(array $rows): array
    {
        $errors = $this->validate($rows);
        $saved = 0;
        $skipped = 0;
        $unmatched = 0;

        foreach ($rows as $index => $row) {
            if (isset($errors[$index])) {
                $skipped++;

                continue;
            }

            $examSubject = ExamSubject::query()->find($row['exam_subject_id']);
            $studentId = $this->resolveStudentId($row, $examSubject?->school_id);

            if ($examSubject === null || $studentId === null) {
                $unmatched++;

                continue;
            }

            try {
                $this->marks->enter($examSubject, $studentId, [
                    'component_id' => $row['component_id'] ?? null,
                    'marks_obtained' => isset($row['marks_obtained']) && $row['marks_obtained'] !== '' ? (float) $row['marks_obtained'] : null,
                    'is_absent' => (bool) ($row['is_absent'] ?? false),
                    'remarks' => $row['remarks'] ?? null,
                ]);
                $saved++;
            } catch (\Throwable) {
                $skipped++;
            }
        }

        return ['saved' => $saved, 'skipped' => $skipped, 'unmatched' => $unmatched];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function resolveStudentId(array $row, ?int $schoolId): ?int
    {
        if (! empty($row['student_id'])) {
            return (int) $row['student_id'];
        }

        $student = Student::query()
            ->where('school_id', $schoolId)
            ->where('admission_number', (string) $row['admission_number'])
            ->first();

        return $student?->id;
    }
}
