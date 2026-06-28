<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Actions;

use App\Modules\Attendance\DTO\AttendanceMarkData;
use App\Modules\Attendance\Enums\AttendanceSource;
use App\Modules\Attendance\Enums\AttendanceStatus;
use App\Modules\Attendance\Services\AttendanceEngine;
use App\Platform\Foundation\Identity\Models\Identity;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;
use App\Platform\Shared\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\Auth;

/**
 * Manual bulk attendance marking — for students or staff. Each entry is written
 * through the Attendance Engine; existing records are reported as skipped (use
 * the correction workflow to change them).
 */
class MarkAttendanceAction implements Action
{
    use AsAction;

    public function __construct(private readonly AttendanceEngine $engine) {}

    /**
     * @param  array{school_id?:int|null, date:string, session_id?:int|null, shift?:string|null, entries:array<int, array<string, mixed>>}  $payload
     * @return array{marked:int, skipped:int, unmatched:int}
     */
    public function handle(array $payload): array
    {
        $marked = 0;
        $skipped = 0;
        $unmatched = 0;

        foreach ($payload['entries'] as $entry) {
            $identity = $this->resolveIdentity($entry, $payload['school_id'] ?? null);
            if ($identity === null) {
                $unmatched++;

                continue;
            }

            try {
                $this->engine->mark($identity, new AttendanceMarkData(
                    date: $payload['date'],
                    status: AttendanceStatus::from((string) $entry['status']),
                    source: AttendanceSource::Manual,
                    sessionId: $payload['session_id'] ?? null,
                    isLate: (bool) ($entry['is_late'] ?? false),
                    remarks: $entry['remarks'] ?? null,
                    shift: $payload['shift'] ?? null,
                    recordedBy: Auth::id(),
                    mode: 'create',
                ));
                $marked++;
            } catch (BusinessRuleException) {
                $skipped++;
            }
        }

        return ['marked' => $marked, 'skipped' => $skipped, 'unmatched' => $unmatched];
    }

    /**
     * @param  array<string, mixed>  $entry
     */
    private function resolveIdentity(array $entry, mixed $schoolId): ?Identity
    {
        if (! empty($entry['identity_id'])) {
            return Identity::query()->find($entry['identity_id']);
        }

        if (! empty($entry['identity_number'])) {
            return Identity::query()
                ->where('school_id', $schoolId)
                ->where('identity_number', (string) $entry['identity_number'])
                ->first();
        }

        return null;
    }
}
