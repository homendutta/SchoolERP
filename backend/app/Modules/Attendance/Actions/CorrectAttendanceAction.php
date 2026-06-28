<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Actions;

use App\Modules\Attendance\DTO\AttendanceMarkData;
use App\Modules\Attendance\Enums\AttendanceSource;
use App\Modules\Attendance\Enums\AttendanceStatus;
use App\Modules\Attendance\Models\AttendanceRecord;
use App\Modules\Attendance\Services\AttendanceEngine;
use App\Platform\Foundation\Identity\Models\Identity;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;
use App\Platform\Shared\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\Auth;

/**
 * Authorized correction of an existing attendance record. Goes through the
 * engine's correction path (audit + timeline), never a raw update.
 */
class CorrectAttendanceAction implements Action
{
    use AsAction;

    public function __construct(private readonly AttendanceEngine $engine) {}

    public function handle(AttendanceRecord $record, AttendanceStatus $status, ?string $remarks = null): AttendanceRecord
    {
        $identity = Identity::query()->find($record->identity_id);
        if ($identity === null) {
            throw BusinessRuleException::make('Attendance record has no identity.', 'ATTENDANCE_NO_IDENTITY');
        }

        return $this->engine->mark($identity, new AttendanceMarkData(
            date: $record->attendance_date->toDateString(),
            status: $status,
            source: AttendanceSource::Manual,
            sessionId: $record->session_id,
            remarks: $remarks,
            recordedBy: Auth::id(),
            mode: 'correct',
        ));
    }
}
