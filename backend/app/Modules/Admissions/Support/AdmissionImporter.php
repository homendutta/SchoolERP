<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Support;

use App\Modules\Admissions\Services\ApplicationService;
use App\Platform\Shared\Contracts\Importer;

/**
 * Admission importer for the generic Import framework (Upload → Validate →
 * Preview → Import → Summary). Creates Admission Applications only — never
 * Student records (those come from approved admissions / migration import).
 */
class AdmissionImporter implements Importer
{
    /** @var array<int, string> */
    private const REQUIRED = ['student_name', 'guardian_name', 'academic_year_id', 'class_id'];

    public function __construct(private readonly ApplicationService $applications) {}

    public function key(): string
    {
        return 'admissions';
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
            foreach (self::REQUIRED as $field) {
                if (empty($row[$field])) {
                    $rowErrors[] = "Missing {$field}";
                }
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

        foreach ($rows as $row) {
            foreach (self::REQUIRED as $field) {
                if (empty($row[$field])) {
                    $skipped++;

                    continue 2;
                }
            }

            $this->applications->create([
                'school_id' => $row['school_id'] ?? null,
                'student_name' => $row['student_name'],
                'guardian_name' => $row['guardian_name'],
                'guardian_phone' => $row['guardian_phone'] ?? null,
                'guardian_email' => $row['guardian_email'] ?? null,
                'academic_year_id' => $row['academic_year_id'],
                'class_id' => $row['class_id'],
                'section_id' => $row['section_id'] ?? null,
            ]);
            $created++;
        }

        return ['created' => $created, 'skipped' => $skipped];
    }
}
