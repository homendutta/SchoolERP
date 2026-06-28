<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Support;

use App\Modules\Attendance\DTO\AttendanceMarkData;
use App\Modules\Attendance\Enums\AttendanceSource;
use App\Modules\Attendance\Enums\AttendanceStatus;
use App\Modules\Attendance\Services\AttendanceEngine;
use App\Platform\Foundation\Identity\Models\Identity;
use App\Platform\Shared\Contracts\Importer;

/**
 * Attendance importer for the generic Import framework (Upload → Validate →
 * Preview → Import → Summary). Rows are matched to people by Identity Number and
 * written through the SAME Attendance Engine as manual/biometric.
 */
class AttendanceImporter implements Importer
{
    public function __construct(private readonly AttendanceEngine $engine) {}

    public function key(): string
    {
        return 'attendance';
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
            if (trim((string) ($row['identity_number'] ?? '')) === '') {
                $rowErrors[] = 'Missing identity_number';
            }
            if (empty($row['date']) || strtotime((string) $row['date']) === false) {
                $rowErrors[] = 'Invalid date';
            }
            if (AttendanceStatus::tryFrom((string) ($row['status'] ?? '')) === null) {
                $rowErrors[] = 'Invalid status';
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
        $marked = 0;
        $skipped = 0;
        $unmatched = 0;

        foreach ($rows as $index => $row) {
            if (isset($errors[$index])) {
                $skipped++;

                continue;
            }

            $identity = Identity::query()
                ->where('school_id', $row['school_id'] ?? null)
                ->where('identity_number', (string) $row['identity_number'])
                ->first();

            if ($identity === null) {
                $unmatched++;

                continue;
            }

            $before = $this->engine->existing($identity->id, (string) $row['date'], $row['session_id'] ?? null);
            $this->engine->mark($identity, new AttendanceMarkData(
                date: (string) $row['date'],
                status: AttendanceStatus::from((string) $row['status']),
                source: AttendanceSource::Import,
                sessionId: $row['session_id'] ?? null,
                remarks: $row['remarks'] ?? null,
                mode: 'skip',
            ));
            $before === null ? $marked++ : $skipped++;
        }

        return ['marked' => $marked, 'skipped' => $skipped, 'unmatched' => $unmatched];
    }
}
