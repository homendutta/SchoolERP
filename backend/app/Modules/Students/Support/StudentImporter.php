<?php

declare(strict_types=1);

namespace App\Modules\Students\Support;

use App\Modules\Academic\Models\AcademicYear;
use App\Modules\Academic\Models\SchoolClass;
use App\Modules\Academic\Models\Section;
use App\Modules\Parents\Models\Guardian;
use App\Modules\Students\Enums\StudentStatus;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentAcademicRecord;
use App\Modules\Students\Services\StudentTimelineService;
use App\Platform\Shared\Contracts\Importer;

/**
 * Student importer for the generic Import framework (Upload → Validate → Preview
 * → Import → Summary). This is the migration-mode path — the only way besides an
 * approved admission to bring a Student into existence.
 */
class StudentImporter implements Importer
{
    public function __construct(private readonly StudentTimelineService $timeline) {}

    public function key(): string
    {
        return 'students';
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<int, string>>
     */
    public function validate(array $rows): array
    {
        $errors = [];
        $seen = [];

        foreach ($rows as $index => $row) {
            $rowErrors = [];
            $admissionNumber = trim((string) ($row['admission_number'] ?? ''));

            if ($admissionNumber === '') {
                $rowErrors[] = 'Missing admission_number';
            } elseif (isset($seen[$admissionNumber])) {
                $rowErrors[] = 'Duplicate admission_number in file';
            } elseif (Student::query()->where('admission_number', $admissionNumber)->exists()) {
                $rowErrors[] = 'Duplicate admission_number (already exists)';
            }
            $seen[$admissionNumber] = true;

            if (trim((string) ($row['name'] ?? '')) === '') {
                $rowErrors[] = 'Missing name';
            }
            if (trim((string) ($row['guardian_name'] ?? '')) === '') {
                $rowErrors[] = 'Missing guardian';
            }
            if (empty($row['academic_year_id']) || ! AcademicYear::query()->whereKey($row['academic_year_id'])->exists()) {
                $rowErrors[] = 'Invalid academic_year';
            }
            if (empty($row['class_id']) || ! SchoolClass::query()->whereKey($row['class_id'])->exists()) {
                $rowErrors[] = 'Invalid class';
            }
            if (! empty($row['section_id']) && ! Section::query()->whereKey($row['section_id'])->exists()) {
                $rowErrors[] = 'Invalid section';
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
        $created = 0;
        $skipped = 0;

        // Re-validate so a bad row never aborts the whole import.
        $errors = $this->validate($rows);

        foreach ($rows as $index => $row) {
            if (isset($errors[$index])) {
                $skipped++;

                continue;
            }

            $schoolId = $row['school_id'] ?? null;

            $student = Student::create([
                'school_id' => $schoolId,
                'admission_number' => $row['admission_number'],
                'name' => $row['name'],
                'gender' => $row['gender'] ?? null,
                'status' => StudentStatus::Active->value,
                'enrolled_on' => $row['enrolled_on'] ?? now()->toDateString(),
            ]);

            // Reuse an existing guardian within the same school so siblings never
            // create duplicates: prefer the Parent Number, else the unique import
            // key (guardian phone). Create one only when no match is found.
            $guardian = $this->resolveGuardian($schoolId, $row);
            $student->guardians()->syncWithoutDetaching([$guardian->id => [
                'is_primary' => true,
                'financial_responsible' => true,
            ]]);

            StudentAcademicRecord::create([
                'school_id' => $schoolId,
                'student_id' => $student->id,
                'academic_year_id' => $row['academic_year_id'],
                'class_id' => $row['class_id'],
                'section_id' => $row['section_id'] ?? null,
                'admission_number' => $row['admission_number'],
                'status' => 'active',
                'is_current' => true,
                'started_on' => now()->toDateString(),
            ]);

            $this->timeline->record($student, TimelineEvent::Created, 'Student imported (migration)');
            $created++;
        }

        return ['created' => $created, 'skipped' => $skipped];
    }

    /**
     * Find an existing guardian in the school (by parent number, else phone) or
     * create a new one. Prevents duplicate guardians for siblings.
     *
     * @param  array<string, mixed>  $row
     */
    private function resolveGuardian(mixed $schoolId, array $row): Guardian
    {
        $parentNumber = trim((string) ($row['guardian_parent_number'] ?? ''));
        $phone = trim((string) ($row['guardian_phone'] ?? ''));

        $existing = Guardian::query()
            ->where('school_id', $schoolId)
            ->when($parentNumber !== '', fn ($q) => $q->where('parent_number', $parentNumber))
            ->when($parentNumber === '' && $phone !== '', fn ($q) => $q->where('phone', $phone))
            ->when($parentNumber === '' && $phone === '', fn ($q) => $q->whereRaw('1 = 0'))
            ->first();

        return $existing ?? Guardian::create([
            'school_id' => $schoolId,
            'parent_number' => $parentNumber !== '' ? $parentNumber : null,
            'name' => $row['guardian_name'],
            'phone' => $phone !== '' ? $phone : null,
            'email' => $row['guardian_email'] ?? null,
            'status' => 'active',
        ]);
    }
}
